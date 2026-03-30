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
            <select class="form-select form-select-sm" id="work-card-materials-per-page" aria-label="Records per page">
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
            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#workCardMaterialsUploadModal"><i class="fas fa-file-excel me-1"></i>Add from Excel / CSV</button>
            <a href="#" class="btn efds-btn efds-btn--primary btn-sm"><i class="fas fa-plus me-1"></i>Add</a>
            <form id="form-delete-work-card-materials" action="{{ route('modules.reliability.settings.inspection.work-card-materials.delete') }}" method="post" class="d-none">
                @csrf
                <button type="submit" class="btn efds-btn efds-btn--danger btn-sm">Delete selected</button>
            </form>
        </div>
    </div>
    <div class="inspection-settings-table-card">
    <div class="card">
        <div class="card-body p-0">
            <form id="form-work-card-materials-table">
                @csrf
            <div class="reliability-table-scroll-wrap">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 2.5rem;"><input type="checkbox" id="work-card-materials-select-all" class="form-check-input" title="Select all on page"></th>
                                @include('Modules.Reliability.inspection_settings.partials.sort_headers_work_card_materials')
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                            <tr>
                                <td class="text-center"><input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input work-card-materials-row-cb"></td>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->project_number }}</td>
                                <td>{{ $row->work_order_number }}</td>
                                <td>{{ $row->zone_number }}</td>
                                <td>{{ $row->item_number }}</td>
                                <td>{{ $row->wip_status }}</td>
                                <td>{{ $row->card_description }}</td>
                                <td>{{ $row->customer_work_card }}</td>
                                <td>{{ $row->source_card_number }}</td>
                                <td>{{ $row->source_customer_card }}</td>
                                <td>{{ $row->tail_number }}</td>
                                <td>{{ $row->est_time }}</td>
                                <td>{{ $row->tag_number }}</td>
                                <td>{{ $row->part_number }}</td>
                                <td>{{ $row->description }}</td>
                                <td>{{ $row->oem_spec_number }}</td>
                                <td>{{ $row->group_code }}</td>
                                <td>{{ $row->expire_dt?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->csp }}</td>
                                <td>{{ $row->order_number }}</td>
                                <td>{{ $row->req_dt?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->req_due_dt?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->req_qty }}</td>
                                <td>{{ $row->req_line_internal_comment }}</td>
                                <td>{{ $row->location }}</td>
                                <td>{{ $row->order_number_2 }}</td>
                                <td>{{ $row->order_dt?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->order_due_dt?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->order_qty }}</td>
                                <td>{{ $row->receipt_dt?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->waybill }}</td>
                                <td>{{ $row->eta_dt?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->status }}</td>
                                <td>{{ $row->reason }}</td>
                                <td>{{ $row->alloc_qty }}</td>
                                <td>{{ $row->unit_cost }}</td>
                                <td>{{ $row->item_list_price }}</td>
                                <td>{{ $row->order_unit_cost }}</td>
                                <td>{{ $row->currency }}</td>
                                <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->updated_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="42" class="text-muted">No data. Upload CSV or XLSX (format IC_0097).</td></tr>
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

<div class="modal fade" id="workCardMaterialsUploadModal" tabindex="-1" aria-labelledby="workCardMaterialsUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workCardMaterialsUploadModalLabel">Add from Excel / CSV</h5>
                <button type="button" class="btn-close" id="wcm-modal-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-upload-work-card-materials-modal" action="{{ route('modules.reliability.settings.inspection.work-card-materials.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="wcm-step-select">
                        <input type="file" name="file" id="work-card-materials-upload-file" class="d-none" accept=".csv,.xlsx,.xls">
                        <div id="work-card-materials-upload-dropzone" class="inspection-upload-dropzone">
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <p class="mb-1">Drag file here</p>
                            <p class="small text-muted mb-0">or click to select file (CSV, XLSX, XLS)</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <i class="fas fa-file-excel text-success"></i>
                            <span id="work-card-materials-upload-filename" class="small text-success fw-bold d-none"></span>
                        </div>
                    </div>
                    <div id="wcm-step-progress" class="d-none">
                        <div class="text-center mb-3">
                            <i class="fas fa-file-excel text-success fa-2x mb-2"></i>
                            <p class="mb-0 small text-muted" id="wcm-prog-filename"></p>
                        </div>
                        <p class="mb-1 small">Uploading: <span id="wcm-prog-processed" class="fw-bold">0</span><span id="wcm-prog-total-wrap"> of <span id="wcm-prog-total" class="fw-bold">—</span> rows</span></p>
                        <div class="progress mb-2" style="height:1.4rem; border-radius:.5rem;">
                            <div id="wcm-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <p id="wcm-prog-error" class="small text-danger d-none mt-2"></p>
                        <p id="wcm-prog-done" class="small text-success d-none mt-2"><i class="fas fa-check-circle me-1"></i>Upload complete!</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="efds-actions mb-0">
                        <button type="button" class="btn efds-btn efds-btn--outline-primary" id="wcm-upload-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="work-card-materials-upload-submit" class="btn efds-btn efds-btn--primary" disabled>Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('Modules.Reliability.inspection_settings.partials.table_styles')
