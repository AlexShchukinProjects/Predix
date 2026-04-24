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
    <div class="efds-table-header__actions d-flex flex-wrap gap-2 align-items-center">
        <button type="button" class="btn efds-btn efds-btn--primary" id="startFailureAnalysisBtn">
            <i class="fas fa-play me-1"></i>Start analysis
        </button>
        <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-toggle="modal" data-bs-target="#taskCardsExcelModal">
            <i class="fas fa-file-excel me-1"></i>Add from Excel
        </button>
        <a href="{{ route('modules.reliability.failures.create') }}" class="btn efds-btn efds-btn--primary">
            <i class="fas fa-plus me-1"></i>Add task card
        </a>
        <a href="{{ route('modules.reliability.export-excel', request()->all()) }}" class="btn efds-btn efds-btn--primary">Excel</a>
        <button type="button" class="btn btn-danger btn-sm d-none" id="deleteSelectedFailuresBtn">Delete</button>
    </div>
</div>

<form id="deleteSelectedFailuresForm" method="POST" action="{{ route('modules.reliability.failures.delete-selected') }}" class="d-none">
    @csrf
    @foreach(request()->query() as $key => $value)
        @if(is_array($value))
            @foreach($value as $nested)
                <input type="hidden" name="{{ $key }}[]" value="{{ $nested }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <div id="deleteSelectedFailuresIds"></div>
</form>

<!-- Таблица отказов -->
<div class="card">
    <div class="card-body p-0">
        <div class="reliability-table-scroll-wrap">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" style="font-size: 0.875rem;">
                <thead style="background: #1E64D4; color: white;">
                    <tr>
                        <th style="padding: 12px; width: 40px;" class="no-click">
                            <input type="checkbox" class="form-check-input" id="selectAllFailuresCheckbox" title="Select all on page">
                        </th>
                        <th style="padding: 12px; width: 40px;"></th>
                        <th style="padding: 12px;">SEQ</th>
                        <th style="padding: 12px;">TASK CARD</th>
                        <th style="padding: 12px; min-width: 200px;">TASK CARD DESCRIPTION</th>
                        <th style="padding: 12px;">MPD</th>
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
                        <tr style="cursor: pointer;" class="clickable-row js-failure-row" data-failure-id="{{ $failure->id }}" data-href="{{ route('modules.reliability.failures.edit', $failure) }}">
                            <td style="padding: 8px;" class="no-click">
                                <input type="checkbox" class="form-check-input js-failure-checkbox" value="{{ $failure->id }}" aria-label="Select failure {{ $failure->id }}">
                            </td>
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
                            <td style="padding: 8px;">{{ $failure->mpd ?? '—' }}</td>
                            <td style="padding: 8px;" data-metric="num_rc"></td>
                            <td style="padding: 8px;" data-metric="max_hours_on_rc"></td>
                            <td style="padding: 8px;" data-metric="num_str_nrcs"></td>
                            <td style="padding: 8px;" data-metric="str_percent"></td>
                            <td style="padding: 8px;" data-metric="max_mhs_str_nrc"></td>
                            <td style="padding: 8px;" data-metric="avg_str_mhs"></td>
                            <td style="padding: 8px;" data-metric="eef_count"></td>
                            <td style="padding: 8px;" data-metric="eef_percent"></td>
                            <td style="padding: 8px;" data-metric="probable_critical_findings"></td>
                            <td style="padding: 8px; min-width: 180px;">—</td>
                            <td style="padding: 8px;">—</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-center py-3 text-muted">
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

