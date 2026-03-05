<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightPax extends Model
{
    use HasFactory;

    protected $table = 'flight_pax';

    protected $fillable = [
        'flight_id',
        'passenger_id',
        'seat_number',
        'status',
        'notes'
    ];

    protected $casts = [
        'flight_id' => 'integer',
        'passenger_id' => 'integer',
        'seat_number' => 'integer',
    ];

    // Отношение к рейсу
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    // Отношение к пассажиру
    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
} 