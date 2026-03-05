<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SrMessageFieldDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LookupController extends Controller
{
    /**
     * Возвращает опции для селектов/lookup по reference ключу.
     * reference может быть:
     *  - ключ реестра (вшитые пресеты)
     *  - имя поля из sr_message_field_definitions.meta.reference
     */
    public function get(Request $request, string $reference): JsonResponse
    {
        try {
            $q = (string) $request->query('q', '');

            // Сначала пробуем статический реестр по ключу
            $options = $this->fromRegistry($reference, $q, $request->query());
            if ($options !== null) {
                return response()->json(['success' => true, 'options' => $options]);
            }

            // Затем пытаемся найти конфиг в sr_message_field_definitions.meta.reference
            $options = $this->fromDefinitions($reference, $q, $request->query());
            if ($options !== null) {
                return response()->json(['success' => true, 'options' => $options]);
            }

            return response()->json([
                'success' => true,
                'options' => [],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lookup error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function fromRegistry(string $reference, string $q, array $params = []): ?array
    {
        $registry = [
            // Пресет: аэропорты, label = IATA + NameRus (с учётом разных регистров столбцов)
            'airports_iata_name' => [
                'table' => 'airports',
                'value' => 'id',
                'label_raw' => "CONCAT(COALESCE(IATA, iata, ''), ' — ', COALESCE(NameRus, City, NameEng, ''))",
                'order_raw' => "COALESCE(NameRus, City, NameEng, COALESCE(IATA, iata, '')) asc",
                'searchable' => ['IATA', 'iata', 'ICAO', 'icao', 'City', 'NameRus', 'NameEng'],
                'limit' => 10000,
                'params_map' => ['id' => 'id'], // при ?id=... вернуть один аэропорт для отображения сохранённого значения
            ],
            // Типы ВС: активные. label = ICAO + name_eng/name_rus
            'aircraft_types_active' => [
                'table' => 'aircrafts_types',
                'value' => 'icao',
                'label_raw' => "CONCAT(COALESCE(icao, ''), ' — ', COALESCE(name_eng, name_rus, ''))",
                'where_raw' => ["active = 1"],
                'order_raw' => "COALESCE(name_eng, name_rus, icao) asc",
                'searchable' => ['icao', 'name_eng', 'name_rus'],
                'limit' => 10000,
            ],
            // Бортовые номера: фильтр по типу (icao) через ?type=ICAO
            'aircraft_by_type' => [
                'table' => 'aircraft',
                'value' => 'RegN',
                'label' => 'RegN',
                'order' => ['RegN' => 'asc'],
                'searchable' => ['RegN', 'Type'],
                'params_map' => [
                    'type' => 'Type',
                ],
                'limit' => 10000,
            ],
            // Этапы эксплуатации
            'operation_stages' => [
                'table' => 'sr_operation_stages',
                'value' => 'id',
                'label' => 'name',
                'order' => ['name' => 'asc'],
                'searchable' => ['name'],
                'limit' => 10000,
            ],
        ];

        if (!isset($registry[$reference])) {
            return null;
        }

        return $this->runQuery($registry[$reference], $q, $params);
    }

    private function fromDefinitions(string $reference, string $q, array $params = []): ?array
    {
        // Ищем любые активные определения, где в meta.reference == $reference
        $defs = SrMessageFieldDefinition::where('is_active', true)->get();
        $matched = $defs->first(function ($def) use ($reference) {
            $meta = is_array($def->meta) ? $def->meta : [];
            return isset($meta['reference']) && $meta['reference'] === $reference;
        });

        if (!$matched) {
            return null;
        }

        $meta = $matched->meta ?? [];
        if (!isset($meta['reference']) && !isset($meta['table'])) {
            return null;
        }

        // Поддержка полного конфига в meta
        $config = [
            'table' => $meta['table'] ?? null,
            'value' => $meta['value'] ?? 'id',
            'label' => $meta['label'] ?? null,
            'label_raw' => $meta['label_raw'] ?? null,
            'where' => $meta['where'] ?? [],
            'order' => $meta['order'] ?? [],
            'searchable' => $meta['searchable'] ?? [],
            'limit' => $meta['limit'] ?? 200,
            'where_raw' => $meta['where_raw'] ?? [],
            'params_map' => $meta['params_map'] ?? [],
        ];

        // Если table не указан, попробуем маппинг по ключу reference
        if (!$config['table'] && isset($meta['reference'])) {
            $preset = $this->fromRegistry($meta['reference'], $q);
            return $preset; // уже готовый массив
        }

        if (!$config['table']) {
            return null;
        }

        return $this->runQuery($config, $q, $params);
    }

    private function runQuery(array $config, string $q, array $params = []): array
    {
        // Белый список таблиц и колонок
        $allowedTables = ['airports', 'aircrafts_types', 'aircraft', 'sr_operation_stages'];
        if (!in_array($config['table'], $allowedTables, true)) {
            return [];
        }

        $valueCol = $config['value'] ?? 'id';

        $query = DB::table($config['table']);

        // label можно задать через столбец или raw выражение
        if (!empty($config['label_raw'])) {
            $query->select([$valueCol . ' as id', DB::raw($config['label_raw'] . ' as name')]);
        } else {
            $labelCol = $config['label'] ?? 'name';
            $query->select([$valueCol . ' as id', $labelCol . ' as name']);
        }

        // where
        foreach ((array) ($config['where'] ?? []) as $col => $val) {
            $query->where($col, $val);
        }

        // where_raw
        foreach ((array) ($config['where_raw'] ?? []) as $raw) {
            if (is_string($raw) && $raw !== '') {
                $query->whereRaw($raw);
            }
        }

        // params_map: сопоставление query param -> column
        foreach ((array) ($config['params_map'] ?? []) as $paramKey => $column) {
            if (isset($params[$paramKey]) && $params[$paramKey] !== '') {
                $query->where($column, $params[$paramKey]);
            }
        }

        // search
        $searchable = (array) ($config['searchable'] ?? []);
        if ($q !== '' && !empty($searchable)) {
            $query->where(function ($sub) use ($searchable, $q) {
                foreach ($searchable as $col) {
                    $sub->orWhere($col, 'like', '%' . $q . '%');
                }
            });
        }

        // order
        if (!empty($config['order_raw'])) {
            $query->orderByRaw($config['order_raw']);
        } else {
            foreach ((array) ($config['order'] ?? []) as $col => $dir) {
                $query->orderBy($col, strtolower($dir) === 'desc' ? 'desc' : 'asc');
            }
        }

        $limit = isset($config['limit']) ? (int) $config['limit'] : 200;
        $rows = $query->limit($limit)->get();

        return $rows->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->all();
    }
}


