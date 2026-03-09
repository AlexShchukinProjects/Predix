<!-- Хидер над таблицей (дизайн-система) -->
<div class="efds-table-header">
    <div class="efds-table-header__stats text-muted">
        @if(isset($failures) && $failures instanceof \Illuminate\Contracts\Pagination\Paginator)
            <span class="me-2">Per page:</span>
            <select class="form-select form-select-sm d-inline-block" style="width: auto;" name="per_page_selector" onchange="updatePerPage(this.value)" aria-label="Records per page">
                @php $currentPerPage = (int) request('per_page', $failures->perPage()); @endphp
                <option value="10" {{ $currentPerPage === 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $currentPerPage === 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $currentPerPage === 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $currentPerPage === 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="ms-2">Total records: {{ $failures->total() }}</span>
        @else
            <span>Total records: {{ count($failures ?? []) }}</span>
        @endif
    </div>
    <div class="efds-table-header__actions">
        <a href="{{ route('modules.reliability.failures.create') }}" class="btn efds-btn efds-btn--primary">
            <i class="fas fa-plus me-1"></i>Add failure
        </a>
        <a href="{{ route('modules.reliability.export-buf', request()->all()) }}" class="btn efds-btn efds-btn--primary">Defects report</a>
        <a href="{{ route('modules.reliability.export-excel', request()->all()) }}" class="btn efds-btn efds-btn--primary">Excel</a>
    </div>
</div>

<!-- Таблица отказов -->
<div class="card">
    <div class="card-body p-0">
        <div class="reliability-table-scroll-wrap">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" style="font-size: 0.875rem;">
                <thead style="background: #1E64D4; color: white;">
                    <tr>
                        <th style="padding: 12px; width: 40px;"></th>
                        <th style="padding: 12px;">SEQ</th>
                        <th style="padding: 12px;">TASK CARD</th>
                        <th style="padding: 12px; min-width: 200px;">TASK CARD DESCRIPTION</th>
                        <th style="padding: 12px;">Task</th>
                        <th style="padding: 12px;"># of RC</th>
                        <th style="padding: 12px;">Max Hours on RC</th>
                        <th style="padding: 12px;"># of STR NRCs</th>
                        <th style="padding: 12px;">%</th>
                        <th style="padding: 12px;">Max MHs on STR NRC</th>
                        <th style="padding: 12px;">AVG STR MHs</th>
                        <th style="padding: 12px;">EEF Count</th>
                        <th style="padding: 12px;">% EEF</th>
                        <th style="padding: 12px; min-width: 180px;">Probabile Critical Findings</th>
                        <th style="padding: 12px;">REF</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $seqBase = isset($failures) && $failures instanceof \Illuminate\Contracts\Pagination\Paginator
                            ? ($failures->currentPage() - 1) * $failures->perPage()
                            : 0;
                    @endphp
                    @forelse(($failures ?? []) as $failure)
                        <tr style="cursor: pointer;" class="clickable-row" data-href="{{ route('modules.reliability.failures.edit', $failure) }}">
                            <td style="padding: 8px;" class="no-click">
                                <i class="fas fa-search text-muted"></i>
                            </td>
                            <td style="padding: 8px;">{{ $seqBase + $loop->iteration }}</td>
                            <td style="padding: 8px;">{{ $failure->wo_number ?? $failure->work_order_number ?? '—' }}</td>
                            <td style="padding: 8px; min-width: 200px;">
                                <div style="white-space: normal; word-wrap: break-word;">
                                    {{ $failure->aircraft_malfunction ?? $failure->component_cause ?? '—' }}
                                </div>
                            </td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px;">—</td>
                            <td style="padding: 8px; min-width: 180px;">—</td>
                            <td style="padding: 8px;">—</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-3 text-muted">
                                No failures saved.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(isset($failures) && $failures instanceof \Illuminate\Contracts\Pagination\Paginator)
    <!-- Пагинация -->
    <div class="d-flex justify-content-between align-items-center" style="margin-top: 20px; margin-bottom: 20px;">
        <div></div>
        <!-- Пагинация -->
        @if($failures->hasPages())
            <div class="pagination-links" style="flex: 1; display: flex; justify-content: center;">
                <nav>
                    <ul class="pagination">
                        {{-- Previous Page Link --}}
                        @if ($failures->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">←</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $failures->previousPageUrl() }}" rel="prev">←</a>
                            </li>
                        @endif

                        {{-- Compact Pagination Elements --}}
                        @php
                            $currentPage = $failures->currentPage();
                            $lastPage = $failures->lastPage();
                            $pages = [];
                            
                            // Всегда показываем первую страницу
                            $pages[] = 1;
                            
                            // Показываем текущую страницу (если не первая и не последняя)
                            if ($currentPage > 1 && $currentPage < $lastPage) {
                                $pages[] = $currentPage;
                            }
                            
                            // Всегда показываем последнюю страницу (если не первая)
                            if ($lastPage > 1) {
                                $pages[] = $lastPage;
                            }
                            
                            // Убираем дубликаты и сортируем
                            $pages = array_unique($pages);
                            sort($pages);
                        @endphp

                        @foreach($pages as $page)
                            @if($loop->first && $currentPage > 2)
                                <li class="page-item">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            
                            <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ $failures->url($page) }}">{{ $page }}</a>
                            </li>
                            
                            @if($loop->last && $currentPage < $lastPage - 1)
                                <li class="page-item">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($failures->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $failures->nextPageUrl() }}" rel="next">→</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">→</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.clickable-row[data-href]').forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('.no-click')) return;
            var href = this.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });
});
var PER_PAGE_STORAGE_KEY = 'reliability_inspection_per_page';
(function applyStoredPerPage() {
    var url = new URL(window.location.href);
    if (!url.searchParams.has('per_page')) {
        var stored = localStorage.getItem(PER_PAGE_STORAGE_KEY);
        if (stored && ['10','25','50','100'].indexOf(stored) !== -1) {
            url.searchParams.set('per_page', stored);
            window.location.replace(url.toString());
            return;
        }
    }
})();
function updatePerPage(value) {
    try { localStorage.setItem(PER_PAGE_STORAGE_KEY, value); } catch (e) {}
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}
</script>

