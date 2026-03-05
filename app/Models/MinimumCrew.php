<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinimumCrew extends Model
{
    use HasFactory;

    protected $table = 'minimum_crew';

    protected $fillable = [
        'aircraft_type_id',
        'position_id',
        'quantity'
    ];

    public function aircraftType()
    {
        return $this->belongsTo(AircraftsType::class, 'aircraft_type_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}
