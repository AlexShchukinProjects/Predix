<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AircraftsType;
use App\Models\RelEngineNumber;
use App\Models\RelFailureAggregate;
use App\Models\RelFailureConsequence;
use App\Models\RelFailureDetectionStage;
use App\Models\RelFailureSystem;
use App\Models\ReliabilityFailure;
use App\Models\RelTakenMeasure;
use App\Models\RelWoStatus;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportReliabilityFailuresFromExcel extends Command
{
    protected $signature = 'reliability:import-failures 
        {path=storage/excel/Отказы.xls : path to .xls or .xlsx file}
        {--skip=1 : skip first N rows (1 = skip header row)}
        {--dry-run : only show what would be imported}';

    protected $description = 'Import reliability failures from Excel (Отказы.xls) into rel_failures';

    /**
     * Map: our DB field => possible Excel header names (any match assigns that column).
     * Рег. № = ID записи в источнике (source_record_id). Бортовой № ВС = регистрационный номер ВС (aircraft_number).
     * Order: more specific / exact Excel headers first.
     */
    private const HEADER_MAP = [
        'source_record_id' => ['рег. №', 'id записи', 'id', 'номер записи'],
        'account_number' => ['учетный номер (код предприятия номер кун)', 'учетный номер', 'код предприятия', 'кун', 'номер кун', 'account number', 'account', '№ счета', 'счет'],
        'failure_date' => ['дата', 'дата отказа', 'дата обнаружения'],
        'aircraft_number' => ['бортовой № вс', 'бортовой номер вс', 'рег. номер', 'номер вс', 'бортовой номер', 'регистрационный номер'],
        'aircraft_type' => ['тип вс'],
        'aircraft_type_code' => ['тип вс (код)', 'тип вс (код)'],
        'modification_code' => ['модификация (код)', 'модификация'],
        'aircraft_serial' => ['заводской номер вс', 'номер вс заводской', 'serial вс'],
        'aircraft_hours' => ['наработка вс в часах'],
        'aircraft_landings' => ['наработка вс в посадках'],
        'aircraft_ppr_hours' => ['наработка вс ппр (час)', 'наработка ппр вс', 'наработка вс ппр (час)'],
        'aircraft_ppr_landings' => ['наработка вс ппр (посадки)'],
        'aircraft_repair_date' => ['дата ремонта вс'],
        'previous_repair_location' => ['место предыдущего ремонта'],
        'aircraft_repairs_count' => ['количество ремонтов вс'],
        'operator' => ['эксплуатант'],
        'detection_stage_name' => ['этап обнаружения', 'этап обнаружения отказа'],
        'aircraft_malfunction' => ['проявление неисправности вс'],
        'event_location' => ['место события'],
        'consequence_name' => ['последствия'],
        'system_name' => ['система'],
        'subsystem_name' => ['подсистема'],
        'aggregate_type' => ['тип ки', 'тип агрегата', 'тип компонента'],
        'part_number_off' => ['p/n off', 'pn off', 'part number off', 'pn снятого', 'pn снятой детали', 'pn снятого ки'],
        'component_serial' => ['s/n off', 'sn off', 'серийный номер off'],
        'part_number_on' => ['p/n on', 'pn on', 'part number on', 'pn установленного', 'pn установленного ки'],
        'serial_number_on' => ['s/n on', 'sn on', 'серийный номер on'],
        'production_date' => ['дата выпуска ки', 'дата выпуска компонента'],
        'component_repair_date' => ['дата последнего кап.ремонта', 'дата кап ремонта ки', 'дата ремонта ки'],
        'component_repairs_count' => ['количество кап.ремонтов отказавшего ки', 'количество ремонтов ки'],
        'previous_installation_date' => ['дата последней установки ки на вс', 'дата последней установки ки'],
        'component_hours_unit' => ['единица измерения наработки ки', 'ед. изм. наработки ки', 'единица наработки ки'],
        'component_sne_hours' => ['наработка снэ ки', 'наработка снэ', 'наработка снэ ки'],
        'component_ppr_hours' => ['наработка ппр ки', 'наработка ппр', 'наработка ппр ки'],
        'component_malfunction' => ['проявление неисправности ки'],
        'component_cause' => ['причина неисправности ки', 'причина отказа', 'причина неисправности'],
        'resolution_method' => ['подтверждено в цлпир', 'отремонтировано', 'метод устранения', 'дополнительные сведения'],
        'taken_measure_name' => ['принятые меры'],
        'work_order_number' => ['work orders', '№ авиационного события', 'номер карты наряд', 'work order', '№ наряд'],
        'wo_number' => ['wo number', '№ wo'],
        'wo_status_name' => ['статус wo', 'статус наряда'],
        'resolution_date' => ['дата устранения', 'дата устранения отказа'],
        'engine_number_text' => ['№ двигателя', 'номер двигателя', '№ су'],
        'engine_sne_hours' => ['наработка двигателя снэ в часах', 'наработка двигателя снэ'],
        'engine_sne_cycles' => ['наработка двигателя снэ в циклах'],
        'engine_ppr_hours' => ['наработка двигателя ппр в часах', 'наработка двигателя ппр'],
        'engine_ppr_cycles' => ['наработка двигателя ппр в циклах'],
        'engine_installation_date' => ['дата последней установки двигателя на вс', 'дата установки двигателя'],
        'engine_release_date' => ['дата выпуска двигателя'],
        'engine_repairs_count' => ['количество кап. ремонтов двигателя', 'количество ремонтов двигателя'],
        'engine_repair_date' => ['дата последнего кап. ремонта двигателя', 'дата кап ремонта двигателя'],
        'engine_repair_location' => ['место ремонта двигателя'],
        'manufacturer' => ['изготовитель', 'производитель', 'manufacturer'],
        'removal_date' => ['дата снятия', 'дата снятия ки'],
    ];

    public function handle(): int
    {
        $pathArg = $this->argument('path');
        $path = str_starts_with($pathArg, '/') || preg_match('#^[A-Za-z]:\\\\#', $pathArg)
            ? $pathArg
            : base_path($pathArg);
        if (!is_file($path)) {
            $path = storage_path('excel/Отказы.xls');
        }
        if (!is_file($path)) {
            $this->error("File not found: {$path}");
            return Command::FAILURE;
        }

        $skip = (int) $this->option('skip');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Loading: {$path}");
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $headerRow = $rows[1] ?? [];
        $colMap = $this->buildColumnMap($headerRow);
        if ($this->output->isVerbose()) {
            $this->line('Mapped columns: ' . implode(', ', array_keys($colMap)));
        }

        $detectionStages = RelFailureDetectionStage::all()->keyBy(fn ($s) => mb_strtolower(trim((string) $s->name)));
        $consequences = RelFailureConsequence::all()->keyBy(fn ($c) => mb_strtolower(trim((string) $c->name)));
        $takenMeasures = RelTakenMeasure::all()->keyBy(fn ($m) => mb_strtolower(trim((string) $m->name)));
        $woStatuses = RelWoStatus::all()->keyBy(fn ($s) => mb_strtolower(trim((string) $s->name)));
        $engineNumbers = RelEngineNumber::with('engineType')->get()->keyBy(fn ($e) => mb_strtolower(trim((string) $e->number)));

        $imported = 0;
        $errors = 0;
        $startRow = 1 + $skip;

        for ($rowIndex = $startRow; $rowIndex <= count($rows); $rowIndex++) {
            $row = $rows[$rowIndex] ?? [];
            $row = array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : '', $row);

            $get = function (string $field) use ($row, $colMap) {
                $col = $colMap[$field] ?? null;
                if ($col === null) {
                    return '';
                }
                return $row[$col] ?? '';
            };

            $failureDate = $this->parseDate($get('failure_date'));
            $aircraftNumber = $get('aircraft_number');
            $aircraftMalfunction = $get('aircraft_malfunction');

            if (!$failureDate && !$aircraftNumber && !$aircraftMalfunction) {
                continue;
            }

            $detectionStageName = $get('detection_stage_name');
            $consequenceName = $get('consequence_name');
            $takenMeasureName = $get('taken_measure_name');
            $woStatusName = $get('wo_status_name');
            $engineNumberText = $get('engine_number_text');

            $detectionStageId = $this->resolveOrCreateDetectionStage($detectionStageName, $detectionStages, $dryRun);
            $consequenceId = $consequenceName !== ''
                ? ($consequences->get(mb_strtolower($consequenceName))?->id) : null;
            $takenMeasureId = $this->resolveOrCreateTakenMeasure($takenMeasureName, $takenMeasures, $dryRun);
            $woStatusId = $woStatusName !== ''
                ? ($woStatuses->get(mb_strtolower($woStatusName))?->id) : null;

            $engineNumberId = null;
            $engineTypeId = null;
            if ($engineNumberText !== '') {
                $engineRec = $engineNumbers->get(mb_strtolower($engineNumberText));
                if ($engineRec) {
                    $engineNumberId = $engineRec->id;
                    $engineTypeId = $engineRec->engine_type_id;
                }
            }

            $systemName = $this->emptyToNull($get('system_name'));
            $subsystemName = $this->emptyToNull($get('subsystem_name'));
            $aggregateType = $this->emptyToNull($get('aggregate_type'));
            $aircraftTypeName = $this->emptyToNull($get('aircraft_type'));
            if (!$dryRun && ($systemName !== null || $subsystemName !== null || $aggregateType !== null)) {
                $this->ensureSystemSubsystemAggregateExist($systemName, $subsystemName, $aggregateType, $aircraftTypeName);
            }

            $payload = [
                'account_number' => $this->emptyToNull($get('account_number')),
                'source_record_id' => $this->emptyToNull($get('source_record_id')),
                'failure_date' => $failureDate,
                'aircraft_number' => $aircraftNumber ?: null,
                'aircraft_type' => $this->emptyToNull($get('aircraft_type')),
                'aircraft_type_code' => $this->emptyToNull($get('aircraft_type_code')),
                'modification_code' => $this->emptyToNull($get('modification_code')),
                'aircraft_serial' => $this->emptyToNull($get('aircraft_serial')),
                'aircraft_manufacture_date' => $this->parseDate($get('aircraft_manufacture_date')),
                'aircraft_hours' => $this->parseDecimal($get('aircraft_hours')),
                'aircraft_landings' => $this->parseInt($get('aircraft_landings')),
                'aircraft_ppr_hours' => $this->parseDecimal($get('aircraft_ppr_hours')),
                'aircraft_ppr_landings' => $this->parseInt($get('aircraft_ppr_landings')),
                'aircraft_repair_date' => $this->parseDate($get('aircraft_repair_date')),
                'previous_repair_location' => $this->emptyToNull($get('previous_repair_location')),
                'aircraft_repairs_count' => $this->parseInt($get('aircraft_repairs_count')),
                'operator' => $this->emptyToNull($get('operator')),
                'detection_stage_id' => $detectionStageId,
                'aircraft_malfunction' => $this->emptyToNull($get('aircraft_malfunction')),
                'event_location' => $this->emptyToNull($get('event_location')),
                'consequence_id' => $consequenceId,
                'wo_number' => $this->emptyToNull($get('wo_number')),
                'wo_status_id' => $woStatusId,
                'work_order_number' => $this->emptyToNull($get('work_order_number')),
                'system_name' => $this->emptyToNull($get('system_name')),
                'subsystem_name' => $this->emptyToNull($get('subsystem_name')),
                'component_malfunction' => $this->emptyToNull($get('component_malfunction')),
                'component_cause' => $this->emptyToNull($get('component_cause')),
                'taken_measure_id' => $takenMeasureId,
                'resolution_method' => $this->emptyToNull($get('resolution_method')),
                'resolution_date' => $this->parseDate($get('resolution_date')),
                'aggregate_type' => $this->emptyToNull($get('aggregate_type')),
                'part_number_off' => $this->emptyToNull($get('part_number_off')),
                'component_serial' => $this->emptyToNull($get('component_serial')),
                'part_number_on' => $this->emptyToNull($get('part_number_on')),
                'serial_number_on' => $this->emptyToNull($get('serial_number_on')),
                'manufacturer' => $this->emptyToNull($get('manufacturer')),
                'removal_date' => $this->parseDate($get('removal_date')),
                'component_sne_hours' => $this->parseDecimal($get('component_sne_hours')),
                'component_ppr_hours' => $this->parseDecimal($get('component_ppr_hours')),
                'component_hours_unit' => $this->emptyToNull($get('component_hours_unit')),
                'production_date' => $this->parseDate($get('production_date')),
                'component_repairs_count' => $this->parseInt($get('component_repairs_count')),
                'previous_installation_date' => $this->parseDate($get('previous_installation_date')),
                'repair_factory' => null,
                'component_repair_date' => $this->parseDate($get('component_repair_date')),
                'engine_type_id' => $engineTypeId,
                'engine_number_id' => $engineNumberId,
                'engine_release_date' => $this->parseDate($get('engine_release_date')),
                'engine_installation_date' => $this->parseDate($get('engine_installation_date')),
                'engine_sne_hours' => $this->parseDecimal($get('engine_sne_hours')),
                'engine_ppr_hours' => $this->parseDecimal($get('engine_ppr_hours')),
                'engine_sne_cycles' => $this->parseDecimal($get('engine_sne_cycles')),
                'engine_ppr_cycles' => $this->parseDecimal($get('engine_ppr_cycles')),
                'engine_repair_date' => $this->parseDate($get('engine_repair_date')),
                'engine_repair_location' => $this->emptyToNull($get('engine_repair_location')),
                'engine_repairs_count' => $this->parseInt($get('engine_repairs_count')),
            ];

            if ($dryRun) {
                $this->line("Row {$rowIndex}: date={$payload['failure_date']}, aircraft={$payload['aircraft_number']}, malfunction=" . mb_substr((string) $payload['aircraft_malfunction'], 0, 40) . '…');
                $imported++;
                continue;
            }

            try {
                ReliabilityFailure::create($payload);
                $imported++;
            } catch (\Throwable $e) {
                $errors++;
                $this->warn("Row {$rowIndex}: " . $e->getMessage());
            }
        }

        $this->info("Imported: {$imported} failures." . ($errors ? " Errors: {$errors}." : ''));
        return $errors ? Command::FAILURE : Command::SUCCESS;
    }

    private function buildColumnMap(array $headerRow): array
    {
        $normalizedHeaders = [];
        foreach ($headerRow as $col => $text) {
            $t = mb_strtolower(trim((string) $text));
            if ($t === '') {
                continue;
            }
            $normalizedHeaders[$col] = $t;
        }

        $colMap = [];
        foreach (self::HEADER_MAP as $field => $possibleNames) {
            foreach ($possibleNames as $name) {
                foreach ($normalizedHeaders as $col => $headerText) {
                    $matches = mb_strpos($headerText, $name) !== false || mb_strpos($name, $headerText) !== false;
                    if (!$matches) {
                        continue;
                    }
                    // Система: не брать колонку "Подсистема" (подсистема содержит "система")
                    if ($field === 'system_name' && mb_strpos($headerText, 'подсистема') !== false) {
                        continue;
                    }
                    // Подсистема: брать только колонку с "подсистема"
                    if ($field === 'subsystem_name' && mb_strpos($headerText, 'подсистема') === false) {
                        continue;
                    }
                    $colMap[$field] = $col;
                    break 2;
                }
            }
        }

        return $colMap;
    }

    private function emptyToNull(?string $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        return trim($value);
    }

    private function parseDate(?string $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $value = trim($value);
        try {
            if (preg_match('/^(\d{1,2})[.\/](\d{1,2})[.\/](\d{4})$/', $value, $m)) {
                $date = Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1]);
                return $date->format('Y-m-d');
            }
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
                return $value;
            }
            $parsed = Carbon::parse($value);
            return $parsed->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = preg_replace('/\s+/', '', (string) $value);
        $value = str_replace(',', '.', $value);
        return is_numeric($value) ? (float) $value : null;
    }

    private function parseInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = preg_replace('/\s+/', '', (string) $value);
        return is_numeric($value) ? (int) (float) $value : null;
    }

    private function resolveOrCreateDetectionStage(string $name, $detectionStages, bool $dryRun): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }
        $key = mb_strtolower($name);
        $existing = $detectionStages->get($key);
        if ($existing) {
            return $existing->id;
        }
        if ($dryRun) {
            return null;
        }
        $stage = RelFailureDetectionStage::create([
            'name' => $name,
            'active' => true,
            'sort_order' => (int) (RelFailureDetectionStage::max('sort_order') ?? 0) + 1,
        ]);
        $detectionStages->put($key, $stage);
        $this->line("  + Этап обнаружения: {$name}");
        return $stage->id;
    }

    private function resolveOrCreateTakenMeasure(string $name, $takenMeasures, bool $dryRun): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }
        $key = mb_strtolower($name);
        $existing = $takenMeasures->get($key);
        if ($existing) {
            return $existing->id;
        }
        if ($dryRun) {
            return null;
        }
        $measure = RelTakenMeasure::create([
            'name' => $name,
            'active' => true,
            'sort_order' => (int) (RelTakenMeasure::max('sort_order') ?? 0) + 1,
        ]);
        $takenMeasures->put($key, $measure);
        $this->line("  + Принятые меры: {$name}");
        return $measure->id;
    }

    private function resolveOrCreateAircraftType(?string $name): ?int
    {
        $name = $name ? trim($name) : '';
        if ($name === '') {
            return null;
        }
        $existing = AircraftsType::where('name_rus', $name)
            ->orWhere('icao', $name)
            ->orWhere('name_eng', $name)
            ->first();
        if ($existing) {
            return $existing->id;
        }
        $created = AircraftsType::create([
            'name_rus' => $name,
            'icao' => mb_substr($name, 0, 10),
            'active' => true,
        ]);
        $this->line("  + Тип ВС: {$name}");
        return $created->id;
    }

    private function ensureSystemSubsystemAggregateExist(?string $systemName, ?string $subsystemName, ?string $aggregateType, ?string $aircraftTypeName): void
    {
        $systemName = $systemName ?? '';
        $subsystemName = $subsystemName ?? '';
        $aggregateType = $aggregateType ?? '';
        $aircraftTypeId = $this->resolveOrCreateAircraftType($aircraftTypeName);

        if ($systemName === '' && $subsystemName === '' && $aggregateType === '') {
            return;
        }

        $sysName = trim($systemName) ?: '—';
        $subName = trim($subsystemName) ?: null;

        $system = RelFailureSystem::firstOrCreate(
            [
                'system_name' => $sysName,
                'subsystem_name' => $subName,
            ],
            [
                'active' => true,
                'sort_order' => (int) (RelFailureSystem::max('sort_order') ?? 0) + 1,
                'aircraft_type_id' => $aircraftTypeId,
            ]
        );
        if ($system->wasRecentlyCreated) {
            $this->line("  + Система/подсистема: {$sysName}" . ($subName ? " / {$subName}" : ''));
        }

        if ($aggregateType !== '') {
            $aggName = trim($aggregateType);
            $aggregate = RelFailureAggregate::firstOrCreate(
                [
                    'failure_system_id' => $system->id,
                    'name' => $aggName,
                ],
                [
                    'active' => true,
                    'sort_order' => (int) (RelFailureAggregate::where('failure_system_id', $system->id)->max('sort_order') ?? 0) + 1,
                    'aircraft_type_id' => $aircraftTypeId,
                ]
            );
            if ($aggregate->wasRecentlyCreated) {
                $this->line("  + Тип КИ (агрегат): {$aggName}");
            }
        }
    }
}
