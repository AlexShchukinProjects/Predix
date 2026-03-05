<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightReadinessType extends Model
{
    use HasFactory;

    protected $table = 'flight_readiness_type';

    protected $fillable = [
        'flight_id',
        'readiness_type_id',
        'is_completed',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function readinessType()
    {
        return $this->belongsTo(ReadinessType::class);
    }
} 