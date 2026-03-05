<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'Name',
        'short_name',
        'crew_type',
        'Active',
        'sort_order'
    ];

    protected $casts = [
        'Active' => 'boolean'
    ];
}
