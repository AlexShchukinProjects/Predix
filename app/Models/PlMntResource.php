<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlMntResource extends Model
{
    use HasFactory;

    protected $table = 'pl_mnt_resources';

    protected $guarded = [];

    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class, 'aircraft_id');
    }
}


