<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Crew;
use Illuminate\Support\Facades\DB;

class MigrateCrewAircraftTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crew:migrate-aircraft-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing TypesAC_id data to crew_aircraft_types pivot table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of crew aircraft types...');

        $crews = Crew::whereNotNull('TypesAC_id')->get();
        $migrated = 0;

        foreach ($crews as $crew) {
            // Проверяем, что тип ВС существует
            $aircraftType = DB::table('aircrafts_types')->where('id', $crew->TypesAC_id)->first();
            
            if ($aircraftType) {
                // Проверяем, что связь еще не существует
                $exists = DB::table('crew_aircraft_types')
                    ->where('crew_id', $crew->id)
                    ->where('aircraft_type_id', $crew->TypesAC_id)
                    ->exists();

                if (!$exists) {
                    DB::table('crew_aircraft_types')->insert([
                        'crew_id' => $crew->id,
                        'aircraft_type_id' => $crew->TypesAC_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $migrated++;
                }
            }
        }

        $this->info("Migration completed. Migrated {$migrated} crew aircraft type relationships.");
    }
}