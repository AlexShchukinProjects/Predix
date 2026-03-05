<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Aircraft;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportFleetTypeCodesFromExcel extends Command
{
    protected $signature = 'fleet:import-type-codes-from-excel 
        {path : path to .xls or .xlsx file (Бортовой № ВС, Тип ВС (код), Модификация (код))}
        {--skip=1 : skip first N rows (1 = header)}
        {--dry-run : only show what would be updated}';

    protected $description = 'Find Тип ВС (код) and Модификация (код) per aircraft in Excel and update fleet (aircraft table)';

    /** Column name variants for mapping (normalized). */
    private const AIRCRAFT_NUMBER_NAMES = ['бортовой № вс', 'бортовой номер вс', 'рег. №', 'номер вс', 'регистрационный номер'];
    private const TYPE_CODE_NAMES = ['тип вс (код)', 'тип вс (код)', 'тип код'];
    private const MODIFICATION_CODE_NAMES = ['модификация (код)', 'модификация', 'модификация код'];

    public function handle(): int
    {
        $path = $this->argument('path');
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

        $aircraftCol = $colMap['aircraft_number'] ?? null;
        $typeCodeCol = $colMap['type_code'] ?? null;
        $modCol = $colMap['modification_code'] ?? null;

        if (!$aircraftCol) {
            $this->error('Column "Бортовой № ВС" (or similar) not found in the file.');
            return Command::FAILURE;
        }
        if (!$typeCodeCol && !$modCol) {
            $this->error('Neither "Тип ВС (код)" nor "Модификация (код)" column found.');
            return Command::FAILURE;
        }

        $perAircraft = [];
        $startRow = 1 + $skip;
        for ($rowIndex = $startRow; $rowIndex <= count($rows); $rowIndex++) {
            $row = $rows[$rowIndex] ?? [];
            $row = array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : '', $row);

            $regN = trim((string) ($row[$aircraftCol] ?? ''));
            if ($regN === '') {
                continue;
            }

            $typeCode = $typeCodeCol !== null ? trim((string) ($row[$typeCodeCol] ?? '')) : '';
            $modCode = $modCol !== null ? trim((string) ($row[$modCol] ?? '')) : '';

            if ($typeCode !== '' || $modCode !== '') {
                if (!isset($perAircraft[$regN])) {
                    $perAircraft[$regN] = ['type_code' => '', 'modification_code' => ''];
                }
                if ($typeCode !== '') {
                    $perAircraft[$regN]['type_code'] = $typeCode;
                }
                if ($modCode !== '') {
                    $perAircraft[$regN]['modification_code'] = $modCode;
                }
            }
        }

        $this->info('Found ' . count($perAircraft) . ' aircraft with codes in file.');

        $updated = 0;
        $notFound = [];

        foreach ($perAircraft as $regN => $codes) {
            $aircraft = Aircraft::where('RegN', $regN)->first();
            if (!$aircraft) {
                $notFound[] = $regN;
                continue;
            }

            if ($dryRun) {
                $this->line("  [dry-run] {$regN}: type_code={$codes['type_code']}, modification_code={$codes['modification_code']}");
                $updated++;
                continue;
            }

            $aircraft->update([
                'type_code' => $codes['type_code'] ?: null,
                'modification_code' => $codes['modification_code'] ?: null,
            ]);
            $updated++;
            $this->line("  Updated: {$regN}");
        }

        if (count($notFound) > 0) {
            $this->warn('Not found in fleet (no update): ' . implode(', ', array_slice($notFound, 0, 20)) . (count($notFound) > 20 ? ' … +' . (count($notFound) - 20) : ''));
        }

        $this->info("Updated: {$updated} aircraft." . ($dryRun ? ' (dry-run)' : ''));
        return Command::SUCCESS;
    }

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
        foreach (['aircraft_number' => self::AIRCRAFT_NUMBER_NAMES, 'type_code' => self::TYPE_CODE_NAMES, 'modification_code' => self::MODIFICATION_CODE_NAMES] as $field => $names) {
            foreach ($names as $name) {
                foreach ($normalized as $col => $headerText) {
                    if (mb_strpos($headerText, $name) !== false || mb_strpos($name, $headerText) !== false) {
                        $map[$field] = $col;
                        break 2;
                    }
                }
            }
        }
        return $map;
    }
}
