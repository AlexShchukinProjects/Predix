<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NrcMasterData extends Model
{
    protected $table = 'NRC_master_data';

    protected $fillable = [
        'id_file',
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
