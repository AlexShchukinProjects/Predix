<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrAircraftEventType extends Model
{
    use HasFactory;

    protected $table = 'sr_aircraft_event_types';

    protected $fillable = [
        'name', 'code', 'active', 'symbol',
    ];

    /** Допустимые значения символа для отображения типа события */
    public const SYMBOL_OPTIONS = [
        'circle' => 'Круг',
        'square' => 'Квадрат',
        'diamond' => 'Ромб',
        'triangle' => 'Треугольник',
    ];
}


