<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrHazardFactorDetail extends Model
{
    use HasFactory;

    protected $table = 'sr_hazard_factor_details';

    protected $fillable = [
        'hazard_factor_id', 'name', 'code', 'active',
    ];
}


