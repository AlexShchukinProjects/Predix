<?php

namespace App\Models\Modules\RiskManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmDepartmentCode extends Model
{
    use HasFactory;

    protected $table = 'rm_department_codes';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
