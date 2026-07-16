<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReliabilityCardFormatRule extends Model
{
    protected $table = 'reliability_card_format_rules';

    protected $fillable = [
        'name',
        'document_type',
        'oem',
        'mask',
        'digit_blocks',
        'is_builtin',
        'is_active',
        'priority',
        'example_raw',
        'example_normalized',
        'mapping',
    ];

    protected $casts = [
        'digit_blocks' => 'array',
        'mapping' => 'array',
        'is_builtin' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];
}
