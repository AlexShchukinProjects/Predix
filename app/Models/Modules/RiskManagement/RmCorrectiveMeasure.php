<?php

namespace App\Models\Modules\RiskManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class RmCorrectiveMeasure extends Model
{
    use HasFactory;

    protected $table = 'rm_corrective_measures';

    protected $fillable = [
        'risk_id',
        'description',
        'deadline',
        'executor_id',
        'confirming_user_id',
        'execution_report',
        'execution_date',
        'executed_by_id',
        'status',
        'sort_order',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'deadline' => 'date',
        'execution_date' => 'date',
    ];

    // Отношения
    public function risk(): BelongsTo
    {
        return $this->belongsTo(RmRisk::class, 'risk_id');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function confirmingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirming_user_id');
    }

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RmRiskDocument::class, 'corrective_measure_id');
    }

    // Скоупы
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Методы (статусы как в safety-reporting Мероприятия)
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-info',
            'in_progress' => 'bg-warning',
            'pending_confirmation' => 'bg-primary',
            'completed' => 'bg-success',
            'cancelled' => 'bg-secondary',
            'overdue' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getStatusText(): string
    {
        return match($this->status) {
            'pending' => 'Назначено',
            'in_progress' => 'В работе',
            'pending_confirmation' => 'На подтверждении',
            'completed' => 'Выполнено',
            'cancelled' => 'Отменено',
            'overdue' => 'Просрочено',
            default => 'Неизвестно'
        };
    }
}
