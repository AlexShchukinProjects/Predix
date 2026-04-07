@extends('layout.main')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="{{ route('modules.reliability.settings.index') }}" class="back-button"><i class="fas fa-arrow-left me-2"></i>Settings</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span class="me-2">Per page:</span>
            <select class="form-select form-select-sm" id="aircrafts-per-page" aria-label="Records per page">
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
            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#aircraftsUploadModal"><i class="fas fa-file-excel me-1"></i>Add from Excel / CSV</button>
            <a href="#" class="btn efds-btn efds-btn--primary btn-sm"><i class="fas fa-plus me-1"></i>Add</a>
            <form id="form-delete-aircrafts" action="{{ route('modules.reliability.settings.inspection.aircrafts.delete') }}" method="post" class="d-none">
                @csrf
                <button type="submit" class="btn efds-btn efds-btn--danger btn-sm">Delete selected</button>
            </form>
        </div>
    </div>
    <div class="inspection-settings-table-card">
    <div class="card">
        <div class="card-body p-0">
            <form id="form-aircrafts-table">
                @csrf
            <div class="reliability-table-scroll-wrap">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 35px; min-width: 35px; max-width: 35px;"><input type="checkbox" id="aircrafts-select-all" class="form-check-input" title="Select all on page"></th>
                            @include('Modules.Reliability.inspection_settings.partials.sort_headers_aircrafts')
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                        <tr>
                            <td class="text-center" style="width: 35px; min-width: 35px; max-width: 35px;"><input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input aircrafts-row-cb"></td>
                            <td>{{ $row->id }}</td>
                            <td>{{ $row->serial_number }}</td>
                            <td>{{ $row->line_no }}</td>
                            <td>{{ $row->aircraft_type }}</td>
                            <td>{{ $row->type_ac }}</td>
                            <td>{{ $row->customer_name }}</td>
                            <td>{{ $row->first_flight?->format('Y-m-d') }}</td>
                            <td>{{ $row->tail_number }}</td>
                            <td>{{ $row->status }}</td>
                            <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $row->updated_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="13" class="text-muted">No data. Upload CSV or XLSX.</td></tr>
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
    <div class="efds-pagination-wrap mt-3 pt-2">
        {{ $items->onEachSide(1)->links('vendor.pagination.safety-reporting') }}
    </div>
    @endif
</div>

