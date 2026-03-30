@php
    $sortColumn = $sortColumn ?? 'id';
    $sortDirection = $sortDirection ?? 'asc';
    $sortTh = 'Modules.Reliability.settings.master_data.partials.sort_th';
    $pairs = [
        ['id', 'id'],
        ['project_number', 'PROJECT #'],
        ['work_order_number', 'WORK ORDER #'],
        ['zone_number', 'ZONE #'],
        ['item_number', 'ITEM #'],
        ['wip_status', 'WIP STATUS'],
        ['card_description', 'CARD DESCRIPTION'],
        ['customer_work_card', 'CUSTOMER WORK CARD'],
        ['source_card_number', 'SOURCE CARD #'],
        ['source_customer_card', 'SOURCE CUSTOMER CARD'],
        ['tail_number', 'TAIL #'],
        ['est_time', 'EST. TIME'],
        ['tag_number', 'TAG #'],
        ['part_number', 'PART #'],
        ['description', 'DESCRIPTION'],
        ['oem_spec_number', 'OEM SPEC. #'],
        ['group_code', 'GROUP CODE'],
        ['expire_dt', 'EXPIRE DT.'],
        ['csp', 'CSP'],
        ['order_number', 'ORDER #'],
        ['req_dt', 'REQ. DT.'],
        ['req_due_dt', 'REQ. DUE DT.'],
        ['req_qty', 'REQ. QTY.'],
        ['req_line_internal_comment', 'REQ. LINE INTERNAL COMMENT'],
        ['location', 'LOCATION'],
        ['order_number_2', 'ORDER #'],
        ['order_dt', 'ORDER DT.'],
        ['order_due_dt', 'ORDER DUE DT.'],
        ['order_qty', 'ORDER QTY.'],
        ['receipt_dt', 'RECEIPT DT.'],
        ['waybill', 'WAYBILL'],
        ['eta_dt', 'ETA DT.'],
        ['status', 'STATUS'],
        ['reason', 'REASON'],
        ['alloc_qty', 'ALLOC. QTY.'],
        ['unit_cost', 'UNIT COST'],
        ['item_list_price', 'ITEM LIST PRICE'],
        ['order_unit_cost', 'ORDER UNIT COST'],
        ['currency', 'CURRENCY'],
        ['created_at', 'created_at'],
        ['updated_at', 'updated_at'],
    ];
@endphp
@foreach ($pairs as [$col, $label])
    @include($sortTh, ['column' => $col, 'label' => $label, 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
@endforeach
