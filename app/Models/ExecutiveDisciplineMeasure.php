<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExecutiveDisciplineMeasure extends Model
{
    protected $table = 'executive_discipline_measures';

    protected $fillable = [
        'parent_measure_id',
        'description',
        'due_date',
        'responsible_user_id',
        'delegated_to_user_id',
        'confirming_user_id',
        'co_executor_user_ids',
        'status',
        'actual_work_volume',
        'comment',
        'files',
        'sort_order',
        'created_by_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'files' => 'array',
        'co_executor_user_ids' => 'array',
    ];

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function delegatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_to_user_id');
    }

    public function confirmingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirming_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExecutiveDisciplineMeasure::class, 'parent_measure_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ExecutiveDisciplineMeasure::class, 'parent_measure_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(ExecutiveDisciplineMeasureHistory::class, 'measure_id')->orderByDesc('created_at');
    }

    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Ожидает выполнения',
            'in_progress' => 'В процессе',
            'pending_confirmation' => 'На подтверждении',
            'completed' => 'Выполнено',
            'cancelled' => 'Отменено',
            'overdue' => 'Просрочено',
            default => 'Неизвестно',
        };
    }
}
