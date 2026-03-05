<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AircraftsType extends Model
{
    protected $guarded = [];
    public $table = 'aircrafts_types';

    /**
     * ВС данного типа (связь по aircraft_type_id в таблице aircraft).
     */
    public function aircrafts()
    {
        return $this->hasMany(Aircraft::class, 'aircraft_type_id');
    }

    public function flights()
    {
        return $this->hasMany(Flight::class, 'aircraft_type_id');
    }
} 