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
            <select class="form-select form-select-sm" id="projects-per-page" aria-label="Records per page">
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
            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#projectsUploadModal"><i class="fas fa-file-excel me-1"></i>Add from Excel / CSV</button>
            <a href="#" class="btn efds-btn efds-btn--primary btn-sm"><i class="fas fa-plus me-1"></i>Add</a>
            <form id="form-delete-projects" action="{{ route('modules.reliability.settings.inspection.projects.delete') }}" method="post" class="d-none">
                @csrf
                <button type="submit" id="btn-delete-projects" class="btn efds-btn efds-btn--danger btn-sm">Delete selected</button>
            </form>
        </div>
    </div>
    <div class="projects-table-card">
    <div class="card">
        <div class="card-body p-0">
            <form id="form-projects-table">
                @csrf
                <div class="reliability-table-scroll-wrap">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 2.5rem;"><input type="checkbox" id="projects-select-all" class="form-check-input" title="Select all on page"></th>
                                <th>id</th>
                                <th>PROJECT #</th>
                                <th>STATUS</th>
                                <th>TAIL NUMBER</th>
                                <th>AIRCRAFT TYPE</th>
                                <th>SCOPE</th>
                                <th>OPEN DATE</th>
                                <th>CLOSE DATE</th>
                                <th>CUSTOMER #</th>
                                <th>CUSTOMER NAME</th>
                                <th>CUSTOMER PO</th>
                                <th>EST.NON-ROUTINE</th>
                                <th>TARGET DAYS</th>
                                <th>ARRIVAL DATE</th>
                                <th>INDUCTION DATE</th>
                                <th>INSPECTION DATE</th>
                                <th>DELIVERY DATE</th>
                                <th>REV.DELIVERY DATE</th>
                                <th>LATEST DELIVERY DATE</th>
                                <th>ACTUAL ARRIVAL DATE</th>
                                <th>ACTUAL INDUCTION DATE</th>
                                <th>ACTUAL INSPECTION DATE</th>
                                <th>ACTUAL DELIVERY DATE</th>
                                <th>PROJECT TYPE</th>
                                <th>APPLICABLE STANDARD</th>
                                <th>RESOURCES</th>
                                <th>BAY</th>
                                <th>PLANNED SPAN</th>
                                <th>DAY OF CHECK</th>
                                <th>AIRCRAFT TSN</th>
                                <th>AIRCRAFT CSN</th>
                                <th>ENGINE TYPE</th>
                                <th>QUOTED MHRS</th>
                                <th>O&A MHRS</th>
                                <th>ADD WORKS MHRS</th>
                                <th>CWR MHRS</th>
                                <th>AIRCRAFT SERIES</th>
                                <th>STATION</th>
                                <th>OPEN REQUISITIONS</th>
                                <th>OPEN ORDER LINES</th>
                                <th>AWAITING TO RETURN STORE</th>
                                <th>UNINVOICE ORDER LINES</th>
                                <th>OPEN WORK CARDS</th>
                                <th>OPEN WORK ORDERS</th>
                                <th>ENG'G MHRS</th>
                                <th>TOTAL MHRS</th>
                                <th>ENGINE 1 SERIAL</th>
                                <th>ENGINE 2 SERIAL</th>
                                <th>ENGINE 3 SERIAL</th>
                                <th>ENGINE 4 SERIAL</th>
                                <th>ENGINE 1 TSN</th>
                                <th>ENGINE 1 CSN</th>
                                <th>ENGINE 2 TSN</th>
                                <th>ENGINE 2 CSN</th>
                                <th>ENGINE 3 TSN</th>
                                <th>ENGINE 3 CSN</th>
                                <th>ENGINE 4 TSN</th>
                                <th>ENGINE 4 CSN</th>
                                <th>APU PN</th>
                                <th>APU SERIAL</th>
                                <th>APU TSN</th>
                                <th>APU CSN</th>
                                <th>SPARES ORDER CUT OFF</th>
                                <th>SPARES DELIVERY CUT OFF</th>
                                <th>MHRS CAP</th>
                                <th>created_at</th>
                                <th>updated_at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                            <tr>
                                <td class="text-center"><input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input project-row-cb"></td>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->project_number }}</td>
                                <td>{{ $row->status }}</td>
                                <td>{{ $row->tail_number }}</td>
                                <td>{{ $row->aircraft_type }}</td>
                                <td>{{ Str::limit($row->scope, 40) }}</td>
                                <td>{{ $row->open_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->close_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->customer_number }}</td>
                                <td>{{ $row->customer_name }}</td>
                                <td>{{ $row->customer_po }}</td>
                                <td>{{ $row->est_non_routine }}</td>
                                <td>{{ $row->target_days }}</td>
                                <td>{{ $row->arrival_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->induction_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->inspection_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->delivery_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->rev_delivery_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->latest_delivery_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->actual_arrival_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->actual_induction_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->actual_inspection_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->actual_delivery_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->project_type }}</td>
                                <td>{{ $row->applicable_standard }}</td>
                                <td>{{ $row->resources }}</td>
                                <td>{{ $row->bay }}</td>
                                <td>{{ $row->planned_span }}</td>
                                <td>{{ $row->day_of_check }}</td>
                                <td>{{ $row->aircraft_tsn }}</td>
                                <td>{{ $row->aircraft_csn }}</td>
                                <td>{{ $row->engine_type }}</td>
                                <td>{{ $row->quoted_mhrs }}</td>
                                <td>{{ $row->oa_mhrs }}</td>
                                <td>{{ $row->add_works_mhrs }}</td>
                                <td>{{ $row->cwr_mhrs }}</td>
                                <td>{{ $row->aircraft_series }}</td>
                                <td>{{ $row->station }}</td>
                                <td>{{ $row->open_requisitions }}</td>
                                <td>{{ $row->open_order_lines }}</td>
                                <td>{{ $row->awaiting_to_return_store }}</td>
                                <td>{{ $row->uninvoice_order_lines }}</td>
                                <td>{{ $row->open_work_cards }}</td>
                                <td>{{ $row->open_work_orders }}</td>
                                <td>{{ $row->eng_mhrs }}</td>
                                <td>{{ $row->total_mhrs }}</td>
                                <td>{{ $row->engine_1_serial }}</td>
                                <td>{{ $row->engine_2_serial }}</td>
                                <td>{{ $row->engine_3_serial }}</td>
                                <td>{{ $row->engine_4_serial }}</td>
                                <td>{{ $row->engine_1_tsn }}</td>
                                <td>{{ $row->engine_1_csn }}</td>
                                <td>{{ $row->engine_2_tsn }}</td>
                                <td>{{ $row->engine_2_csn }}</td>
                                <td>{{ $row->engine_3_tsn }}</td>
                                <td>{{ $row->engine_3_csn }}</td>
                                <td>{{ $row->engine_4_tsn }}</td>
                                <td>{{ $row->engine_4_csn }}</td>
                                <td>{{ $row->apu_pn }}</td>
                                <td>{{ $row->apu_serial }}</td>
                                <td>{{ $row->apu_tsn }}</td>
                                <td>{{ $row->apu_csn }}</td>
                                <td>{{ $row->spares_order_cut_off?->format('Y-m-d') }}</td>
                                <td>{{ $row->spares_delivery_cut_off?->format('Y-m-d') }}</td>
                                <td>{{ $row->mhrs_cap }}</td>
                                <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->updated_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="68" class="text-muted">No data. Upload CSV or XLSX.</td></tr>
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

