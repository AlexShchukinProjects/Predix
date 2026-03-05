<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrFactor extends Model
{
    use HasFactory;

    protected $table = 'sr_factors';

    protected $fillable = [
        'name',
        'color',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
