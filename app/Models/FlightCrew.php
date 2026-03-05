<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightCrew extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'crew_id',
        'position',
        'status',
        'comment'
    ];

    protected $casts = [
        'flight_id' => 'integer',
        'crew_id' => 'integer',
    ];

    // Отношение к рейсу
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    // Отношение к сотруднику
    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }
}
