@php
    $sortColumn = $sortColumn ?? 'id';
    $sortDirection = $sortDirection ?? 'asc';
    $sortTh = 'Modules.Reliability.settings.master_data.partials.sort_th';
    $pairs = [
        ['id', 'id'],
        ['code', 'code'],
        ['name', 'name'],
        ['created_at', 'created_at'],
        ['updated_at', 'updated_at'],
    ];
@endphp
@foreach ($pairs as [$col, $label])
    @include($sortTh, ['column' => $col, 'label' => $label, 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
@endforeach
