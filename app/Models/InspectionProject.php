<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionProject extends Model
{
    protected $table = 'inspection_projects';

    protected $fillable = [
        'project_number', 'status', 'tail_number', 'aircraft_type', 'scope', 'open_date', 'close_date',
        'customer_number', 'customer_name', 'customer_po', 'est_non_routine', 'target_days', 'arrival_date',
        'induction_date', 'inspection_date', 'delivery_date', 'rev_delivery_date', 'latest_delivery_date',
        'actual_arrival_date', 'actual_induction_date', 'actual_inspection_date', 'actual_delivery_date',
        'project_type', 'applicable_standard', 'resources', 'bay', 'planned_span', 'day_of_check',
        'aircraft_tsn', 'aircraft_csn', 'engine_type', 'quoted_mhrs', 'oa_mhrs', 'add_works_mhrs', 'cwr_mhrs',
        'aircraft_series', 'station', 'open_requisitions', 'open_order_lines', 'awaiting_to_return_store',
        'uninvoice_order_lines', 'open_work_cards', 'open_work_orders', 'eng_mhrs', 'total_mhrs',
        'engine_1_serial', 'engine_2_serial', 'engine_3_serial', 'engine_4_serial',
        'engine_1_tsn', 'engine_1_csn', 'engine_2_tsn', 'engine_2_csn', 'engine_3_tsn', 'engine_3_csn',
        'engine_4_tsn', 'engine_4_csn', 'apu_pn', 'apu_serial', 'apu_tsn', 'apu_csn',
        'spares_order_cut_off', 'spares_delivery_cut_off', 'mhrs_cap',
    ];

    protected $casts = [
        'open_date' => 'date',
        'close_date' => 'date',
        'arrival_date' => 'date',
        'induction_date' => 'date',
        'inspection_date' => 'date',
        'delivery_date' => 'date',
        'rev_delivery_date' => 'date',
        'latest_delivery_date' => 'date',
        'actual_arrival_date' => 'date',
        'actual_induction_date' => 'date',
        'actual_inspection_date' => 'date',
        'actual_delivery_date' => 'date',
        'spares_order_cut_off' => 'date',
        'spares_delivery_cut_off' => 'date',
        'aircraft_tsn' => 'decimal:2',
        'aircraft_csn' => 'decimal:2',
        'quoted_mhrs' => 'decimal:2',
        'oa_mhrs' => 'decimal:2',
        'add_works_mhrs' => 'decimal:2',
        'cwr_mhrs' => 'decimal:2',
        'eng_mhrs' => 'decimal:2',
        'total_mhrs' => 'decimal:2',
        'engine_1_tsn' => 'decimal:2',
        'engine_1_csn' => 'decimal:2',
        'engine_2_tsn' => 'decimal:2',
        'engine_2_csn' => 'decimal:2',
        'engine_3_tsn' => 'decimal:2',
        'engine_3_csn' => 'decimal:2',
        'engine_4_tsn' => 'decimal:2',
        'engine_4_csn' => 'decimal:2',
        'apu_tsn' => 'decimal:2',
        'apu_csn' => 'decimal:2',
        'mhrs_cap' => 'decimal:2',
    ];
}
