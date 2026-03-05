<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpiSetting extends Model
{
    protected $table = 'spi_settings';

    /** Кэш значений в рамках одного запроса (снижает число запросов к БД) */
    protected static array $valueCache = [];

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description'
    ];

    /**
     * Получить значение настройки (с кэшированием по ключу в рамках запроса).
     */
    public static function getValue($key, $default = null)
    {
        if (array_key_exists($key, static::$valueCache)) {
            return static::$valueCache[$key];
        }

        $setting = self::where('setting_key', $key)->first();

        if (!$setting) {
            static::$valueCache[$key] = $default;
            return $default;
        }

        $value = static::parseSettingValue($setting);
        static::$valueCache[$key] = $value;
        return $value;
    }

    /**
     * Преобразовать запись настройки в значение по setting_type.
     */
    protected static function parseSettingValue(self $setting)
    {
        switch ($setting->setting_type) {
            case 'boolean':
                return (bool) $setting->setting_value;
            case 'numeric':
                return (float) $setting->setting_value;
            case 'json':
                return json_decode($setting->setting_value, true);
            default:
                return $setting->setting_value;
        }
    }

    /**
     * Установить значение настройки
     */
    public static function setValue($key, $value, $type = 'string', $description = null)
    {
        $settingValue = $value;
        
        switch ($type) {
            case 'boolean':
                $settingValue = $value ? '1' : '0';
                break;
            case 'json':
                $settingValue = json_encode($value);
                break;
            default:
                $settingValue = (string) $value;
        }
        
        return self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $settingValue,
                'setting_type' => $type,
                'description' => $description
            ]
        );
    }

    /**
     * Получить все настройки показателей (один запрос к spi_settings).
     */
    public static function getIndicatorsSettings()
    {
        $keysWithDefaults = [
            'integral_indicator' => true,
            'absolute_incidents' => true,
            'absolute_events_by_types' => false,
            'absolute_incidents_by_types' => false,
            'absolute_events_by_types_period' => 'month',
            'absolute_incidents_by_types_period' => 'month',
            'incidents_per_1000_hours' => true,
            'incidents_per_1000_cycles' => true,
            'flights_count_indicator' => false,
            'flight_hours_amount_indicator' => false,
            'flights_count_indicator_period' => 'month',
            'flight_hours_amount_indicator_period' => 'month',
            'aviation_events' => true,
            'aviation_events_show_code' => true,
            'aviation_events_show_type_os' => true,
            'aviation_events_show_description' => true,
            'aviation_events_show_note' => true,
            'safety_level_sensor' => true,
            'show_trends' => true,
            'show_comparison' => true,
            'absolute_incidents_matrix' => [],
            'absolute_incidents_event_types' => [],
            'absolute_incidents_event_type_values' => [],
            'incidents_per_1000_hours_event_types' => [],
            'incidents_per_1000_hours_event_type_values' => [],
            'incidents_per_1000_cycles_event_types' => [],
            'incidents_per_1000_cycles_event_type_values' => [],
            'integral_indicator_chart_min' => 0,
            'integral_indicator_chart_max' => 100,
            'absolute_incidents_chart_min' => 0,
            'absolute_incidents_chart_max' => 10,
            'incidents_per_1000_hours_chart_min' => 0,
            'incidents_per_1000_hours_chart_max' => 10,
            'incidents_per_1000_cycles_chart_min' => 0,
            'incidents_per_1000_cycles_chart_max' => 10,
            'incidents_per_1000_hours_display_type' => 'bar_chart',
            'incidents_per_1000_cycles_display_type' => 'bar_chart',
            'incidents_per_1000_hours_period' => 365,
            'incidents_per_1000_cycles_period' => 365,
            'incidents_per_1000_hours_show_table' => true,
            'incidents_per_1000_cycles_show_table' => true,
            'incidents_per_1000_hours_table_period' => 'month',
            'incidents_per_1000_cycles_table_period' => 'month',
            'flight_data_period_type' => 'month',
            'custom_cycles_1' => false,
            'custom_cycles_2' => false,
            'custom_cycles_3' => false,
            'custom_cycles_1_title' => 'Кастомный Инциденты на 1000 летных циклов',
            'custom_cycles_2_title' => 'Кастомный2 Инциденты на 1000 летных циклов',
            'custom_cycles_3_title' => 'Кастомный3 Инциденты на 1000 летных циклов',
            'custom_cycles_1_aircraft_type_ids' => [],
            'custom_cycles_1_event_types' => [],
            'custom_cycles_1_event_type_values' => [],
            'custom_cycles_1_display_type' => 'bar_chart',
            'custom_cycles_1_period' => 365,
            'custom_cycles_1_show_table' => true,
            'custom_cycles_1_table_period' => 'month',
            'custom_cycles_1_chart_min' => 0,
            'custom_cycles_1_chart_max' => 10,
            'custom_cycles_2_aircraft_type_ids' => [],
            'custom_cycles_2_event_types' => [],
            'custom_cycles_2_event_type_values' => [],
            'custom_cycles_2_display_type' => 'bar_chart',
            'custom_cycles_2_period' => 365,
            'custom_cycles_2_show_table' => true,
            'custom_cycles_2_table_period' => 'month',
            'custom_cycles_2_chart_min' => 0,
            'custom_cycles_2_chart_max' => 10,
            'custom_cycles_3_aircraft_type_ids' => [],
            'custom_cycles_3_event_types' => [],
            'custom_cycles_3_event_type_values' => [],
            'custom_cycles_3_display_type' => 'bar_chart',
            'custom_cycles_3_period' => 365,
            'custom_cycles_3_show_table' => true,
            'custom_cycles_3_table_period' => 'month',
            'custom_cycles_3_chart_min' => 0,
            'custom_cycles_3_chart_max' => 10,
            'indicators_order' => [
                'integral_indicator',
                'absolute_incidents',
                'absolute_events_by_types',
                'absolute_incidents_by_types',
                'incidents_per_1000_hours',
                'incidents_per_1000_cycles',
                'flights_count_indicator',
                'flight_hours_amount_indicator',
                'custom_cycles_1',
                'custom_cycles_2',
                'custom_cycles_3',
            ],
        ];

        $keys = array_keys($keysWithDefaults);
        $rows = self::whereIn('setting_key', $keys)->get()->keyBy('setting_key');

        $settings = [];
        foreach ($keysWithDefaults as $key => $default) {
            $setting = $rows->get($key);
            if (!$setting) {
                $settings[$key] = $default;
            } else {
                $val = static::parseSettingValue($setting);
                $floatChartKeys = ['integral_indicator_chart_min', 'integral_indicator_chart_max', 'absolute_incidents_chart_min', 'absolute_incidents_chart_max', 'incidents_per_1000_hours_chart_min', 'incidents_per_1000_hours_chart_max', 'incidents_per_1000_cycles_chart_min', 'incidents_per_1000_cycles_chart_max', 'custom_cycles_1_chart_min', 'custom_cycles_1_chart_max', 'custom_cycles_2_chart_min', 'custom_cycles_2_chart_max', 'custom_cycles_3_chart_min', 'custom_cycles_3_chart_max'];
                $intPeriodKeys = ['incidents_per_1000_hours_period', 'incidents_per_1000_cycles_period', 'custom_cycles_1_period', 'custom_cycles_2_period', 'custom_cycles_3_period'];
                $settings[$key] = in_array($key, $floatChartKeys, true)
                    ? (float) $val
                    : (in_array($key, $intPeriodKeys, true) ? (int) $val : $val);
            }
            static::$valueCache[$key] = $settings[$key];
        }

        // Загружаем коэффициенты из справочника special_situation_types (один запрос)
        $specialSituationTypes = \App\Models\SpecialSituationType::getActiveTypes();
        foreach ($specialSituationTypes as $type) {
            $coefficientKey = 'coefficient_' . $type->abbreviation;
            $settings[$coefficientKey] = $type->coefficient;
        }

        return $settings;
    }
}