<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SRMessageData extends Model
{
    use HasFactory;

    protected $table = 'sr_message_data';

    protected $fillable = [
        'sr_message_id',
        'sr_message_type_section_id',
        'sr_message_type_field_id',
        'value',
        'file_data'
    ];

    protected $casts = [
        'file_data' => 'array',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(SRMessage::class, 'sr_message_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SRMessageTypeSection::class, 'sr_message_type_section_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(SRMessageTypeField::class, 'sr_message_type_field_id');
    }
}
