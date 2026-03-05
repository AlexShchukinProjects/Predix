<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SRMessage extends Model
{
    use HasFactory;

    protected $table = 'sr_messages';

    protected $fillable = [
        'sr_message_type_id',
        'title',
        'description',
        'status',
        'created_by',
        'assigned_to',
        'is_aviation_event',
        'responsible_user_id',
        'actions_required',
        'metadata',
        'initial_safety_notification_sent_at',
        'final_safety_notification_sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'actions_required' => 'boolean',
        'initial_safety_notification_sent_at' => 'datetime',
        'final_safety_notification_sent_at' => 'datetime',
    ];

    public function messageType(): BelongsTo
    {
        return $this->belongsTo(SRMessageType::class, 'sr_message_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function data(): HasMany
    {
        return $this->hasMany(SRMessageData::class, 'sr_message_id');
    }

    public function eventDescription(): HasOne
    {
        return $this->hasOne(SRMessageEventDescription::class, 'sr_message_id');
    }

    public function riskAssessment(): HasOne
    {
        return $this->hasOne(SRMessageRiskAssessment::class, 'sr_message_id');
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(SRMessageAnalysis::class, 'sr_message_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(SRMessageAction::class, 'sr_message_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(SRMessageFeedback::class, 'sr_message_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(SRMessageNotification::class, 'sr_message_id');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(SRMessageChange::class, 'sr_message_id');
    }
}
