<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'requirement_type_id',
        'name',
        'short_name',
        'description',
        'active',
        'sort_order',
        'validity_period_months',
        'control_level_days',
        'warning_level_days',
        'for_all_aircraft_types'
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
        'validity_period_months' => 'integer',
        'control_level_days' => 'integer',
        'warning_level_days' => 'integer',
        'for_all_aircraft_types' => 'boolean'
    ];

    /**
     * Get the requirement type that owns this requirement
     */
    public function requirementType(): BelongsTo
    {
        return $this->belongsTo(RequirementType::class);
    }

    /**
     * Scope to get only active requirements
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
