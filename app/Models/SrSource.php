<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrSource extends Model
{
    use HasFactory;

    protected $table = 'sr_sources';

    protected $fillable = [
        'name', 'code', 'active',
    ];
}


