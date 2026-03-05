<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class ImportAsobpFromExcel extends Command
{
    protected $signature = 'sr:import-asobp {path=storage/excel/asobp1.xlsx} {--truncate}';
    protected $description = 'Import ASOBP hierarchical codes from Excel into sr_asobp_codes';

    public function handle(): int
    {
        $path = base_path($this->argument('path'));
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return Command::FAILURE;
        }
        if ($this->option('truncate')) {
            DB::table('sr_asobp_codes')->truncate();
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Expecting columns: A root_parent_code, B ext_id, C code, D name, E ext_parent_id, F level
        $inserted = 0;
        DB::beginTransaction();
        try {
            // First pass: insert all rows without parent_id, store ext_id => id
            $extToId = [];
            foreach ($rows as $i => $row) {
                // skip header if present
                if ($i === 1 && !is_numeric($row['B'])) { continue; }
                $rootParent = trim((string)($row['A'] ?? '')) ?: null;
                $extId = $row['B'] !== null && $row['B'] !== '' ? (int)$row['B'] : null;
                $code = trim((string)($row['C'] ?? ''));
                $name = trim((string)($row['D'] ?? ''));
                $extParent = $row['E'] !== null && $row['E'] !== '' ? (int)$row['E'] : null;
                $level = $row['F'] !== null && $row['F'] !== '' ? (int)$row['F'] : null;
                if ($code === '' && $name === '') { continue; }

                $id = DB::table('sr_asobp_codes')->insertGetId([
                    'ext_id' => $extId,
                    'ext_parent_id' => $extParent,
                    'root_parent_code' => $rootParent,
                    'code' => $code,
                    'name' => $name,
                    'level' => $level ?? 1,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if ($extId) { $extToId[$extId] = $id; }
                $inserted++;
            }

            // Second pass: update parent_id by ext_parent_id
            $records = DB::table('sr_asobp_codes')->select('id','ext_parent_id')->get();
            foreach ($records as $rec) {
                if ($rec->ext_parent_id && isset($extToId[$rec->ext_parent_id])) {
                    DB::table('sr_asobp_codes')->where('id', $rec->id)->update([
                        'parent_id' => $extToId[$rec->ext_parent_id],
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        $this->info("Imported {$inserted} rows from {$path}");
        return Command::SUCCESS;
    }
}


