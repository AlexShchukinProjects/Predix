<?php

declare(strict_types=1);

namespace App\Http\Controllers\Modules\Reliability;

use App\Http\Controllers\Controller;
use App\Models\InspectionAircraft;
use App\Models\InspectionCaseAnalysis;
use App\Models\InspectionEefRegistry;
use App\Models\InspectionProject;
use App\Models\InspectionSourceCardRef;
use App\Models\InspectionWorkCard;
use App\Models\InspectionWorkCardMaterial;
use App\Models\ReliabilityMasterData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InspectionDataController extends Controller
{
    public function index(): View
    {
        return view('Modules.Reliability.inspection_settings.index');
    }

    public function projects(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000], true)) {
            $perPage = 50;
        }
        $items = InspectionProject::orderBy('id')->paginate($perPage)->withQueryString();
        return view('Modules.Reliability.inspection_settings.projects', compact('items', 'perPage'));
    }

    public function projectsUpload(Request $request)
    {
        set_time_limit(0);
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:51200']);
        $count = $this->importFromFile($request->file('file'), $this->projectsHeaderMap(), function (array $row) {
            InspectionProject::create($this->sanitizeProjectRow($row));
        });
        return redirect()->route('modules.reliability.settings.inspection.projects')->with('success', "Imported records: {$count}");
    }

    public function projectsDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $ids = array_filter(array_map('intval', (array) $ids));
        if ($ids !== []) {
            InspectionProject::whereIn('id', $ids)->delete();
        }
        return redirect()->route('modules.reliability.settings.inspection.projects')->with('success', 'Выбранные записи удалены.');
    }

    public function aircraftsDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $ids = array_filter(array_map('intval', (array) $ids));
        if ($ids !== []) {
            InspectionAircraft::whereIn('id', $ids)->delete();
        }
        return redirect()->route('modules.reliability.settings.inspection.aircrafts')->with('success', 'Выбранные записи удалены.');
    }

    public function workCardsDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $ids = array_filter(array_map('intval', (array) $ids));
        if ($ids !== []) {
            InspectionWorkCard::whereIn('id', $ids)->delete();
        }
        return redirect()->route('modules.reliability.settings.inspection.work-cards')->with('success', 'Выбранные записи удалены.');
    }

    /** Master Data: список с пагинацией (RC / NRC) и фильтрами — одна таблица work_cards_master, вкладки = фильтр по ORDER TYPE. */
    public function masterData(Request $request): View
    {
        $source = $this->resolveMasterDataSource($request);
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000], true)) {
            $perPage = 50;
        }
        $query = ReliabilityMasterData::query();
        $this->applyMasterDataTabFilter($query, $source);
        $this->applyMasterDataFilters($query, $request, $source);
        [$sortColumn, $sortDirection] = $this->resolveMasterDataSort($request);
        $this->applyMasterDataSort($query, $sortColumn, $sortDirection);
        $items = $query->paginate($perPage)->withQueryString();
        $this->enrichMasterDataPaginator($items);
        return view('Modules.Reliability.settings.master_data.index', compact('items', 'perPage', 'source', 'sortColumn', 'sortDirection'));
    }

    /**
     * MSN / AGE / FC / FH: aircrafts (by tail) + projects (FH=aircraft_tsn, FC=aircraft_csn by project+tail).
     * EEF# / DATA SOURCE: eef_registry — only when NRC Number matches WORK ORDER + "-" + ITEM (4-digit padded), e.g. 17767-0001;
     *   same PROJECT first, else global NRC key (no ATA / “first in project” fallback).
     * MATERIAL: IC_0097 materials by PROJECT#/WORK ORDER#/ITEM#; EQUIPMENT: engine type (project or aircraft).
     */
    private function enrichMasterDataPaginator(LengthAwarePaginator $paginator): void
    {
        $this->enrichWorkCardMasterRows($paginator->getCollection());
    }

    private function enrichWorkCardMasterRows(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }
        $tails = $rows->pluck('tail_number')->map(fn ($t) => $this->normalizeTailKey((string) $t))->unique()->filter()->values()->all();
        $aircraftByTail = [];
        if ($tails !== []) {
            $trimTails = array_values(array_unique(array_filter(array_map('trim', $rows->pluck('tail_number')->all()))));
            $candidates = $trimTails !== []
                ? InspectionAircraft::query()
                    ->where(function ($q) use ($trimTails) {
                        foreach ($trimTails as $t) {
                            $q->orWhereRaw('TRIM(tail_number) = ?', [$t]);
                        }
                    })
                    ->get()
                : collect();
            foreach ($candidates as $a) {
                $k = $this->normalizeTailKey((string) $a->tail_number);
                if ($k !== '' && in_array($k, $tails, true) && !isset($aircraftByTail[$k])) {
                    $aircraftByTail[$k] = $a;
                }
            }
        }
        $projectKeys = [];
        foreach ($rows as $r) {
            $pk = $this->projectTailKey((string) $r->project, (string) $r->tail_number);
            if ($pk !== '') {
                $projectKeys[$pk] = true;
            }
        }
        $projectByKey = [];
        if ($projectKeys !== []) {
            $projectNos = $rows->pluck('project')->map(fn ($p) => trim((string) $p))->unique()->filter()->values()->all();
            $projects = $projectNos !== []
                ? InspectionProject::query()->where(function ($q) use ($projectNos) {
                    foreach ($projectNos as $pn) {
                        $q->orWhereRaw('TRIM(COALESCE(project_number, \'\')) = ?', [$pn]);
                    }
                })->get()
                : collect();
            foreach ($projects as $p) {
                $k = $this->projectTailKey((string) $p->project_number, (string) $p->tail_number);
                if ($k !== '' && isset($projectKeys[$k]) && !isset($projectByKey[$k])) {
                    $projectByKey[$k] = $p;
                }
            }
        }
        $eefProjectNos = $rows->pluck('project')->map(fn ($p) => trim((string) $p))->unique()->filter()->values()->all();
        $eefPool = $eefProjectNos !== []
            ? InspectionEefRegistry::query()->where(function ($q) use ($eefProjectNos) {
                foreach ($eefProjectNos as $pn) {
                    $q->orWhereRaw('TRIM(COALESCE(project_no, \'\')) = ?', [trim($pn)]);
                }
            })->get()
            : collect();
        $canonicalNrcKeys = $rows->map(
            fn ($r) => $this->eefCanonicalNrcKeyFromWorkCard((string) $r->work_order, (string) $r->item)
        )->filter()->unique()->values()->all();
        $eefByCanonicalNrc = [];
        if ($canonicalNrcKeys !== []) {
            $nrcRows = InspectionEefRegistry::query()
                ->where(function ($q) use ($canonicalNrcKeys) {
                    foreach ($canonicalNrcKeys as $k) {
                        $q->orWhereRaw(
                            'LOWER(REPLACE(TRIM(COALESCE(nrc_number, \'\')), \' \', \'\')) = ?',
                            [$k]
                        );
                    }
                })
                ->get();
            foreach ($nrcRows as $e) {
                $nk = $this->normalizeEefNrcString((string) $e->nrc_number);
                if ($nk !== '' && !isset($eefByCanonicalNrc[$nk])) {
                    $eefByCanonicalNrc[$nk] = $e;
                }
            }
        }
        $matPairs = [];
        foreach ($rows as $r) {
            $matPairs[] = [
                'project_number' => trim((string) $r->project),
                'work_order_number' => trim((string) $r->work_order),
                'item_number' => trim((string) $r->item),
            ];
        }
        $materialByKey = [];
        if ($matPairs !== []) {
            $matOr = false;
            $mats = InspectionWorkCardMaterial::query()
                ->where(function ($q) use ($matPairs, &$matOr) {
                    foreach ($matPairs as $pair) {
                        if ($pair['project_number'] === '' && $pair['work_order_number'] === '') {
                            continue;
                        }
                        $matOr = true;
                        $q->orWhere(function ($q2) use ($pair) {
                            $q2->where('project_number', $pair['project_number'])
                                ->where('work_order_number', $pair['work_order_number'])
                                ->where('item_number', $pair['item_number']);
                        });
                    }
                });
            $mats = $matOr ? $mats->get() : collect();
            foreach ($mats as $m) {
                $mk = $this->materialKey((string) $m->project_number, (string) $m->work_order_number, (string) $m->item_number);
                if (!isset($materialByKey[$mk])) {
                    $materialByKey[$mk] = [];
                }
                $desc = trim((string) ($m->description ?? $m->part_number ?? ''));
                if ($desc !== '') {
                    $materialByKey[$mk][] = $desc;
                }
            }
        }
        foreach ($rows as $row) {
            $tn = $this->normalizeTailKey((string) $row->tail_number);
            $ac = $tn !== '' ? ($aircraftByTail[$tn] ?? null) : null;
            $pk = $this->projectTailKey((string) $row->project, (string) $row->tail_number);
            $pr = $pk !== '' ? ($projectByKey[$pk] ?? null) : null;
            $eef = $this->pickEefForWorkCardRow($row, $eefPool, $eefByCanonicalNrc);
            $mk = $this->materialKey((string) $row->project, (string) $row->work_order, (string) $row->item);
            $matList = $materialByKey[$mk] ?? [];
            $materialStr = $matList !== [] ? implode('; ', array_slice(array_unique($matList), 0, 5)) : null;
            $engine = $pr?->engine_type ?? $ac?->engine_type;
            $row->setAttribute('master_msn', $ac?->serial_number);
            $row->setAttribute('master_age', $ac?->manufactured);
            $row->setAttribute('master_fc', $pr?->aircraft_csn);
            $row->setAttribute('master_fh', $pr?->aircraft_tsn);
            $row->setAttribute('master_eef', $eef?->eef_number);
            $row->setAttribute('master_data_source', $eef?->inspection_source_task);
            $row->setAttribute('master_material', $materialStr);
            $row->setAttribute('master_equipment', $engine);
        }
    }

    private function normalizeTailKey(string $tail): string
    {
        return strtoupper(preg_replace('/\s+/', ' ', trim($tail)));
    }

    private function projectTailKey(string $project, string $tail): string
    {
        $p = trim($project);
        $t = $this->normalizeTailKey($tail);
        if ($p === '' || $t === '') {
            return '';
        }
        return $p . '|' . $t;
    }

    private function materialKey(string $project, string $wo, string $item): string
    {
        return trim($project) . '|' . trim($wo) . '|' . trim($item);
    }

    private function ataNorm(?string $a): string
    {
        return strtolower(preg_replace('/\s+/', '', trim((string) $a)));
    }

    private function acTypeNorm(?string $a): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim((string) $a)));
    }

    /** Spaces removed, lowercased — for comparing NRC Number strings. */
    private function normalizeEefNrcString(string $s): string
    {
        return strtolower(preg_replace('/\s+/', '', trim($s)));
    }

    /** Canonical lookup key: normalized "{WORK ORDER}-{ITEM 4-digit zero-padded}" e.g. 17766-0066. */
    private function eefCanonicalNrcKeyFromWorkCard(string $workOrder, string $item): ?string
    {
        $wo = trim($workOrder);
        $it = trim((string) $item);
        if ($wo === '' || $it === '') {
            return null;
        }
        $itemDigits = preg_replace('/\D+/', '', $it) ?? '';
        if ($itemDigits === '') {
            return null;
        }
        $padded = str_pad($itemDigits, 4, '0', STR_PAD_LEFT);

        return $this->normalizeEefNrcString($wo . '-' . $padded);
    }

    /**
     * EEF registry NRC Number = WORK ORDER + "-" + ITEM zero-padded to 4 digits (e.g. WO 17766 + item 66 → 17766-0066).
     * Also accepts legacy variants (no padding, digits-only concat) for older data.
     */
    private function eefRegistryNrcMatchesWorkOrderItem(?string $registryNrc, string $workOrder, string $item): bool
    {
        $wo = trim($workOrder);
        $it = trim((string) $item);
        $n = trim((string) $registryNrc);
        if ($wo === '' || $it === '' || $n === '') {
            return false;
        }
        $itemDigits = preg_replace('/\D+/', '', $it) ?? '';
        if ($itemDigits === '') {
            return false;
        }
        $itemPadded4 = str_pad($itemDigits, 4, '0', STR_PAD_LEFT);
        $woDigits = preg_replace('/\D+/', '', $wo) ?? '';

        $candidates = [
            $wo . '-' . $itemPadded4,
            $wo . '-' . $itemDigits,
            $wo . $it,
            $wo . $itemDigits,
        ];
        if ($woDigits !== '') {
            $candidates[] = $woDigits . '-' . $itemPadded4;
            $candidates[] = $woDigits . '-' . $itemDigits;
            $candidates[] = $woDigits . $itemDigits;
        }
        $candidates[] = $wo . '/' . $itemDigits;
        $candidates[] = $wo . ' ' . $itemDigits;

        $nNorm = $this->normalizeEefNrcString($n);
        foreach (array_unique(array_filter($candidates, static fn (string $s): bool => $s !== '')) as $c) {
            if ($this->normalizeEefNrcString($c) === $nNorm) {
                return true;
            }
        }

        $digitsN = preg_replace('/\D+/', '', $n) ?? '';
        if ($woDigits !== '' && $digitsN !== '' && $digitsN === $woDigits . $itemPadded4) {
            return true;
        }
        if ($woDigits !== '' && $digitsN !== '' && $digitsN === $woDigits . $itemDigits) {
            return true;
        }

        return false;
    }

    /**
     * @param array<string, InspectionEefRegistry> $eefByCanonicalNrc normalized NRC key → row (fallback when project differs)
     */
    private function pickEefForWorkCardRow(
        ReliabilityMasterData $row,
        Collection $eefPool,
        array $eefByCanonicalNrc = []
    ): ?InspectionEefRegistry {
        $wo = (string) $row->work_order;
        $item = (string) $row->item;
        $p = trim((string) $row->project);
        $candidates = $p !== ''
            ? $eefPool->filter(fn (InspectionEefRegistry $e) => trim((string) $e->project_no) === $p)
            : collect();
        if ($candidates->isNotEmpty()) {
            $byWoItem = $candidates->first(
                fn (InspectionEefRegistry $e) => $this->eefRegistryNrcMatchesWorkOrderItem($e->nrc_number, $wo, $item)
            );
            if ($byWoItem !== null) {
                return $byWoItem;
            }
        }
        $canonKey = $this->eefCanonicalNrcKeyFromWorkCard($wo, $item);
        if ($canonKey !== null && isset($eefByCanonicalNrc[$canonKey])) {
            return $eefByCanonicalNrc[$canonKey];
        }

        return null;
    }

    /**
     * NRC: ORDER TYPE = ADDNRC или NONROUTINE и непустой SRC. CUST. CARD (как в Power Query).
     * RC: дополнение к NRC — иначе строки ADDNRC/NONROUTINE с пустым SRC. CUST. CARD не попадали ни на одну вкладку.
     *      Формула: (тип не ADDNRC/NONROUTINE) ИЛИ (SRC. CUST. CARD пустой).
     */
    private function applyMasterDataTabFilter(Builder $query, string $source): void
    {
        if ($source === 'nrc') {
            $query->whereRaw('LOWER(TRIM(COALESCE(order_type, \'\'))) IN (?, ?)', ['addnrc', 'nonroutine'])
                ->whereRaw('TRIM(COALESCE(src_cust_card, \'\')) <> \'\'');
            return;
        }
        $query->where(function (Builder $q) {
            $q->whereRaw('LOWER(TRIM(COALESCE(order_type, \'\'))) NOT IN (?, ?)', ['addnrc', 'nonroutine'])
                ->orWhereRaw('TRIM(COALESCE(src_cust_card, \'\')) = \'\'');
        });
    }

    private function applyMasterDataFilters($query, Request $request, string $source = 'rc'): void
    {
        $id = $request->input('id');
        if ($id !== null && trim((string) $id) !== '') {
            $id = trim((string) $id);
            if (is_numeric($id)) {
                $query->where('id', (int) $id);
            }
        }
        $fields = [
            'project',
            'project_type',
            'aircraft_type',
            'tail_number',
            'wo_station',
            'work_order',
            'item',
            'src_order',
            'src_item',
            'src_cust_card',
            'description',
            'corrective_action',
            'ata',
            'cust_card',
            'order_type',
            'avg_time',
            'act_time',
            'aircraft_location',
        ];
        foreach ($fields as $col) {
            $val = $request->input($col);
            if ($val !== null && trim((string) $val) !== '') {
                $query->where($col, 'like', '%' . trim((string) $val) . '%');
            }
        }
    }

    /** @return list<string> */
    private function masterDataSortableColumnNames(): array
    {
        return [
            'id',
            'project',
            'project_type',
            'aircraft_type',
            'tail_number',
            'wo_station',
            'work_order',
            'item',
            'src_order',
            'src_item',
            'src_cust_card',
            'description',
            'corrective_action',
            'ata',
            'cust_card',
            'order_type',
            'avg_time',
            'act_time',
            'aircraft_location',
        ];
    }

    /** @return array{0: string, 1: string} [column, asc|desc] */
    private function resolveMasterDataSort(Request $request): array
    {
        $allowed = $this->masterDataSortableColumnNames();
        $sort = (string) $request->input('sort', 'id');
        if (!in_array($sort, $allowed, true)) {
            $sort = 'id';
        }
        $dir = strtolower((string) $request->input('dir', 'asc'));
        if (!in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'asc';
        }

        return [$sort, $dir];
    }

    private function applyMasterDataSort(Builder $query, string $column, string $direction): void
    {
        $query->orderBy($column, $direction);
        if ($column !== 'id') {
            $query->orderBy('id', 'asc');
        }
    }

    private function resolveMasterDataSource(Request $request): string
    {
        $source = strtolower(trim((string) $request->get('source', 'rc')));
        return in_array($source, ['rc', 'nrc'], true) ? $source : 'rc';
    }

    private function getMasterDataModelClass(string $source): string
    {
        return ReliabilityMasterData::class;
    }

    /** Master Data: подсчёт строк и список листов в загружаемом файле */
    public function masterDataCount(Request $request): \Illuminate\Http\JsonResponse
    {
        set_time_limit(120);
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:204800']);
        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            $ext = strtolower($file->getClientOriginalExtension());
            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $sheets = $this->getMasterDataSheets($path, $ext);
                $total = $sheets[0]['total'] ?? 0;
                return response()->json(['total' => $total, 'sheets' => $sheets]);
            }
            $total = $this->countMasterDataRows($path, $ext, 0);
            return response()->json(['total' => $total]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Master Data: список листов файла на диске (path относительно корня проекта) */
    public function masterDataSheetsFromPath(Request $request): \Illuminate\Http\JsonResponse
    {
        $rawInput = trim((string) ($request->input('path') ?? $request->input('local_path') ?? ''));
        if ($rawInput === '') {
            return response()->json(['error' => 'Path is required'], 422);
        }
        $relPath = ltrim(str_replace(['..', '\\'], ['', '/'], $rawInput), '/');
        $absPath = base_path($relPath);
        if (!file_exists($absPath)) {
            return response()->json(['error' => "File not found: $relPath"], 404);
        }
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'], true)) {
            return response()->json(['error' => 'Invalid file type'], 422);
        }
        try {
            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $sheets = $this->getMasterDataSheets($absPath, $ext);
                return response()->json(['sheets' => $sheets]);
            }
            $total = $this->countMasterDataRows($absPath, $ext, 0);
            return response()->json(['sheets' => [['name' => 'Sheet1', 'index' => 0, 'total' => $total]]]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Master Data: загрузка файла (импорт) */
    public function masterDataUpload(Request $request)
    {
        set_time_limit(0);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '1536M');
        }
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:204800']);
        $source = $this->resolveMasterDataSource($request);
        $clearBefore = $request->boolean('clear_before');
        $file = $request->file('file');
        $sheetIndex = (int) $request->input('sheet_index', 0);
        if ($sheetIndex < 0) {
            $sheetIndex = 0;
        }
        if ($request->header('X-WC-Stream') === '1') {
            return $this->masterDataUploadStream($file, $sheetIndex, $source, $clearBefore);
        }
        try {
            if ($clearBefore) {
                $this->getMasterDataModelClass($source)::query()->delete();
            }
            $count = $this->importMasterDataChunked($file, null, null, $sheetIndex, $source);
            return redirect()->route('modules.reliability.settings.master-data.index', ['source' => $source])->with('success', "Imported records: {$count}");
        } catch (\Throwable $e) {
            report($e);
            $msg = strlen($e->getMessage()) > 200 ? substr($e->getMessage(), 0, 200) . '…' : $e->getMessage();
            return redirect()->route('modules.reliability.settings.master-data.index', ['source' => $source])->with('error', 'Upload error: ' . $msg);
        }
    }

    /** Master Data: потоковая отдача прогресса при загрузке */
    private function masterDataUploadStream($file, int $sheetIndex = 0, string $source = 'rc', bool $clearBefore = false): StreamedResponse
    {
        $send = function (array $data) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        };
        return new StreamedResponse(function () use ($file, $sheetIndex, $source, $clearBefore, $send) {
            try {
                if ($clearBefore) {
                    $this->getMasterDataModelClass($source)::query()->delete();
                }
                $count = $this->importMasterDataChunked(
                    $file,
                    function (int $processed, int $total) use ($send) {
                        $send(['processed' => $processed, 'total' => $total]);
                    },
                    null,
                    $sheetIndex,
                    $source
                );
                $send(['done' => true, 'count' => $count]);
            } catch (\Throwable $e) {
                report($e);
                $msg = strlen($e->getMessage()) > 300 ? substr($e->getMessage(), 0, 300) . '…' : $e->getMessage();
                $send(['error' => $msg]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /** Master Data: импорт с диска сервера (NDJSON) */
    public function masterDataImportLocal(Request $request): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        try {
            set_time_limit(0);
            if (function_exists('ini_set')) {
                @ini_set('memory_limit', '1536M');
            }
            $rawInput = trim((string) ($request->input('path') ?? $request->input('local_path') ?? ''));
            if ($rawInput === '') {
                return response()->json(['error' => 'Path is required'], 422);
            }
            $relPath = ltrim(str_replace(['..', '\\'], ['', '/'], $rawInput), '/');
            $absPath = base_path($relPath);
            if (!file_exists($absPath)) {
                return response()->json(['error' => "File not found: $relPath"], 404);
            }
            $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
            if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'], true)) {
                return response()->json(['error' => 'Invalid file type'], 422);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        $send = function (array $data) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        };
        $sheetIndex = (int) $request->input('sheet_index', 0);
        if ($sheetIndex < 0) {
            $sheetIndex = 0;
        }
        $source = $this->resolveMasterDataSource($request);
        $clearBefore = $request->boolean('clear_before');
        return new StreamedResponse(function () use ($absPath, $ext, $sheetIndex, $source, $clearBefore, $send) {
            try {
                if ($clearBefore) {
                    $this->getMasterDataModelClass($source)::query()->delete();
                }
                $total = $this->countMasterDataRows($absPath, $ext, $sheetIndex);
                $send(['total' => $total]);
                $count = $this->importMasterDataChunked(
                    $absPath,
                    function (int $processed, int $tot) use ($send) {
                        $send(['processed' => $processed, 'total' => $tot]);
                    },
                    $total,
                    $sheetIndex,
                    $source
                );
                $send(['done' => true, 'count' => $count]);
            } catch (\Throwable $e) {
                report($e);
                $msg = strlen($e->getMessage()) > 300 ? substr($e->getMessage(), 0, 300) . '…' : $e->getMessage();
                $send(['error' => $msg]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function masterDataDelete(Request $request)
    {
        $source = $this->resolveMasterDataSource($request);
        $modelClass = $this->getMasterDataModelClass($source);
        $ids = $request->input('ids', []);
        $ids = array_filter(array_map('intval', (array) $ids));
        if ($ids !== []) {
            $modelClass::whereIn('id', $ids)->delete();
        }
        return redirect()->route('modules.reliability.settings.master-data.index', ['source' => $source])->with('success', 'Selected records deleted.');
    }

    /** Экспорт master data в CSV (только поля Work Card, как при импорте). Учитывает фильтры в query string. */
    public function masterDataExport(Request $request): StreamedResponse
    {
        $source = $this->resolveMasterDataSource($request);
        $query = ReliabilityMasterData::query();
        $this->applyMasterDataTabFilter($query, $source);
        $this->applyMasterDataFilters($query, $request, $source);
        [$sortColumn, $sortDirection] = $this->resolveMasterDataSort($request);
        $this->applyMasterDataSort($query, $sortColumn, $sortDirection);
        $filename = 'master_data_' . $source . '_' . date('Y-m-d_His') . '.csv';
        $columns = [
            'project' => 'PROJECT',
            'project_type' => 'PROJECT TYPE',
            'aircraft_type' => 'AIRCRAFT TYPE',
            'tail_number' => 'TAIL NUMBER',
            'master_msn' => 'MSN',
            'master_age' => 'AGE',
            'master_fc' => 'FC',
            'master_fh' => 'FH',
            'master_eef' => 'EEF#',
            'master_data_source' => 'DATA SOURCE',
            'master_material' => 'MATERIAL',
            'master_equipment' => 'EQUIPMENT',
            'wo_station' => 'WO STATION',
            'work_order' => 'WORK ORDER',
            'item' => 'ITEM',
            'src_order' => 'SRC. ORDER',
            'src_item' => 'SRC. ITEM',
            'src_cust_card' => 'SRC. CUST. CARD',
            'description' => 'DESCRIPTION',
            'corrective_action' => 'CORRECTIVE ACTION',
            'ata' => 'ATA',
            'cust_card' => 'CUST. CARD',
            'order_type' => 'ORDER TYPE',
            'avg_time' => 'AVG. TIME',
            'act_time' => 'ACT. TIME',
            'aircraft_location' => 'AIRCRAFT LOCATION',
        ];
        return new StreamedResponse(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_values($columns));
            $query->chunk(2000, function ($rows) use ($out, $columns) {
                $this->enrichWorkCardMasterRows($rows);
                foreach ($rows as $row) {
                    $line = [];
                    foreach (array_keys($columns) as $db) {
                        $line[] = $row->getAttribute($db);
                    }
                    fputcsv($out, $line);
                }
            });
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Заголовки Work Card (CSV/XLSX) → поля БД. Импортируются только эти колонки; остальные в файле игнорируются.
     */
    private function masterDataWorkCardImportHeaderMap(): array
    {
        return [
            'PROJECT' => 'project',
            'PROJECT TYPE' => 'project_type',
            'AIRCRAFT TYPE' => 'aircraft_type',
            'TAIL NUMBER' => 'tail_number',
            'TAIL NUMBER ' => 'tail_number',
            'WO STATION' => 'wo_station',
            'WORK ORDER' => 'work_order',
            'ITEM' => 'item',
            'SRC. ORDER' => 'src_order',
            'SRC. ITEM' => 'src_item',
            'SRC. CUST. CARD' => 'src_cust_card',
            'DESCRIPTION' => 'description',
            'CORRECTIVE ACTION' => 'corrective_action',
            'ATA' => 'ata',
            'CUST. CARD' => 'cust_card',
            'CUST CARD' => 'cust_card',
            'ORDER TYPE' => 'order_type',
            'AVG. TIME' => 'avg_time',
            'ACT. TIME' => 'act_time',
            'AIRCRAFT LOCATION' => 'aircraft_location',
        ];
    }

    private function masterDataHeaderMap(string $source): array
    {
        return $this->masterDataWorkCardImportHeaderMap();
    }

    /** Нормализация заголовка для сопоставления (пробелы, регистр). */
    private function normalizeMasterDataHeader(string $header): string
    {
        $s = strtoupper(trim((string) $header));
        $s = (string) preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    /**
     * Список листов Excel (xlsx/xls) с количеством строк в каждом.
     * @return array<int, array{name: string, index: int, total: int}>
     */
    private function getMasterDataSheets(string $path, string $ext): array
    {
        if (!in_array($ext, ['xlsx', 'xls'], true)) {
            return [];
        }
        $r = IOFactory::createReaderForFile($path);
        if (!method_exists($r, 'listWorksheetInfo')) {
            return [['name' => 'Sheet1', 'index' => 0, 'total' => 0]];
        }
        $info = $r->listWorksheetInfo($path);
        $sheets = [];
        foreach ($info as $idx => $sheetInfo) {
            $name = $sheetInfo['worksheetName'] ?? 'Sheet' . ($idx + 1);
            $total = max(0, (int) ($sheetInfo['totalRows'] ?? 0) - 1); // без заголовка
            $sheets[] = ['name' => $name, 'index' => $idx, 'total' => $total];
        }
        return $sheets;
    }

    private function countMasterDataRows(string $path, string $ext, int $sheetIndex = 0): int
    {
        if ($ext === 'csv' || $ext === 'txt') {
            $fh = fopen($path, 'r');
            if ($fh === false) {
                return 0;
            }
            $first = fgetcsv($fh, 0, ',');
            if ($first !== false && isset($first[0]) && str_starts_with(trim((string) $first[0]), 'sep=')) {
                fgetcsv($fh, 0, ',');
            }
            $lines = 0;
            while (!feof($fh)) {
                $line = fgetcsv($fh, 0, ',');
                if ($line !== false && $line !== null) {
                    $lines++;
                }
            }
            fclose($fh);
            return max(0, $lines);
        }
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $r = IOFactory::createReaderForFile($path);
            if (method_exists($r, 'listWorksheetInfo')) {
                $info = $r->listWorksheetInfo($path);
                $sheet = $info[$sheetIndex] ?? $info[0] ?? null;
                if ($sheet !== null) {
                    return max(0, (int) ($sheet['totalRows'] ?? 0) - 1);
                }
            }
            return 0;
        }
        return 0;
    }

    private function importMasterDataChunked($file, ?callable $onProgress = null, ?int $knownTotal = null, int $sheetIndex = 0, string $source = 'rc'): int
    {
        $path = is_string($file) ? $file : $file->getRealPath();
        $ext = is_string($file)
            ? strtolower(pathinfo($file, PATHINFO_EXTENSION))
            : strtolower($file->getClientOriginalExtension());
        if ($sheetIndex < 0) {
            $sheetIndex = 0;
        }
        $modelClass = $this->getMasterDataModelClass($source);
        $chunkSize = 500;
        $count = 0;
        $now = now()->toDateTimeString();
        $dataColumns = [
            'project',
            'project_type',
            'aircraft_type',
            'tail_number',
            'wo_station',
            'work_order',
            'item',
            'src_order',
            'src_item',
            'src_cust_card',
            'description',
            'corrective_action',
            'ata',
            'cust_card',
            'order_type',
            'avg_time',
            'act_time',
            'aircraft_location',
        ];
        $insertColumns = array_merge($dataColumns, ['created_at', 'updated_at']);
        $map = $this->masterDataHeaderMap($source);
        $mapUpper = [];
        foreach ($map as $fileCol => $dbCol) {
            if ($dbCol !== null) {
                $key = $this->normalizeMasterDataHeader($fileCol);
                $mapUpper[$key] = $dbCol;
            }
        }
        $buildIndexMap = function (array $fileHeaders) use ($mapUpper): array {
            $indexMap = [];
            foreach ($fileHeaders as $i => $h) {
                $key = $this->normalizeMasterDataHeader((string) $h);
                if (isset($mapUpper[$key])) {
                    $indexMap[$i] = $mapUpper[$key];
                }
            }
            return $indexMap;
        };
        $buildRow = function (array $indexMap, array $rowValues) use ($now, $insertColumns): array {
            $data = array_fill_keys(array_slice($insertColumns, 0, -2), null);
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            foreach ($indexMap as $i => $dbCol) {
                $val = $rowValues[$i] ?? null;
                if ($val === null || (is_string($val) && trim($val) === '')) {
                    continue;
                }
                if ($val instanceof \DateTimeInterface) {
                    $val = $val->format('Y-m-d H:i:s');
                }
                $data[$dbCol] = is_string($val) ? trim($val) : $val;
            }
            return $data;
        };
        $flush = function (array &$buffer) use (&$count, $onProgress, $knownTotal, $modelClass): void {
            if ($buffer === []) {
                return;
            }
            $modelClass::insert($buffer);
            $count += count($buffer);
            $buffer = [];
            if ($onProgress !== null) {
                $onProgress($count, $knownTotal ?? $count);
            }
        };
        if ($ext === 'csv' || $ext === 'txt') {
            $fh = fopen($path, 'r');
            if ($fh === false) {
                return 0;
            }
            $sep = ',';
            $firstLine = fgetcsv($fh, 0, ',');
            if ($firstLine !== false && isset($firstLine[0]) && str_starts_with(trim((string) $firstLine[0]), 'sep=')) {
                $sep = trim(substr(trim((string) $firstLine[0]), 4)) ?: ',';
                $firstLine = fgetcsv($fh, 0, $sep);
            }
            $fileHeaders = $firstLine !== false ? array_map('trim', $firstLine) : [];
            $indexMap = $buildIndexMap($fileHeaders);
            $buffer = [];
            while (($row = fgetcsv($fh, 0, $sep)) !== false) {
                $data = $buildRow($indexMap, $row);
                $hasAny = false;
                foreach ($dataColumns as $c) {
                    if (isset($data[$c]) && $data[$c] !== null) {
                        $hasAny = true;
                        break;
                    }
                }
                if ($hasAny) {
                    $buffer[] = $data;
                    if (count($buffer) >= $chunkSize) {
                        $flush($buffer);
                    }
                }
            }
            fclose($fh);
            $flush($buffer);
        } else {
            $buffer = [];
            $reader = new XlsxReader();
            if ($ext === 'xls') {
                $reader = \OpenSpout\Reader\Common\Creator\ReaderEntityFactory::createXLSReader();
            }
            $reader->open($path);
            $fileHeaders = [];
            $indexMap = [];
            $currentSheetIndex = 0;
            foreach ($reader->getSheetIterator() as $sheet) {
                if ($currentSheetIndex !== $sheetIndex) {
                    $currentSheetIndex++;
                    continue;
                }
                $rowIndex = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $values = [];
                    foreach ($row->getCells() as $idx => $cell) {
                        $values[$idx] = $cell->getValue();
                    }
                    if ($rowIndex === 0) {
                        $fileHeaders = array_values($values);
                        $indexMap = $buildIndexMap($fileHeaders);
                        $rowIndex++;
                        continue;
                    }
                    $data = $buildRow($indexMap, $values);
                    $hasAny = false;
                    foreach ($dataColumns as $c) {
                        if (isset($data[$c]) && $data[$c] !== null) {
                            $hasAny = true;
                            break;
                        }
                    }
                    if ($hasAny) {
                        $buffer[] = $data;
                        if (count($buffer) >= $chunkSize) {
                            $flush($buffer);
                        }
                    }
                    $rowIndex++;
                }
                break;
            }
            $reader->close();
            $flush($buffer);
        }
        return $count;
    }

    public function eefRegistryClear(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['_token' => 'required']);
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
            \DB::table('eef_registry')->delete();
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function eefRegistryDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $ids = array_filter(array_map('intval', (array) $ids));
        if ($ids !== []) {
            InspectionEefRegistry::whereIn('id', $ids)->delete();
        }
        return redirect()->route('modules.reliability.settings.inspection.eef-registry')->with('success', 'Выбранные записи удалены.');
    }

    public function workCardMaterialsDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $ids = array_filter(array_map('intval', (array) $ids));
        if ($ids !== []) {
            InspectionWorkCardMaterial::whereIn('id', $ids)->delete();
        }
        return redirect()->route('modules.reliability.settings.inspection.work-card-materials')->with('success', 'Выбранные записи удалены.');
    }

    public function sourceCardRefsDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $ids = array_filter(array_map('intval', (array) $ids));
        if ($ids !== []) {
            InspectionSourceCardRef::whereIn('id', $ids)->delete();
        }
        return redirect()->route('modules.reliability.settings.inspection.source-card-refs')->with('success', 'Выбранные записи удалены.');
    }

    public function caseAnalysesDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        $ids = array_filter(array_map('intval', (array) $ids));
        if ($ids !== []) {
            InspectionCaseAnalysis::whereIn('id', $ids)->delete();
        }
        return redirect()->route('modules.reliability.settings.inspection.case-analyses')->with('success', 'Выбранные записи удалены.');
    }

    /** Приводит значения числовых/датовых полей к допустимым типам; нечисловой текст → null; невалидные даты → null */
    private function sanitizeProjectRow(array $row): array
    {
        $decimalKeys = [
            'aircraft_tsn', 'aircraft_csn', 'quoted_mhrs', 'oa_mhrs', 'add_works_mhrs', 'cwr_mhrs',
            'eng_mhrs', 'total_mhrs', 'engine_1_tsn', 'engine_1_csn', 'engine_2_tsn', 'engine_2_csn',
            'engine_3_tsn', 'engine_3_csn', 'engine_4_tsn', 'engine_4_csn', 'apu_tsn', 'apu_csn', 'mhrs_cap',
        ];
        $intKeys = [
            'target_days', 'open_requisitions', 'open_order_lines', 'awaiting_to_return_store',
            'uninvoice_order_lines', 'open_work_cards', 'open_work_orders',
        ];
        $dateKeys = [
            'open_date', 'close_date', 'arrival_date', 'induction_date', 'inspection_date', 'delivery_date',
            'rev_delivery_date', 'latest_delivery_date', 'actual_arrival_date', 'actual_induction_date',
            'actual_inspection_date', 'actual_delivery_date', 'spares_order_cut_off', 'spares_delivery_cut_off',
        ];
        foreach ($decimalKeys as $key) {
            if (array_key_exists($key, $row)) {
                $v = $row[$key];
                $row[$key] = (is_numeric(str_replace(',', '.', (string) $v))) ? (float) str_replace(',', '.', $v) : null;
            }
        }
        foreach ($intKeys as $key) {
            if (array_key_exists($key, $row)) {
                $v = $row[$key];
                $row[$key] = (is_numeric((string) $v)) ? (int) $v : null;
            }
        }
        foreach ($dateKeys as $key) {
            if (array_key_exists($key, $row)) {
                $row[$key] = $this->parseDateSafe($row[$key]);
            }
        }
        return $row;
    }

    /** Парсит строку даты; при ошибке или неполной дате возвращает null */
    private function parseDateSafe(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }
        if (strlen($s) < 8) {
            return null;
        }
        try {
            $dt = new \DateTime($s);
            return $dt->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Project data: маппинг заголовков файла на поля БД */
    private function projectsHeaderMap(): array
    {
        return [
            'PROJECT #' => 'project_number',
            'STATUS' => 'status',
            'TAIL NUMBER' => 'tail_number',
            'AIRCRAFT TYPE' => 'aircraft_type',
            'SCOPE' => 'scope',
            'OPEN DATE' => 'open_date',
            'CLOSE DATE' => 'close_date',
            'CUSTOMER #' => 'customer_number',
            'CUSTOMER NAME' => 'customer_name',
            'CUSTOMER PO' => 'customer_po',
            'EST.NON-ROUTINE' => 'est_non_routine',
            'TARGET DAYS' => 'target_days',
            'ARRIVAL DATE' => 'arrival_date',
            'INDUCTION DATE' => 'induction_date',
            'INSPECTION DATE' => 'inspection_date',
            'DELIVERY DATE' => 'delivery_date',
            'REV.DELIVERY DATE' => 'rev_delivery_date',
            'LATEST DELIVERY DATE' => 'latest_delivery_date',
            'ACTUAL ARRIVAL DATE' => 'actual_arrival_date',
            'ACTUAL INDUCTION DATE' => 'actual_induction_date',
            'ACTUAL INSPECTION DATE' => 'actual_inspection_date',
            'ACTUAL DELIVERY DATE' => 'actual_delivery_date',
            'PROJECT TYPE' => 'project_type',
            'APPLICABLE STANDARD' => 'applicable_standard',
            'RESOURCES' => 'resources',
            'BAY' => 'bay',
            'PLANNED SPAN' => 'planned_span',
            'DAY OF CHECK' => 'day_of_check',
            'AIRCRAFT TSN' => 'aircraft_tsn',
            'AIRCRAFT CSN' => 'aircraft_csn',
            'ENGINE TYPE' => 'engine_type',
            'QUOTED MHRS' => 'quoted_mhrs',
            'O&A MHRS' => 'oa_mhrs',
            'ADD WORKS MHRS' => 'add_works_mhrs',
            'CWR MHRS' => 'cwr_mhrs',
            'AIRCRAFT SERIES' => 'aircraft_series',
            'STATION' => 'station',
            'OPEN REQUISITIONS' => 'open_requisitions',
            'OPEN ORDER LINES' => 'open_order_lines',
            'AWAITING TO RETURN STORE' => 'awaiting_to_return_store',
            'UNINVOICE ORDER LINES' => 'uninvoice_order_lines',
            'OPEN WORK CARDS' => 'open_work_cards',
            'OPEN WORK ORDERS' => 'open_work_orders',
            "ENG'G MHRS" => 'eng_mhrs',
            'TOTAL MHRS' => 'total_mhrs',
            'ENGINE 1 SERIAL' => 'engine_1_serial',
            'ENGINE 2 SERIAL' => 'engine_2_serial',
            'ENGINE 3 SERIAL' => 'engine_3_serial',
            'ENGINE 4 SERIAL' => 'engine_4_serial',
            'ENGINE 1 TSN' => 'engine_1_tsn',
            'ENGINE 1 CSN' => 'engine_1_csn',
            'ENGINE 2 TSN' => 'engine_2_tsn',
            'ENGINE 2 CSN' => 'engine_2_csn',
            'ENGINE 3 TSN' => 'engine_3_tsn',
            'ENGINE 3 CSN' => 'engine_3_csn',
            'ENGINE 4 TSN' => 'engine_4_tsn',
            'ENGINE 4 CSN' => 'engine_4_csn',
            'APU PN' => 'apu_pn',
            'APU SERIAL' => 'apu_serial',
            'APU TSN' => 'apu_tsn',
            'APU CSN' => 'apu_csn',
            'SPARES ORDER CUT OFF' => 'spares_order_cut_off',
            'SPARES DELIVERY CUT OFF' => 'spares_delivery_cut_off',
            'MHRS CAP' => 'mhrs_cap',
        ];
    }

    public function aircrafts(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000], true)) {
            $perPage = 50;
        }
        $items = InspectionAircraft::orderBy('id')->paginate($perPage)->withQueryString();
        return view('Modules.Reliability.inspection_settings.aircrafts', compact('items', 'perPage'));
    }

    public function aircraftsUpload(Request $request)
    {
        set_time_limit(0);
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:51200']);
        $count = $this->importFromFile($request->file('file'), $this->aircraftHeaderMap(), function (array $row) {
            InspectionAircraft::create($row);
        });
        return redirect()->route('modules.reliability.settings.inspection.aircrafts')->with('success', "Imported records: {$count}");
    }

    /** Aircraft: маппинг заголовков файла на поля БД */
    private function aircraftHeaderMap(): array
    {
        return [
            'SERIAL #' => 'serial_number',
            'TAIL #' => 'tail_number',
            'AIRCRAFT TYPE' => 'aircraft_type',
            'VISIT' => 'visit',
            'CUSTOMER #' => 'customer_number',
            'OWNER #' => 'owner_number',
            'ENGINE TYPE' => 'engine_type',
            'APU TYPE' => 'apu_type',
            'GROUP CODE' => 'group_code',
            'DELIVERY DATE' => 'delivery_date',
            'REDELIVERY DATE' => 'redelivery_date',
            'ETOPS' => 'etops',
            'AMM GROUP' => 'amm_group',
            'CUSTOMER NAME' => 'customer_name',
            'OWNER NAME' => 'owner_name',
            'APP. STD' => 'app_std',
            'LINE NO' => 'line_no',
            'VARIABLE NO' => 'variable_no',
            'EFFECTIVITY' => 'effectivity',
            'SELCAL' => 'selcal',
            'LEASE DATE' => 'lease_date',
            'MANUFACTURED' => 'manufactured',
            'INS. DATE' => 'ins_date',
            'PAS.CAP.' => 'pas_cap',
            'SEAT MAT.' => 'seat_mat',
            'MAX.TAXI' => 'max_taxi',
            'MAX.TO.' => 'max_to',
            'MAX.LAND' => 'max_land',
            'MAXIMUM ZERO FUEL WEIGHT' => 'maximum_zero_fuel_weight',
            'MAX.PAY.' => 'max_pay',
            'DRY OPE.' => 'dry_ope',
            'FUEL' => 'fuel',
            'FUEL BURN RATIO' => 'fuel_burn_ratio',
            'FWD CARGO' => 'fwd_cargo',
            'AFT CARGO' => 'aft_cargo',
            'FWD AREA' => 'fwd_area',
            'AFT AREA' => 'aft_area',
            'SIDE NOISE' => 'side_noise',
            'APP.NOISE' => 'app_noise',
            'START NOISE' => 'start_noise',
            'ENG.RATE' => 'eng_rate',
            'MOD' => 'mod',
            'COLOR' => 'color',
            'FLIGHT NUMBER' => 'flight_number',
            'SCHEDULED FROM' => 'scheduled_from',
            'SCHEDULED TO' => 'scheduled_to',
            'SCHEDULED OFF BLOCK' => 'scheduled_off_block',
            'SCHEDULED ON BLOCK' => 'scheduled_on_block',
            'ACTUAL FROM' => 'actual_from',
            'ACTUAL TO' => 'actual_to',
            'ACTUAL OFF BLOCK' => 'actual_off_block',
            'ACTUAL ON BLOCK' => 'actual_on_block',
            'ROUTE DEV. DIST.' => 'route_dev_dist',
            'ROUTE DEV. TIME' => 'route_dev_time',
            'WNG.' => 'wng',
            'ARCHIVE' => 'archive',
            'ACTIVE' => 'active',
        ];
    }

    public function workCards(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000], true)) {
            $perPage = 50;
        }
        $items = InspectionWorkCard::orderBy('id')->paginate($perPage)->withQueryString();
        return view('Modules.Reliability.inspection_settings.work_cards', compact('items', 'perPage'));
    }

    /**
     * Импорт Work Cards прямо с диска сервера (файл уже лежит в /excel/).
     * Возвращает StreamedResponse с NDJSON-прогрессом.
     */
    public function workCardsImportLocal(Request $request): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        // Все ошибки возвращаем JSON, чтобы JS не получил HTML-страницу
        try {
            set_time_limit(0);
            if (function_exists('ini_set')) {
                @ini_set('memory_limit', '1536M');
            }
            $rawInput = trim((string) ($request->input('path') ?? $request->input('local_path') ?? ''));
            if ($rawInput === '') {
                return response()->json(['error' => 'Путь не указан'], 422);
            }
            $relPath = ltrim(str_replace(['..', '\\'], ['', '/'], $rawInput), '/');
            $absPath = base_path($relPath);
            if (!file_exists($absPath)) {
                return response()->json(['error' => "Файл не найден: $relPath"], 404);
            }
            $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
            if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'], true)) {
                return response()->json(['error' => 'Недопустимый тип файла (допустимо: xlsx, xls, csv, txt)'], 422);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $send = function (array $data) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        };

        $countOnly = $request->header('X-WC-Count-Only') === '1';

        return new StreamedResponse(function () use ($absPath, $ext, $send, $countOnly) {
            try {
                $total = $this->countWorkCardsRows($absPath, $ext);
                $send(['total' => $total]);
                if ($countOnly) {
                    return;
                }
                $count = $this->importWorkCardsChunkedFromPath(
                    $absPath, $ext,
                    function (int $processed, int $tot) use ($send) {
                        $send(['processed' => $processed, 'total' => $tot]);
                    },
                    $total
                );
                $send(['done' => true, 'count' => $count]);
            } catch (\Throwable $e) {
                report($e);
                $msg = strlen($e->getMessage()) > 300 ? substr($e->getMessage(), 0, 300) . '…' : $e->getMessage();
                $send(['error' => $msg]);
            }
        }, 200, [
            'Content-Type'      => 'application/x-ndjson; charset=utf-8',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Обёртка importWorkCardsChunked для пути к файлу на диске.
     */
    private function importWorkCardsChunkedFromPath(
        string $absPath,
        string $ext,
        ?callable $onProgress = null,
        ?int $knownTotal = null
    ): int {
        // Создаём «псевдо-файл»-обёртку со строковым путём
        return $this->importWorkCardsChunked($absPath, $onProgress, $knownTotal);
    }

    /**
     * Быстрый подсчёт строк файла без импорта данных (JSON-ответ).
     */
    public function workCardsCount(Request $request): \Illuminate\Http\JsonResponse
    {
        set_time_limit(120);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '512M');
        }
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:204800']);
        try {
            $file = $request->file('file');
            $total = $this->countWorkCardsRows($file->getRealPath(), strtolower($file->getClientOriginalExtension()));
            return response()->json(['total' => $total]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function workCardsUpload(Request $request)
    {
        set_time_limit(0);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '1536M');
        }
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:204800']);
        $file = $request->file('file');

        if ($request->header('X-WC-Stream') === '1') {
            return $this->workCardsUploadStream($file);
        }

        try {
            $count = $this->importWorkCardsChunked($file, null, null);
            return redirect()->route('modules.reliability.settings.inspection.work-cards')->with('success', "Imported records: {$count}");
        } catch (\Throwable $e) {
            report($e);
            $msg = $e->getMessage();
            $short = strlen($msg) > 200 ? substr($msg, 0, 200) . '…' : $msg;
            return redirect()->route('modules.reliability.settings.inspection.work-cards')->with('error', 'Ошибка загрузки: ' . $short);
        }
    }

    private function workCardsUploadStream($file): StreamedResponse
    {
        $send = function (array $data) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        };

        return new StreamedResponse(function () use ($file, $send) {
            try {
                $count = $this->importWorkCardsChunked(
                    $file,
                    function (int $processed, int $total) use ($send) {
                        $send(['processed' => $processed, 'total' => $total]);
                    },
                    null
                );
                $send(['done' => true, 'count' => $count]);
            } catch (\Throwable $e) {
                report($e);
                $msg = strlen($e->getMessage()) > 300 ? substr($e->getMessage(), 0, 300) . '…' : $e->getMessage();
                $send(['error' => $msg]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Импорт Work cards пачками (bulk insert) для больших файлов (десятки тысяч строк).
     * @param callable(int,int)|null $onProgress  (processed, total)
     * @param int|null $knownTotal  заранее известное число строк (для прогресса)
     */
    private function importWorkCardsChunked($file, ?callable $onProgress = null, ?int $knownTotal = null): int
    {
        $path = is_string($file) ? $file : $file->getRealPath();
        $ext  = is_string($file)
            ? strtolower(pathinfo($file, PATHINFO_EXTENSION))
            : strtolower($file->getClientOriginalExtension());

        // MySQL limit ~65535 placeholders; ~150 cols → floor(65535/150) = 436 max, берём 400 с запасом
        $chunkSize = 400;
        $count = 0;
        $now = now()->toDateTimeString();

        // ── Подготовка колонок (один раз) ─────────────────────────────────────
        $tableColumns    = Schema::getColumnListing('work_cards');
        $allowedSet      = array_flip(array_diff($tableColumns, ['id']));
        $insertColumns   = array_merge(array_keys($allowedSet), ['created_at', 'updated_at']);

        $dateOnlySet = array_flip([
            'open_date', 'close_date', 'planned_finish_date', 'card_start_date', 'card_finish_date',
            'ms_start_date', 'ms_finish_date', 'inspection_date', 'maint_start_date', 'dmi_due_date',
        ]);
        $dateTimeSet = array_flip([
            'src_open_dt', 'planned_start', 'performed_date', 'check_date', 'completed_time_utc',
        ]);
        $allDateSet = $dateOnlySet + $dateTimeSet;

        // Числовые колонки — нечисловые значения сбрасываем в null
        $numericSet = array_flip([
            'mpd_nrc_mhrs', 'appr_time', 'bill_time', 'rem_est', 'rem_appr', 'appl_time',
            'avg_time', 'act_time', 'comp_qty', 'print_count', 'open_steps_number',
            'total_steps_number', 'child_card_count', 'est_mhrs', 'serv_hrs', 'barcode_print_count',
            'ac_msn', 'ms_finish_day', 'ms_start_day',
        ]);

        // ── Быстрый парсинг дат без Carbon (нативный DateTime) ───────────────
        // Кэш: один раз определяем формат из первого попавшегося значения каждой колонки
        $detectedFormats = [];
        $parseDate = static function (string $v, bool $dateOnly, string $col) use (&$detectedFormats): ?string {
            $v = trim($v);
            if ($v === '' || $v === '?') {
                return null;
            }
            // Если формат уже определён — используем сразу
            if (isset($detectedFormats[$col])) {
                $dt = \DateTime::createFromFormat($detectedFormats[$col], $v);
                if ($dt !== false) {
                    return $dateOnly ? $dt->format('Y-m-d') : $dt->format('Y-m-d H:i:s');
                }
                unset($detectedFormats[$col]); // значение с другим форматом — определим заново
            }
            // Определяем формат по внешнему виду строки
            $fmts = $dateOnly
                ? ['Y-m-d', 'd-m-Y', 'd/m/Y', 'd.m.Y', 'd-m-Y H:i:s', 'd-m-Y H:i']
                : ['Y-m-d H:i:s', 'Y-m-d H:i', 'd-m-Y H:i:s', 'd-m-Y H:i', 'd/m/Y H:i:s', 'd/m/Y H:i', 'Y-m-d', 'd-m-Y'];
            foreach ($fmts as $fmt) {
                $dt = \DateTime::createFromFormat($fmt, $v);
                $errs = \DateTime::getLastErrors();
                if ($dt !== false && ($errs === false || $errs['warning_count'] === 0)) {
                    $detectedFormats[$col] = $fmt; // кэшируем
                    return $dateOnly ? $dt->format('Y-m-d') : $dt->format('Y-m-d H:i:s');
                }
            }
            return null;
        };

        // ── Flush: нормализация и bulk-insert ────────────────────────────────
        $flush = static function (
            array &$buffer,
            array $indexToDb,
            array $insertColumns,
            array $allDateSet,
            array $dateOnlySet,
            callable $parseDate
        ) use (&$count, $now, $allowedSet, $numericSet, $onProgress, $knownTotal): void {
            if ($buffer === []) {
                return;
            }
            $normalized = [];
            foreach ($buffer as $rawRow) {
                $ordered = array_fill_keys($insertColumns, null);
                $ordered['created_at'] = $now;
                $ordered['updated_at'] = $now;
                foreach ($rawRow as $dbCol => $val) {
                    if (!isset($allowedSet[$dbCol])) {
                        continue;
                    }
                    if ($val === null) {
                        continue;
                    }
                    if (!($val instanceof \DateTimeInterface) && ($val === '' || $val === '?')) {
                        continue;
                    }
                    if (isset($allDateSet[$dbCol])) {
                        // OpenSpout возвращает даты как DateTimeImmutable — используем напрямую
                        if ($val instanceof \DateTimeInterface) {
                            $val = isset($dateOnlySet[$dbCol])
                                ? $val->format('Y-m-d')
                                : $val->format('Y-m-d H:i:s');
                        } else {
                            $val = $parseDate((string) $val, isset($dateOnlySet[$dbCol]), $dbCol);
                        }
                    } elseif ($val instanceof \DateTimeInterface) {
                        // Не датовая колонка, но OpenSpout отдал DateTime — в строку
                        $val = $val->format('Y-m-d H:i:s');
                    } elseif (isset($numericSet[$dbCol])) {
                        // Числовая колонка: нечисловые значения → null
                        $strVal = trim((string) $val);
                        if ($strVal === '' || !is_numeric($strVal)) {
                            continue;
                        }
                        $val = $strVal + 0; // приводим к числу
                    }
                    $ordered[$dbCol] = $val;
                }
                $normalized[] = $ordered;
            }
            DB::table('work_cards')->insert($normalized);
            $count += count($buffer);
            $buffer = [];
            if ($onProgress !== null) {
                $onProgress($count, $knownTotal ?? $count);
            }
        };

        // ── Маппинг: строим один раз ПОСЛЕ чтения заголовков ────────────────
        // Возвращает [columnIndex => dbColumnName] для быстрого доступа по индексу
        $buildIndexMap = function (array $fileHeaders): array {
            $map = $this->workCardsHeaderMap();
            // Нормализуем ключи карты к upper для case-insensitive сравнения
            $mapUpper = [];
            foreach ($map as $fileCol => $dbCol) {
                $mapUpper[strtoupper(trim($fileCol))] = $dbCol;
            }
            $indexMap = [];
            foreach ($fileHeaders as $i => $h) {
                $hUp = strtoupper(trim((string) $h));
                if (isset($mapUpper[$hUp])) {
                    $indexMap[$i] = $mapUpper[$hUp];
                } elseif (isset($allowedSet[$hUp]) || isset($allowedSet[strtolower($hUp)])) {
                    $indexMap[$i] = strtolower($hUp);
                }
            }
            return $indexMap;
        };

        // ── Быстрое построение строки по индексу ─────────────────────────────
        $buildRow = static function (array $indexMap, array $rowValues): array {
            $data = [];
            foreach ($indexMap as $i => $dbCol) {
                $val = isset($rowValues[$i]) ? $rowValues[$i] : null;
                if ($val === null) {
                    continue;
                }
                // DateTime-объекты (OpenSpout) пропускаем как есть — flush обработает
                if (!($val instanceof \DateTimeInterface) && (string) $val === '') {
                    continue;
                }
                $data[$dbCol] = $val;
            }
            return $data;
        };

        if ($ext === 'csv' || $ext === 'txt') {
            $fh = fopen($path, 'r');
            if ($fh === false) {
                return 0;
            }
            $sep = ',';
            $firstLine = fgetcsv($fh, 0, ',');
            if ($firstLine !== false && isset($firstLine[0]) && str_starts_with(trim((string) $firstLine[0]), 'sep=')) {
                $sep = trim(substr(trim((string) $firstLine[0]), 4)) ?: ',';
                $firstLine = fgetcsv($fh, 0, $sep);
            }
            $fileHeaders = $firstLine !== false ? array_map('trim', $firstLine) : [];
            $indexMap = $buildIndexMap($fileHeaders);
            $buffer = [];
            while (($row = fgetcsv($fh, 0, $sep)) !== false) {
                $data = $buildRow($indexMap, $row);
                if ($data !== []) {
                    $buffer[] = $data;
                    if (count($buffer) >= $chunkSize) {
                        $flush($buffer, $indexMap, $insertColumns, $allDateSet, $dateOnlySet, $parseDate);
                    }
                }
            }
            fclose($fh);
            $flush($buffer, $indexMap, $insertColumns, $allDateSet, $dateOnlySet, $parseDate);
        } else {
            $this->importWorkCardsViaOpenspout(
                $path, $chunkSize, $buildIndexMap, $buildRow, $flush,
                $insertColumns, $allDateSet, $dateOnlySet, $parseDate
            );
        }

        return $count;
    }

    /**
     * Быстрый потоковый импорт XLSX через openspout (один проход по файлу).
     */
    private function importWorkCardsViaOpenspout(
        string $path,
        int $chunkSize,
        callable $buildIndexMap,
        callable $buildRow,
        callable $flush,
        array $insertColumns,
        array $allDateSet,
        array $dateOnlySet,
        callable $parseDate
    ): void {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext !== 'xlsx') {
            // XLS — PhpSpreadsheet RowIterator (одна загрузка)
            $this->importWorkCardsViaPhpSpreadsheet(
                $path, $chunkSize, $buildIndexMap, $buildRow, $flush,
                $insertColumns, $allDateSet, $dateOnlySet, $parseDate
            );
            return;
        }

        $reader = new XlsxReader();
        $reader->open($path);
        $indexMap = [];
        $buffer   = [];
        $firstRow = true;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $values = [];
                foreach ($row->getCells() as $cell) {
                    $values[] = $cell->getValue();
                }
                if ($firstRow) {
                    $indexMap = $buildIndexMap(array_map(fn($v) => trim((string) $v), $values));
                    $firstRow = false;
                    continue;
                }
                $data = $buildRow($indexMap, $values);
                if ($data !== []) {
                    $buffer[] = $data;
                    if (count($buffer) >= $chunkSize) {
                        $flush($buffer, $indexMap, $insertColumns, $allDateSet, $dateOnlySet, $parseDate);
                    }
                }
            }
            break;
        }
        $reader->close();
        $flush($buffer, $indexMap, $insertColumns, $allDateSet, $dateOnlySet, $parseDate);
    }

    /**
     * Импорт XLS через PhpSpreadsheet RowIterator (файл загружается один раз).
     */
    private function importWorkCardsViaPhpSpreadsheet(
        string $path,
        int $chunkSize,
        callable $buildIndexMap,
        callable $buildRow,
        callable $flush,
        array $insertColumns,
        array $allDateSet,
        array $dateOnlySet,
        callable $parseDate
    ): void {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $worksheet   = $spreadsheet->getActiveSheet();
        $rowIterator = $worksheet->getRowIterator();

        $indexMap = [];
        $buffer   = [];
        $firstRow = true;

        foreach ($rowIterator as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $values = [];
            foreach ($cellIterator as $cell) {
                $values[] = $cell->getValue();
            }
            if ($firstRow) {
                $indexMap = $buildIndexMap(array_map(fn($v) => trim((string) $v), $values));
                $firstRow = false;
                continue;
            }
            $data = $buildRow($indexMap, $values);
            if ($data !== []) {
                $buffer[] = $data;
                if (count($buffer) >= $chunkSize) {
                    $flush($buffer, $indexMap, $insertColumns, $allDateSet, $dateOnlySet, $parseDate);
                }
            }
        }
        unset($spreadsheet);
        $flush($buffer, $indexMap, $insertColumns, $allDateSet, $dateOnlySet, $parseDate);
    }

    /**
     * Быстрый подсчёт строк данных (без заголовка) для отображения перед импортом.
     */
    private function countWorkCardsRows(string $path, string $ext): int
    {
        if ($ext === 'csv' || $ext === 'txt') {
            $fh = fopen($path, 'r');
            if ($fh === false) {
                return 0;
            }
            $first = fgetcsv($fh, 0, ',');
            if ($first !== false && isset($first[0]) && str_starts_with(trim((string) $first[0]), 'sep=')) {
                fgetcsv($fh, 0, ','); // пропустить sep= и строку заголовка
            }
            $lines = 0;
            while (!feof($fh)) {
                $line = fgetcsv($fh, 0, ',');
                if ($line !== false && $line !== null) {
                    $lines++;
                }
            }
            fclose($fh);
            return max(0, $lines);
        }

        if ($ext === 'xlsx') {
            // Мгновенный способ: читаем атрибут dimension из ZIP-архива XLSX
            // <dimension ref="A1:GX40001"/> — последняя цифра = количество строк
            try {
                $zip = new \ZipArchive();
                if ($zip->open($path) === true) {
                    // Перебираем первый лист (обычно sheet1.xml)
                    for ($i = 0; $i < min($zip->numFiles, 20); $i++) {
                        $name = $zip->getNameIndex($i);
                        if ($name !== false && preg_match('#xl/worksheets/sheet\d+\.xml$#', $name)) {
                            // Читаем только первые 2KB — там точно есть <dimension>
                            $partial = $zip->getFromIndex($i, 2048);
                            $zip->close();
                            if ($partial !== false && preg_match('/<dimension\s+ref="[A-Z]+\d+:[A-Z]+(\d+)"/i', $partial, $m)) {
                                return max(0, (int) $m[1] - 1);
                            }
                            break;
                        }
                    }
                    if ($zip->status === \ZipArchive::ER_OK) {
                        $zip->close();
                    }
                }
            } catch (\Throwable) {
                // fallback ниже
            }
            // Запасной вариант: PhpSpreadsheet listWorksheetInfo (читает только метаданные)
            $r = IOFactory::createReaderForFile($path);
            if (method_exists($r, 'listWorksheetInfo')) {
                $info = $r->listWorksheetInfo($path);
                return max(0, (int) ($info[0]['totalRows'] ?? 0) - 1);
            }
            return 0;
        }

        // XLS — PhpSpreadsheet listWorksheetInfo
        $r = IOFactory::createReaderForFile($path);
        if (method_exists($r, 'listWorksheetInfo')) {
            $info = $r->listWorksheetInfo($path);
            $total = (int) ($info[0]['totalRows'] ?? 0);
            return max(0, $total - 1);
        }
        return 0;
    }

    /** Work cards: маппинг заголовков файла на поля БД */
    private function workCardsHeaderMap(): array
    {
        return [
            'PROJECT' => 'project',
            'PROJECT TYPE' => 'project_type',
            'AIRCRAFT TYPE' => 'aircraft_type',
            'TAIL NUMBER' => 'tail_number',
            'TAIL NUMBER ' => 'tail_number',
            'BAY' => 'bay',
            'WO STATION' => 'wo_station',
            'WORK ORDER' => 'work_order',
            'ZONE' => 'zone',
            'ITEM' => 'item',
            'QUALITY CODE' => 'quality_code',
            'ZONES' => 'zones',
            'STATUS' => 'status',
            'WIP STATUS' => 'wip_status',
            'REASON' => 'reason',
            'SRC. ORDER' => 'src_order',
            'SRC. ZONE' => 'src_zone',
            'SRC. ITEM' => 'src_item',
            'SRC. CUST. CARD' => 'src_cust_card',
            'SRC. OPEN DT.' => 'src_open_dt',
            'DESCRIPTION' => 'description',
            'CORRECTIVE ACTION' => 'corrective_action',
            'OPEN DATE' => 'open_date',
            'CLOSE DATE' => 'close_date',
            'PLANNED START' => 'planned_start',
            'PLANNED FINISH DATE' => 'planned_finish_date',
            'CARD START DATE' => 'card_start_date',
            'CARD FINISH DATE' => 'card_finish_date',
            'MS START DAY' => 'ms_start_day',
            'MS FINISH DAY' => 'ms_finish_day',
            'MS START DATE' => 'ms_start_date',
            'MS FINISH DATE' => 'ms_finish_date',
            'MS DESCIPTION' => 'ms_description',
            'PRIM. SKILL' => 'prim_skill',
            'SKILL CODES' => 'skill_codes',
            'DOT' => 'dot',
            'ATA' => 'ata',
            'CUST. CARD' => 'cust_card',
            'TASK CODE' => 'task_code',
            'ORDER TYPE' => 'order_type',
            'CONTRACT' => 'contract',
            'CONTRACT DESCRIPTION' => 'contract_description',
            'MPD/NRC MHRS' => 'mpd_nrc_mhrs',
            'APPR. TIME' => 'appr_time',
            'BILL. TIME' => 'bill_time',
            'REM. EST.' => 'rem_est',
            'REM. APPR.' => 'rem_appr',
            'APPL. TIME' => 'appl_time',
            'AVG. TIME' => 'avg_time',
            'ACT. TIME' => 'act_time',
            'APP.USER' => 'app_user',
            'AIRCRAFT LOCATION' => 'aircraft_location',
            'MILESTONE' => 'milestone',
            'INDEPENDENT INSPECTOR NUMBER' => 'independent_inspector_number',
            'INSPECTOR' => 'inspector',
            'INSPECTOR NAME' => 'inspector_name',
            'CREATED BY' => 'created_by',
            'CREATED BY NAME' => 'created_by_name',
            'PERFORMED BY EMPLOYEE#' => 'performed_by_employee_number',
            'PERFORMED DATE' => 'performed_date',
            'W/O DEPT' => 'wo_dept',
            'WORK ORDER DEPARTMENT NAME' => 'work_order_department_name',
            'SHOP' => 'shop',
            'SHOP DESCRIPTION' => 'shop_description',
            'DEPARTMENT' => 'department',
            'DEPARTMENT NAME' => 'department_name',
            'APPLICABLE STANDARD' => 'applicable_standard',
            'FORM APPLICABLE STANDARD' => 'form_applicable_standard',
            'FORM NUMBER' => 'form_number',
            'PANEL CODES' => 'panel_codes',
            'COMPONENT NUMBER' => 'component_number',
            'COMP. QTY' => 'comp_qty',
            'SERIAL NUMBER' => 'serial_number',
            'SERVICES' => 'services',
            'PRINT COUNT' => 'print_count',
            'CHECK STATUS ' => 'check_status',
            'CHECK STATUS' => 'check_status',
            'CHECK BY EMPLOYEE NUMBER' => 'check_by_employee_number',
            'CHECK BY EMPLOYEE NAME' => 'check_by_employee_name',
            'CHECK DATE' => 'check_date',
            'DOCUMENTS' => 'documents',
            'MANUFACTURER' => 'manufacturer',
            'ESTIMATOR COMMENT' => 'estimator_comment',
            'REPRESENTATIVE COMMENT' => 'representative_comment',
            'CONTROLLER COMMENT' => 'controller_comment',
            'FINDINGS' => 'findings',
            'CUSTOMER#' => 'customer_number',
            'CUSTOMER' => 'customer',
            'INSPECTION DATE' => 'inspection_date',
            'PART DESCRIPTION' => 'part_description',
            'AUTH. TYPE' => 'auth_type',
            'CONDITION CODE' => 'condition_code',
            'CONDITION' => 'condition',
            'ETOPS ' => 'etops',
            'ETOPS' => 'etops',
            'CRITICAL' => 'critical',
            'ILS ' => 'ils',
            'ILS' => 'ils',
            'RII' => 'rii',
            'CDCCL' => 'cdccl',
            'LEAK C.' => 'leak_c',
            'OPEN' => 'open',
            'CLOSE' => 'close',
            'LUBE' => 'lube',
            'SDR' => 'sdr',
            'STRUCTURAL' => 'structural',
            'ENGINE RUN' => 'engine_run',
            'ON FLOOR' => 'on_floor',
            'MAJOR' => 'major',
            'ALTER' => 'alter',
            'CPCP' => 'cpcp',
            'LOGON' => 'logon',
            'ONLY ASSIGNED' => 'only_assigned',
            'AIRCRAFT' => 'aircraft',
            'GQAR' => 'gqar',
            'BILLABLE' => 'billable',
            'LOCK' => 'lock',
            'OPEN STEPS#' => 'open_steps_number',
            'TOTAL STEPS#' => 'total_steps_number',
            'MAINT.START DATE' => 'maint_start_date',
            'CHILD CARD COUNT' => 'child_card_count',
            'GROUP CODE' => 'group_code',
            'POCKET#' => 'pocket_number',
            'PIN POCKET' => 'pin_pocket',
            'HANDOVER' => 'handover',
            'INCOMING DEFECT' => 'incoming_defect',
            'MANDATORY' => 'mandatory',
            'EST. MHRS' => 'est_mhrs',
            'DMI DUE DATE' => 'dmi_due_date',
            'DMI REFERENCE' => 'dmi_reference',
            'CMM REFERENCE' => 'cmm_reference',
            'EXT NO' => 'ext_no',
            'AC MSN' => 'ac_msn',
            'SERV. HRS.' => 'serv_hrs',
            'BARCODE PRINT COUNT' => 'barcode_print_count',
            'COMPLETED TIME (UTC)' => 'completed_time_utc',
        ];
    }

    public function eefRegistry(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000], true)) {
            $perPage = 50;
        }
        $items = InspectionEefRegistry::orderBy('id')->paginate($perPage)->withQueryString();
        return view('Modules.Reliability.inspection_settings.eef_registry', compact('items', 'perPage'));
    }

    public function eefRegistryUpload(Request $request)
    {
        set_time_limit(0);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '768M');
        }
        $request->validate(['file' => 'required|file|max:204800']);
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt', 'xlsx', 'xlsm', 'xls'], true)) {
            if ($request->header('Accept') === 'application/x-ndjson') {
                return response()->json(['error' => 'Invalid file type. Use CSV, TXT, XLSX, XLSM or XLS.'], 422);
            }
            return redirect()->route('modules.reliability.settings.inspection.eef-registry')->with('error', 'Invalid file type. Use CSV, TXT, XLSX, XLSM or XLS.');
        }
        if ($request->header('X-EEF-Stream') === '1') {
            return $this->eefRegistryUploadStream($file);
        }
        $count = $this->importEefRegistryChunked($file, null, null);
        return redirect()->route('modules.reliability.settings.inspection.eef-registry')->with('success', "Imported records: {$count}");
    }

    /** Подсчёт строк файла EEF без импорта (JSON). */
    public function eefRegistryCount(Request $request): \Illuminate\Http\JsonResponse
    {
        set_time_limit(120);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '512M');
        }
        $request->validate(['file' => 'required|file|max:204800']);
        try {
            $file = $request->file('file');
            $total = $this->countEefRegistryRows($file->getRealPath(), strtolower($file->getClientOriginalExtension()));
            return response()->json(['total' => $total]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Импорт EEF с диска сервера (NDJSON progress). */
    public function eefRegistryImportLocal(Request $request): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        set_time_limit(0);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '768M');
        }
        $rawInput = trim((string) ($request->input('path') ?? $request->input('local_path') ?? ''));
        if ($rawInput === '') {
            return response()->json(['error' => 'Путь не указан'], 422);
        }
        $relPath = ltrim(str_replace(['..', '\\'], ['', '/'], $rawInput), '/');
        $absPath = base_path($relPath);
        if (!file_exists($absPath)) {
            return response()->json(['error' => "Файл не найден: $relPath"], 404);
        }
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xlsm', 'xls', 'csv', 'txt'], true)) {
            return response()->json(['error' => 'Недопустимый тип файла'], 422);
        }

        $send = function (array $data) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        };
        $countOnly = $request->header('X-EEF-Count-Only') === '1';

        return new StreamedResponse(function () use ($absPath, $ext, $send, $countOnly) {
            try {
                $send(['total' => 0]); // EEF: не считаем заранее
                if ($countOnly) {
                    return;
                }
                $count = $this->importEefRegistryChunked($absPath, function (int $processed, int $tot) use ($send) {
                    $send(['processed' => $processed, 'total' => $tot]);
                }, null);
                $send(['done' => true, 'count' => $count]);
            } catch (\Throwable $e) {
                report($e);
                $msg = strlen($e->getMessage()) > 300 ? substr($e->getMessage(), 0, 300) . '…' : $e->getMessage();
                $send(['error' => $msg]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function eefRegistryUploadStream($file): StreamedResponse
    {
        $send = function (array $data) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        };
        return new StreamedResponse(function () use ($file, $send) {
            try {
                $send(['total' => 0]); // EEF: не считаем заранее (XLSX часто даёт завышенное число)
                $count = $this->importEefRegistryChunked($file, function (int $processed, int $tot) use ($send) {
                    $send(['processed' => $processed, 'total' => $tot]);
                }, null);
                $send(['done' => true, 'count' => $count]);
            } catch (\Throwable $e) {
                report($e);
                $msg = strlen($e->getMessage()) > 300 ? substr($e->getMessage(), 0, 300) . '…' : $e->getMessage();
                $send(['error' => $msg]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Потоковый импорт EEF: bulk insert по 500 строк (Excel через PhpSpreadsheet).
     * $file — UploadedFile или путь (string).
     */
    private function importEefRegistryChunked($file, ?callable $onProgress = null, ?int $knownTotal = null): int
    {
        $path = is_string($file) ? $file : $file->getRealPath();
        $ext = is_string($file) ? strtolower(pathinfo($file, PATHINFO_EXTENSION)) : strtolower($file->getClientOriginalExtension());
        $chunkSize = 500;
        $count = 0;
        $now = now()->toDateTimeString();
        $tableColumns = Schema::getColumnListing('eef_registry');
        $allowedSet = array_flip(array_diff($tableColumns, ['id']));
        $insertColumns = array_merge(array_keys($allowedSet), ['created_at', 'updated_at']);
        $dateCols = ['open_date'];
        $numericCols = array_flip(['man_hours']);

        $buildIndexMap = function (array $fileHeaders): array {
            $map = $this->eefRegistryHeaderMap();
            $normalize = fn (string $s): string => $this->normalizeEefFileHeader($s);
            $mapNorm = [];
            foreach ($map as $fileCol => $dbCol) {
                $key = $normalize($fileCol);
                $mapNorm[$key] = $dbCol;
            }
            $tableColumns = Schema::getColumnListing('eef_registry');
            $allowedSet = array_flip(array_diff($tableColumns, ['id']));
            $indexMap = [];
            foreach ($fileHeaders as $i => $h) {
                $hNorm = $normalize((string) $h);
                if (isset($mapNorm[$hNorm])) {
                    $indexMap[$i] = $mapNorm[$hNorm];
                } elseif ($hNorm !== '' && isset($allowedSet[strtolower(str_replace(' ', '_', $hNorm))])) {
                    $indexMap[$i] = strtolower(str_replace(' ', '_', $hNorm));
                } elseif (isset($allowedSet[strtolower($hNorm)])) {
                    $indexMap[$i] = strtolower($hNorm);
                }
            }
            return $indexMap;
        };

        $buildRow = static function (array $indexMap, array $rowValues): array {
            $data = [];
            foreach ($indexMap as $i => $dbCol) {
                $val = $rowValues[$i] ?? null;
                if ($val === null || (is_string($val) && trim($val) === '')) {
                    continue;
                }
                $data[$dbCol] = $val instanceof \DateTimeInterface ? $val->format('Y-m-d') : $val;
            }
            return $data;
        };

        $flush = static function (array &$buffer, array $indexMap, array $insertColumns, array $dateCols, array $numericCols) use (&$count, $now, $allowedSet, $onProgress, $knownTotal): void {
            if ($buffer === []) {
                return;
            }
            $normalized = [];
            foreach ($buffer as $rawRow) {
                $ordered = array_fill_keys($insertColumns, null);
                $ordered['created_at'] = $now;
                $ordered['updated_at'] = $now;
                foreach ($rawRow as $dbCol => $val) {
                    if (!isset($allowedSet[$dbCol])) {
                        continue;
                    }
                    if ($val === null || $val === '') {
                        continue;
                    }
                    if ($val instanceof \DateTimeInterface) {
                        $val = $val->format('Y-m-d');
                    } elseif (isset($numericCols[$dbCol]) && !is_numeric(trim((string) $val))) {
                        continue;
                    } elseif (in_array($dbCol, $dateCols, true)) {
                        $valStr = trim((string) $val);
                        if ($valStr === '' || $valStr === '-' || $valStr === '0') {
                            $val = null;
                        } elseif (is_string($val) && $valStr !== '') {
                            try {
                                $dt = \DateTime::createFromFormat('Y-m-d', $valStr) ?: \DateTime::createFromFormat('d-m-Y', $valStr) ?: \DateTime::createFromFormat('d/m/Y', $valStr);
                                if ($dt) {
                                    $val = $dt->format('Y-m-d');
                                } else {
                                    $val = null;
                                }
                            } catch (\Throwable) {
                                $val = null;
                            }
                        } elseif (is_numeric($val) && $val > 0) {
                            try {
                                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $val);
                                $val = $dt->format('Y-m-d');
                            } catch (\Throwable) {
                                $val = null;
                            }
                        } else {
                            $val = null;
                        }
                    }
                    if ($val !== null) {
                        if (isset($numericCols[$dbCol])) {
                            $ordered[$dbCol] = $val + 0;
                        } else {
                            $len = is_string($val) ? strlen($val) : 0;
                            $max = ['subject' => 500, 'link' => 500, 'link_path' => 500, 'inspection_source_task' => 500, 'oem_communication_reference' => 500, 'latest_processing' => 255, 'location' => 255, 'assigned_engineering_engineer' => 255, 'open_continuation_raised_by_production_dates' => 255, 'answer_provided_by_engineering_dates' => 255, 'gaes_eo' => 255, 'manual_limits_out_within' => 255, 'backup_engineer' => 255, 'customer_name' => 255, 'eef_number' => 100, 'nrc_number' => 100, 'ac_type' => 100, 'ata' => 50, 'project_no' => 100, 'eef_status' => 100, 'eef_priority' => 100, 'project_status' => 100, 'project_status2' => 100, 'project_status3' => 100, 'rc_number' => 100, 'chargeable_to_customer' => 50, 'eef_with' => 100, 'remarks' => 65535, 'standard_remarks_on_current_progress' => 65535, 'latest_comments_short_answer' => 65535][$dbCol] ?? 255;
                            $ordered[$dbCol] = ($len > $max) ? substr((string) $val, 0, $max) : $val;
                        }
                    }
                }
                $normalized[] = $ordered;
            }
            DB::table('eef_registry')->insert($normalized);
            $count += count($buffer);
            $buffer = [];
            if ($onProgress !== null) {
                $onProgress($count, $knownTotal ?? $count);
            }
        };

        if ($ext === 'csv' || $ext === 'txt') {
            $fh = fopen($path, 'r');
            if ($fh === false) {
                return 0;
            }
            $sep = ',';
            $firstLine = fgetcsv($fh, 0, ',');
            if ($firstLine !== false && isset($firstLine[0]) && str_starts_with(trim((string) $firstLine[0]), 'sep=')) {
                $sep = trim(substr(trim((string) $firstLine[0]), 4)) ?: ',';
                $firstLine = fgetcsv($fh, 0, $sep);
            }
            $fileHeaders = $firstLine !== false ? array_map('trim', $firstLine) : [];
            $indexMap = $buildIndexMap($fileHeaders);
            $buffer = [];
            while (($row = fgetcsv($fh, 0, $sep)) !== false) {
                $data = $buildRow($indexMap, $row);
                if ($data !== []) {
                    $buffer[] = $data;
                    if (count($buffer) >= $chunkSize) {
                        $flush($buffer, $indexMap, $insertColumns, $dateCols, $numericCols);
                    }
                }
            }
            fclose($fh);
            $flush($buffer, $indexMap, $insertColumns, $dateCols, $numericCols);
        } else {
            // XLS/XLSX/XLSM: один путь — PhpSpreadsheet (лист «EEF Registry» со строкой заголовков 3, баннер в 1–2; старый .xlsx — «Registry» строка 1)
            $this->importEefRegistryViaPhpSpreadsheet($path, $chunkSize, $buildIndexMap, $buildRow, $flush, $insertColumns, $dateCols, $numericCols);
        }
        return $count;
    }

    /** Нормализация заголовка колонки EEF для сопоставления с маппингом (Excel/CSV). */
    private function normalizeEefFileHeader(string $s): string
    {
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s);
        $s = str_replace(["\r", "\n", "\t"], ' ', $s);
        $s = preg_replace('/[\/\\\\]+/', ' ', $s);
        $s = preg_replace('/[.,;:?]+/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', trim($s));

        return strtoupper($s);
    }

    /** Одна строка листа как плотный массив (для согласованности индексов с маппингом). */
    private function readEefSheetRowAsArray(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row): array
    {
        $highestCol = $sheet->getHighestDataColumn($row);
        if ($highestCol === 'A') {
            $cell = $sheet->getCell('A' . $row);
            if ($cell->getValue() === null || $cell->getValue() === '') {
                return [];
            }
        }
        $lastIdx = Coordinate::columnIndexFromString($highestCol);
        $vals = [];
        for ($i = 1; $i <= $lastIdx; $i++) {
            $coord = Coordinate::stringFromColumnIndex($i);
            $v = $sheet->getCell($coord . $row)->getCalculatedValue();
            if ($v instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                $v = $v->getPlainText();
            }
            $vals[] = $v;
        }

        return $vals;
    }

    /** Сколько известных колонок EEF распознано в строке (для поиска строки заголовков). */
    private function scoreEefHeaderRowForSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row): int
    {
        $fileHeaders = $this->readEefSheetRowAsArray($sheet, $row);
        if ($fileHeaders === []) {
            return 0;
        }
        $map = $this->eefRegistryHeaderMap();
        $mapNorm = [];
        foreach ($map as $fileCol => $dbCol) {
            $mapNorm[$this->normalizeEefFileHeader($fileCol)] = $dbCol;
        }
        $count = 0;
        foreach ($fileHeaders as $h) {
            $hNorm = $this->normalizeEefFileHeader((string) $h);
            if ($hNorm === '' || $hNorm === '0') {
                continue;
            }
            if (isset($mapNorm[$hNorm])) {
                $count++;
            }
        }

        return $count;
    }

    /** Строка с максимальным числом распознанных заголовков (строки 1–80). */
    private function findBestEefHeaderRowInSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): ?int
    {
        $maxRow = min(80, max(1, $sheet->getHighestRow()));
        $bestRow = null;
        $bestScore = 0;
        for ($r = 1; $r <= $maxRow; $r++) {
            $sc = $this->scoreEefHeaderRowForSheet($sheet, $r);
            if ($sc > $bestScore) {
                $bestScore = $sc;
                $bestRow = $r;
            }
        }

        return $bestScore >= 2 ? $bestRow : null;
    }

    /**
     * Выбор листа и строки заголовков: приоритет у листа с наибольшим числом совпадений
     * (полный реестр на «EEF Registry» со строкой 3, краткий GAES — «Work Card Inquiry» строка 1).
     *
     * @return array{0: \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet, 1: int}
     */
    private function resolveEefImportSheetAndHeaderRow(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): array
    {
        $bestSheet = null;
        $bestScore = -1;
        $bestHeaderRow = 1;
        foreach ($spreadsheet->getAllSheets() as $ws) {
            $row = $this->findBestEefHeaderRowInSheet($ws);
            if ($row === null) {
                continue;
            }
            $sc = $this->scoreEefHeaderRowForSheet($ws, $row);
            if ($sc > $bestScore) {
                $bestScore = $sc;
                $bestSheet = $ws;
                $bestHeaderRow = $row;
            }
        }
        if ($bestSheet === null || $bestScore < 2) {
            $sheet = $spreadsheet->getActiveSheet();
            $row = $this->findBestEefHeaderRowInSheet($sheet) ?? 1;

            return [$sheet, $row];
        }

        return [$bestSheet, $bestHeaderRow];
    }

    /** Импорт EEF через PhpSpreadsheet: авто-выбор листа и строки заголовков. */
    private function importEefRegistryViaPhpSpreadsheet(
        string $path,
        int $chunkSize,
        callable $buildIndexMap,
        callable $buildRow,
        callable $flush,
        array $insertColumns,
        array $dateCols,
        array $numericCols
    ): void {
        $maxConsecutiveEmpty = 200;
        $maxRowsWithoutData = 5000;
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        [$sheet, $headerRow] = $this->resolveEefImportSheetAndHeaderRow($spreadsheet);
        $headerVals = $this->readEefSheetRowAsArray($sheet, $headerRow);
        $fileHeaders = array_map(function ($v) {
            if ($v instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                $v = $v->getPlainText();
            }

            return trim((string) $v);
        }, $headerVals);
        $indexMap = $buildIndexMap($fileHeaders);
        $buffer = [];
        $consecutiveEmpty = 0;
        $hasData = false;
        $rowsRead = 0;
        $maxRow = min(max($headerRow + 1, $sheet->getHighestRow()), 100000);
        for ($r = $headerRow + 1; $r <= $maxRow; $r++) {
            $vals = $this->readEefSheetRowAsArray($sheet, $r);
            $rowsRead++;
            $data = $buildRow($indexMap, $vals);
            $eefVal = $data['eef_number'] ?? null;
            $eefStr = $eefVal !== null ? trim((string) $eefVal) : '';
            $hasEefNumber = $data !== [] && $eefStr !== '' && $eefStr !== '0' && $eefStr !== '-';
            if ($data !== [] && $hasEefNumber) {
                $hasData = true;
                $consecutiveEmpty = 0;
                $buffer[] = $data;
                if (count($buffer) >= $chunkSize) {
                    $flush($buffer, $indexMap, $insertColumns, $dateCols, $numericCols);
                }
            } else {
                if ($hasData && ++$consecutiveEmpty >= $maxConsecutiveEmpty) {
                    break;
                }
                if (!$hasData && $rowsRead >= $maxRowsWithoutData) {
                    break;
                }
            }
        }
        unset($spreadsheet, $sheet);
        $flush($buffer, $indexMap, $insertColumns, $dateCols, $numericCols);
    }

    private function countEefRegistryRows(string $path, string $ext): int
    {
        if ($ext === 'csv' || $ext === 'txt') {
            $fh = fopen($path, 'r');
            if ($fh === false) {
                return 0;
            }
            $first = fgetcsv($fh, 0, ',');
            if ($first !== false && isset($first[0]) && str_starts_with(trim((string) $first[0]), 'sep=')) {
                fgetcsv($fh, 0, ',');
            }
            $n = 0;
            while (fgetcsv($fh, 0, ',') !== false) {
                $n++;
            }
            fclose($fh);
            return $n;
        }
        if ($ext === 'xlsx' || $ext === 'xlsm') {
            // Быстрый подсчёт без OpenSpout: потоково читаем sheet XML, считаем строки с ячейками (<row>...</row> с <c r=" внутри)
            try {
                $zip = new \ZipArchive();
                if ($zip->open($path) !== true) {
                    return 0;
                }
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if ($name === false || !preg_match('#xl/worksheets/sheet\d+\.xml$#', $name)) {
                        continue;
                    }
                    $stream = $zip->getStream($name);
                    if ($stream === false) {
                        $zip->close();
                        return 0;
                    }
                    $dataRowCount = 0;
                    $buffer = '';
                    $inRow = false;
                    $rowHasCell = false;
                    while (!feof($stream)) {
                        $buffer .= fread($stream, 8192);
                        // Обрабатываем только полные фрагменты до последнего </row>
                        $lastRow = strrpos($buffer, '</row>');
                        if ($lastRow !== false) {
                            $chunk = substr($buffer, 0, $lastRow + 6);
                            $buffer = substr($buffer, $lastRow + 6);
                            $pos = 0;
                            while (($r = strpos($chunk, '<row ', $pos)) !== false) {
                                $end = strpos($chunk, '</row>', $r);
                                if ($end === false) {
                                    break;
                                }
                                $rowContent = substr($chunk, $r, $end - $r + 6);
                                // Считаем только строки с непустым значением (<v>текст</v> или <v>число</v>)
                                if (preg_match('/<v>[^<]+<\/v>/', $rowContent)) {
                                    $dataRowCount++;
                                }
                                $pos = $end + 6;
                            }
                        }
                    }
                    fclose($stream);
                    $zip->close();
                    return max(0, $dataRowCount - 1); // минус заголовок
                }
                $zip->close();
            } catch (\Throwable) {
                // fallback
            }
            return 0;
        }
        if ($ext === 'xls') {
            $r = IOFactory::createReaderForFile($path);
            if (method_exists($r, 'listWorksheetInfo')) {
                $info = $r->listWorksheetInfo($path);
                return max(0, (int) ($info[0]['totalRows'] ?? 0) - 1);
            }
        }
        return 0;
    }

    /** EEF registry: маппинг заголовков файла на поля БД.
     * Форматы: GAES 7 колонок; полный реестр (EEF.xlsx); старый ENGINEERING ENQUIRY FORM REGISTER.xlsx; выгрузка .xlsm (EEF No в строке 3). */
    private function eefRegistryHeaderMap(): array
    {
        return [
            // Формат 1: GAES / Work Card Inquiry, 7 колонок
            'GAES WO#' => 'eef_number',
            'GAES Source Card#' => 'inspection_source_task',
            'CUST. CARD' => 'customer_name',
            'PROJECT' => 'project_no',
            'ORDER TYPE' => 'project_status',
            'DESCRIPTION' => 'subject',
            'CORRECTIVE ACTION' => 'remarks',
            // Полный реестр (общие названия)
            'EEF Number' => 'eef_number',
            '1. EEF Number' => 'eef_number',
            'EEF No' => 'eef_number',
            'EEF NO' => 'eef_number',
            'NRC Number' => 'nrc_number',
            'NRC No' => 'nrc_number',
            'NRC No.' => 'nrc_number',
            'AC Type' => 'ac_type',
            'ATA' => 'ata',
            'Project No.' => 'project_no',
            'Project No' => 'project_no',
            'Subject' => 'subject',
            'Remarks' => 'remarks',
            'Location' => 'location',
            'EEF Status' => 'eef_status',
            'Link' => 'link',
            'Link Path' => 'link_path',
            'Link' . "\n" . 'Path' => 'link_path',
            'Link' . "\r\n" . 'Path' => 'link_path',
            'Man Hours' => 'man_hours',
            'MHRs' => 'man_hours',
            'Total Engineering Man Hours' => 'man_hours',
            'Chargeable to Customer?' => 'chargeable_to_customer',
            'CHARGEABLE' => 'chargeable_to_customer',
            'Customer Name' => 'customer_name',
            'Customer' => 'customer_name',
            'Customer' . "\n" . 'Name' => 'customer_name',
            'Customer' . "\r\n" . 'Name' => 'customer_name',
            'Inspection Source Task' => 'inspection_source_task',
            'RC#' => 'rc_number',
            'Open Date' => 'open_date',
            'Assigned Engineering Engineer' => 'assigned_engineering_engineer',
            'Engineer' => 'assigned_engineering_engineer',
            'OPEN/Continuation Raised by Production Dates' => 'open_continuation_raised_by_production_dates',
            'Continuation Raised by Production Dates' => 'open_continuation_raised_by_production_dates',
            'Answer provided by Engineering  Dates' => 'answer_provided_by_engineering_dates',
            'Answer provided by Engineering Dates' => 'answer_provided_by_engineering_dates',
            'OEM Communication Reference' => 'oem_communication_reference',
            'GAES EO' => 'gaes_eo',
            'VDG EO' => 'gaes_eo',
            'Manual limits (OUT / WITHIN)' => 'manual_limits_out_within',
            'Manual limits' . "\n" . '(OUT / WITHIN)' => 'manual_limits_out_within',
            'Back-up Engineer' => 'backup_engineer',
            'Project Status' => 'project_status',
            'EEF Priority' => 'eef_priority',
            'Latest Processing' => 'latest_processing',
            'Latest Processing Date' => 'latest_processing',
            'Project Status2' => 'project_status2',
            'Project Status3' => 'project_status3',
            'EEF with' => 'eef_with',
            'Standard Remarks On Current Progress' => 'standard_remarks_on_current_progress',
            'Latest Comments / Short Answer' => 'latest_comments_short_answer',
        ];
    }

    public function workCardMaterials(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000], true)) {
            $perPage = 50;
        }
        $items = InspectionWorkCardMaterial::orderBy('id')->paginate($perPage)->withQueryString();
        return view('Modules.Reliability.inspection_settings.work_card_materials', compact('items', 'perPage'));
    }

    /** IC_0097 Material Data – колонки как в CSV. Дубликат "ORDER #" маппится: 1-й → order_number, 2-й → order_number_2 */
    private static function workCardMaterialsHeaderMap(): array
    {
        return [
            'PROJECT #' => 'project_number',
            'WORK ORDER #' => 'work_order_number',
            'ZONE #' => 'zone_number',
            'ITEM #' => 'item_number',
            'WIP STATUS' => 'wip_status',
            'CARD DESCRIPTION' => 'card_description',
            'CUSTOMER WORK CARD' => 'customer_work_card',
            'SOURCE CARD #' => 'source_card_number',
            'SOURCE CUSTOMER CARD' => 'source_customer_card',
            'TAIL #' => 'tail_number',
            'EST. TIME' => 'est_time',
            'TAG #' => 'tag_number',
            'PART #' => 'part_number',
            'DESCRIPTION' => 'description',
            'OEM SPEC. #' => 'oem_spec_number',
            'GROUP CODE' => 'group_code',
            'EXPIRE DT.' => 'expire_dt',
            'CSP' => 'csp',
            'ORDER #' => 'order_number',
            'REQ. DT.' => 'req_dt',
            'REQ. DUE DT.' => 'req_due_dt',
            'REQ. QTY.' => 'req_qty',
            'REQ. LINE INTERNAL COMMENT' => 'req_line_internal_comment',
            'LOCATION' => 'location',
            'ORDER DT.' => 'order_dt',
            'ORDER DUE DT.' => 'order_due_dt',
            'ORDER QTY.' => 'order_qty',
            'RECEIPT DT.' => 'receipt_dt',
            'WAYBILL' => 'waybill',
            'ETA DT.' => 'eta_dt',
            'STATUS' => 'status',
            'REASON' => 'reason',
            'ALLOC. QTY.' => 'alloc_qty',
            'UNIT COST' => 'unit_cost',
            'ITEM LIST PRICE' => 'item_list_price',
            'ORDER UNIT COST' => 'order_unit_cost',
            'CURRENCY' => 'currency',
        ];
    }

    /** Для заголовков-дубликатов: первый "ORDER #" → order_number, второй → order_number_2 */
    private static function workCardMaterialsDuplicateHeaders(): array
    {
        return ['ORDER #' => ['order_number', 'order_number_2']];
    }

    /** Строит маппинг индекс колонки → поле БД с учётом дубликатов заголовков */
    private function workCardMaterialsIndexToKey(array $headers): array
    {
        $map = self::workCardMaterialsHeaderMap();
        $dups = self::workCardMaterialsDuplicateHeaders();
        $counters = [];
        $indexToKey = [];
        foreach ($headers as $i => $h) {
            if (isset($dups[$h])) {
                $arr = $dups[$h];
                $idx = $counters[$h] ?? 0;
                $counters[$h] = $idx + 1;
                $indexToKey[$i] = $arr[$idx] ?? null;
            } else {
                $indexToKey[$i] = $map[$h] ?? null;
            }
        }
        return $indexToKey;
    }

    public function workCardMaterialsUpload(Request $request)
    {
        set_time_limit(0);
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '768M');
        }
        $request->validate(['file' => 'required|file|max:204800']);
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt', 'xlsx', 'xls'], true)) {
            return redirect()->route('modules.reliability.settings.inspection.work-card-materials')->with('error', 'Invalid file type. Use CSV, TXT, XLSX or XLS.');
        }
        if ($request->header('X-WCM-Stream') === '1') {
            return $this->workCardMaterialsUploadStream($file);
        }
        try {
            $count = $this->importWorkCardMaterialsChunked($file, null, null);
            return redirect()->route('modules.reliability.settings.inspection.work-card-materials')->with('success', "Imported records: {$count}");
        } catch (\Throwable $e) {
            report($e);
            $msg = strlen($e->getMessage()) > 200 ? substr($e->getMessage(), 0, 200) . '…' : $e->getMessage();
            return redirect()->route('modules.reliability.settings.inspection.work-card-materials')->with('error', 'Upload error: ' . $msg);
        }
    }

    private function workCardMaterialsUploadStream($file): StreamedResponse
    {
        $send = function (array $data) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        };
        return new StreamedResponse(function () use ($file, $send) {
            try {
                $count = $this->importWorkCardMaterialsChunked(
                    $file,
                    function (int $processed, int $total) use ($send) {
                        $send(['processed' => $processed, 'total' => $total]);
                    },
                    null
                );
                $send(['done' => true, 'count' => $count]);
            } catch (\Throwable $e) {
                report($e);
                $msg = strlen($e->getMessage()) > 300 ? substr($e->getMessage(), 0, 300) . '…' : $e->getMessage();
                $send(['error' => $msg]);
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Импорт Work card materials пачками (CSV построчно, XLSX через OpenSpout или PhpSpreadsheet).
     * @param callable(int,int)|null $onProgress  (processed, total) total может быть 0 для CSV
     */
    private function importWorkCardMaterialsChunked($file, ?callable $onProgress = null, ?int $knownTotal = null): int
    {
        $path = is_string($file) ? $file : $file->getRealPath();
        $ext = is_string($file) ? strtolower(pathinfo($file, PATHINFO_EXTENSION)) : strtolower($file->getClientOriginalExtension());
        $chunkSize = 500;
        $count = 0;
        $now = now()->toDateTimeString();

        if ($ext === 'csv' || $ext === 'txt') {
            $fh = fopen($path, 'r');
            if ($fh === false) {
                throw new \RuntimeException('Cannot open file.');
            }
            $firstLine = fgetcsv($fh, 0, ',');
            if ($firstLine !== false && isset($firstLine[0]) && str_starts_with(trim((string) $firstLine[0]), 'sep=')) {
                $firstLine = fgetcsv($fh, 0, ',');
            }
            $headers = $firstLine !== false ? array_map('trim', $firstLine) : [];
            $indexToKey = $this->workCardMaterialsIndexToKey($headers);
            $buffer = [];
            while (($row = fgetcsv($fh, 0, ',')) !== false) {
                $data = [];
                foreach ($headers as $i => $h) {
                    $key = $indexToKey[$i] ?? null;
                    if ($key !== null && isset($row[$i]) && (string) $row[$i] !== '') {
                        $data[$key] = $this->castMaterialValue($key, trim((string) $row[$i]));
                    }
                }
                if (!empty($data)) {
                    $data['created_at'] = $now;
                    $data['updated_at'] = $now;
                    $buffer[] = $data;
                    if (count($buffer) >= $chunkSize) {
                        DB::table('work_card_materials')->insert($buffer);
                        $count += count($buffer);
                        $buffer = [];
                        if ($onProgress !== null) {
                            $onProgress($count, $knownTotal ?? 0);
                        }
                    }
                }
            }
            fclose($fh);
            if (!empty($buffer)) {
                DB::table('work_card_materials')->insert($buffer);
                $count += count($buffer);
            }
            if ($onProgress !== null) {
                $onProgress($count, $knownTotal ?? 0);
            }
            return $count;
        }

        // XLSX / XLS: потоково через OpenSpout для xlsx, иначе PhpSpreadsheet
        if ($ext === 'xlsx') {
            $reader = new XlsxReader();
            $reader->open($path);
            $headers = [];
            $indexToKey = [];
            $buffer = [];
            $first = true;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $vals = [];
                    foreach ($row->getCells() as $colIndex => $cell) {
                        $vals[$colIndex] = $cell->getValue();
                    }
                    if ($first) {
                        $headers = array_map('trim', array_map(fn($v) => (string) $v, $vals));
                        $indexToKey = $this->workCardMaterialsIndexToKey($headers);
                        $first = false;
                        continue;
                    }
                    $data = [];
                    foreach ($headers as $i => $h) {
                        $key = $indexToKey[$i] ?? null;
                        if ($key !== null && isset($vals[$i]) && (string) $vals[$i] !== '') {
                            $data[$key] = $this->castMaterialValue($key, (string) $vals[$i]);
                        }
                    }
                    if (!empty($data)) {
                        $data['created_at'] = $now;
                        $data['updated_at'] = $now;
                        $buffer[] = $data;
                        if (count($buffer) >= $chunkSize) {
                            DB::table('work_card_materials')->insert($buffer);
                            $count += count($buffer);
                            $buffer = [];
                            if ($onProgress !== null) {
                                $onProgress($count, $knownTotal ?? 0);
                            }
                        }
                    }
                }
                break;
            }
            $reader->close();
            if (!empty($buffer)) {
                DB::table('work_card_materials')->insert($buffer);
                $count += count($buffer);
            }
            if ($onProgress !== null) {
                $onProgress($count, $knownTotal ?? 0);
            }
            return $count;
        }

        // XLS: PhpSpreadsheet (файлы обычно меньше)
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $headers = array_map('trim', (array) array_shift($rows));
        $indexToKey = $this->workCardMaterialsIndexToKey($headers);
        $buffer = [];
        foreach ($rows as $row) {
            $data = [];
            foreach ($headers as $i => $h) {
                $key = $indexToKey[$i] ?? null;
                if ($key !== null && isset($row[$i]) && (string) $row[$i] !== '') {
                    $data[$key] = $this->castMaterialValue($key, (string) $row[$i]);
                }
            }
            if (!empty($data)) {
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                $buffer[] = $data;
                if (count($buffer) >= $chunkSize) {
                    DB::table('work_card_materials')->insert($buffer);
                    $count += count($buffer);
                    $buffer = [];
                    if ($onProgress !== null) {
                        $onProgress($count, $knownTotal ?? 0);
                    }
                }
            }
        }
        if (!empty($buffer)) {
            DB::table('work_card_materials')->insert($buffer);
            $count += count($buffer);
        }
        if ($onProgress !== null) {
            $onProgress($count, $knownTotal ?? 0);
        }
        unset($spreadsheet, $sheet);
        return $count;
    }

    private function castMaterialValue(string $key, string $value): mixed
    {
        if (in_array($key, ['req_dt', 'req_due_dt', 'order_dt', 'order_due_dt', 'receipt_dt', 'eta_dt', 'expire_dt'])) {
            $value = preg_replace('/\.0+$/', '', $value);
            return $value ?: null;
        }
        if (in_array($key, ['est_time', 'req_qty', 'order_qty', 'alloc_qty', 'unit_cost', 'item_list_price', 'order_unit_cost'])) {
            return is_numeric(str_replace(',', '.', $value)) ? (float)str_replace(',', '.', $value) : null;
        }
        return $value ?: null;
    }

    public function sourceCardRefs(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000], true)) {
            $perPage = 50;
        }
        $items = InspectionSourceCardRef::orderBy('id')->paginate($perPage)->withQueryString();
        return view('Modules.Reliability.inspection_settings.source_card_refs', compact('items', 'perPage'));
    }

    public function sourceCardRefsUpload(Request $request)
    {
        set_time_limit(0);
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:51200']);
        $count = $this->importFromFile($request->file('file'), [
            'code' => 'code',
            'name' => 'name',
        ], function (array $row) {
            InspectionSourceCardRef::create($row);
        });
        return redirect()->route('modules.reliability.settings.inspection.source-card-refs')->with('success', "Imported records: {$count}");
    }

    public function caseAnalyses(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000], true)) {
            $perPage = 50;
        }
        $items = InspectionCaseAnalysis::with('workCard')->orderBy('id')->paginate($perPage)->withQueryString();
        return view('Modules.Reliability.inspection_settings.case_analyses', compact('items', 'perPage'));
    }

    public function caseAnalysesUpload(Request $request)
    {
        set_time_limit(0);
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:51200',
        ]);
        $count = $this->importFromFile($request->file('file'), [
            'work_card_id' => 'work_card_id',
            'tc_number' => 'tc_number',
            'file_path' => 'file_path',
            'file_name' => 'file_name',
            'is_critical' => 'is_critical',
            'remarks' => 'remarks',
        ], function (array $row) {
            if (isset($row['is_critical'])) {
                $row['is_critical'] = in_array(strtolower((string)$row['is_critical']), ['1', 'true', 'yes', 'да'], true);
            }
            InspectionCaseAnalysis::create($row);
        });
        return redirect()->route('modules.reliability.settings.inspection.case-analyses')->with('success', "Imported records: {$count}");
    }

    /**
     * Generic import: first row = headers (matched to map keys), then data rows.
     * $headerToDbMap: [ 'File Header' => 'db_column' ] or [ 'db_column' => 'db_column' ] for same name.
     */
    private function importFromFile($file, array $headerToDbMap, callable $createRow): int
    {
        $path = $file->getRealPath();
        $ext = strtolower($file->getClientOriginalExtension());
        $count = 0;

        if ($ext === 'csv' || $ext === 'txt') {
            $sep = ',';
            $content = file_get_contents($path);
            $lines = preg_split('/\r\n|\r|\n/', $content);
            if (!empty($lines) && str_starts_with($lines[0], 'sep=')) {
                $sep = trim(substr($lines[0], 4));
                $lines = array_slice($lines, 1);
            }
            $rows = [];
            foreach ($lines as $line) {
                $rows[] = str_getcsv($line, $sep);
            }
        } else {
            $spreadsheet = IOFactory::load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray();
        }

        if (empty($rows)) {
            return 0;
        }
        $fileHeaders = array_map('trim', $rows[0]);
        $rows = array_slice($rows, 1);

        foreach ($rows as $row) {
            $data = [];
            foreach ($fileHeaders as $i => $h) {
                $val = isset($row[$i]) ? trim((string)$row[$i]) : '';
                if ($val === '') {
                    continue;
                }
                foreach ($headerToDbMap as $fileCol => $dbCol) {
                    if (strcasecmp($h, $fileCol) === 0 || $h === $dbCol) {
                        $data[$dbCol] = $val;
                        break;
                    }
                }
            }
            if (!empty($data)) {
                $createRow($data);
                $count++;
            }
        }
        return $count;
    }
}
