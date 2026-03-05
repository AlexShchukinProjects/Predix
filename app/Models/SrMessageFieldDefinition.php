<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrMessageFieldDefinition extends Model
{
    use HasFactory;

    protected $table = 'sr_message_field_definitions';

    protected $fillable = [
        'code',
        'name',
        'field_type',
        'meta',
        'is_active',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];
}


