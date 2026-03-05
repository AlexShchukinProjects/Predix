<?php

namespace App\Models;

use App\Models\Concerns\StubRelModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReliabilityFailure extends Model
{
    use HasFactory;
    use StubRelModel;

    protected $table = 'rel_stub';

    protected $fillable = [
        'account_number',
        'source_record_id',
        'failure_date',
        'aircraft_number',
        'aircraft_type',
        'aircraft_type_code',
        'modification_code',
        'aircraft_serial',
        'aircraft_manufacture_date',
        'aircraft_hours',
        'aircraft_landings',
        'aircraft_ppr_hours',
        'aircraft_ppr_landings',
        'aircraft_repair_date',
        'previous_repair_location',
        'aircraft_repairs_count',
        'operator',
        'detection_stage_id',
        'aircraft_malfunction',
        'event_location',
        'consequence_id',
        'wo_number',
        'wo_status_id',
        'work_order_number',
        'system_name',
        'subsystem_name',
        'component_malfunction',
        'component_cause',
        'taken_measure_id',
        'resolution_method',
        'resolution_date',
        'aggregate_type',
        'part_number_off',
        'component_serial',
        'part_number_on',
        'serial_number_on',
        'manufacturer',
        'removal_date',
        'component_sne_hours',
        'component_ppr_hours',
        'component_hours_unit',
        'production_date',
        'component_repairs_count',
        'previous_installation_date',
        'repair_factory',
        'component_repair_date',
        'engine_type_id',
        'engine_number_id',
        'engine_release_date',
        'engine_installation_date',
        'engine_sne_hours',
        'engine_ppr_hours',
        'engine_sne_cycles',
        'engine_ppr_cycles',
        'engine_repair_date',
        'engine_repair_location',
        'engine_repairs_count',
        'owner',
        'position',
        'created_by_id',
        'include_in_buf',
    ];

    protected $attributes = [
        'include_in_buf' => true,
    ];

    protected $casts = [
        'include_in_buf' => 'boolean',
    ];

    public function detectionStage()
    {
        return $this->belongsTo(RelFailureDetectionStage::class, 'detection_stage_id');
    }

    public function consequence()
    {
        return $this->belongsTo(RelFailureConsequence::class, 'consequence_id');
    }

    public function takenMeasure()
    {
        return $this->belongsTo(\App\Models\RelTakenMeasure::class, 'taken_measure_id');
    }

    public function woStatus()
    {
        return $this->belongsTo(RelWoStatus::class, 'wo_status_id');
    }

    public function engineType()
    {
        return $this->belongsTo(RelEngineType::class, 'engine_type_id');
    }

    public function engineNumber()
    {
        return $this->belongsTo(RelEngineNumber::class, 'engine_number_id');
    }

    public function attachments()
    {
        return $this->hasMany(RelFailureAttachment::class, 'failure_id')->orderBy('sort_order');
    }
}
