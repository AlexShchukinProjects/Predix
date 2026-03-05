<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SRMessageEventDescription extends Model
{
    use HasFactory;

    protected $table = 'sr_message_event_descriptions';

    protected $fillable = [
        'sr_message_id',
        'event_date',
        'event_time_utc',
        'event_time_local',
        'time_of_day_id',
        'hazardous_weather_id',
        'weather_cond_id',
        'operation_stage_id',
        'aircraft_event_type_id',
        'special_situation_type',
        'event_causes',
        'other_cause_description',
        'source_id',
        'customer_id',
        'flight_number',
        'airport',
        'aircraft_type_icao',
        'aircraft_regn',
        'department',
        'asobp',
        'description',
        'captain',
        'first_officer',
        'other_participants',
        'external_participants',
        'departure_airport',
        'arrival_airport',
        'summary',
        'details',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'event_date' => 'date',
        'special_situation_type' => 'integer',
        'event_causes' => 'array',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(SRMessage::class, 'sr_message_id');
    }

    public function aircraftEventType(): BelongsTo
    {
        return $this->belongsTo(SrAircraftEventType::class, 'aircraft_event_type_id');
    }

    public function specialSituationType(): BelongsTo
    {
        return $this->belongsTo(SpecialSituationType::class, 'special_situation_type');
    }
}


