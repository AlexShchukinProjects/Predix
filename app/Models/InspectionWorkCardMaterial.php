<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionWorkCardMaterial extends Model
{
    protected $table = 'inspection_work_card_materials';

    protected $fillable = [
        'project_number',
        'work_order_number',
        'zone_number',
        'item_number',
        'wip_status',
        'card_description',
        'customer_work_card',
        'source_card_number',
        'source_customer_card',
        'tail_number',
        'est_time',
        'tag_number',
        'part_number',
        'description',
        'oem_spec_number',
        'group_code',
        'expire_dt',
        'csp',
        'order_number',
        'req_dt',
        'req_due_dt',
        'req_qty',
        'req_line_internal_comment',
        'location',
        'order_number_2',
        'order_dt',
        'order_due_dt',
        'order_qty',
        'receipt_dt',
        'waybill',
        'eta_dt',
        'status',
        'reason',
        'alloc_qty',
        'unit_cost',
        'item_list_price',
        'order_unit_cost',
        'currency',
    ];

    protected $casts = [
        'est_time' => 'decimal:2',
        'req_qty' => 'decimal:4',
        'order_qty' => 'decimal:4',
        'alloc_qty' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'item_list_price' => 'decimal:4',
        'order_unit_cost' => 'decimal:4',
        'expire_dt' => 'datetime',
        'req_dt' => 'datetime',
        'req_due_dt' => 'datetime',
        'order_dt' => 'datetime',
        'order_due_dt' => 'datetime',
        'receipt_dt' => 'datetime',
        'eta_dt' => 'datetime',
    ];
}
