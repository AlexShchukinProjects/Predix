<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('rel_stub')) {
            Schema::create('rel_stub', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->string('account_number', 100)->nullable();
                $table->unsignedBigInteger('source_record_id')->nullable();
                $table->date('failure_date')->nullable();
                $table->string('aircraft_number', 50)->nullable();
                $table->string('aircraft_type', 100)->nullable();
                $table->string('aircraft_type_code', 50)->nullable();
                $table->string('modification_code', 50)->nullable();
                $table->string('aircraft_serial', 100)->nullable();
                $table->date('aircraft_manufacture_date')->nullable();
                $table->decimal('aircraft_hours', 15, 2)->nullable();
                $table->unsignedInteger('aircraft_landings')->nullable();
                $table->decimal('aircraft_ppr_hours', 15, 2)->nullable();
                $table->unsignedInteger('aircraft_ppr_landings')->nullable();
                $table->date('aircraft_repair_date')->nullable();
                $table->string('previous_repair_location', 255)->nullable();
                $table->unsignedInteger('aircraft_repairs_count')->nullable();
                $table->string('operator', 255)->nullable();
                $table->unsignedBigInteger('detection_stage_id')->nullable();
                $table->text('aircraft_malfunction')->nullable();
                $table->string('event_location', 255)->nullable();
                $table->unsignedBigInteger('consequence_id')->nullable();
                $table->string('wo_number', 100)->nullable();
                $table->unsignedBigInteger('wo_status_id')->nullable();
                $table->string('work_order_number', 100)->nullable();
                $table->string('mpd', 255)->nullable();
                $table->string('system_name', 255)->nullable();
                $table->string('subsystem_name', 255)->nullable();
                $table->text('component_malfunction')->nullable();
                $table->text('component_cause')->nullable();
                $table->unsignedBigInteger('taken_measure_id')->nullable();
                $table->string('resolution_method', 100)->nullable();
                $table->date('resolution_date')->nullable();
                $table->string('aggregate_type', 100)->nullable();
                $table->string('part_number_off', 100)->nullable();
                $table->string('component_serial', 100)->nullable();
                $table->string('part_number_on', 100)->nullable();
                $table->string('serial_number_on', 100)->nullable();
                $table->string('manufacturer', 255)->nullable();
                $table->date('removal_date')->nullable();
                $table->decimal('component_sne_hours', 15, 2)->nullable();
                $table->decimal('component_ppr_hours', 15, 2)->nullable();
                $table->string('component_hours_unit', 50)->nullable();
                $table->date('production_date')->nullable();
                $table->unsignedInteger('component_repairs_count')->nullable();
                $table->date('previous_installation_date')->nullable();
                $table->string('repair_factory', 255)->nullable();
                $table->date('component_repair_date')->nullable();
                $table->unsignedBigInteger('engine_type_id')->nullable();
                $table->unsignedBigInteger('engine_number_id')->nullable();
                $table->date('engine_release_date')->nullable();
                $table->date('engine_installation_date')->nullable();
                $table->decimal('engine_sne_hours', 15, 2)->nullable();
                $table->decimal('engine_ppr_hours', 15, 2)->nullable();
                $table->decimal('engine_sne_cycles', 15, 2)->nullable();
                $table->decimal('engine_ppr_cycles', 15, 2)->nullable();
                $table->date('engine_repair_date')->nullable();
                $table->string('engine_repair_location', 255)->nullable();
                $table->unsignedInteger('engine_repairs_count')->nullable();
                $table->string('owner', 255)->nullable();
                $table->string('position', 255)->nullable();
                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->boolean('include_in_buf')->default(true);
            });
            return;
        }

        if (!Schema::hasColumn('rel_stub', 'mpd')) {
            Schema::table('rel_stub', function (Blueprint $table) {
                $table->string('mpd', 255)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rel_stub') && Schema::hasColumn('rel_stub', 'mpd')) {
            Schema::table('rel_stub', function (Blueprint $table) {
                $table->dropColumn('mpd');
            });
        }
    }
};
