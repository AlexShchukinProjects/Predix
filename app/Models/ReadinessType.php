<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadinessType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function flights()
    {
        return $this->belongsToMany(Flight::class, 'flight_readiness_type')
                    ->withPivot(['is_completed', 'completed_at', 'notes'])
                    ->withTimestamps();
    }

    public function flightReadinessTypes()
    {
        return $this->hasMany(FlightReadinessType::class);
    }
}
