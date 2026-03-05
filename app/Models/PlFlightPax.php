<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlFlightPax extends Model
{
    use HasFactory;

    protected $table = 'pl_flight_pax';

    protected $fillable = [
        'flight_id',
        'full_name',
        'passport_number',
        'issued_by',
        'issue_date',
    ];

    protected $casts = [
        'flight_id' => 'integer',
        'issue_date' => 'date',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }
}
