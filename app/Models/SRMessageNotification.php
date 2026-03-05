<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SRMessageNotification extends Model
{
    use HasFactory;

    protected $table = 'sr_event_description_message_notifications';

    protected $fillable = [
        'sr_message_id',
        'user_id',
        'initial',
        'final',
    ];

    protected $casts = [
        'initial' => 'boolean',
        'final' => 'boolean',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(SRMessage::class, 'sr_message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
