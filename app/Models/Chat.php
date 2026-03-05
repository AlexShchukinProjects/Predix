<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Участники чата
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class);
    }

    /**
     * Сообщения чата
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Создатель чата
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Проверка, является ли чат приватным
     */
    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }

    /**
     * Проверка, является ли чат групповым
     */
    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    /**
     * Получить другого участника приватного чата
     */
    public function getOtherParticipant(int $userId): ?User
    {
        if (!$this->isPrivate()) {
            return null;
        }

        $participant = $this->participants()
            ->where('user_id', '!=', $userId)
            ->first();

        return $participant?->user;
    }

    /**
     * Получить название чата для отображения
     */
    public function getDisplayName(int $currentUserId): string
    {
        if ($this->isGroup()) {
            return $this->name ?? 'Группа';
        }

        $otherUser = $this->getOtherParticipant($currentUserId);
        return $otherUser?->name ?? 'Пользователь';
    }
}
