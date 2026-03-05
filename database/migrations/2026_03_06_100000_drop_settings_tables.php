<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Таблицы настроек/справочников, которые удаляем вместе с контроллерами. */
    private const TABLES_TO_DROP = [
        'requirements',
        'requirement_types',
        'events_crew',
        'flight_readiness_type',
        'flights',
        'crew_aircraft_types',
        'crews',
        'minimum_crew',
        'positions',
        'readiness_types',
        'flight_statuses',
        'events',
        'airports',
        'templatetlgxlsx',
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
