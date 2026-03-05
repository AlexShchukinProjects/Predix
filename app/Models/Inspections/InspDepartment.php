<?php

declare(strict_types=1);

namespace App\Models\Inspections;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspDepartment extends Model
{
    use HasFactory;

    protected $table = 'insp_departments';

    protected $fillable = [
        'name',
    ];
}

