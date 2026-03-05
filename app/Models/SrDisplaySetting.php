<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrDisplaySetting extends Model
{
    protected $fillable = [
        'section_name',
        'field_name',
        'field_label',
        'is_visible',
        'sort_order'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Получить настройки отображения для раздела
     */
    public static function getFieldsForSection($sectionName = 'event_description')
    {
        return self::where('section_name', $sectionName)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Получить только видимые поля
     */
    public static function getVisibleFields($sectionName = 'event_description')
    {
        return self::where('section_name', $sectionName)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->pluck('field_name')
            ->toArray();
    }

    /**
     * Проверить видимость поля
     */
    public static function isFieldVisible($fieldName, $sectionName = 'event_description')
    {
        $setting = self::where('section_name', $sectionName)
            ->where('field_name', $fieldName)
            ->first();

        return $setting ? $setting->is_visible : true;
    }
}
