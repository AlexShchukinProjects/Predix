<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\StubRelModel;
use Illuminate\Database\Eloquent\Model;

class RelFailureFormSetting extends Model
{
    use StubRelModel;

    protected $table = 'rel_stub';

    protected $fillable = ['key', 'value'];

    public const VISIBILITY_KEY = 'field_visibility';
    public const TABS_VISIBILITY_KEY = 'tabs_visibility';

    /**
     * Список полей формы отказа: ключ поля => подпись для настроек.
     */
    public static function formFieldsList(): array
    {
        return [
            'account_number' => 'Учетный номер (Код предприятия номер КУН)',
            'failure_date' => 'Дата обнаружения',
            'aircraft_number' => 'Бортовой № ВС',
            'aircraft_type' => 'Тип ВС',
            'type_code' => 'Тип ВС (код)',
            'modification_code' => 'Модификация (код)',
            'aircraft_hours' => 'Наработка ВС в часах',
            'aircraft_landings' => 'Наработка ВС в посадках',
            'aircraft_ppr_hours' => 'Наработка ВС ППР (час)',
            'aircraft_ppr_landings' => 'Наработка ВС ППР (посадки)',
            'detection_stage' => 'Этап обнаружения отказа',
            'aircraft_malfunction' => 'Проявление неисправности ВС',
            'aggregate_type' => 'Тип агрегата',
            'part_number_off' => 'P/N OFF',
            'component_serial' => 'S/N OFF',
            'part_number_on' => 'P/N ON',
            'serial_number_on' => 'S/N ON',
            'system_name' => 'Система',
            'subsystem_name' => 'Подсистема',
            'component_sne_hours' => 'Наработка СНЭ',
            'component_ppr_hours' => 'Наработка ППР',
            'component_hours_unit' => 'Единица измерения наработки КИ',
            'resolution_date' => 'Дата устранения',
            'component_cause' => 'Причина неисправности КИ',
            'taken_measure_id' => 'Принятые меры',
            'wo_number' => 'Work orders',
            'wo_status_id' => 'Статус WO',
            'work_order_number' => 'Номер карты наряд',
            'resolution_method' => 'Метод устранения',
            'aircraft_serial' => 'Заводской номер ВС',
            'aircraft_manufacture_date' => 'Дата изготовления ВС',
            'aircraft_repair_date' => 'Дата ремонта ВС',
            'previous_repair_location' => 'Место предыдущего ремонта',
            'aircraft_repairs_count' => 'Количество ремонтов ВС',
            'operator' => 'Эксплуатант',
            'event_location' => 'Место события',
            'consequence_id' => 'Последствия',
            'component_malfunction' => 'Проявление неисправности КИ',
            'manufacturer' => 'Завод изготовитель',
            'removal_date' => 'Дата демонтажа',
            'production_date' => 'Дата производства',
            'component_repairs_count' => 'Количество ремонтов КИ',
            'previous_installation_date' => 'Предыдущая дата установки агрегата',
            'repair_factory' => 'Ремонтный завод',
            'component_repair_date' => 'Дата ремонта КИ',
            'engine_type_id' => 'Тип двигателя',
            'engine_number_id' => 'Номер двигателя',
            'engine_release_date' => 'Дата выпуска двигателя',
            'engine_installation_date' => 'Дата последней установки на ВС',
            'engine_sne_hours' => 'Наработка двигателя СНЭ (часы)',
            'engine_ppr_hours' => 'Наработка двигателя ППР (часы)',
            'engine_sne_cycles' => 'Наработка двигателя СНЭ (циклы)',
            'engine_ppr_cycles' => 'Наработка двигателя ППР (циклы)',
            'engine_repair_date' => 'Дата ремонта двигателя',
            'engine_repair_location' => 'Место ремонта двигателя',
            'engine_repairs_count' => 'Количество ремонтов двигателя',
            'owner' => 'Собственник',
            'position' => 'Позиция',
            'created_by' => 'Пользователь создавший КУНАТ',
        ];
    }

    /**
     * Получить массив видимости полей: [ field_key => true|false ].
     * Заглушка: все поля видимы.
     */
    public static function getFieldVisibility(): array
    {
        $list = self::formFieldsList();
        return array_fill_keys(array_keys($list), true);
    }

    /**
     * Сохранить видимость полей. Заглушка: не сохраняет.
     */
    public static function setFieldVisibility(array $visibility): void
    {
        // no-op
    }

    /**
     * Список вкладок модуля Надёжность.
     */
    public static function tabsList(): array
    {
        return [
            'failures' => 'Отказы',
            'defects' => 'Дефекты',
            'monitoring' => 'Мониторинг',
            'aging_aircraft' => 'Старение ВС',
            'aging_components' => 'Старение КИ',
            'systems' => 'Системы',
        ];
    }

    /**
     * Видимость вкладок. Заглушка: все вкладки видимы.
     */
    public static function getTabsVisibility(): array
    {
        $list = self::tabsList();
        return array_fill_keys(array_keys($list), true);
    }

    /**
     * Сохранить видимость вкладок. Заглушка: не сохраняет.
     */
    public static function setTabsVisibility(array $visibility): void
    {
        // no-op
    }
}