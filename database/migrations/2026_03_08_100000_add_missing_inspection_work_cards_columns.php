<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Колонки из 2026_03_07_130000_update_inspection_work_cards_columns:
     * добавляем только те, которых ещё нет в таблице.
     * Отключено: таблица пересоздаётся миграцией 2026_03_08_120000_recreate_inspection_work_cards_table.
     */
    public function up(): void
    {
        // No-op: полная структура таблицы создаётся в 2026_03_08_120000_recreate_inspection_work_cards_table
    }

    /**
     * @return array<string, callable(Blueprint, string): void>
     */
    private function getColumnsDefinition(): array
    {
        // VARCHAR учитывается в лимите размера строки MySQL (8126 байт), TEXT — нет. Используем TEXT для всех строк.
        $defs = [
            'project' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'project_type' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'aircraft_type' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'tail_number' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'bay' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'wo_station' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'work_order' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'zone' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'item' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'quality_code' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'zones' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'status' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'wip_status' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'reason' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'src_order' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'src_zone' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'src_item' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'src_cust_card' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'src_open_dt' => fn (Blueprint $t, string $c) => $t->dateTime($c)->nullable(),
            'description' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'corrective_action' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'open_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'close_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'planned_start' => fn (Blueprint $t, string $c) => $t->dateTime($c)->nullable(),
            'planned_finish_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'card_start_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'card_finish_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'ms_start_day' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'ms_finish_day' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'ms_start_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'ms_finish_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'ms_description' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'prim_skill' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'skill_codes' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'dot' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'ata' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'cust_card' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'task_code' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'order_type' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'contract' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'contract_description' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'mpd_nrc_mhrs' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'appr_time' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'bill_time' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'rem_est' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'rem_appr' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'appl_time' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'avg_time' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'act_time' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'app_user' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'aircraft_location' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'milestone' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'independent_inspector_number' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'inspector' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'inspector_name' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'created_by' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'created_by_name' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'performed_by_employee_number' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'performed_date' => fn (Blueprint $t, string $c) => $t->dateTime($c)->nullable(),
            'wo_dept' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'work_order_department_name' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'shop' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'shop_description' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'department' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'department_name' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'applicable_standard' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'form_applicable_standard' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'form_number' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'panel_codes' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'component_number' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'comp_qty' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 4)->nullable(),
            'serial_number' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'services' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'print_count' => fn (Blueprint $t, string $c) => $t->unsignedInteger($c)->nullable(),
            'check_status' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'check_by_employee_number' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'check_by_employee_name' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'check_date' => fn (Blueprint $t, string $c) => $t->dateTime($c)->nullable(),
            'documents' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'manufacturer' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'estimator_comment' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'representative_comment' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'controller_comment' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'findings' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'customer_number' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'customer' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'inspection_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'part_description' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'auth_type' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'condition_code' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'condition' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'etops' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'critical' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'ils' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'rii' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'cdccl' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'leak_c' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'open' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'close' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'lube' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'sdr' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'structural' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'engine_run' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'on_floor' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'major' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'alter' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'cpcp' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'logon' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'only_assigned' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'aircraft' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'gqar' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'billable' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'lock' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'open_steps_number' => fn (Blueprint $t, string $c) => $t->unsignedInteger($c)->nullable(),
            'total_steps_number' => fn (Blueprint $t, string $c) => $t->unsignedInteger($c)->nullable(),
            'maint_start_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'child_card_count' => fn (Blueprint $t, string $c) => $t->unsignedInteger($c)->nullable(),
            'group_code' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'pocket_number' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'pin_pocket' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'handover' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'incoming_defect' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'mandatory' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'est_mhrs' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'dmi_due_date' => fn (Blueprint $t, string $c) => $t->date($c)->nullable(),
            'dmi_reference' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'cmm_reference' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'ext_no' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'ac_msn' => fn (Blueprint $t, string $c) => $t->text($c)->nullable(),
            'serv_hrs' => fn (Blueprint $t, string $c) => $t->decimal($c, 15, 2)->nullable(),
            'barcode_print_count' => fn (Blueprint $t, string $c) => $t->unsignedInteger($c)->nullable(),
            'completed_time_utc' => fn (Blueprint $t, string $c) => $t->dateTime($c)->nullable(),
        ];

        return $defs;
    }

    public function down(): void
    {
        // No-op (up не добавлял колонки)
    }
};
