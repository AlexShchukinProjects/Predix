<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillRequirementIds extends Command
{
    protected $signature = 'requirements:backfill-ids {--dry-run : Do not write changes, only show counts}';
    protected $description = 'Backfill requirement_id in permissions/flightdocs/flightchecks/trainings by matching Description to requirements.name';

    public function handle(): int
    {
        if (!Schema::hasTable('requirements') || !Schema::hasTable('permissions')) {
            $this->warn('Таблицы requirements/permissions удалены; команда отключена.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        // Build normalized name => id map
        $reqMap = DB::table('requirements')->select('id', 'name')->get()
            ->mapWithKeys(function ($r) {
                $key = $this->normalize((string) $r->name);
                return [$key => (int) $r->id];
            });

        $tables = ['permissions', 'flightdocs', 'flightchecks', 'trainings'];
        foreach ($tables as $table) {
            $this->info("Processing table: {$table}");
            $updated = 0;
            DB::table($table)
                ->whereNull('requirement_id')
                ->whereNotNull('Description')
                ->orderBy('id')
                ->chunkById(1000, function ($rows) use (&$updated, $table, $reqMap, $dryRun) {
                    $toUpdate = [];
                    foreach ($rows as $row) {
                        $key = $this->normalize((string) $row->Description);
                        if ($key === '') { continue; }
                        $rid = $reqMap[$key] ?? null;
                        if ($rid) {
                            $toUpdate[(int) $row->id] = $rid;
                        }
                    }
                    if (!$dryRun && !empty($toUpdate)) {
                        foreach ($toUpdate as $id => $rid) {
                            DB::table($table)->where('id', $id)->update(['requirement_id' => $rid]);
                            $updated++;
                        }
                    } else {
                        $updated += count($toUpdate);
                    }
                });
            $this->info("{$table}: matched and " . ($dryRun ? 'would update' : 'updated') . " {$updated} rows");
        }

        $this->info('Backfill complete.');
        return self::SUCCESS;
    }

    private function normalize(string $s): string
    {
        $s = preg_replace('/\s+/u', ' ', $s);
        $s = trim($s);
        return mb_strtolower($s, 'UTF-8');
    }
}


