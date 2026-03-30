@php
    $sortColumn = $sortColumn ?? 'id';
    $sortDirection = $sortDirection ?? 'asc';
    $sortTh = 'Modules.Reliability.settings.master_data.partials.sort_th';
    $pairs = [
        ['id', 'id'],
        ['work_card_id', 'work_card_id'],
        ['tc_number', 'tc_number'],
        ['file_path', 'file_path'],
        ['file_name', 'file_name'],
        ['is_critical', 'is_critical'],
        ['remarks', 'remarks'],
        ['created_at', 'created_at'],
        ['updated_at', 'updated_at'],
    ];
@endphp
@foreach ($pairs as [$col, $label])
    @include($sortTh, ['column' => $col, 'label' => $label, 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
@endforeach