{{-- Modal: upload from Excel/CSV with drag-and-drop --}}
<div class="modal fade" id="projectsUploadModal" tabindex="-1" aria-labelledby="projectsUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectsUploadModalLabel">Add from Excel / CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-upload-projects-modal" action="{{ route('modules.reliability.settings.inspection.projects.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="file" name="file" id="projects-upload-file" class="d-none" accept=".csv,.xlsx,.xls" required>
                    <div id="projects-upload-dropzone" class="projects-upload-dropzone">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-1">Drag file here</p>
                        <p class="small text-muted mb-0">or click to select file (CSV, XLSX, XLS)</p>
                        <p id="projects-upload-filename" class="mt-2 mb-0 small text-success fw-bold d-none"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="efds-actions mb-0">
                        <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="projects-upload-submit" class="btn efds-btn efds-btn--primary" disabled>Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Back button (same as /settings/modules) */
.back-button {
    color: #007bff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 500;
    transition: color 0.3s ease;
    border: none;
    background: none;
    padding: 8px 0;
}
.back-button:hover {
    color: #0056b3;
    text-decoration: none;
}
.back-button i {
    font-size: 14px;
}

/* Stretch main_screen to full width (same as /modules/reliability) */
.main_screen {
    width: 100% !important;
}
/* Page container 100% width, no horizontal scroll */
.projects-table-card {
    width: 100%;
    overflow: hidden;
}
.projects-table-card .card,
.projects-table-card .card-body,
.projects-table-card #form-projects-table {
    min-width: 0;
    overflow: hidden;
}
/* Table scroll area */
.projects-table-card .reliability-table-scroll-wrap {
    width: 100%;
    min-width: 0;
    height: calc(100vh - 320px);
    min-height: 300px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.projects-table-card .reliability-table-scroll-wrap .table-responsive {
    width: 100%;
    flex: 1;
    min-height: 0;
    min-width: 0;
    overflow-x: auto;
    overflow-y: auto;
}
/* Sticky table header on vertical scroll */
.projects-table-card .table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8f9fa;
    box-shadow: 0 1px 0 0 #dee2e6;
}
.projects-table-card .table thead th::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 1px;
    background: #dee2e6;
}
/* Spacing between table and pagination */
.efds-pagination-wrap {
    margin-top: 1rem !important;
    padding-top: 0.75rem;
}
/* Per page / Total records block — no text wrap */
.efds-table-header__stats {
    white-space: nowrap;
    flex-shrink: 0;
    min-width: 280px;
}

/* Drop zone in modal */
.projects-upload-dropzone {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background-color 0.2s;
}
.projects-upload-dropzone:hover,
.projects-upload-dropzone.drag-over {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}
</style>
<script>
(function() {
    var formDelete = document.getElementById('form-delete-projects');
    var formTable = document.getElementById('form-projects-table');
    var checkboxes = document.querySelectorAll('.project-row-cb');
    var selectAll = document.getElementById('projects-select-all');
    var perPageSelect = document.getElementById('projects-per-page');
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
    function updateDeleteVisibility() {
        var any = Array.prototype.some.call(checkboxes, function(cb) { return cb.checked; });
        formDelete.classList.toggle('d-none', !any);
    }

    function updateSelectAll() {
        if (!selectAll) return;
        var all = document.querySelectorAll('.project-row-cb');
        var checked = document.querySelectorAll('.project-row-cb:checked');
        selectAll.checked = all.length > 0 && checked.length === all.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
    }

    Array.prototype.forEach.call(checkboxes, function(cb) {
        cb.addEventListener('change', function() {
            updateDeleteVisibility();
            updateSelectAll();
        });
    });
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            Array.prototype.forEach.call(checkboxes, function(cb) { cb.checked = selectAll.checked; });
            updateDeleteVisibility();
        });
    }

    formDelete.addEventListener('submit', function(e) {
        e.preventDefault();
        var ids = [];
        document.querySelectorAll('.project-row-cb:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        ids.forEach(function(id) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'ids[]';
            inp.value = id;
            formDelete.appendChild(inp);
        });
        formDelete.submit();
    });

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

    // Upload modal: drag-and-drop and file selection
    var uploadModal = document.getElementById('projectsUploadModal');
    var dropzone = document.getElementById('projects-upload-dropzone');
    var fileInput = document.getElementById('projects-upload-file');
    var filenameEl = document.getElementById('projects-upload-filename');
    var submitBtn = document.getElementById('projects-upload-submit');
    var formUpload = document.getElementById('form-upload-projects-modal');

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
    }

    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', resetUploadModal);
    }
    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() { fileInput.click(); });
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) setFile(this.files[0]);
        });
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('drag-over');
        });
        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('drag-over');
        });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('drag-over');
            var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) setFile(file);
        });
    }
})();
</script>
@endsection
