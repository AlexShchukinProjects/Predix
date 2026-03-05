<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ImportMessageFieldDefinitions extends Command
{
    protected $signature = 'sr:import-field-defs {path=storage/excel/Данные для сообщений.xlsx} {--truncate}';
    protected $description = 'Импорт классификатора полей сообщений из Excel в sr_message_field_definitions';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("Файл не найден: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            DB::table('sr_message_field_definitions')->truncate();
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Возможные форматы:
        // 1) A:code, B:name, C:field_type, D:meta(json), E:is_active
        // 2) A:name, B:field_type (как у вас сейчас)
        $header = array_shift($rows) ?: [];
        $normalizedHeader = array_map(static function($v){ return mb_strtolower(trim((string)$v)); }, $header);
        $isTwoColumns = false;
        if (in_array('название поля', $normalizedHeader, true) || in_array('name', $normalizedHeader, true)) {
            // проверим вторую колонку на тип
            if (in_array('тип поля', $normalizedHeader, true) || in_array('field_type', $normalizedHeader, true)) {
                $isTwoColumns = true;
            }
        }
        $inserted = 0;
        foreach ($rows as $row) {
            if ($isTwoColumns) {
                $name = trim((string)($row['A'] ?? ''));
                $type = trim((string)($row['B'] ?? ''));
                $code = Str::slug(mb_substr($name, 0, 100), '_');
                $metaRaw = '';
                $active = '1';
            } else {
                $code = trim((string)($row['A'] ?? ''));
                $name = trim((string)($row['B'] ?? ''));
                $type = trim((string)($row['C'] ?? ''));
                $metaRaw = (string)($row['D'] ?? '');
                $active = (string)($row['E'] ?? '1');
            }
            if ($code === '' || $name === '' || $type === '') continue;
            $meta = null;
            if ($metaRaw !== '') {
                // Попробуем распарсить JSON, если не JSON — сохраним строку
                $decoded = json_decode($metaRaw, true);
                $meta = json_last_error() === JSON_ERROR_NONE ? $decoded : ['raw' => $metaRaw];
            }
            DB::table('sr_message_field_definitions')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'field_type' => $type,
                    'meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                    'is_active' => in_array(strtolower($active), ['1','true','yes','да'], true),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $inserted++;
        }

        $this->info("Импортировано записей: {$inserted}");
        return self::SUCCESS;
    }
}


