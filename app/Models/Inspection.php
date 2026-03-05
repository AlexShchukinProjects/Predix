<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    use HasFactory;

    protected $table = 'insp_inspections';

    protected $fillable = [
        'checklist_id',
        'user_id',
        'doc_number',
        'conducted_date',
        'status',
        'score',
        'title_page',
        'notes',
        'completion_recommendations',
        'report_approval_status',
    ];

    protected $casts = [
        'conducted_date' => 'date',
        'title_page' => 'array',
        'score' => 'integer',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InspectionAnswer::class, 'inspection_id');
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(InspectionRemark::class, 'inspection_id');
    }

    public function reportApprovals(): HasMany
    {
        return $this->hasMany(InspectionReportApproval::class, 'inspection_id')->orderBy('sort_order')->orderBy('id');
    }
}
