<?php

declare(strict_types=1);

namespace App\Models\Modules\RiskManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RmRiskResidualAssessmentHistory extends Model
{
    protected $table = 'rm_risk_residual_assessment_history';

    protected $fillable = [
        'risk_id',
        'assessed_by_id',
        'assessment_date',
        'risk_level',
    ];

    protected $casts = [
        'assessment_date' => 'date',
    ];

    public function risk(): BelongsTo
    {
        return $this->belongsTo(RmRisk::class, 'risk_id');
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by_id');
    }
}
