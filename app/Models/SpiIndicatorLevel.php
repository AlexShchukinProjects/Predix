<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpiIndicatorLevel extends Model
{
    protected $table = 'spi_indicator_levels';
    
    protected $fillable = [
        'indicator_name',
        'year',
        'avg_level_prev_year',
        'standard_deviation',
        'recommended_level',
        'target_level',
        'plus_1_sd',
        'plus_2_sd',
        'plus_3_sd',
        'target_level_enabled',
        'plus_1_sd_enabled',
        'plus_2_sd_enabled',
        'plus_3_sd_enabled'
    ];

    protected $casts = [
        'avg_level_prev_year' => 'decimal:4',
        'standard_deviation' => 'decimal:4',
        'recommended_level' => 'decimal:4',
        'target_level' => 'decimal:4',
        'plus_1_sd' => 'decimal:4',
        'plus_2_sd' => 'decimal:4',
        'plus_3_sd' => 'decimal:4',
        'target_level_enabled' => 'boolean',
        'plus_1_sd_enabled' => 'boolean',
        'plus_2_sd_enabled' => 'boolean',
        'plus_3_sd_enabled' => 'boolean'
    ];

    /**
     * Получить данные за определенный год
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Получить данные для определенного показателя
     */
    public function scopeForIndicator($query, $indicatorName)
    {
        return $query->where('indicator_name', $indicatorName);
}

    /**
     * Получить все показатели за год
     */
    public static function getIndicatorsForYear($year)
    {
        $indicators = [
            'Интегральный',
            'Абсолютное количество авиационных событий (КПН, ЧФ,ВФ)',
            'Абсолютное количество авиационных событий по типам',
            'Абсолютное количество авиационных инцидентов по типам (Ч,М,С)',
            'Инциденты на 1000 часов налета',
            'Инциденты на 1000 летных циклов',
        ];

        $data = [];
        foreach ($indicators as $indicator) {
            $level = self::where('indicator_name', $indicator)
                ->where('year', $year)
                ->first();
            
            $data[] = [
                'indicator_name' => $indicator,
                'avg_level_prev_year' => $level ? $level->avg_level_prev_year : null,
                'standard_deviation' => $level ? $level->standard_deviation : null,
                'recommended_level' => $level ? $level->recommended_level : null,
                'target_level' => $level ? $level->target_level : null,
                'plus_1_sd' => $level ? $level->plus_1_sd : null,
                'plus_2_sd' => $level ? $level->plus_2_sd : null,
                'plus_3_sd' => $level ? $level->plus_3_sd : null,
                'target_level_enabled' => $level ? $level->target_level_enabled : false,
                'plus_1_sd_enabled' => $level ? $level->plus_1_sd_enabled : false,
                'plus_2_sd_enabled' => $level ? $level->plus_2_sd_enabled : false,
                'plus_3_sd_enabled' => $level ? $level->plus_3_sd_enabled : false,
            ];
        }

        return $data;
    }
}