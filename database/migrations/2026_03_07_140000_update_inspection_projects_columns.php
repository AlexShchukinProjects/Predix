<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_projects', function (Blueprint $table) {
            $table->dropColumn(['project', 'scope', 'customer', 'flight_cycles', 'flight_hours']);
        });

        Schema::table('inspection_projects', function (Blueprint $table) {
            $table->string('project_number', 100)->nullable()->after('id');
            $table->string('status', 50)->nullable();
            $table->string('tail_number', 100)->nullable();
            $table->string('aircraft_type', 100)->nullable();
            $table->string('scope', 500)->nullable();
            $table->date('open_date')->nullable();
            $table->date('close_date')->nullable();
            $table->string('customer_number', 100)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_po', 255)->nullable();
            $table->string('est_non_routine', 100)->nullable();
            $table->unsignedInteger('target_days')->nullable();
            $table->date('arrival_date')->nullable();
            $table->date('induction_date')->nullable();
            $table->date('inspection_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->date('rev_delivery_date')->nullable();
            $table->date('latest_delivery_date')->nullable();
            $table->date('actual_arrival_date')->nullable();
            $table->date('actual_induction_date')->nullable();
            $table->date('actual_inspection_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->string('project_type', 100)->nullable();
            $table->string('applicable_standard', 255)->nullable();
            $table->text('resources')->nullable();
            $table->string('bay', 50)->nullable();
            $table->string('planned_span', 100)->nullable();
            $table->string('day_of_check', 100)->nullable();
            $table->decimal('aircraft_tsn', 15, 2)->nullable();
            $table->decimal('aircraft_csn', 15, 2)->nullable();
            $table->string('engine_type', 100)->nullable();
            $table->decimal('quoted_mhrs', 15, 2)->nullable();
            $table->decimal('oa_mhrs', 15, 2)->nullable();
            $table->decimal('add_works_mhrs', 15, 2)->nullable();
            $table->decimal('cwr_mhrs', 15, 2)->nullable();
            $table->string('aircraft_series', 100)->nullable();
            $table->string('station', 100)->nullable();
            $table->unsignedInteger('open_requisitions')->nullable();
            $table->unsignedInteger('open_order_lines')->nullable();
            $table->unsignedInteger('awaiting_to_return_store')->nullable();
            $table->unsignedInteger('uninvoice_order_lines')->nullable();
            $table->unsignedInteger('open_work_cards')->nullable();
            $table->unsignedInteger('open_work_orders')->nullable();
            $table->decimal('eng_mhrs', 15, 2)->nullable();
            $table->decimal('total_mhrs', 15, 2)->nullable();
            $table->string('engine_1_serial', 100)->nullable();
            $table->string('engine_2_serial', 100)->nullable();
            $table->string('engine_3_serial', 100)->nullable();
            $table->string('engine_4_serial', 100)->nullable();
            $table->decimal('engine_1_tsn', 15, 2)->nullable();
            $table->decimal('engine_1_csn', 15, 2)->nullable();
            $table->decimal('engine_2_tsn', 15, 2)->nullable();
            $table->decimal('engine_2_csn', 15, 2)->nullable();
            $table->decimal('engine_3_tsn', 15, 2)->nullable();
            $table->decimal('engine_3_csn', 15, 2)->nullable();
            $table->decimal('engine_4_tsn', 15, 2)->nullable();
            $table->decimal('engine_4_csn', 15, 2)->nullable();
            $table->string('apu_pn', 100)->nullable();
            $table->string('apu_serial', 100)->nullable();
            $table->decimal('apu_tsn', 15, 2)->nullable();
            $table->decimal('apu_csn', 15, 2)->nullable();
            $table->date('spares_order_cut_off')->nullable();
            $table->date('spares_delivery_cut_off')->nullable();
            $table->decimal('mhrs_cap', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_projects', function (Blueprint $table) {
            $table->dropColumn([
                'project_number', 'status', 'tail_number', 'aircraft_type', 'scope', 'open_date', 'close_date',
                'customer_number', 'customer_name', 'customer_po', 'est_non_routine', 'target_days', 'arrival_date',
                'induction_date', 'inspection_date', 'delivery_date', 'rev_delivery_date', 'latest_delivery_date',
                'actual_arrival_date', 'actual_induction_date', 'actual_inspection_date', 'actual_delivery_date',
                'project_type', 'applicable_standard', 'resources', 'bay', 'planned_span', 'day_of_check',
                'aircraft_tsn', 'aircraft_csn', 'engine_type', 'quoted_mhrs', 'oa_mhrs', 'add_works_mhrs', 'cwr_mhrs',
                'aircraft_series', 'station', 'open_requisitions', 'open_order_lines', 'awaiting_to_return_store',
                'uninvoice_order_lines', 'open_work_cards', 'open_work_orders', 'eng_mhrs', 'total_mhrs',
                'engine_1_serial', 'engine_2_serial', 'engine_3_serial', 'engine_4_serial',
                'engine_1_tsn', 'engine_1_csn', 'engine_2_tsn', 'engine_2_csn', 'engine_3_tsn', 'engine_3_csn',
                'engine_4_tsn', 'engine_4_csn', 'apu_pn', 'apu_serial', 'apu_tsn', 'apu_csn',
                'spares_order_cut_off', 'spares_delivery_cut_off', 'mhrs_cap',
            ]);
        });

        Schema::table('inspection_projects', function (Blueprint $table) {
            $table->string('project', 100)->nullable()->after('id');
            $table->string('scope', 500)->nullable();
            $table->string('customer', 255)->nullable();
            $table->decimal('flight_cycles', 15, 2)->nullable();
            $table->decimal('flight_hours', 15, 2)->nullable();
        });
    }
};
