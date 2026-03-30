<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aircrafts', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::table('aircrafts', function (Blueprint $table) {
            $table->dropColumn(['project_id', 'msn', 'first_flight', 'current_status']);
        });

        Schema::table('aircrafts', function (Blueprint $table) {
            $table->string('serial_number', 100)->nullable()->after('id');
            $table->string('tail_number', 100)->nullable();
            $table->string('aircraft_type', 100)->nullable();
            $table->string('visit', 100)->nullable();
            $table->string('customer_number', 100)->nullable();
            $table->string('owner_number', 100)->nullable();
            $table->string('engine_type', 100)->nullable();
            $table->string('apu_type', 100)->nullable();
            $table->string('group_code', 100)->nullable();
            $table->date('delivery_date')->nullable();
            $table->date('redelivery_date')->nullable();
            $table->string('etops', 50)->nullable();
            $table->string('amm_group', 100)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('owner_name', 255)->nullable();
            $table->string('app_std', 100)->nullable();
            $table->string('line_no', 100)->nullable();
            $table->string('variable_no', 100)->nullable();
            $table->string('effectivity', 255)->nullable();
            $table->string('selcal', 50)->nullable();
            $table->date('lease_date')->nullable();
            $table->string('manufactured', 100)->nullable();
            $table->date('ins_date')->nullable();
            $table->string('pas_cap', 50)->nullable();
            $table->string('seat_mat', 100)->nullable();
            $table->string('max_taxi', 50)->nullable();
            $table->string('max_to', 50)->nullable();
            $table->string('max_land', 50)->nullable();
            $table->string('maximum_zero_fuel_weight', 50)->nullable();
            $table->string('max_pay', 50)->nullable();
            $table->string('dry_ope', 50)->nullable();
            $table->string('fuel', 50)->nullable();
            $table->string('fuel_burn_ratio', 50)->nullable();
            $table->string('fwd_cargo', 50)->nullable();
            $table->string('aft_cargo', 50)->nullable();
            $table->string('fwd_area', 100)->nullable();
            $table->string('aft_area', 100)->nullable();
            $table->string('side_noise', 50)->nullable();
            $table->string('app_noise', 50)->nullable();
            $table->string('start_noise', 50)->nullable();
            $table->string('eng_rate', 50)->nullable();
            $table->string('mod', 255)->nullable();
            $table->string('color', 100)->nullable();
            $table->string('flight_number', 100)->nullable();
            $table->string('scheduled_from', 100)->nullable();
            $table->string('scheduled_to', 100)->nullable();
            $table->string('scheduled_off_block', 100)->nullable();
            $table->string('scheduled_on_block', 100)->nullable();
            $table->string('actual_from', 100)->nullable();
            $table->string('actual_to', 100)->nullable();
            $table->string('actual_off_block', 100)->nullable();
            $table->string('actual_on_block', 100)->nullable();
            $table->string('route_dev_dist', 50)->nullable();
            $table->string('route_dev_time', 50)->nullable();
            $table->string('wng', 50)->nullable();
            $table->string('archive', 50)->nullable();
            $table->string('active', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('aircrafts', function (Blueprint $table) {
            $table->dropColumn([
                'serial_number', 'tail_number', 'aircraft_type', 'visit', 'customer_number', 'owner_number',
                'engine_type', 'apu_type', 'group_code', 'delivery_date', 'redelivery_date', 'etops', 'amm_group',
                'customer_name', 'owner_name', 'app_std', 'line_no', 'variable_no', 'effectivity', 'selcal',
                'lease_date', 'manufactured', 'ins_date', 'pas_cap', 'seat_mat', 'max_taxi', 'max_to', 'max_land',
                'maximum_zero_fuel_weight', 'max_pay', 'dry_ope', 'fuel', 'fuel_burn_ratio', 'fwd_cargo', 'aft_cargo',
                'fwd_area', 'aft_area', 'side_noise', 'app_noise', 'start_noise', 'eng_rate', 'mod', 'color',
                'flight_number', 'scheduled_from', 'scheduled_to', 'scheduled_off_block', 'scheduled_on_block',
                'actual_from', 'actual_to', 'actual_off_block', 'actual_on_block', 'route_dev_dist', 'route_dev_time',
                'wng', 'archive', 'active',
            ]);
        });

        Schema::table('aircrafts', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained('projects')->nullOnDelete();
            $table->string('msn', 100)->nullable();
            $table->date('first_flight')->nullable();
            $table->string('current_status', 255)->nullable();
        });
    }
};
