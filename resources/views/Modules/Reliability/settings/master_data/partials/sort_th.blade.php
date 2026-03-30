@if($sortable ?? true)
@php
    $isActive = isset($sortColumn, $sortDirection) && $sortColumn === $column;
    $dir = $sortDirection ?? 'asc';
    $nextDir = $isActive && $dir === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'dir' => $nextDir, 'page' => 1]);
@endphp
<th scope="col" class="text-nowrap align-middle master-data-sort-th">
    <a href="{{ $url }}" class="link-dark text-decoration-none d-inline-flex align-items-center gap-1">
        <span>{{ $label }}</span>
        @if($isActive)
            <i class="fas fa-sort-{{ $dir === 'asc' ? 'up' : 'down' }}" aria-hidden="true"></i>
        @else
            <i class="fas fa-sort text-muted opacity-50" style="font-size:0.8em" aria-hidden="true"></i>
        @endif
    </a>
</th>
@else
<th scope="col" class="text-nowrap align-middle">
    <span class="d-inline-flex align-items-center gap-1 text-muted">
        <span class="text-body">{{ $label }}</span>
        <i class="fas fa-sort opacity-25" style="font-size:0.8em" aria-hidden="true"></i>
    </span>
</th>
@endif
