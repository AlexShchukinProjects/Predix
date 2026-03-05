<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SRMessageTypeSection extends Model
{
    use HasFactory;

    protected $table = 'sr_message_type_sections';
    
    protected $fillable = [
        'message_type_id',
        'name',
        'sort_order'
    ];

    public function messageType(): BelongsTo
    {
        return $this->belongsTo(SRMessageType::class, 'message_type_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(SRMessageTypeField::class, 'section_id')->orderBy('sort_order');
    }
}