<style>
.reliability-table-scroll-wrap {
    max-height: 70vh;
    overflow-y: auto;
}

.reliability-table-scroll-wrap thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background-color: #f5f7fa;
}

.table thead th {
    font-weight: bold;
    font-size: 0.75rem;
    text-transform: uppercase;
    white-space: nowrap;
}

.table tbody td {
    vertical-align: top;
    border-top: 1px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.readonly-field {
    background-color: whitesmoke !important;
    color: #6b7280 !important;
    cursor: default;
}

.pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.pagination .page-item.active .page-link {
    background-color: #1E64D4;
    border-color: #1E64D4;
}

/* Стили для пагинации */
.helper-count-rows {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    padding: .275rem .75rem;
    margin-bottom: 0;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    text-align: center;
    white-space: nowrap;
    background-color: #e9ecef;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: 0;
    height: 35px;
    box-sizing: border-box;
}

.selector-count-rows {
    width: 180px;
    height: 35px !important;
    border: 1px solid #dee2e6;
    float: left;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: 0;
    margin-right: 15px;
    display: flex;
    align-items: center;
}

.selector-count-rows select {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: left;
    vertical-align: middle;
    line-height: 1.5;
    color: #495057;
    padding: 4px 8px;
}

.selector-count-rows .form-control {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: left;
    vertical-align: middle;
}

.pagination-links {
    display: flex;
    justify-content: center;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #495057;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
}

.pagination .page-link:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.form-label {
    font-weight: 500;
}

.main_screen {
    width: 100% !important;   
}
.row.mb-3 {
    align-items: center;
}

</style>

