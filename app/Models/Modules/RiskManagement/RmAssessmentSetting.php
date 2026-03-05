<?php

namespace App\Models\Modules\RiskManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmAssessmentSetting extends Model
{
    use HasFactory;

    protected $table = 'rm_assessment_settings';

    protected $fillable = [
        'risk_matrix'
    ];

    protected $casts = [
        'risk_matrix' => 'string'
    ];
}