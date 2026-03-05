<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillAllRelatedTablesIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'related-tables:backfill-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill requirement_id in all related tables (permissions, flightdocs, flightchecks, trainings) by matching Description with requirements.name';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!Schema::hasTable('requirements') || !Schema::hasTable('permissions')) {
            $this->warn('Таблицы удалены; команда отключена.');
            return 0;
        }

        $this->info('Starting backfill of requirement_id in all related tables...');
        
        $tables = ['permissions', 'flightdocs', 'flightchecks', 'trainings'];
        $totalUpdated = 0;
        $totalNotFound = 0;
        
        foreach ($tables as $table) {
            $this->info("Processing table: {$table}");
            
            $updated = 0;
            $notFound = 0;
            
            // Получаем все записи из таблицы
            $records = DB::table($table)->get();
            
            foreach ($records as $record) {
                if (empty($record->Description)) {
                    continue;
                }
                
                // Ищем соответствующее требование по названию
                $requirement = DB::table('requirements')
                    ->where('name', $record->Description)
                    ->first();
                
                if ($requirement) {
                    // Обновляем requirement_id
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update(['requirement_id' => $requirement->id]);
                    
                    $updated++;
                } else {
                    $notFound++;
                    $this->warn("Not found in {$table}: {$record->Description}");
                }
            }
            
            $this->info("Table {$table}: Updated {$updated} records, Not found: {$notFound}");
            $totalUpdated += $updated;
            $totalNotFound += $notFound;
        }
        
        $this->info("Backfill completed!");
        $this->info("Total updated: {$totalUpdated} records");
        $this->warn("Total not found: {$totalNotFound} records");
        
        return 0;
    }
}
