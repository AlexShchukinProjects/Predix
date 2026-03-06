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
     * Failure form fields list: field key => label for settings.
     */
    public static function formFieldsList(): array
    {
        return [
            'account_number' => 'Account number (Enterprise code KUN)',
            'failure_date' => 'Detection date',
            'aircraft_number' => 'Aircraft reg. no.',
            'aircraft_type' => 'Aircraft type',
            'type_code' => 'Aircraft type (code)',
            'modification_code' => 'Modification (code)',
            'aircraft_hours' => 'Aircraft hours',
            'aircraft_landings' => 'Aircraft landings',
            'aircraft_ppr_hours' => 'Aircraft MRO (hours)',
            'aircraft_ppr_landings' => 'Aircraft MRO (landings)',
            'detection_stage' => 'Failure detection stage',
            'aircraft_malfunction' => 'Aircraft malfunction manifestation',
            'aggregate_type' => 'Aggregate type',
            'part_number_off' => 'P/N OFF',
            'component_serial' => 'S/N OFF',
            'part_number_on' => 'P/N ON',
            'serial_number_on' => 'S/N ON',
            'system_name' => 'System',
            'subsystem_name' => 'Subsystem',
            'component_sne_hours' => 'Component TSN',
            'component_ppr_hours' => 'Component TSO',
            'component_hours_unit' => 'Component hours unit',
            'resolution_date' => 'Resolution date',
            'component_cause' => 'Component malfunction cause',
            'taken_measure_id' => 'Taken measures',
            'wo_number' => 'Work orders',
            'wo_status_id' => 'WO status',
            'work_order_number' => 'Work order card number',
            'resolution_method' => 'Resolution method',
            'aircraft_serial' => 'Aircraft serial no.',
            'aircraft_manufacture_date' => 'Aircraft manufacture date',
            'aircraft_repair_date' => 'Aircraft repair date',
            'previous_repair_location' => 'Previous repair location',
            'aircraft_repairs_count' => 'Aircraft repairs count',
            'operator' => 'Operator',
            'event_location' => 'Event location',
            'consequence_id' => 'Consequences',
            'component_malfunction' => 'Component malfunction manifestation',
            'manufacturer' => 'Manufacturer',
            'removal_date' => 'Removal date',
            'production_date' => 'Production date',
            'component_repairs_count' => 'Component repairs count',
            'previous_installation_date' => 'Previous aggregate installation date',
            'repair_factory' => 'Repair facility',
            'component_repair_date' => 'Component repair date',
            'engine_type_id' => 'Engine type',
            'engine_number_id' => 'Engine number',
            'engine_release_date' => 'Engine release date',
            'engine_installation_date' => 'Last installation on aircraft date',
            'engine_sne_hours' => 'Engine TSN (hours)',
            'engine_ppr_hours' => 'Engine TSO (hours)',
            'engine_sne_cycles' => 'Engine TSN (cycles)',
            'engine_ppr_cycles' => 'Engine TSO (cycles)',
            'engine_repair_date' => 'Engine repair date',
            'engine_repair_location' => 'Engine repair location',
            'engine_repairs_count' => 'Engine repairs count',
            'owner' => 'Owner',
            'position' => 'Position',
            'created_by' => 'User who created KUNAT',
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
     * Reliability module tabs list.
     */
    public static function tabsList(): array
    {
        return [
            'failures' => 'Failures',
            'defects' => 'Defects',
            'monitoring' => 'Monitoring',
            'aging_aircraft' => 'Aircraft aging',
            'aging_components' => 'Component aging',
            'systems' => 'Systems',
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