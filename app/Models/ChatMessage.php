<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'chat_id',
        'user_id',
        'message',
        'reply_to_message_id',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime',
        'attachment_size',
        'is_image',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Чат
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Пользователь, отправивший сообщение
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Сообщение, на которое отвечают
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_message_id');
    }

    /**
     * Boot метод для автоматического обновления updated_at у чата
     */
    protected static function boot(): void
    {
        parent::boot();

        static::created(function (ChatMessage $message): void {
            $message->chat->touch();
        });
    }
}
