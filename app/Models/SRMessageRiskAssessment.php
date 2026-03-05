<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SRMessageRiskAssessment extends Model
{
    use HasFactory;

    protected $table = 'sr_message_risk_assessments';

    protected $fillable = [
        'sr_message_id',
        'impact_on_safety',
        'barrier_effectiveness',
        'severity_type',
        'probability',
        'severity',
        'risk_level',
        'risk_class',
        'hazards',
        'consequences',
        'matrix',
        'meta',
        'comment',
    ];

    protected $casts = [
        'impact_on_safety' => 'boolean',
        'meta' => 'array',
        'matrix' => 'array',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(SRMessage::class, 'sr_message_id');
    }
}


