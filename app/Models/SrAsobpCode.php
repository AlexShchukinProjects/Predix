<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrAsobpCode extends Model
{
    use HasFactory;

    protected $table = 'sr_asobp_codes';

    protected $fillable = [
        'name', 'code', 'active', 'parent_id',
    ];
}


