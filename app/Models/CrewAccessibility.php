<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrewAccessibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'crew_id',
        'aircraft_type_id',
        'date',
        'activity_type',
        'event_id',
        'work_description',
        'flight_time_minutes',
        'flying_time_minutes',
        'night_time_minutes',
        'work_time_minutes',
        'activity_group',
        'display_order',
    ];

    protected $casts = [
        'date' => 'date',
        'flight_time_minutes' => 'integer',
        'flying_time_minutes' => 'integer',
        'night_time_minutes' => 'integer',
        'work_time_minutes' => 'integer',
        'display_order' => 'integer',
    ];
}


