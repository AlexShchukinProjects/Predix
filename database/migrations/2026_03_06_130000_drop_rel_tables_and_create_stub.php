<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLES_TO_DROP = [
        'rel_failure_attachments',
        'rel_failures',
        'rel_failure_aggregates',
        'rel_failure_systems',
        'rel_engine_numbers',
        'rel_engine_types',
        'rel_buf_settings',
        'rel_failure_consequences',
        'rel_failure_detection_stages',
        'rel_failure_form_settings',
        'rel_taken_measures',
        'rel_wo_statuses',
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

        if (!Schema::hasTable('rel_stub')) {
            Schema::create('rel_stub', function (Blueprint $table) {
                $table->id();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rel_stub');
    }
};
