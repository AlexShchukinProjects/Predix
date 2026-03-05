<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionRemark extends Model
{
    protected $table = 'insp_remarks';

    protected $fillable = [
        'inspection_id',
        'inspection_answer_id',
        'ncr_number',
        'question_text',
        'nonconformity_type_id',
        'issue_date',
        'deadline',
        'responsible_id',
        'confirming_user_id',
        'status',
        'comment',
        'place',
        'aircraft_number',
        'flight_number',
        'auditor',
        'root_cause',
        'corrective_actions',
        'corrective_comments',
        'confirm_comment',
        'media_files',
        'corrective_attachments',
        'report_attachments',
        'sort_order',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'deadline' => 'date',
        'media_files' => 'array',
        'corrective_attachments' => 'array',
        'report_attachments' => 'array',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id');
    }

    public function inspectionAnswer(): BelongsTo
    {
        return $this->belongsTo(InspectionAnswer::class, 'inspection_answer_id');
    }

    public function nonconformityType(): BelongsTo
    {
        return $this->belongsTo(NonconformityType::class, 'nonconformity_type_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function confirmingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirming_user_id');
    }
}
