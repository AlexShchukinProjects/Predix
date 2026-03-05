<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    use HasFactory;
    
    protected $table = 'aircraft';
    protected $guarded = [];

    /**
     * Связь с типом ВС (таблица aircrafts_types) по внешнему ключу aircraft_type_id.
     */
    public function aircraftType()
    {
        return $this->belongsTo(AircraftsType::class, 'aircraft_type_id');
    }

    public function flights()
    {
        return $this->hasMany(Flight::class, 'aircraft', 'RegN');
    }
}
