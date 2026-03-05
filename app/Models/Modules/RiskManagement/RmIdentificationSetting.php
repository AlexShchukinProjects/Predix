<?php

namespace App\Models\Modules\RiskManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmIdentificationSetting extends Model
{
    use HasFactory;

    protected $table = 'rm_identification_settings';

    protected $fillable = [
        'hazard_factor',
        'area',
        'hazard_manifestation',
        'document_upload',
        'risk_card_number',
        'safety_admin',
        'registration_date',
        'risk_owner',
        'department_code',
        'risk_assessor',
        'danger_area',
        'hazard_source',
        'email_notifications'
    ];

    protected $casts = [
        'hazard_factor' => 'boolean',
        'area' => 'boolean',
        'hazard_manifestation' => 'boolean',
        'document_upload' => 'boolean',
        'risk_card_number' => 'boolean',
        'safety_admin' => 'boolean',
        'registration_date' => 'boolean',
        'risk_owner' => 'boolean',
        'department_code' => 'boolean',
        'risk_assessor' => 'boolean',
        'danger_area' => 'boolean',
        'hazard_source' => 'boolean',
        'email_notifications' => 'boolean',
    ];
}
