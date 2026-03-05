<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['project_id', 'source_card_ref_id'] as $column) {
            try {
                Schema::table('inspection_work_cards', function (Blueprint $table) use ($column) {
                    $table->dropForeign([$column]);
                });
            } catch (QueryException $e) {
                if (str_contains($e->getMessage(), 'check that it exists') === false) {
                    throw $e;
                }
            }
        }

        $columnsToDrop = [
            'tc_number', 'project_id', 'ac_registration', 'source_card_ref', 'source_card_ref_id',
            'rc_nrc_description', 'rectification_action_ref', 'skill_code', 'mhrs_spent',
            'no_of_child_cards', 'source', 'flight_cycles', 'flight_hours',
        ];
        $existingColumns = Schema::getColumnListing('inspection_work_cards');
        $dropThese = array_values(array_intersect($columnsToDrop, $existingColumns));
        if ($dropThese !== []) {
            Schema::table('inspection_work_cards', function (Blueprint $table) use ($dropThese) {
                $table->dropColumn($dropThese);
            });
        }

        if (Schema::hasColumn('inspection_work_cards', 'project')) {
            return;
        }

        Schema::table('inspection_work_cards', function (Blueprint $table) {
            $table->string('project', 80)->nullable()->after('id');
            $table->text('project_type')->nullable();
            $table->text('aircraft_type')->nullable();
            $table->text('tail_number')->nullable();
            $table->string('bay', 50)->nullable();
            $table->text('wo_station')->nullable();
            $table->text('work_order')->nullable();
            $table->string('zone', 50)->nullable();
            $table->string('item', 50)->nullable();
            $table->string('quality_code', 50)->nullable();
            $table->text('zones')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('wip_status', 50)->nullable();
            $table->text('reason')->nullable();
            $table->string('src_order', 80)->nullable();
            $table->string('src_zone', 50)->nullable();
            $table->string('src_item', 50)->nullable();
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
            $table->string('ms_start_day', 50)->nullable();
            $table->string('ms_finish_day', 50)->nullable();
            $table->date('ms_start_date')->nullable();
            $table->date('ms_finish_date')->nullable();
            $table->text('ms_description')->nullable();
            $table->string('prim_skill', 50)->nullable();
            $table->text('skill_codes')->nullable();
            $table->string('dot', 50)->nullable();
            $table->string('ata', 50)->nullable();
            $table->text('cust_card')->nullable();
            $table->text('task_code')->nullable();
            $table->string('order_type', 50)->nullable();
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
            $table->string('app_user', 80)->nullable();
            $table->text('aircraft_location')->nullable();
            $table->text('milestone')->nullable();
            $table->string('independent_inspector_number', 80)->nullable();
            $table->text('inspector')->nullable();
            $table->text('inspector_name')->nullable();
            $table->string('created_by', 80)->nullable();
            $table->text('created_by_name')->nullable();
            $table->string('performed_by_employee_number', 80)->nullable();
            $table->dateTime('performed_date')->nullable();
            $table->string('wo_dept', 80)->nullable();
            $table->text('work_order_department_name')->nullable();
            $table->text('shop')->nullable();
            $table->text('shop_description')->nullable();
            $table->text('department')->nullable();
            $table->text('department_name')->nullable();
            $table->text('applicable_standard')->nullable();
            $table->text('form_applicable_standard')->nullable();
            $table->string('form_number', 80)->nullable();
            $table->text('panel_codes')->nullable();
            $table->text('component_number')->nullable();
            $table->decimal('comp_qty', 15, 4)->nullable();
            $table->text('serial_number')->nullable();
            $table->text('services')->nullable();
            $table->unsignedInteger('print_count')->nullable();
            $table->string('check_status', 80)->nullable();
            $table->string('check_by_employee_number', 80)->nullable();
            $table->text('check_by_employee_name')->nullable();
            $table->dateTime('check_date')->nullable();
            $table->text('documents')->nullable();
            $table->text('manufacturer')->nullable();
            $table->text('estimator_comment')->nullable();
            $table->text('representative_comment')->nullable();
            $table->text('controller_comment')->nullable();
            $table->text('findings')->nullable();
            $table->string('customer_number', 80)->nullable();
            $table->text('customer')->nullable();
            $table->date('inspection_date')->nullable();
            $table->text('part_description')->nullable();
            $table->string('auth_type', 80)->nullable();
            $table->string('condition_code', 50)->nullable();
            $table->text('condition')->nullable();
            $table->string('etops', 50)->nullable();
            $table->string('critical', 50)->nullable();
            $table->string('ils', 50)->nullable();
            $table->string('rii', 50)->nullable();
            $table->string('cdccl', 50)->nullable();
            $table->string('leak_c', 50)->nullable();
            $table->string('open', 50)->nullable();
            $table->string('close', 50)->nullable();
            $table->string('lube', 50)->nullable();
            $table->string('sdr', 50)->nullable();
            $table->string('structural', 50)->nullable();
            $table->string('engine_run', 50)->nullable();
            $table->string('on_floor', 50)->nullable();
            $table->string('major', 50)->nullable();
            $table->string('alter', 50)->nullable();
            $table->string('cpcp', 50)->nullable();
            $table->string('logon', 50)->nullable();
            $table->string('only_assigned', 50)->nullable();
            $table->text('aircraft')->nullable();
            $table->string('gqar', 50)->nullable();
            $table->string('billable', 50)->nullable();
            $table->string('lock', 50)->nullable();
            $table->unsignedInteger('open_steps_number')->nullable();
            $table->unsignedInteger('total_steps_number')->nullable();
            $table->date('maint_start_date')->nullable();
            $table->unsignedInteger('child_card_count')->nullable();
            $table->string('group_code', 50)->nullable();
            $table->string('pocket_number', 50)->nullable();
            $table->string('pin_pocket', 80)->nullable();
            $table->text('handover')->nullable();
            $table->text('incoming_defect')->nullable();
            $table->string('mandatory', 50)->nullable();
            $table->decimal('est_mhrs', 15, 2)->nullable();
            $table->date('dmi_due_date')->nullable();
            $table->text('dmi_reference')->nullable();
            $table->text('cmm_reference')->nullable();
            $table->string('ext_no', 80)->nullable();
            $table->string('ac_msn', 80)->nullable();
            $table->decimal('serv_hrs', 15, 2)->nullable();
            $table->unsignedInteger('barcode_print_count')->nullable();
            $table->dateTime('completed_time_utc')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_work_cards', function (Blueprint $table) {
            $cols = [
                'project', 'project_type', 'aircraft_type', 'tail_number', 'bay', 'wo_station', 'work_order',
                'zone', 'item', 'quality_code', 'zones', 'status', 'wip_status', 'reason', 'src_order', 'src_zone',
                'src_item', 'src_cust_card', 'src_open_dt', 'description', 'corrective_action', 'open_date', 'close_date',
                'planned_start', 'planned_finish_date', 'card_start_date', 'card_finish_date', 'ms_start_day', 'ms_finish_day',
                'ms_start_date', 'ms_finish_date', 'ms_description', 'prim_skill', 'skill_codes', 'dot', 'ata', 'cust_card',
                'task_code', 'order_type', 'contract', 'contract_description', 'mpd_nrc_mhrs', 'appr_time', 'bill_time',
                'rem_est', 'rem_appr', 'appl_time', 'avg_time', 'act_time', 'app_user', 'aircraft_location', 'milestone',
                'independent_inspector_number', 'inspector', 'inspector_name', 'created_by', 'created_by_name',
                'performed_by_employee_number', 'performed_date', 'wo_dept', 'work_order_department_name', 'shop', 'shop_description',
                'department', 'department_name', 'applicable_standard', 'form_applicable_standard', 'form_number', 'panel_codes',
                'component_number', 'comp_qty', 'serial_number', 'services', 'print_count', 'check_status', 'check_by_employee_number',
                'check_by_employee_name', 'check_date', 'documents', 'manufacturer', 'estimator_comment', 'representative_comment',
                'controller_comment', 'findings', 'customer_number', 'customer', 'inspection_date', 'part_description', 'auth_type',
                'condition_code', 'condition', 'etops', 'critical', 'ils', 'rii', 'cdccl', 'leak_c', 'open', 'close', 'lube', 'sdr',
                'structural', 'engine_run', 'on_floor', 'major', 'alter', 'cpcp', 'logon', 'only_assigned', 'aircraft', 'gqar', 'billable',
                'lock', 'open_steps_number', 'total_steps_number', 'maint_start_date', 'child_card_count', 'group_code', 'pocket_number',
                'pin_pocket', 'handover', 'incoming_defect', 'mandatory', 'est_mhrs', 'dmi_due_date', 'dmi_reference', 'cmm_reference',
                'ext_no', 'ac_msn', 'serv_hrs', 'barcode_print_count', 'completed_time_utc',
            ];
            $table->dropColumn($cols);
        });

        Schema::table('inspection_work_cards', function (Blueprint $table) {
            $table->string('tc_number', 100)->nullable()->after('id');
            $table->foreignId('project_id')->nullable()->constrained('inspection_projects')->nullOnDelete();
            $table->string('ac_registration', 50)->nullable();
            $table->string('source_card_ref', 255)->nullable();
            $table->foreignId('source_card_ref_id')->nullable()->constrained('inspection_source_card_refs')->nullOnDelete();
            $table->text('rc_nrc_description')->nullable();
            $table->text('rectification_action_ref')->nullable();
            $table->string('skill_code', 50)->nullable();
            $table->decimal('mhrs_spent', 12, 2)->nullable();
            $table->unsignedInteger('no_of_child_cards')->nullable();
            $table->string('source', 20)->nullable();
            $table->decimal('flight_cycles', 15, 2)->nullable();
            $table->decimal('flight_hours', 15, 2)->nullable();
        });
    }
};
