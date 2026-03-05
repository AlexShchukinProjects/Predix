<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'Name',
        'Pass',
        'DateofBirth',
        'Nationality',
        'ValidTill'
    ];

    protected $casts = [
        'DateofBirth' => 'date',
        'ValidTill' => 'date'
    ];
} 