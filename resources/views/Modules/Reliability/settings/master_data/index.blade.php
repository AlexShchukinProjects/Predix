@extends('layout.main')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="{{ route('modules.reliability.settings.index') }}" class="back-button"><i class="fas fa-arrow-left me-2"></i>Settings</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span class="me-2">Per page:</span>
            <select class="form-select form-select-sm" id="master-data-per-page" aria-label="Records per page">
                @php $currentPerPage = (int) request('per_page', $perPage ?? 50); @endphp
                <option value="10" {{ $currentPerPage === 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $currentPerPage === 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $currentPerPage === 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $currentPerPage === 100 ? 'selected' : '' }}>100</option>
                <option value="500" {{ $currentPerPage === 500 ? 'selected' : '' }}>500</option>
                <option value="1000" {{ $currentPerPage === 1000 ? 'selected' : '' }}>1000</option>
            </select>
            <span class="ms-2">Total records: {{ $items->total() }}</span>
        </div>
        <div class="efds-table-header__actions">
            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#masterDataUploadModal"><i class="fas fa-file-excel me-1"></i>Add from Excel / CSV</button>
            <form id="form-delete-master-data" action="{{ route('modules.reliability.settings.master-data.delete') }}" method="post" class="d-none">
                @csrf
                <button type="submit" class="btn efds-btn efds-btn--danger btn-sm">Delete selected</button>
            </form>
        </div>
    </div>
    <div class="inspection-settings-table-card">
    <div class="card">
        <div class="card-body p-0">
            <form id="form-master-data-table">
                @csrf
            <div class="reliability-table-scroll-wrap">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 2.5rem;"><input type="checkbox" id="master-data-select-all" class="form-check-input" title="Select all on page"></th>
                                <th>ID</th>
                                <th>AIRCRAFT TYPE</th>
                                <th>SRC. CUST. CARD</th>
                                <th>DESCRIPTION</th>
                                <th>PRIM. SKILL</th>
                                <th>ORDER TYPE</th>
                                <th>ACT. TIME</th>
                                <th>CHILD CARD COUNT</th>
                                <th>EEF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                            <tr>
                                <td class="text-center"><input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input master-data-row-cb"></td>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->aircraft_type }}</td>
                                <td>{{ $row->src_cust_card }}</td>
                                <td>{{ Str::limit($row->description, 80) }}</td>
                                <td>{{ $row->prim_skill }}</td>
                                <td>{{ $row->order_type }}</td>
                                <td>{{ $row->act_time }}</td>
                                <td>{{ $row->child_card_count }}</td>
                                <td>{{ $row->eef }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">No records. Upload data from Excel / CSV.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </form>
        </div>
    </div>
    </div>
    @if($items->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $items->withQueryString()->links() }}
    </div>
    @endif
</div>

