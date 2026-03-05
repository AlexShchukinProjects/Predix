<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsCrew extends Model
{
    use HasFactory;
    
    protected $table = 'events_crew';
    
    protected $fillable = [
        'flight_id',
        'crew_id', 
        'event_id'
    ];
    
    // Отношения
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }
    
    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }
    
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
