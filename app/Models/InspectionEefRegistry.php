<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionEefRegistry extends Model
{
    protected $table = 'inspection_eef_registry';

    protected $fillable = [
        'eef_number',
        'nrc_number',
        'ac_type',
        'ata',
        'project_no',
        'subject',
        'remarks',
        'location',
        'eef_status',
        'link',
        'link_path',
        'man_hours',
        'chargeable_to_customer',
        'customer_name',
        'inspection_source_task',
        'rc_number',
        'open_date',
        'assigned_engineering_engineer',
        'open_continuation_raised_by_production_dates',
        'answer_provided_by_engineering_dates',
        'oem_communication_reference',
        'gaes_eo',
        'manual_limits_out_within',
        'backup_engineer',
        'project_status',
        'eef_priority',
        'latest_processing',
        'project_status2',
        'eef_with',
        'standard_remarks_on_current_progress',
        'latest_comments_short_answer',
        'project_status3',
    ];

    protected $casts = [
        'man_hours' => 'decimal:2',
        'open_date' => 'date',
    ];
}
