<?php

declare(strict_types=1);

namespace App\Models\Modules\RiskManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RmRiskNotification extends Model
{
    protected $table = 'rm_risk_notifications';

    protected $fillable = [
        'risk_id',
        'user_id',
        'initial',
        'final',
    ];

    protected $casts = [
        'initial' => 'boolean',
        'final' => 'boolean',
    ];

    public function risk(): BelongsTo
    {
        return $this->belongsTo(RmRisk::class, 'risk_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
