<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SRMessageType extends Model
{
    use HasFactory;

    protected $table = 'sr_message_types';
    
    protected $fillable = [
        'name',
        'responsible_user_id',
        'short_name',
        'title',
        'description',
        'color',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(SRMessageTypeSection::class, 'message_type_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
