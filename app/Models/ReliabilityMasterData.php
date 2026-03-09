<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReliabilityMasterData extends Model
{
    protected $table = 'reliability_master_data';

    protected $fillable = [
        'aircraft_type',
        'src_cust_card',
        'description',
        'prim_skill',
        'order_type',
        'act_time',
        'child_card_count',
        'eef',
    ];
}
