<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillCrewRequirementsIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crew-requirements:backfill-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill requirement_id in crew_requirements table by matching requirement names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!Schema::hasTable('crew_requirements')) {
            $this->warn('Таблица crew_requirements удалена; команда отключена.');
            return 0;
        }

        $this->info('Starting backfill of requirement_id in crew_requirements table...');
        
        $updated = 0;
        $notFound = 0;
        
        // Получаем все записи из crew_requirements
        $crewRequirements = DB::table('crew_requirements')->get();
        
        foreach ($crewRequirements as $crewReq) {
            // Ищем соответствующее требование по названию
            $requirement = DB::table('requirements')
                ->where('name', $crewReq->requirement)
                ->first();
            
            if ($requirement) {
                // Обновляем requirement_id
                DB::table('crew_requirements')
                    ->where('id', $crewReq->id)
                    ->update(['requirement_id' => $requirement->id]);
                
                $updated++;
                $this->line("Updated: {$crewReq->requirement} -> requirement_id: {$requirement->id}");
            } else {
                $notFound++;
                $this->warn("Not found: {$crewReq->requirement}");
            }
        }
        
        $this->info("Backfill completed!");
        $this->info("Updated: {$updated} records");
        $this->warn("Not found: {$notFound} records");
        
        return 0;
    }
}
