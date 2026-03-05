<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrTimeOfDay extends Model
{
    use HasFactory;

    protected $table = 'sr_time_of_day';

    protected $fillable = [
        'name', 'code', 'active',
    ];
}
