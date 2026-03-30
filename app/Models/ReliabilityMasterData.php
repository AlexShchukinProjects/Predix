<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReliabilityMasterData extends Model
{
    /** Master data Work Cards (единая таблица; вкладки RC/NRC — фильтр в запросе). */
    protected $table = 'work_cards_master';

    protected $fillable = [
        'project',
        'project_type',
        'aircraft_type',
        'tail_number',
        'wo_station',
        'work_order',
        'item',
        'src_order',
        'src_item',
        'src_cust_card',
        'description',
        'corrective_action',
        'ata',
        'cust_card',
        'order_type',
        'avg_time',
        'act_time',
        'aircraft_location',
    ];
}
