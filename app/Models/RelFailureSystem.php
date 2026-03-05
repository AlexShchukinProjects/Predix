<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\StubRelModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelFailureSystem extends Model
{
    use HasFactory;
    use StubRelModel;

    protected $table = 'rel_stub';

    protected $guarded = [];

    public function aircraftType()
    {
        return $this->belongsTo(\App\Models\AircraftsType::class, 'aircraft_type_id');
    }

    public function aggregates()
    {
        return $this->hasMany(RelFailureAggregate::class, 'failure_system_id');
    }
}


