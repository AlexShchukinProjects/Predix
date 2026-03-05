<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionCaseAnalysis extends Model
{
    protected $table = 'inspection_case_analyses';

    protected $fillable = [
        'work_card_id',
        'tc_number',
        'file_path',
        'file_name',
        'is_critical',
        'remarks',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
    ];

    public function workCard(): BelongsTo
    {
        return $this->belongsTo(InspectionWorkCard::class, 'work_card_id');
    }
}
