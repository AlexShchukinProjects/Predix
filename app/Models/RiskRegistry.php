<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskRegistry extends Model
{
    use HasFactory;

    protected $fillable = [
        'dangerous_factor',
        'factor_manifestation',
        'area',
        'responsible_person',
        'initial_risk_level',
        'initial_assessment_date',
        'measures',
        'residual_risk_level',
        'reassessment_date',
        'next_assessment_date'
    ];

    protected $casts = [
        'initial_assessment_date' => 'date',
        'reassessment_date' => 'date',
        'next_assessment_date' => 'date'
    ];
}
