<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SRMessageTypeField extends Model
{
    use HasFactory;

    protected $table = 'sr_message_type_fields';
    
    protected $fillable = [
        'section_id',
        'name',
        'type',
        'is_required',
        'is_wide',
        'grid_row',
        'grid_col',
        'grid_span_cols',
        'grid_span_rows',
        'options',
        'reference',
        'sort_order'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_wide' => 'boolean',
        'options' => 'array'
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(SRMessageTypeSection::class, 'section_id');
    }
}
