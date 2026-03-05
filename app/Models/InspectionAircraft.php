<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionAircraft extends Model
{
    protected $table = 'inspection_aircrafts';

    protected $fillable = [
        'serial_number',
        'tail_number',
        'aircraft_type',
        'visit',
        'customer_number',
        'owner_number',
        'engine_type',
        'apu_type',
        'group_code',
        'delivery_date',
        'redelivery_date',
        'etops',
        'amm_group',
        'customer_name',
        'owner_name',
        'app_std',
        'line_no',
        'variable_no',
        'effectivity',
        'selcal',
        'lease_date',
        'manufactured',
        'ins_date',
        'pas_cap',
        'seat_mat',
        'max_taxi',
        'max_to',
        'max_land',
        'maximum_zero_fuel_weight',
        'max_pay',
        'dry_ope',
        'fuel',
        'fuel_burn_ratio',
        'fwd_cargo',
        'aft_cargo',
        'fwd_area',
        'aft_area',
        'side_noise',
        'app_noise',
        'start_noise',
        'eng_rate',
        'mod',
        'color',
        'flight_number',
        'scheduled_from',
        'scheduled_to',
        'scheduled_off_block',
        'scheduled_on_block',
        'actual_from',
        'actual_to',
        'actual_off_block',
        'actual_on_block',
        'route_dev_dist',
        'route_dev_time',
        'wng',
        'archive',
        'active',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'redelivery_date' => 'date',
        'lease_date' => 'date',
        'ins_date' => 'date',
    ];
}
