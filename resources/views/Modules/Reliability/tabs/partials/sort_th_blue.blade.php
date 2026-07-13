@php
    $sortColumn = $sortColumn ?? 'failure_date';
    $sortDirection = $sortDirection ?? 'desc';
    $isActive = ($sortable ?? true) && isset($column) && $sortColumn === $column;
    $dir = $sortDirection;
    $nextDir = $isActive && $dir === 'asc' ? 'desc' : 'asc';
    $activeClass = $isActive ? 'is-active ' . $dir : '';
    $extraStyle = $extraStyle ?? '';
@endphp
<th style="padding: 12px;{{ $extraStyle }}" @if(isset($clientSortKey)) data-client-sort="{{ $clientSortKey }}" @endif>
    @if(($sortable ?? true) && isset($column))
        <a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'dir' => $nextDir, 'page' => 1]) }}"
           class="rel-th-sort text-white text-decoration-none d-inline-flex align-items-center {{ $activeClass }}">
            <span class="rel-sort-arrows" aria-hidden="true">
                <span class="rel-sort-arrows__up"></span>
                <span class="rel-sort-arrows__down"></span>
            </span>
            <span>{{ $label }}</span>
        </a>
    @elseif($clientSort ?? false)
        <button type="button"
                class="rel-th-sort rel-th-sort--client text-white border-0 bg-transparent p-0 d-inline-flex align-items-center {{ $activeClass }}"
                data-client-sort-key="{{ $clientSortKey }}">
            <span class="rel-sort-arrows" aria-hidden="true">
                <span class="rel-sort-arrows__up"></span>
                <span class="rel-sort-arrows__down"></span>
            </span>
            <span>{{ $label }}</span>
        </button>
    @else
        <span class="d-inline-flex align-items-center text-white">
            <span class="rel-sort-arrows" aria-hidden="true">
                <span class="rel-sort-arrows__up"></span>
                <span class="rel-sort-arrows__down"></span>
            </span>
            <span>{{ $label }}</span>
        </span>
    @endif
</th>
