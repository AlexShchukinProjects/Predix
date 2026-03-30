<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Удаляем таблицу work_cards и создаём заново: все строковые колонки — TEXT,
     * чтобы не превышать лимит размера строки MySQL (8126 байт).
     */
    public function up(): void
    {
        $this->dropForeignKeys();

        Schema::dropIfExists('work_cards');

        Schema::create('work_cards', function (Blueprint $table) {
            $table->id();
            $table->text('project')->nullable();
            $table->text('project_type')->nullable();
            $table->text('aircraft_type')->nullable();
            $table->text('tail_number')->nullable();
            $table->text('bay')->nullable();
            $table->text('wo_station')->nullable();
            $table->text('work_order')->nullable();
            $table->text('zone')->nullable();
            $table->text('item')->nullable();
            $table->text('quality_code')->nullable();
            $table->text('zones')->nullable();
            $table->text('status')->nullable();
            $table->text('wip_status')->nullable();
            $table->text('reason')->nullable();
            $table->text('src_order')->nullable();
            $table->text('src_zone')->nullable();
            $table->text('src_item')->nullable();
            $table->text('src_cust_card')->nullable();
            $table->dateTime('src_open_dt')->nullable();
            $table->text('description')->nullable();
            $table->text('corrective_action')->nullable();
            $table->date('open_date')->nullable();
            $table->date('close_date')->nullable();
            $table->dateTime('planned_start')->nullable();
            $table->date('planned_finish_date')->nullable();
            $table->date('card_start_date')->nullable();
            $table->date('card_finish_date')->nullable();
            $table->text('ms_start_day')->nullable();
            $table->text('ms_finish_day')->nullable();
            $table->date('ms_start_date')->nullable();
            $table->date('ms_finish_date')->nullable();
            $table->text('ms_description')->nullable();
            $table->text('prim_skill')->nullable();
            $table->text('skill_codes')->nullable();
            $table->text('dot')->nullable();
            $table->text('ata')->nullable();
            $table->text('cust_card')->nullable();
            $table->text('task_code')->nullable();
            $table->text('order_type')->nullable();
            $table->text('contract')->nullable();
            $table->text('contract_description')->nullable();
            $table->decimal('mpd_nrc_mhrs', 15, 2)->nullable();
            $table->decimal('appr_time', 15, 2)->nullable();
            $table->decimal('bill_time', 15, 2)->nullable();
            $table->decimal('rem_est', 15, 2)->nullable();
            $table->decimal('rem_appr', 15, 2)->nullable();
            $table->decimal('appl_time', 15, 2)->nullable();
            $table->decimal('avg_time', 15, 2)->nullable();
            $table->decimal('act_time', 15, 2)->nullable();
            $table->text('app_user')->nullable();
            $table->text('aircraft_location')->nullable();
            $table->text('milestone')->nullable();
            $table->text('independent_inspector_number')->nullable();
            $table->text('inspector')->nullable();
            $table->text('inspector_name')->nullable();
            $table->text('created_by')->nullable();
            $table->text('created_by_name')->nullable();
            $table->text('performed_by_employee_number')->nullable();
            $table->dateTime('performed_date')->nullable();
            $table->text('wo_dept')->nullable();
            $table->text('work_order_department_name')->nullable();
            $table->text('shop')->nullable();
            $table->text('shop_description')->nullable();
            $table->text('department')->nullable();
            $table->text('department_name')->nullable();
            $table->text('applicable_standard')->nullable();
            $table->text('form_applicable_standard')->nullable();
            $table->text('form_number')->nullable();
            $table->text('panel_codes')->nullable();
            $table->text('component_number')->nullable();
            $table->decimal('comp_qty', 15, 4)->nullable();
            $table->text('serial_number')->nullable();
            $table->text('services')->nullable();
            $table->unsignedInteger('print_count')->nullable();
            $table->text('check_status')->nullable();
            $table->text('check_by_employee_number')->nullable();
            $table->text('check_by_employee_name')->nullable();
            $table->dateTime('check_date')->nullable();
            $table->text('documents')->nullable();
            $table->text('manufacturer')->nullable();
            $table->text('estimator_comment')->nullable();
            $table->text('representative_comment')->nullable();
            $table->text('controller_comment')->nullable();
            $table->text('findings')->nullable();
            $table->text('customer_number')->nullable();
            $table->text('customer')->nullable();
            $table->date('inspection_date')->nullable();
            $table->text('part_description')->nullable();
            $table->text('auth_type')->nullable();
            $table->text('condition_code')->nullable();
            $table->text('condition')->nullable();
            $table->text('etops')->nullable();
            $table->text('critical')->nullable();
            $table->text('ils')->nullable();
            $table->text('rii')->nullable();
            $table->text('cdccl')->nullable();
            $table->text('leak_c')->nullable();
            $table->text('open')->nullable();
            $table->text('close')->nullable();
            $table->text('lube')->nullable();
            $table->text('sdr')->nullable();
            $table->text('structural')->nullable();
            $table->text('engine_run')->nullable();
            $table->text('on_floor')->nullable();
            $table->text('major')->nullable();
            $table->text('alter')->nullable();
            $table->text('cpcp')->nullable();
            $table->text('logon')->nullable();
            $table->text('only_assigned')->nullable();
            $table->text('aircraft')->nullable();
            $table->text('gqar')->nullable();
            $table->text('billable')->nullable();
            $table->text('lock')->nullable();
            $table->unsignedInteger('open_steps_number')->nullable();
            $table->unsignedInteger('total_steps_number')->nullable();
            $table->date('maint_start_date')->nullable();
            $table->unsignedInteger('child_card_count')->nullable();
            $table->text('group_code')->nullable();
            $table->text('pocket_number')->nullable();
            $table->text('pin_pocket')->nullable();
            $table->text('handover')->nullable();
            $table->text('incoming_defect')->nullable();
            $table->text('mandatory')->nullable();
            $table->decimal('est_mhrs', 15, 2)->nullable();
            $table->date('dmi_due_date')->nullable();
            $table->text('dmi_reference')->nullable();
            $table->text('cmm_reference')->nullable();
            $table->text('ext_no')->nullable();
            $table->text('ac_msn')->nullable();
            $table->decimal('serv_hrs', 15, 2)->nullable();
            $table->unsignedInteger('barcode_print_count')->nullable();
            $table->dateTime('completed_time_utc')->nullable();
            $table->timestamps();
        });

        $this->restoreForeignKeys();
    }

    private function dropForeignKeys(): void
    {
        foreach (['eef_registry', 'case_analyses'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['work_card_id']);
                });
            } catch (QueryException $e) {
                if (str_contains($e->getMessage(), 'check that it exists') === false) {
                    throw $e;
                }
            }
        }
    }

    private function restoreForeignKeys(): void
    {
        if (Schema::hasTable('eef_registry')) {
            if (! Schema::hasColumn('eef_registry', 'work_card_id')) {
                Schema::table('eef_registry', function (Blueprint $table) {
                    $table->unsignedBigInteger('work_card_id')->nullable()->after('id');
                });
            }
            Schema::table('eef_registry', function (Blueprint $table) {
                $table->foreign('work_card_id')->references('id')->on('work_cards')->nullOnDelete();
            });
        }
        if (Schema::hasTable('case_analyses')) {
            if (! Schema::hasColumn('case_analyses', 'work_card_id')) {
                Schema::table('case_analyses', function (Blueprint $table) {
                    $table->unsignedBigInteger('work_card_id')->nullable()->after('id');
                });
            }
            Schema::table('case_analyses', function (Blueprint $table) {
                $table->foreign('work_card_id')->references('id')->on('work_cards')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $this->dropForeignKeys();
        Schema::dropIfExists('work_cards');
        $this->restoreForeignKeys();

        // Восстанавливаем исходную таблицу из create_inspection_data_tables (без колонок из update)
        Schema::create('work_cards', function (Blueprint $table) {
            $table->id();
            $table->string('tc_number', 100)->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('ac_registration', 50)->nullable();
            $table->string('source_card_ref', 255)->nullable();
            $table->foreignId('source_card_ref_id')->nullable()->constrained('source_card_refs')->nullOnDelete();
            $table->text('rc_nrc_description')->nullable();
            $table->text('rectification_action_ref')->nullable();
            $table->string('skill_code', 50)->nullable();
            $table->decimal('mhrs_spent', 12, 2)->nullable();
            $table->unsignedInteger('no_of_child_cards')->nullable();
            $table->string('source', 20)->nullable();
            $table->decimal('flight_cycles', 15, 2)->nullable();
            $table->decimal('flight_hours', 15, 2)->nullable();
            $table->timestamps();
        });

        $this->restoreForeignKeys();
    }
};