<div class="modal fade" id="taskCardsExcelModal" tabindex="-1" aria-labelledby="taskCardsExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskCardsExcelModalLabel">Add from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Select a file, OEM, and Excel column. The system automatically detects whether a value is a Task Card or an EASA/FAA bulletin. Masks: Task card Boeing - <code>dd-ddd-dd-dd</code>, Task card Airbus - <code>dddddd-dd-d</code>, EASA - <code>dddd-dddd</code>, FAA - <code>dddd-dd-dd</code>.</p>
                <div class="mb-3">
                    <label class="form-label" for="taskCardsExcelFile">Excel / CSV file</label>
                    <input type="file" class="form-control form-control-sm" id="taskCardsExcelFile" accept=".xlsx,.xls,.csv,.txt">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="taskCardsExcelColumn">Excel column number</label>
                    <input type="text" class="form-control form-control-sm" id="taskCardsExcelColumn" placeholder="For example: 3 or C" autocomplete="off">
                    <div class="form-text">Use either a numeric index (1 = A, 2 = B) or a letter notation. Up to column 4096 is supported.</div>
                </div>
                <div class="mb-3" id="taskCardsExcelOemWrap">
                    <label class="form-label d-block">Aircraft type</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="taskCardsExcelOem" id="taskCardsExcelOemAirbus" value="airbus">
                            <label class="form-check-label" for="taskCardsExcelOemAirbus">Airbus</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="taskCardsExcelOem" id="taskCardsExcelOemBoeing" value="boeing">
                            <label class="form-check-label" for="taskCardsExcelOemBoeing">Boeing</label>
                        </div>
                    </div>
                </div>
                <div id="taskCardsExcelAlert" class="alert alert-danger py-2 px-3 small d-none" role="alert"></div>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-primary btn-sm d-none" id="taskCardsExcelSubmit">Load</button>
                    <button type="button" class="btn btn-success btn-sm d-none" id="taskCardsExcelAddForAnalysis">Add for analysis</button>
                </div>
                <div id="taskCardsExcelPreviewWrap" class="d-none">
                    <p id="taskCardsExcelTruncated" class="text-warning small d-none mb-2">Only the first 2000 rows are shown.</p>
                    <div class="table-responsive" style="max-height: 50vh;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Source value</th>
                                    <th>Normalised</th>
                                </tr>
                            </thead>
                            <tbody id="taskCardsExcelPreviewBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.clickable-row[data-href]').forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('.no-click')) return;
            var href = this.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });

    (function failureRowsMetricsInit() {
        var rows = Array.prototype.slice.call(document.querySelectorAll('.js-failure-row[data-failure-id]'));
        if (!rows.length) return;
        var startBtn = document.getElementById('startFailureAnalysisBtn');
        if (!startBtn) return;
        var metricsUrlTemplate = "{{ route('modules.reliability.failures.metrics', ['id' => '__ID__']) }}";
        var isStarted = false;

        function metricCell(row, key) {
            return row.querySelector('[data-metric="' + key + '"]');
        }
        function setMetricValue(row, key, value) {
            var cell = metricCell(row, key);
            if (!cell) return;
            cell.textContent = value;
        }
        function formatFixed(value) {
            var n = Number(value);
            if (!isFinite(n)) return '—';
            return n.toFixed(2);
        }
        function clearMetricCells() {
            rows.forEach(function(row) {
                row.querySelectorAll('[data-metric]').forEach(function(cell) {
                    cell.textContent = '';
                });
            });
        }
        function showRowSpinner(row) {
            row.classList.add('is-calculating');
            row.querySelectorAll('[data-metric]').forEach(function(cell) {
                cell.innerHTML = '<span class="failure-metric-spinner spinner-border spinner-border-sm" role="status" aria-label="Calculating"></span>';
            });
        }
        function fillRowMetrics(row, metrics) {
            var numRc = Number(metrics && metrics.num_rc ? metrics.num_rc : 0);
            var numStrNrcs = Number(metrics && metrics.num_str_nrcs ? metrics.num_str_nrcs : 0);
            var eefCount = Number(metrics && metrics.eef_count ? metrics.eef_count : 0);
            var strPercent = numRc > 0 ? (numStrNrcs / numRc) * 100 : 0;
            var eefPercent = numStrNrcs > 0 ? (eefCount / numStrNrcs) * 100 : 0;
            var probableCritical = (strPercent * eefPercent) / 100;

            setMetricValue(row, 'num_rc', String(numRc));
            setMetricValue(row, 'max_hours_on_rc', metrics && metrics.max_hours_on_rc != null ? formatFixed(metrics.max_hours_on_rc) : '—');
            setMetricValue(row, 'num_str_nrcs', String(numStrNrcs));
            setMetricValue(row, 'str_percent', formatFixed(strPercent));
            setMetricValue(row, 'max_mhs_str_nrc', metrics && metrics.max_mhs_str_nrc != null ? formatFixed(metrics.max_mhs_str_nrc) : '—');
            setMetricValue(row, 'avg_str_mhs', metrics && metrics.avg_str_mhs_raw != null ? formatFixed(metrics.avg_str_mhs_raw) : '0.00');
            setMetricValue(row, 'eef_count', String(eefCount));
            setMetricValue(row, 'eef_percent', formatFixed(eefPercent));
            setMetricValue(row, 'probable_critical_findings', formatFixed(probableCritical));
            row.classList.remove('is-calculating');
        }
        function markRowError(row) {
            row.querySelectorAll('[data-metric]').forEach(function(cell) {
                cell.textContent = '';
            });
            row.classList.remove('is-calculating');
        }
        function fetchRowMetricsSequentially(index) {
            if (index >= rows.length) {
                startBtn.innerHTML = '<i class="fas fa-check me-1"></i>Analysis completed';
                return;
            }
            var row = rows[index];
            var failureId = row.getAttribute('data-failure-id');
            if (!failureId) {
                fetchRowMetricsSequentially(index + 1);
                return;
            }

            showRowSpinner(row);
            var url = metricsUrlTemplate.replace('__ID__', String(failureId));
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, j: j }; }); })
                .then(function(res) {
                    if (!res.ok || !res.j || !res.j.metrics) {
                        markRowError(row);
                        return;
                    }
                    fillRowMetrics(row, res.j.metrics);
                })
                .catch(function() {
                    markRowError(row);
                })
                .finally(function() {
                    fetchRowMetricsSequentially(index + 1);
                });
        }
        clearMetricCells();
        startBtn.addEventListener('click', function() {
            if (isStarted) return;
            isStarted = true;
            startBtn.disabled = true;
            startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Analyzing...';
            fetchRowMetricsSequentially(0);
        });
    })();

    (function failureSelectionInit() {
        var selectAll = document.getElementById('selectAllFailuresCheckbox');
        var rowCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.js-failure-checkbox'));
        var deleteBtn = document.getElementById('deleteSelectedFailuresBtn');
        var deleteForm = document.getElementById('deleteSelectedFailuresForm');
        var idsWrap = document.getElementById('deleteSelectedFailuresIds');
        if (!deleteBtn || !deleteForm || !idsWrap || !rowCheckboxes.length) return;

        function selectedIds() {
            return rowCheckboxes.filter(function(cb) { return cb.checked; }).map(function(cb) { return cb.value; });
        }
        function renderSelectedIds(ids) {
            idsWrap.innerHTML = '';
            ids.forEach(function(id) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                idsWrap.appendChild(input);
            });
        }
        function updateControls() {
            var ids = selectedIds();
            renderSelectedIds(ids);
            if (ids.length > 0) {
                deleteBtn.classList.remove('d-none');
            } else {
                deleteBtn.classList.add('d-none');
            }
            if (selectAll) {
                var checkedCount = ids.length;
                selectAll.checked = checkedCount > 0 && checkedCount === rowCheckboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
            }
        }

        rowCheckboxes.forEach(function(cb) {
            cb.addEventListener('change', updateControls);
            cb.addEventListener('click', function(e) { e.stopPropagation(); });
        });
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                var checked = !!selectAll.checked;
                rowCheckboxes.forEach(function(cb) { cb.checked = checked; });
                updateControls();
            });
            selectAll.addEventListener('click', function(e) { e.stopPropagation(); });
        }
        deleteBtn.addEventListener('click', function() {
            var ids = selectedIds();
            if (!ids.length) return;
            if (!window.confirm('Delete selected rows?')) return;
            deleteForm.submit();
        });
        updateControls();
    })();

    (function taskCardsExcelModalInit() {
        var modalEl = document.getElementById('taskCardsExcelModal');
        if (!modalEl) return;
        var fileInput = document.getElementById('taskCardsExcelFile');
        var colInput = document.getElementById('taskCardsExcelColumn');
        var oemInputs = modalEl.querySelectorAll('input[name="taskCardsExcelOem"]');
        var btnSubmit = document.getElementById('taskCardsExcelSubmit');
        var btnAddForAnalysis = document.getElementById('taskCardsExcelAddForAnalysis');
        var alertEl = document.getElementById('taskCardsExcelAlert');
        var previewWrap = document.getElementById('taskCardsExcelPreviewWrap');
        var previewBody = document.getElementById('taskCardsExcelPreviewBody');
        var truncatedEl = document.getElementById('taskCardsExcelTruncated');
        var previewUrl = "{{ route('modules.reliability.task-cards-excel.preview') }}";
        var addForAnalysisUrl = "{{ route('modules.reliability.task-cards-excel.add-for-analysis') }}";
        var reliabilityIndexUrl = "{{ route('modules.reliability.index', ['per_page' => 100]) }}";
        var normalisedCards = [];

        function csrfHeaders() {
            var token = document.querySelector('meta[name="csrf-token"]');
            return {
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            };
        }
        function hideAlert() {
            alertEl.classList.add('d-none');
            alertEl.textContent = '';
        }
        function showAlert(msg) {
            alertEl.textContent = msg;
            alertEl.classList.remove('d-none');
        }
        function firstErrorMessage(j) {
            if (!j) return 'Request failed';
            if (j.message) return j.message;
            if (j.errors) {
                var keys = Object.keys(j.errors);
                if (keys.length && j.errors[keys[0]][0]) return j.errors[keys[0]][0];
            }
            return 'Request failed';
        }
        function resetModalState() {
            fileInput.value = '';
            colInput.value = '';
            oemInputs.forEach(function(input) { input.checked = false; });
            btnSubmit.classList.add('d-none');
            btnAddForAnalysis.classList.add('d-none');
            btnAddForAnalysis.disabled = false;
            previewWrap.classList.add('d-none');
            truncatedEl.classList.add('d-none');
            previewBody.innerHTML = '';
            normalisedCards = [];
            hideAlert();
        }
        function getSelectedOem() {
            var selected = modalEl.querySelector('input[name="taskCardsExcelOem"]:checked');
            return selected ? selected.value : '';
        }
        function invalidatePreview() {
            previewWrap.classList.add('d-none');
            truncatedEl.classList.add('d-none');
            previewBody.innerHTML = '';
            btnAddForAnalysis.classList.add('d-none');
            btnAddForAnalysis.disabled = false;
            normalisedCards = [];
        }
        /** @returns {number|null} 0-based column index for API */
        function parseColumnIndexFromInput(str) {
            var s = String(str || '').trim();
            if (!s) return null;
            if (/^[A-Za-z]+$/.test(s)) {
                s = s.toUpperCase();
                var n = 0;
                for (var i = 0; i < s.length; i++) {
                    var code = s.charCodeAt(i);
                    if (code < 65 || code > 90) return null;
                    n = n * 26 + (code - 64);
                    if (n > 4096) return null;
                }
                return n - 1;
            }
            if (/^\d+$/.test(s)) {
                var num = parseInt(s, 10);
                if (num < 1 || num > 4096) return null;
                return num - 1;
            }
            return null;
        }
        function toggleSubmit() {
            var hasFile = fileInput.files && fileInput.files.length > 0;
            var idx = parseColumnIndexFromInput(colInput.value);
            var oem = getSelectedOem();
            if (hasFile && idx !== null && oem) {
                btnSubmit.classList.remove('d-none');
            } else {
                btnSubmit.classList.add('d-none');
            }
        }
        modalEl.addEventListener('hidden.bs.modal', resetModalState);
        fileInput.addEventListener('change', function() {
            hideAlert();
            invalidatePreview();
            btnSubmit.classList.add('d-none');
            if (!fileInput.files || !fileInput.files.length) {
                toggleSubmit();
                return;
            }
            toggleSubmit();
        });
        colInput.addEventListener('input', function() {
            hideAlert();
            invalidatePreview();
            toggleSubmit();
        });
        colInput.addEventListener('change', function() {
            hideAlert();
            invalidatePreview();
            toggleSubmit();
        });
        oemInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                hideAlert();
                invalidatePreview();
                toggleSubmit();
            });
        });
        btnSubmit.addEventListener('click', function() {
            hideAlert();
            if (!fileInput.files || !fileInput.files.length) return;
            var colIdx = parseColumnIndexFromInput(colInput.value);
            var oem = getSelectedOem();
            if (colIdx === null) {
                showAlert('Specify a column: letters (A, AB...) or a number from 1 to 4096.');
                return;
            }
            if (!oem) {
                showAlert('Select aircraft type: Airbus or Boeing.');
                return;
            }
            var fd = new FormData();
            fd.append('file', fileInput.files[0]);
            fd.append('column_index', String(colIdx));
            fd.append('oem', oem);
            btnSubmit.disabled = true;
            fetch(previewUrl, { method: 'POST', headers: csrfHeaders(), body: fd, credentials: 'same-origin' })
                .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, j: j }; }); })
                .then(function(res) {
                    btnSubmit.disabled = false;
                    if (!res.ok) {
                        showAlert(firstErrorMessage(res.j));
                        return;
                    }
                    previewBody.innerHTML = '';
                    normalisedCards = [];
                    (res.j.rows || []).forEach(function(row) {
                        var tr = document.createElement('tr');
                        var td1 = document.createElement('td');
                        td1.textContent = row.task_card != null && row.task_card !== '' ? row.task_card : '—';
                        var td2 = document.createElement('td');
                        td2.textContent = row.task_card_normalised != null && row.task_card_normalised !== '' ? row.task_card_normalised : '—';
                        if (row.task_card_normalised != null) {
                            var normalized = String(row.task_card_normalised).trim();
                            if (normalized !== '') {
                                normalisedCards.push(normalized);
                            }
                        }
                        tr.appendChild(td1);
                        tr.appendChild(td2);
                        previewBody.appendChild(tr);
                    });
                    normalisedCards = Array.from(new Set(normalisedCards));
                    if (res.j.truncated) {
                        truncatedEl.classList.remove('d-none');
                    } else {
                        truncatedEl.classList.add('d-none');
                    }
                    previewWrap.classList.remove('d-none');
                    if (normalisedCards.length > 0) {
                        btnAddForAnalysis.classList.remove('d-none');
                    } else {
                        btnAddForAnalysis.classList.add('d-none');
                    }
                })
                .catch(function() {
                    btnSubmit.disabled = false;
                    showAlert('Network or server is unavailable.');
                });
        });
        btnAddForAnalysis.addEventListener('click', function() {
            hideAlert();
            if (!normalisedCards.length) {
                showAlert('There are no normalized values to add.');
                return;
            }

            btnAddForAnalysis.disabled = true;
            fetch(addForAnalysisUrl, {
                method: 'POST',
                headers: Object.assign(csrfHeaders(), { 'Content-Type': 'application/json' }),
                body: JSON.stringify({ task_cards: normalisedCards }),
                credentials: 'same-origin'
            })
                .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, j: j }; }); })
                .then(function(res) {
                    btnAddForAnalysis.disabled = false;
                    if (!res.ok) {
                        showAlert(firstErrorMessage(res.j));
                        return;
                    }
                    window.location.href = reliabilityIndexUrl;
                })
                .catch(function() {
                    btnAddForAnalysis.disabled = false;
                    showAlert('Network or server is unavailable.');
                });
        });
    })();
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

.js-failure-row.is-calculating td[data-metric] {
    color: #6b7280;
}

.failure-metric-spinner {
    width: 0.9rem;
    height: 0.9rem;
    border-width: 0.12em;
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

