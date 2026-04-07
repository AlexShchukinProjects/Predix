@php
    $sortColumn = $sortColumn ?? 'id';
    $sortDirection = $sortDirection ?? 'asc';
    $sortTh = 'Modules.Reliability.settings.master_data.partials.sort_th';
    $pairs = [
        ['id', 'id'],
        ['serial_number', 'SERIAL #'],
        ['line_no', 'LN'],
        ['aircraft_type', 'TYPE'],
        ['type_ac', 'TYPE AC'],
        ['customer_name', 'AIRLINE'],
        ['first_flight', 'FIRST FLIGHT'],
        ['tail_number', 'TAIL #'],
        ['status', 'STATUS'],
        ['created_at', 'created_at'],
        ['updated_at', 'updated_at'],
    ];
@endphp
@foreach ($pairs as [$col, $label])
    @include($sortTh, ['column' => $col, 'label' => $label, 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
@endforeach
