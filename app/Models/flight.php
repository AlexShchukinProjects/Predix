<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function readinessTypes()
    {
        return $this->belongsToMany(ReadinessType::class, 'flight_readiness_type')
            ->withPivot(['is_completed', 'completed_at', 'notes'])
            ->withTimestamps();
            
    }

    public function flightReadinessTypes()
    {
        return $this->hasMany(FlightReadinessType::class);
    }

    public function crew()
    {
        return $this->belongsToMany(Crew::class, 'flight_crews', 'flight_id', 'crew_id')
            ->withTimestamps();
    }
    
    public function crews()
    {
        return $this->belongsToMany(Crew::class, 'flight_crews', 'flight_id', 'crew_id')
            ->withTimestamps();
    }

    public function passengers()
    {
        return $this->belongsToMany(Passenger::class, 'flight_pax')
            ->withPivot(['seat_number', 'status', 'notes'])
            ->withTimestamps();
    }

    public function plFlightPax()
    {
        return $this->hasMany(PlFlightPax::class, 'flight_id');
    }

    public function eventsCrew()
    {
        return $this->hasMany(EventsCrew::class);
    }

    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class, 'aircraft_id');
    }

    public function aircraftRelation()
    {
        return $this->belongsTo(Aircraft::class, 'aircraft_id');
    }

    public function aircraftType()
    {
        return $this->belongsTo(AircraftsType::class, 'aircraft_type_id');
    }

    public function departureAirportRelation()
    {
        return $this->belongsTo(Airports::class, 'departure_airport_id');
    }

    public function arrivalAirportRelation()
    {
        return $this->belongsTo(Airports::class, 'arrival_airport_id');
    }
}


