<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrewPerformance extends Model
{
    use HasFactory;
    
    protected $table = 'crewperformances';

    protected $guarded = [];
    
    protected $fillable = [
        'Name',
        'Date',
        'TakeoffLanding',
        'crew_id',
        'aircraft_type_id',
        'Active'
    ];
    
    /**
     * Get the crew member that owns this performance record.
     */
    public function crew()
    {
        return $this->belongsTo(Crew::class, 'crew_id');
    }
}
