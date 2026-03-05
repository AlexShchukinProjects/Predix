<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SRMessageAction extends Model
{
    use HasFactory;

    protected $table = 'sr_message_actions';

    protected $fillable = [
        'sr_message_id',
        'description',
        'due_date',
        'responsible_user_id',
        'confirming_user_id',
        'actual_work_volume',
        'actual_due_date',
        'comment',
        'files',
        'status',
        'order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'actual_due_date' => 'date',
        'files' => 'array',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(SRMessage::class, 'sr_message_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function confirmingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirming_user_id');
    }

    /**
     * Получить статус мероприятия на русском языке
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Ожидает выполнения',
            'in_progress' => 'В процессе',
            'pending_confirmation' => 'На подтверждении',
            'completed' => 'Выполнено',
            'cancelled' => 'Отменено',
            'overdue' => 'Просрочено',
            default => 'Неизвестно'
        };
    }

    /**
     * Проверить, просрочено ли мероприятие
     */
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Обновить статус на основе дат
     */
    public function updateStatus(): void
    {
        if ($this->isOverdue()) {
            $this->status = 'overdue';
            $this->save();
        }
    }
}


