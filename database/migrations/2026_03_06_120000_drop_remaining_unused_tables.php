<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Обучение, экипажи/рейсы, прочее, архив. */
    private const TABLES_TO_DROP = [
        'course_modules',
        'courses',
        'tr_course_lesson_questions',
        'tr_courses',
        'tr_training_materials',
        'trainings',
        'crew_accessibilities',
        'crew_requirements',
        'crews_archive',
        'flight_crews',
        'flight_readiness_types',
        'flightchecks',
        'flightdocs',
        'fleets',
        'maintenance_aircraft',
        'passengers',
        'requirements2',
        'risk_registries',
        'spi_aircraft_types',
        'sr_display_settings',
        'sr_event_causes',
        'sr_message_event_cause',
        'permissions',
        'country',
        'owners',
        'parkings',
        'aircrafts_types_archive',
    ];

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        foreach (self::TABLES_TO_DROP as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        // Восстановление не предусмотрено.
    }
};
