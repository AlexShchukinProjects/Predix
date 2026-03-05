<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AircraftsType;
use App\Models\RelFailureSystem;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Из файла отказов извлекает полный перечень систем и подсистем,
 * затем создаёт их в rel_failure_systems для каждого типа ВС (из файла или из БД).
 */
class ImportReliabilitySystemsFromExcel extends Command
{
    protected $signature = 'reliability:import-systems-from-excel 
        {path : path to .xls or .xlsx (колонки: Система, Подсистема, Тип ВС)}
        {--skip=1 : skip first N rows}
        {--all-types : для всех типов ВС из БД (иначе только типы из файла)}
        {--dry-run : only show what would be created}';

    protected $description = 'Load full list of systems/subsystems from Excel into each aircraft type';

    private function buildColumnMap(array $headerRow): array
    {
        $normalized = [];
        foreach ($headerRow as $col => $text) {
            $t = mb_strtolower(trim((string) $text));
            if ($t !== '') {
                $normalized[$col] = $t;
            }
        }

        $map = [];
        foreach (['aircraft_type' => ['тип вс'], 'system_name' => ['система'], 'subsystem_name' => ['подсистема']] as $field => $names) {
            foreach ($names as $name) {
                foreach ($normalized as $col => $headerText) {
                    if ($field === 'system_name' && mb_strpos($headerText, 'подсистема') !== false) {
                        continue;
                    }
                    if ($field === 'subsystem_name' && mb_strpos($headerText, 'подсистема') === false) {
                        continue;
                    }
                    if (mb_strpos($headerText, $name) !== false || mb_strpos($name, $headerText) !== false) {
                        $map[$field] = $col;
                        break 2;
                    }
                }
            }
        }
        return $map;
    }

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!is_file($path)) {
            $this->error("File not found: {$path}");
            return Command::FAILURE;
        }

        $skip = (int) $this->option('skip');
        $allTypes = (bool) $this->option('all-types');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Loading: {$path}");
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $headerRow = $rows[1] ?? [];
        $colMap = $this->buildColumnMap($headerRow);

        $colSystem = $colMap['system_name'] ?? null;
        $colSubsystem = $colMap['subsystem_name'] ?? null;
        $colAircraftType = $colMap['aircraft_type'] ?? null;

        if (!$colSystem && !$colSubsystem) {
            $this->error('Columns "Система" and/or "Подсистема" not found.');
            return Command::FAILURE;
        }

        $pairs = [];
        $typesFromFile = [];

        $startRow = 1 + $skip;
        for ($rowIndex = $startRow; $rowIndex <= count($rows); $rowIndex++) {
            $row = $rows[$rowIndex] ?? [];
            $row = array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : '', $row);

            $sys = $colSystem !== null ? trim((string) ($row[$colSystem] ?? '')) : '';
            $sub = $colSubsystem !== null ? trim((string) ($row[$colSubsystem] ?? '')) : '';
            $typeName = $colAircraftType !== null ? trim((string) ($row[$colAircraftType] ?? '')) : '';

            if ($sys === '' && $sub === '') {
                continue;
            }
            $sysKey = $sys !== '' ? $sys : '—';
            $subKey = $sub !== '' ? $sub : null;
            $pairs[$sysKey . '|' . ($subKey ?? '')] = ['system_name' => $sysKey, 'subsystem_name' => $subKey];
            if ($typeName !== '') {
                $typesFromFile[$typeName] = true;
            }
        }

        $pairs = array_values($pairs);
        $this->info('Found ' . count($pairs) . ' unique system/subsystem pairs in file.');

        if ($allTypes) {
            $aircraftTypes = AircraftsType::orderBy('name_rus')->get();
            $this->info('Using all ' . $aircraftTypes->count() . ' aircraft types from DB.');
        } else {
            $typeNames = array_keys($typesFromFile);
            if (count($typeNames) === 0) {
                $this->warn('No aircraft types in file. Use --all-types to use all types from DB.');
                $aircraftTypes = AircraftsType::orderBy('name_rus')->get();
            } else {
                $aircraftTypes = AircraftsType::where(function ($q) use ($typeNames) {
                    foreach ($typeNames as $name) {
                        $q->orWhere('name_rus', $name)->orWhere('icao', $name)->orWhere('name_eng', $name);
                    }
                })->orderBy('name_rus')->get();
                $this->info('Using ' . $aircraftTypes->count() . ' aircraft types from file.');
            }
        }

        if ($aircraftTypes->isEmpty()) {
            $this->error('No aircraft types to assign.');
            return Command::FAILURE;
        }

        $created = 0;
        foreach ($aircraftTypes as $aircraftType) {
            $this->line('Type: ' . ($aircraftType->name_rus ?? $aircraftType->icao ?? $aircraftType->id));
            foreach ($pairs as $pair) {
                $exists = RelFailureSystem::where('system_name', $pair['system_name'])
                    ->where('subsystem_name', $pair['subsystem_name'])
                    ->where('aircraft_type_id', $aircraftType->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("  [dry-run] + {$pair['system_name']}" . ($pair['subsystem_name'] ? " / {$pair['subsystem_name']}" : ''));
                    $created++;
                    continue;
                }

                $maxOrder = (int) RelFailureSystem::where('aircraft_type_id', $aircraftType->id)->max('sort_order');
                RelFailureSystem::create([
                    'system_name' => $pair['system_name'],
                    'subsystem_name' => $pair['subsystem_name'],
                    'aircraft_type_id' => $aircraftType->id,
                    'active' => true,
                    'sort_order' => $maxOrder + 1,
                ]);
                $created++;
                $this->line("  + {$pair['system_name']}" . ($pair['subsystem_name'] ? " / {$pair['subsystem_name']}" : ''));
            }
        }

        $this->info("Done. Created: {$created} system/subsystem records.");
        return Command::SUCCESS;
    }
}
