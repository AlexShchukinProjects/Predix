<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightService extends Model
{
    use HasFactory;

    protected $table = 'flight_servises';

    protected $fillable = [
        'flight_id',
        'service_id',
        'service_name',
        'template',
    ];

    protected $casts = [
        'flight_id' => 'integer',
        'service_id' => 'integer',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}