<script>
(function() {
    var formDelete = document.getElementById('form-delete-work-card-materials');
    var checkboxes = document.querySelectorAll('.work-card-materials-row-cb');
    var selectAll = document.getElementById('work-card-materials-select-all');
    function updateDeleteVisibility() {
        var any = Array.prototype.some.call(checkboxes, function(cb) { return cb.checked; });
        formDelete.classList.toggle('d-none', !any);
    }
    function updateSelectAll() {
        if (!selectAll) return;
        var all = document.querySelectorAll('.work-card-materials-row-cb');
        var checked = document.querySelectorAll('.work-card-materials-row-cb:checked');
        selectAll.checked = all.length > 0 && checked.length === all.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
    }
    Array.prototype.forEach.call(checkboxes, function(cb) {
        cb.addEventListener('change', function() { updateDeleteVisibility(); updateSelectAll(); });
    });
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            Array.prototype.forEach.call(document.querySelectorAll('.work-card-materials-row-cb'), function(cb) { cb.checked = selectAll.checked; });
            updateDeleteVisibility();
        });
    }
    formDelete.addEventListener('submit', function(e) {
        e.preventDefault();
        var ids = [];
        document.querySelectorAll('.work-card-materials-row-cb:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        ids.forEach(function(id) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
            formDelete.appendChild(inp);
        });
        formDelete.submit();
    });

    var perPageSelect = document.getElementById('work-card-materials-per-page');
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
    var uploadModal = document.getElementById('workCardMaterialsUploadModal');
    var dropzone = document.getElementById('work-card-materials-upload-dropzone');
    var fileInput = document.getElementById('work-card-materials-upload-file');
    var filenameEl = document.getElementById('work-card-materials-upload-filename');
    var submitBtn = document.getElementById('work-card-materials-upload-submit');
    var stepSelect = document.getElementById('wcm-step-select');
    var stepProgress = document.getElementById('wcm-step-progress');
    var progFilename = document.getElementById('wcm-prog-filename');
    var progProcessed = document.getElementById('wcm-prog-processed');
    var progTotalWrap = document.getElementById('wcm-prog-total-wrap');
    var progTotal = document.getElementById('wcm-prog-total');
    var progressBar = document.getElementById('wcm-progress-bar');
    var progError = document.getElementById('wcm-prog-error');
    var progDone = document.getElementById('wcm-prog-done');
    var cancelBtn = document.getElementById('wcm-upload-cancel-btn');
    var formUpload = document.getElementById('form-upload-work-card-materials-modal');
    var importing = false;

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
        stepSelect.classList.remove('d-none');
        stepProgress.classList.add('d-none');
        progError.classList.add('d-none');
        progDone.classList.add('d-none');
        importing = false;
        cancelBtn.setAttribute('data-bs-dismiss', 'modal');
        submitBtn.textContent = 'Upload';
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
        progTotal.textContent = '—';
        setProgressBar(0);
        progError.classList.add('d-none');
        progDone.classList.add('d-none');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading…';
        cancelBtn.removeAttribute('data-bs-dismiss');
        if (!response.ok) {
            return response.text().then(function(t) {
                var msg = 'HTTP ' + response.status;
                try { var j = JSON.parse(t); if (j.message) msg = j.message; else if (j.error) msg = j.error; } catch (e) {}
                throw new Error(msg);
            });
        }
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
                        if (d.error) {
                            progError.textContent = d.error;
                            progError.classList.remove('d-none');
                            importing = false;
                            cancelBtn.setAttribute('data-bs-dismiss', 'modal');
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Upload';
                            return;
                        }
                        if (typeof d.processed !== 'undefined') {
                            progProcessed.textContent = d.processed.toLocaleString();
                            if (d.total > 0) {
                                progTotal.textContent = d.total.toLocaleString();
                                setProgressBar(Math.min(100, Math.round(100 * d.processed / d.total)));
                            }
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
                                window.location.href = '{{ route("modules.reliability.settings.inspection.work-card-materials") }}?success=' + encodeURIComponent('Imported records: ' + (d.count || 0));
                            }, 1200);
                            return;
                        }
                    } catch (err) {}
                }
                return pump();
            });
        }
        return pump();
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
            if (!fileInput.files || !fileInput.files[0]) return;
            importing = true;
            var fd = new FormData(formUpload);
            fetch(formUpload.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-WCM-Stream': '1', 'Accept': 'application/x-ndjson' }
            })
            .then(function(r) { return readNdjsonStream(r, fileInput.files[0].name); })
            .catch(function(err) {
                progError.textContent = err.message || 'Upload error';
                progError.classList.remove('d-none');
                importing = false;
                cancelBtn.setAttribute('data-bs-dismiss', 'modal');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Upload';
            });
        });
    }
})();
</script>
@endsection
