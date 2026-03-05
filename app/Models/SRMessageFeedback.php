<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SRMessageFeedback extends Model
{
    use HasFactory;

    protected $table = 'sr_message_feedback';

    protected $fillable = [
        'sr_message_id',
        'feedback_status',
        'responsible_user_id',
        'content',
        'recipients',
        'status',
        'files',
    ];

    protected $casts = [
        'recipients' => 'array',
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
}


