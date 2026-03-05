<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flightcheck extends Model
{
    use HasFactory;
    protected $guarded=[];
    
    public function requirement()
    {
        return $this->belongsTo(Requirement::class, 'requirement_id');
    }
}
