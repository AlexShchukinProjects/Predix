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
                            <th class="text-center" style="width: 2.5rem;"><input type="checkbox" id="aircrafts-select-all" class="form-check-input" title="Select all on page"></th>
                            <th>id</th>
                            <th>SERIAL #</th>
                            <th>TAIL #</th>
                            <th>AIRCRAFT TYPE</th>
                            <th>VISIT</th>
                            <th>CUSTOMER #</th>
                            <th>OWNER #</th>
                            <th>ENGINE TYPE</th>
                            <th>APU TYPE</th>
                            <th>GROUP CODE</th>
                            <th>DELIVERY DATE</th>
                            <th>REDELIVERY DATE</th>
                            <th>ETOPS</th>
                            <th>AMM GROUP</th>
                            <th>CUSTOMER NAME</th>
                            <th>OWNER NAME</th>
                            <th>APP. STD</th>
                            <th>LINE NO</th>
                            <th>VARIABLE NO</th>
                            <th>EFFECTIVITY</th>
                            <th>SELCAL</th>
                            <th>LEASE DATE</th>
                            <th>MANUFACTURED</th>
                            <th>INS. DATE</th>
                            <th>PAS.CAP.</th>
                            <th>SEAT MAT.</th>
                            <th>MAX.TAXI</th>
                            <th>MAX.TO.</th>
                            <th>MAX.LAND</th>
                            <th>MAXIMUM ZERO FUEL WEIGHT</th>
                            <th>MAX.PAY.</th>
                            <th>DRY OPE.</th>
                            <th>FUEL</th>
                            <th>FUEL BURN RATIO</th>
                            <th>FWD CARGO</th>
                            <th>AFT CARGO</th>
                            <th>FWD AREA</th>
                            <th>AFT AREA</th>
                            <th>SIDE NOISE</th>
                            <th>APP.NOISE</th>
                            <th>START NOISE</th>
                            <th>ENG.RATE</th>
                            <th>MOD</th>
                            <th>COLOR</th>
                            <th>FLIGHT NUMBER</th>
                            <th>SCHEDULED FROM</th>
                            <th>SCHEDULED TO</th>
                            <th>SCHEDULED OFF BLOCK</th>
                            <th>SCHEDULED ON BLOCK</th>
                            <th>ACTUAL FROM</th>
                            <th>ACTUAL TO</th>
                            <th>ACTUAL OFF BLOCK</th>
                            <th>ACTUAL ON BLOCK</th>
                            <th>ROUTE DEV. DIST.</th>
                            <th>ROUTE DEV. TIME</th>
                            <th>WNG.</th>
                            <th>ARCHIVE</th>
                            <th>ACTIVE</th>
                            <th>created_at</th>
                            <th>updated_at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                        <tr>
                            <td class="text-center"><input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input aircrafts-row-cb"></td>
                            <td>{{ $row->id }}</td>
                            <td>{{ $row->serial_number }}</td>
                            <td>{{ $row->tail_number }}</td>
                            <td>{{ $row->aircraft_type }}</td>
                            <td>{{ $row->visit }}</td>
                            <td>{{ $row->customer_number }}</td>
                            <td>{{ $row->owner_number }}</td>
                            <td>{{ $row->engine_type }}</td>
                            <td>{{ $row->apu_type }}</td>
                            <td>{{ $row->group_code }}</td>
                            <td>{{ $row->delivery_date?->format('Y-m-d') }}</td>
                            <td>{{ $row->redelivery_date?->format('Y-m-d') }}</td>
                            <td>{{ $row->etops }}</td>
                            <td>{{ $row->amm_group }}</td>
                            <td>{{ $row->customer_name }}</td>
                            <td>{{ $row->owner_name }}</td>
                            <td>{{ $row->app_std }}</td>
                            <td>{{ $row->line_no }}</td>
                            <td>{{ $row->variable_no }}</td>
                            <td>{{ $row->effectivity }}</td>
                            <td>{{ $row->selcal }}</td>
                            <td>{{ $row->lease_date?->format('Y-m-d') }}</td>
                            <td>{{ $row->manufactured }}</td>
                            <td>{{ $row->ins_date?->format('Y-m-d') }}</td>
                            <td>{{ $row->pas_cap }}</td>
                            <td>{{ $row->seat_mat }}</td>
                            <td>{{ $row->max_taxi }}</td>
                            <td>{{ $row->max_to }}</td>
                            <td>{{ $row->max_land }}</td>
                            <td>{{ $row->maximum_zero_fuel_weight }}</td>
                            <td>{{ $row->max_pay }}</td>
                            <td>{{ $row->dry_ope }}</td>
                            <td>{{ $row->fuel }}</td>
                            <td>{{ $row->fuel_burn_ratio }}</td>
                            <td>{{ $row->fwd_cargo }}</td>
                            <td>{{ $row->aft_cargo }}</td>
                            <td>{{ $row->fwd_area }}</td>
                            <td>{{ $row->aft_area }}</td>
                            <td>{{ $row->side_noise }}</td>
                            <td>{{ $row->app_noise }}</td>
                            <td>{{ $row->start_noise }}</td>
                            <td>{{ $row->eng_rate }}</td>
                            <td>{{ $row->mod }}</td>
                            <td>{{ $row->color }}</td>
                            <td>{{ $row->flight_number }}</td>
                            <td>{{ $row->scheduled_from }}</td>
                            <td>{{ $row->scheduled_to }}</td>
                            <td>{{ $row->scheduled_off_block }}</td>
                            <td>{{ $row->scheduled_on_block }}</td>
                            <td>{{ $row->actual_from }}</td>
                            <td>{{ $row->actual_to }}</td>
                            <td>{{ $row->actual_off_block }}</td>
                            <td>{{ $row->actual_on_block }}</td>
                            <td>{{ $row->route_dev_dist }}</td>
                            <td>{{ $row->route_dev_time }}</td>
                            <td>{{ $row->wng }}</td>
                            <td>{{ $row->archive }}</td>
                            <td>{{ $row->active }}</td>
                            <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $row->updated_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="61" class="text-muted">No data. Upload CSV or XLSX.</td></tr>
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
                    <input type="file" name="file" id="aircrafts-upload-file" class="d-none" accept=".csv,.xlsx,.xls" required>
                    <div id="aircrafts-upload-dropzone" class="inspection-upload-dropzone">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-1">Drag file here</p>
                        <p class="small text-muted mb-0">or click to select file (CSV, XLSX, XLS)</p>
                        <p id="aircrafts-upload-filename" class="mt-2 mb-0 small text-success fw-bold d-none"></p>
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
    if (uploadModal) uploadModal.addEventListener('show.bs.modal', resetUploadModal);
    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() { fileInput.click(); });
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
})();
</script>
@endsection
