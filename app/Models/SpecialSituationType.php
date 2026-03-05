<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialSituationType extends Model
{
    protected $table = 'special_situation_types';
    
    protected $fillable = [
        'name',
        'abbreviation',
        'coefficient',
        'active',
        'sort_order'
    ];

    protected $casts = [
        'coefficient' => 'integer',
        'active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Получить активные типы особых ситуаций
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Получить типы особых ситуаций, отсортированные по порядку
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Получить коэффициент по сокращению
     */
    public static function getCoefficientByAbbreviation($abbreviation)
    {
        $type = self::where('abbreviation', $abbreviation)->active()->first();
        return $type ? $type->coefficient : 1;
    }

    /**
     * Получить все активные типы с коэффициентами
     */
    public static function getActiveTypes()
    {
        return self::active()->ordered()->get();
    }
}