<div class="modal fade" id="masterDataUploadModal" tabindex="-1" aria-labelledby="masterDataUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="masterDataUploadModalLabel">Add from Excel / CSV</h5>
                <button type="button" class="btn-close" id="md-modal-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-upload-master-data-modal" action="{{ route('modules.reliability.settings.master-data.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="md-step-select">
                        <ul class="nav nav-tabs mb-3" id="md-upload-tabs">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" id="md-tab-upload" onclick="mdSwitchTab('upload')">
                                    <i class="fas fa-upload me-1"></i>Upload file
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" id="md-tab-local" onclick="mdSwitchTab('local')">
                                    <i class="fas fa-server me-1"></i>From server disk
                                </button>
                            </li>
                        </ul>
                        <div id="md-panel-upload">
                            <input type="file" name="file" id="master-data-upload-file" class="d-none" accept=".csv,.xlsx,.xls">
                            <div id="master-data-upload-dropzone" class="inspection-upload-dropzone">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="mb-1">Drag file here</p>
                                <p class="small text-muted mb-0">or click to select file (CSV, XLSX, XLS)</p>
                            </div>
                        </div>
                        <div id="md-panel-local" class="d-none">
                            <p class="small text-muted mb-2">Enter file path relative to project root:</p>
                            <input type="text" id="md-local-path" class="form-control form-control-sm font-monospace"
                                   placeholder="excel/Master Data/Master Data.xlsx"
                                   value="excel/Master Data/Master Data.xlsx">
                            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm mt-2" id="md-local-check">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="md-local-spin"></span>
                                Check and count rows
                            </button>
                        </div>
                        <div id="md-file-info" class="mt-3 d-none">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-file-excel text-success"></i>
                                <span id="master-data-upload-filename" class="small text-success fw-bold"></span>
                            </div>
                            <div id="md-sheet-wrap" class="mb-2 d-none">
                                <label class="form-label small mb-1">Sheet</label>
                                <select id="md-sheet-select" class="form-select form-select-sm" style="max-width: 280px;">
                                    <option value="0">Sheet1</option>
                                </select>
                                <input type="hidden" name="sheet_index" id="md-sheet-index-input" value="0">
                            </div>
                            <div id="md-counting" class="small text-muted d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>Counting rows…
                            </div>
                            <div id="md-count-result" class="d-none">
                                <span class="small text-muted">Rows to upload: </span>
                                <strong id="md-total-rows" class="text-primary fs-6">—</strong>
                            </div>
                            <div id="md-count-error" class="small text-danger mt-1 d-none"></div>
                        </div>
                    </div>
                    <div id="md-step-progress" class="d-none">
                        <div class="text-center mb-3">
                            <i class="fas fa-file-excel text-success fa-2x mb-2"></i>
                            <p class="mb-0 small text-muted" id="md-prog-filename"></p>
                        </div>
                        <p class="mb-1 small">Uploading: <span id="md-prog-processed" class="fw-bold">0</span> of <span id="md-prog-total" class="fw-bold">—</span> rows</p>
                        <div class="progress mb-2" style="height:1.4rem; border-radius:.5rem;">
                            <div id="md-progress-bar"
                                 class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                0%
                            </div>
                        </div>
                        <p id="md-prog-error" class="small text-danger d-none mt-2"></p>
                        <p id="md-prog-done" class="small text-success d-none mt-2">
                            <i class="fas fa-check-circle me-1"></i>Upload complete!
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn efds-btn efds-btn--outline-primary" id="md-upload-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="master-data-upload-submit" class="btn efds-btn efds-btn--primary" disabled>Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('Modules.Reliability.inspection_settings.partials.table_styles')
<script>
(function() {
    var formDelete = document.getElementById('form-delete-master-data');
    var checkboxes = document.querySelectorAll('.master-data-row-cb');
    var selectAll = document.getElementById('master-data-select-all');
    function updateDeleteVisibility() {
        var any = Array.prototype.some.call(checkboxes, function(cb) { return cb.checked; });
        formDelete.classList.toggle('d-none', !any);
    }
    function updateSelectAll() {
        if (!selectAll) return;
        var all = document.querySelectorAll('.master-data-row-cb');
        var checked = document.querySelectorAll('.master-data-row-cb:checked');
        selectAll.checked = all.length > 0 && checked.length === all.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
    }
    Array.prototype.forEach.call(checkboxes, function(cb) {
        cb.addEventListener('change', function() { updateDeleteVisibility(); updateSelectAll(); });
    });
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            Array.prototype.forEach.call(document.querySelectorAll('.master-data-row-cb'), function(cb) { cb.checked = selectAll.checked; });
            updateDeleteVisibility();
        });
    }
    formDelete.addEventListener('submit', function(e) {
        e.preventDefault();
        var ids = [];
        document.querySelectorAll('.master-data-row-cb:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        ids.forEach(function(id) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
            formDelete.appendChild(inp);
        });
        formDelete.submit();
    });

    var perPageSelect = document.getElementById('master-data-per-page');
    var PER_PAGE_STORAGE_KEY = 'reliability_master_data_per_page';
    (function applyStoredPerPage() {
        var url = new URL(window.location.href);
        if (!url.searchParams.has('per_page')) {
            var stored = localStorage.getItem(PER_PAGE_STORAGE_KEY);
            if (stored && ['10','25','50','100','500','1000'].indexOf(stored) !== -1) {
                url.searchParams.set('per_page', stored);
                window.location.replace(url.toString());
                return;
            }
        }
    })();
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            var val = this.value;
            try { localStorage.setItem(PER_PAGE_STORAGE_KEY, val); } catch (e) {}
            var url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    var uploadModal = document.getElementById('masterDataUploadModal');
    var dropzone = document.getElementById('master-data-upload-dropzone');
    var fileInput = document.getElementById('master-data-upload-file');
    var submitBtn = document.getElementById('master-data-upload-submit');
    var cancelBtn = document.getElementById('md-upload-cancel-btn');
    var formUpload = document.getElementById('form-upload-master-data-modal');
    var stepSelect = document.getElementById('md-step-select');
    var fileInfoEl = document.getElementById('md-file-info');
    var filenameEl = document.getElementById('master-data-upload-filename');
    var countingEl = document.getElementById('md-counting');
    var countResultEl = document.getElementById('md-count-result');
    var totalRowsEl = document.getElementById('md-total-rows');
    var countErrorEl = document.getElementById('md-count-error');
    var stepProgress = document.getElementById('md-step-progress');
    var progFilename = document.getElementById('md-prog-filename');
    var progProcessed = document.getElementById('md-prog-processed');
    var progTotal = document.getElementById('md-prog-total');
    var progressBar = document.getElementById('md-progress-bar');
    var progError = document.getElementById('md-prog-error');
    var progDone = document.getElementById('md-prog-done');
    var knownTotal = 0;
    var importing = false;
    var activeTab = 'upload';
    var localPathOk = false;
    var sheetsList = [];

    var sheetWrap = document.getElementById('md-sheet-wrap');
    var sheetSelect = document.getElementById('md-sheet-select');
    var sheetIndexInput = document.getElementById('md-sheet-index-input');

    function fillSheetSelect(sheets) {
        sheetsList = sheets || [];
        if (!sheetSelect) return;
        sheetSelect.innerHTML = '';
        sheetsList.forEach(function(s) {
            var opt = document.createElement('option');
            opt.value = s.index;
            opt.textContent = s.name + ' (' + (s.total || 0).toLocaleString() + ' rows)';
            sheetSelect.appendChild(opt);
        });
        if (sheetWrap) {
            sheetWrap.classList.toggle('d-none', sheetsList.length <= 1);
        }
        if (sheetsList.length > 0) {
            var first = sheetsList[0];
            knownTotal = first.total || 0;
            if (totalRowsEl) totalRowsEl.textContent = knownTotal.toLocaleString();
            if (sheetIndexInput) sheetIndexInput.value = String(first.index);
        }
    }
    function onSheetSelectChange() {
        if (!sheetSelect || !sheetsList.length) return;
        var idx = parseInt(sheetSelect.value, 10);
        var s = sheetsList.find(function(x) { return x.index === idx; });
        if (s) {
            knownTotal = s.total || 0;
            if (totalRowsEl) totalRowsEl.textContent = knownTotal.toLocaleString();
            if (sheetIndexInput) sheetIndexInput.value = String(idx);
        }
    }
    if (sheetSelect) {
        sheetSelect.addEventListener('change', onSheetSelectChange);
    }

    window.mdSwitchTab = function(tab) {
        activeTab = tab;
        document.getElementById('md-tab-upload').classList.toggle('active', tab === 'upload');
        document.getElementById('md-tab-local').classList.toggle('active', tab === 'local');
        document.getElementById('md-panel-upload').classList.toggle('d-none', tab !== 'upload');
        document.getElementById('md-panel-local').classList.toggle('d-none', tab !== 'local');
        fileInfoEl.classList.add('d-none');
        submitBtn.disabled = true;
        knownTotal = 0;
        localPathOk = false;
    };

    function resetModal() {
        importing = false;
        localPathOk = false;
        sheetsList = [];
        if (fileInput) fileInput.value = '';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Upload';
        cancelBtn.setAttribute('data-bs-dismiss', 'modal');
        stepSelect.classList.remove('d-none');
        stepProgress.classList.add('d-none');
        fileInfoEl.classList.add('d-none');
        countingEl.classList.add('d-none');
        countResultEl.classList.add('d-none');
        countErrorEl.classList.add('d-none');
        progError.classList.add('d-none');
        progDone.classList.add('d-none');
        knownTotal = 0;
        if (sheetWrap) sheetWrap.classList.add('d-none');
        if (sheetIndexInput) sheetIndexInput.value = '0';
        if (sheetSelect) { sheetSelect.innerHTML = '<option value="0">Sheet1</option>'; }
        mdSwitchTab('upload');
    }

    function setProgressBar(pct) {
        progressBar.style.width = pct + '%';
        progressBar.textContent = pct + '%';
        progressBar.setAttribute('aria-valuenow', pct);
    }

    function readNdjsonStream(response, displayName) {
        stepSelect.classList.add('d-none');
        stepProgress.classList.remove('d-none');
        progFilename.textContent = displayName;
        progProcessed.textContent = '0';
        progTotal.textContent = knownTotal > 0 ? knownTotal.toLocaleString() : '—';
        setProgressBar(0);
        progError.classList.add('d-none');
        progDone.classList.add('d-none');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading…';
        cancelBtn.removeAttribute('data-bs-dismiss');
        if (!response.ok) { throw new Error('HTTP ' + response.status); }
        var reader = response.body.getReader();
        var decoder = new TextDecoder();
        var buf = '';
        function pump() {
            return reader.read().then(function(res) {
                if (res.done) return;
                buf += decoder.decode(res.value, { stream: true });
                var lines = buf.split('\n');
                buf = lines.pop();
                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i].trim();
                    if (!line) continue;
                    try {
                        var d = JSON.parse(line);
                        if (d.total && !knownTotal) {
                            knownTotal = d.total;
                            progTotal.textContent = d.total.toLocaleString();
                        }
                        if (d.error) {
                            progError.textContent = d.error;
                            progError.classList.remove('d-none');
                            importing = false;
                            cancelBtn.setAttribute('data-bs-dismiss', 'modal');
                            return;
                        }
                        if (typeof d.processed !== 'undefined') {
                            var tot = knownTotal || d.total || 0;
                            progProcessed.textContent = d.processed.toLocaleString();
                            progTotal.textContent = tot > 0 ? tot.toLocaleString() : '—';
                            setProgressBar(tot > 0 ? Math.min(100, Math.round(100 * d.processed / tot)) : 0);
                        }
                        if (d.done) {
                            setProgressBar(100);
                            progProcessed.textContent = (d.count || 0).toLocaleString();
                            progressBar.classList.remove('progress-bar-animated');
                            progDone.classList.remove('d-none');
                            importing = false;
                            cancelBtn.setAttribute('data-bs-dismiss', 'modal');
                            submitBtn.textContent = 'Done';
                            setTimeout(function() {
                                window.location.href = '{{ route("modules.reliability.settings.master-data.index") }}?success=' + encodeURIComponent('Imported records: ' + (d.count || 0));
                            }, 1200);
                            return;
                        }
                    } catch(err) {}
                }
                return pump();
            });
        }
        return pump();
    }

    function startCounting(file) {
        knownTotal = 0;
        submitBtn.disabled = true;
        filenameEl.textContent = file.name;
        fileInfoEl.classList.remove('d-none');
        countingEl.classList.remove('d-none');
        countResultEl.classList.add('d-none');
        countErrorEl.classList.add('d-none');
        var fd = new FormData();
        fd.append('file', file);
        fd.append('_token', document.querySelector('#form-upload-master-data-modal [name=_token]').value);
        fetch('{{ route("modules.reliability.settings.master-data.count") }}', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            countingEl.classList.add('d-none');
            if (data.error) { countErrorEl.textContent = data.error; countErrorEl.classList.remove('d-none'); return; }
            if (data.sheets && data.sheets.length) {
                fillSheetSelect(data.sheets);
            } else {
                knownTotal = data.total || 0;
                totalRowsEl.textContent = knownTotal.toLocaleString();
                if (sheetWrap) sheetWrap.classList.add('d-none');
                if (sheetIndexInput) sheetIndexInput.value = '0';
            }
            countResultEl.classList.remove('d-none');
            submitBtn.disabled = false;
        }).catch(function(err) {
            countingEl.classList.add('d-none');
            countErrorEl.textContent = 'Count error: ' + err.message;
            countErrorEl.classList.remove('d-none');
        });
    }

    var localCheckBtn = document.getElementById('md-local-check');
    var localSpin = document.getElementById('md-local-spin');
    if (localCheckBtn) {
        localCheckBtn.addEventListener('click', function() {
            var path = document.getElementById('md-local-path').value.trim();
            if (!path) return;
            localPathOk = false;
            submitBtn.disabled = true;
            localSpin.classList.remove('d-none');
            localCheckBtn.disabled = true;
            fileInfoEl.classList.remove('d-none');
            filenameEl.textContent = path.split('/').pop();
            countingEl.classList.add('d-none');
            countResultEl.classList.add('d-none');
            countErrorEl.classList.add('d-none');
            var fd2 = new FormData();
            fd2.append('_token', document.querySelector('#form-upload-master-data-modal [name=_token]').value);
            fd2.append('path', path);
            fetch('{{ route("modules.reliability.settings.master-data.sheets-from-path") }}', {
                method: 'POST', body: fd2, headers: { 'Accept': 'application/json' }
            }).then(function(r) { return r.json(); })
            .then(function(data) {
                localSpin.classList.add('d-none');
                localCheckBtn.disabled = false;
                if (data.error) {
                    countErrorEl.textContent = data.error;
                    countErrorEl.classList.remove('d-none');
                    return;
                }
                if (data.sheets && data.sheets.length) {
                    fillSheetSelect(data.sheets);
                } else {
                    knownTotal = 0;
                    totalRowsEl.textContent = '0';
                    if (sheetIndexInput) sheetIndexInput.value = '0';
                }
                countResultEl.classList.remove('d-none');
                countErrorEl.classList.add('d-none');
                localPathOk = true;
                submitBtn.disabled = false;
            }).catch(function(err) {
                localSpin.classList.add('d-none');
                localCheckBtn.disabled = false;
                countErrorEl.textContent = err.message || 'Connection error';
                countErrorEl.classList.remove('d-none');
            });
        });
    }

    if (uploadModal) uploadModal.addEventListener('show.bs.modal', resetModal);
    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() { if (!importing) fileInput.click(); });
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) startCounting(this.files[0]);
        });
        dropzone.addEventListener('dragover', function(e) { e.preventDefault(); e.stopPropagation(); dropzone.classList.add('drag-over'); });
        dropzone.addEventListener('dragleave', function(e) { e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('drag-over'); });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault(); e.stopPropagation();
            dropzone.classList.remove('drag-over');
            var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) { var dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files; startCounting(file); }
        });
    }
    if (formUpload) {
        formUpload.addEventListener('submit', function(e) {
            e.preventDefault();
            importing = true;
            if (activeTab === 'local') {
                var path = document.getElementById('md-local-path').value.trim();
                if (!path || !localPathOk) return;
                var fd3 = new FormData();
                fd3.append('_token', document.querySelector('#form-upload-master-data-modal [name=_token]').value);
                fd3.append('path', path);
                fd3.append('sheet_index', sheetIndexInput ? sheetIndexInput.value : '0');
                fetch('{{ route("modules.reliability.settings.master-data.import-local") }}', {
                    method: 'POST', body: fd3, headers: { 'Accept': 'application/x-ndjson' }
                }).then(function(r) { return readNdjsonStream(r, path.split('/').pop()); })
                .catch(function(err) {
                    progError.textContent = err.message || 'Error';
                    progError.classList.remove('d-none');
                    importing = false;
                    cancelBtn.setAttribute('data-bs-dismiss', 'modal');
                });
            } else {
                if (!fileInput.files || !fileInput.files[0]) return;
                var fd = new FormData(formUpload);
                fetch(formUpload.action, {
                    method: 'POST', body: fd,
                    headers: { 'X-WC-Stream': '1', 'Accept': 'application/x-ndjson' }
                }).then(function(r) { return readNdjsonStream(r, fileInput.files[0].name); })
                .catch(function(err) {
                    progError.textContent = err.message || 'Upload error';
                    progError.classList.remove('d-none');
                    importing = false;
                    cancelBtn.setAttribute('data-bs-dismiss', 'modal');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Upload';
                });
            }
        });
    }
})();
</script>
@endsection
