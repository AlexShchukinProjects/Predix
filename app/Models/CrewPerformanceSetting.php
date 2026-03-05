<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrewPerformanceSetting extends Model
{
    use HasFactory;

    protected $table = 'pl_crew_performance_settings';

    protected $fillable = [
        'red_threshold',
        'yellow_threshold',
        'green_threshold',
    ];

    protected $casts = [
        'red_threshold' => 'integer',
        'yellow_threshold' => 'integer',
        'green_threshold' => 'integer',
    ];

    /**
     * Получить настройки порогов (singleton - всегда одна запись)
     */
    public static function getThresholds()
    {
        $settings = self::first();
        
        if (!$settings) {
            // Если записи нет, создаем с настройками по умолчанию
            $settings = self::create([
                'red_threshold' => 0,
                'yellow_threshold' => 10,
                'green_threshold' => 10,
            ]);
        }
        
        return [
            'red_threshold' => $settings->red_threshold,
            'yellow_threshold' => $settings->yellow_threshold,
            'green_threshold' => $settings->green_threshold,
        ];
    }

    /**
     * Обновить настройки порогов
     */
    public static function updateThresholds($red, $yellow, $green)
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'red_threshold' => $red,
                'yellow_threshold' => $yellow,
                'green_threshold' => $green,
            ]);
        } else {
            $settings->update([
                'red_threshold' => $red,
                'yellow_threshold' => $yellow,
                'green_threshold' => $green,
            ]);
        }
        
        return $settings;
    }
}
