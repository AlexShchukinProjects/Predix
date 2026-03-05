<?php

namespace App\Models\Modules\RiskManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class RmRisk extends Model
{
    use HasFactory;

    protected $table = 'rm_risks';

    protected $fillable = [
        'risk_number',
        'title',
        'description',
        'area_id',
        'department_code_id',
        'program_id',
        'category_id',
        'danger_characteristic_id',
        'hazard_source',
        'hazard_manifestation',
        'performed_work',
        'causes',
        'consequences',
        'safety_admin_id',
        'risk_owner_id',
        'responsible_person_id',
        'risk_level',
        'risk_assessment_comment',
        'assessment_date',
        'assessed_by_id',
        'residual_risk_level',
        'residual_risk_comment',
        'residual_assessment_date',
        'residual_assessed_by_id',
        'status',
        'assessment_model',
        'bowtie_threat_name',
        'bowtie_consequence_name',
        'bowtie_top_event_name',
        'bowtie_effectiveness_preventive',
        'bowtie_effectiveness_mitigative',
        'bowtie_consequence_severity',
        'bowtie_preventive_barriers',
        'bowtie_mitigative_barriers',
        'bowtie_residual_effectiveness_preventive',
        'bowtie_residual_effectiveness_mitigative',
        'bowtie_residual_consequence_severity',
        'bowtie_residual_preventive_barriers',
        'bowtie_residual_mitigative_barriers',
        'measures_required',
        'next_assessment_date',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'causes' => 'array',
        'consequences' => 'array',
        'bowtie_preventive_barriers' => 'array',
        'bowtie_mitigative_barriers' => 'array',
        'bowtie_residual_preventive_barriers' => 'array',
        'bowtie_residual_mitigative_barriers' => 'array',
        'assessment_date' => 'date',
        'residual_assessment_date' => 'date',
        'next_assessment_date' => 'date',
    ];

    // Отношения с классификаторами
    public function area(): BelongsTo
    {
        return $this->belongsTo(RmArea::class, 'area_id');
    }

    /** Множественные области (many-to-many через rm_risk_areas) */
    public function areas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(RmArea::class, 'rm_risk_areas', 'risk_id', 'area_id');
    }

    public function departmentCode(): BelongsTo
    {
        return $this->belongsTo(RmDepartmentCode::class, 'department_code_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(RmProgram::class, 'program_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RmCategory::class, 'category_id');
    }

    public function dangerCharacteristic(): BelongsTo
    {
        return $this->belongsTo(RmDangerCharacteristic::class, 'danger_characteristic_id');
    }

    // Отношения с пользователями
    public function safetyAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'safety_admin_id');
    }

    public function riskOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'risk_owner_id');
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by_id');
    }

    public function residualAssessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'residual_assessed_by_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    // Отношения с корректирующими мероприятиями и документами
    public function correctiveMeasures(): HasMany
    {
        return $this->hasMany(RmCorrectiveMeasure::class, 'risk_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(RmRiskNotification::class, 'risk_id');
    }

    // История оценки риска
    public function assessmentHistory(): HasMany
    {
        return $this->hasMany(RmRiskAssessmentHistory::class, 'risk_id')->orderByDesc('assessment_date')->orderByDesc('created_at');
    }

    // История оценки остаточного риска
    public function residualAssessmentHistory(): HasMany
    {
        return $this->hasMany(RmRiskResidualAssessmentHistory::class, 'risk_id')->orderByDesc('assessment_date')->orderByDesc('created_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RmRiskDocument::class, 'risk_id');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(RmRiskChange::class, 'rm_risk_id');
    }

    // Скоупы
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Методы
    public function generateRiskNumber(): string
    {
        $year = date('Y');
        $prefix = "RM-{$year}-";
        
        $lastRisk = self::where('risk_number', 'like', $prefix . '%')
            ->orderBy('risk_number', 'desc')
            ->first();
        
        if ($lastRisk) {
            $lastNumber = (int) substr($lastRisk->risk_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