<div class="modal fade" id="aircraftsUploadModal" tabindex="-1" aria-labelledby="aircraftsUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aircraftsUploadModalLabel">Add from Excel / CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-upload-aircrafts-modal" action="{{ route('modules.reliability.settings.inspection.aircrafts.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted mb-2">Expected columns: MSN, LN, Type, Airline, First flight, Registration, Status. Field <strong>Type AC</strong> is taken from the worksheet name.</p>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="clear_before" value="1" id="aircrafts-clear-before">
                        <label class="form-check-label" for="aircrafts-clear-before">Clear Database</label>
                    </div>
                    <input type="file" name="file" id="aircrafts-upload-file" class="d-none" accept=".csv,.xlsx,.xls" required>
                    <div id="aircrafts-upload-dropzone" class="inspection-upload-dropzone">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-1">Drag file here</p>
                        <p class="small text-muted mb-0">or click to select file (CSV, XLSX, XLS)</p>
                        <p id="aircrafts-upload-filename" class="mt-2 mb-0 small text-success fw-bold d-none"></p>
                    </div>
                    <div id="aircrafts-upload-progress-wrap" class="d-none mt-3">
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <div><strong id="aircrafts-prog-file">—</strong></div>
                            <div><span id="aircrafts-prog-processed">0</span> / <span id="aircrafts-prog-total">—</span></div>
                        </div>
                        <div class="progress mb-2" style="height:1.2rem; border-radius:.5rem;">
                            <div id="aircrafts-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div id="aircrafts-prog-status" class="small text-muted">Ready to upload</div>
                        <div id="aircrafts-prog-error" class="alert alert-danger py-2 px-2 mt-2 d-none"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="efds-actions mb-0">
                        <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="aircrafts-upload-submit" class="btn efds-btn efds-btn--primary" disabled>Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('Modules.Reliability.inspection_settings.partials.table_styles')
<script>
(function() {
    var formDelete = document.getElementById('form-delete-aircrafts');
    var checkboxes = document.querySelectorAll('.aircrafts-row-cb');
    var selectAll = document.getElementById('aircrafts-select-all');

    function updateDeleteVisibility() {
        var any = Array.prototype.some.call(checkboxes, function(cb) { return cb.checked; });
        formDelete.classList.toggle('d-none', !any);
    }
    function updateSelectAll() {
        if (!selectAll) return;
        var all = document.querySelectorAll('.aircrafts-row-cb');
        var checked = document.querySelectorAll('.aircrafts-row-cb:checked');
        selectAll.checked = all.length > 0 && checked.length === all.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
    }
    Array.prototype.forEach.call(checkboxes, function(cb) {
        cb.addEventListener('change', function() { updateDeleteVisibility(); updateSelectAll(); });
    });
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            Array.prototype.forEach.call(document.querySelectorAll('.aircrafts-row-cb'), function(cb) { cb.checked = selectAll.checked; });
            updateDeleteVisibility();
        });
    }
    formDelete.addEventListener('submit', function(e) {
        e.preventDefault();
        var ids = [];
        document.querySelectorAll('.aircrafts-row-cb:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        ids.forEach(function(id) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
            formDelete.appendChild(inp);
        });
        formDelete.submit();
    });

    var perPageSelect = document.getElementById('aircrafts-per-page');
    var PER_PAGE_STORAGE_KEY = 'reliability_inspection_per_page';
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
    var uploadModal = document.getElementById('aircraftsUploadModal');
    var dropzone = document.getElementById('aircrafts-upload-dropzone');
    var fileInput = document.getElementById('aircrafts-upload-file');
    var filenameEl = document.getElementById('aircrafts-upload-filename');
    var submitBtn = document.getElementById('aircrafts-upload-submit');
    var formUpload = document.getElementById('form-upload-aircrafts-modal');
    var progressWrap = document.getElementById('aircrafts-upload-progress-wrap');
    var progFile = document.getElementById('aircrafts-prog-file');
    var progProcessed = document.getElementById('aircrafts-prog-processed');
    var progTotal = document.getElementById('aircrafts-prog-total');
    var progStatus = document.getElementById('aircrafts-prog-status');
    var progError = document.getElementById('aircrafts-prog-error');
    var progressBar = document.getElementById('aircrafts-progress-bar');
    var importing = false;
    var knownTotal = 0;

    function setProgress(pct) {
        var v = Math.max(0, Math.min(100, pct | 0));
        progressBar.style.width = v + '%';
        progressBar.textContent = v + '%';
        progressBar.setAttribute('aria-valuenow', v);
    }

    function resetProgress() {
        knownTotal = 0;
        importing = false;
        if (progressWrap) progressWrap.classList.add('d-none');
        if (progError) progError.classList.add('d-none');
        if (progStatus) progStatus.textContent = 'Ready to upload';
        if (progProcessed) progProcessed.textContent = '0';
        if (progTotal) progTotal.textContent = '—';
        if (progFile) progFile.textContent = '—';
        if (progressBar) {
            progressBar.classList.add('progress-bar-animated');
            setProgress(0);
        }
    }

    function readNdjsonStream(response, displayName) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        if (progressWrap) progressWrap.classList.remove('d-none');
        if (progFile) progFile.textContent = displayName;
        if (progStatus) progStatus.textContent = 'Uploading...';
        if (progError) progError.classList.add('d-none');
        setProgress(0);

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
                        if (typeof d.total !== 'undefined' && !knownTotal) {
                            knownTotal = Number(d.total) || 0;
                            if (progTotal) progTotal.textContent = knownTotal > 0 ? knownTotal.toLocaleString() : '—';
                        }
                        if (d.error) {
                            if (progError) {
                                progError.textContent = d.error;
                                progError.classList.remove('d-none');
                            }
                            if (progStatus) progStatus.textContent = 'Upload failed';
                            importing = false;
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Upload';
                            return;
                        }
                        if (typeof d.processed !== 'undefined') {
                            var processed = Number(d.processed) || 0;
                            var total = knownTotal || Number(d.total) || 0;
                            if (progProcessed) progProcessed.textContent = processed.toLocaleString();
                            if (progTotal) progTotal.textContent = total > 0 ? total.toLocaleString() : '—';
                            setProgress(total > 0 ? Math.round((processed / total) * 100) : 0);
                        }
                        if (d.done) {
                            setProgress(100);
                            if (progressBar) progressBar.classList.remove('progress-bar-animated');
                            if (progStatus) progStatus.textContent = 'Imported records: ' + (d.count || 0);
                            importing = false;
                            submitBtn.textContent = 'Done';
                            setTimeout(function() { window.location.reload(); }, 1000);
                            return;
                        }
                    } catch (e) {}
                }
                return pump();
            });
        }
        return pump();
    }

    function setFile(file) {
        if (!file) return;
        var dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        filenameEl.textContent = file.name;
        filenameEl.classList.remove('d-none');
        submitBtn.disabled = false;
    }
    function resetUploadModal() {
        fileInput.value = '';
        filenameEl.classList.add('d-none');
        filenameEl.textContent = '';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Upload';
        resetProgress();
    }
    if (uploadModal) uploadModal.addEventListener('show.bs.modal', resetUploadModal);
    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() { if (!importing) fileInput.click(); });
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) setFile(this.files[0]);
        });
        dropzone.addEventListener('dragover', function(e) { e.preventDefault(); e.stopPropagation(); dropzone.classList.add('drag-over'); });
        dropzone.addEventListener('dragleave', function(e) { e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('drag-over'); });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('drag-over');
            var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) setFile(file);
        });
    }
    if (formUpload) {
        formUpload.addEventListener('submit', function(e) {
            e.preventDefault();
            if (importing || !fileInput.files || !fileInput.files[0]) return;
            importing = true;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
            var fd = new FormData(formUpload);
            fetch(formUpload.action, {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/x-ndjson', 'X-WC-Stream': '1' }
            }).then(function(resp) {
                return readNdjsonStream(resp, fileInput.files[0].name);
            }).catch(function(err) {
                importing = false;
                submitBtn.disabled = false;
                submitBtn.textContent = 'Upload';
                if (progressWrap) progressWrap.classList.remove('d-none');
                if (progError) {
                    progError.textContent = err.message || 'Upload failed';
                    progError.classList.remove('d-none');
                }
                if (progStatus) progStatus.textContent = 'Upload failed';
            });
        });
    }
})();
</script>
@endsection
