<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    use HasFactory;

    protected $guarded=[];
    
    protected $fillable = [
        'SurName', 'FirstName', 'MiddleName', 'ShortName', 'NameENG', 'Position', 'position_id', 'TabNumber',
        'DateOfBirth', 'PlaceOfBirth', 'Phone', 'email', 'Address', 'Comment'
    ];
    
    public function aircraftType()
    {
        return $this->belongsTo(AircraftsType::class, 'TypesAC_id');
    }

    public function aircraftTypes()
    {
        return $this->belongsToMany(AircraftsType::class, 'crew_aircraft_types', 'crew_id', 'aircraft_type_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}
