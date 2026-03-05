<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'duration_hours',
        'valid_from',
        'valid_to',
        'active',
    ];

    protected $casts = [
        'duration_hours' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'active' => 'boolean',
    ];

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order');
    }
}


