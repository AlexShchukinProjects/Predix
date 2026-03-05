<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionWorkCard extends Model
{
    protected $table = 'inspection_work_cards';

    protected $fillable = [
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

    protected $casts = [
        'src_open_dt' => 'datetime',
        'open_date' => 'date',
        'close_date' => 'date',
        'planned_start' => 'datetime',
        'planned_finish_date' => 'date',
        'card_start_date' => 'date',
        'card_finish_date' => 'date',
        'ms_start_date' => 'date',
        'ms_finish_date' => 'date',
        'performed_date' => 'datetime',
        'check_date' => 'datetime',
        'inspection_date' => 'date',
        'maint_start_date' => 'date',
        'dmi_due_date' => 'date',
        'completed_time_utc' => 'datetime',
        'mpd_nrc_mhrs' => 'decimal:2',
        'appr_time' => 'decimal:2',
        'bill_time' => 'decimal:2',
        'rem_est' => 'decimal:2',
        'rem_appr' => 'decimal:2',
        'appl_time' => 'decimal:2',
        'avg_time' => 'decimal:2',
        'act_time' => 'decimal:2',
        'comp_qty' => 'decimal:4',
        'est_mhrs' => 'decimal:2',
        'serv_hrs' => 'decimal:2',
    ];

    public function caseAnalyses(): HasMany
    {
        return $this->hasMany(InspectionCaseAnalysis::class, 'work_card_id');
    }
}
