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
            <select class="form-select form-select-sm" id="work-cards-per-page" aria-label="Records per page">
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
            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#workCardsUploadModal"><i class="fas fa-file-excel me-1"></i>Add from Excel / CSV</button>
            <a href="#" class="btn efds-btn efds-btn--primary btn-sm"><i class="fas fa-plus me-1"></i>Add</a>
            <form id="form-delete-work-cards" action="{{ route('modules.reliability.settings.inspection.work-cards.delete') }}" method="post" class="d-none">
                @csrf
                <button type="submit" class="btn efds-btn efds-btn--danger btn-sm">Delete selected</button>
            </form>
        </div>
    </div>
    <div class="inspection-settings-table-card">
    <div class="card">
        <div class="card-body p-0">
            <form id="form-work-cards-table">
                @csrf
            <div class="reliability-table-scroll-wrap">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 2.5rem;"><input type="checkbox" id="work-cards-select-all" class="form-check-input" title="Select all on page"></th>
                                <th>id</th>
                                <th>PROJECT</th>
                                <th>PROJECT TYPE</th>
                                <th>AIRCRAFT TYPE</th>
                                <th>TAIL NUMBER</th>
                                <th>BAY</th>
                                <th>WO STATION</th>
                                <th>WORK ORDER</th>
                                <th>ZONE</th>
                                <th>ITEM</th>
                                <th>QUALITY CODE</th>
                                <th>ZONES</th>
                                <th>STATUS</th>
                                <th>WIP STATUS</th>
                                <th>REASON</th>
                                <th>SRC. ORDER</th>
                                <th>SRC. ZONE</th>
                                <th>SRC. ITEM</th>
                                <th>SRC. CUST. CARD</th>
                                <th>SRC. OPEN DT.</th>
                                <th>DESCRIPTION</th>
                                <th>CORRECTIVE ACTION</th>
                                <th>OPEN DATE</th>
                                <th>CLOSE DATE</th>
                                <th>PLANNED START</th>
                                <th>PLANNED FINISH DATE</th>
                                <th>CARD START DATE</th>
                                <th>CARD FINISH DATE</th>
                                <th>MS START DAY</th>
                                <th>MS FINISH DAY</th>
                                <th>MS START DATE</th>
                                <th>MS FINISH DATE</th>
                                <th>MS DESCIPTION</th>
                                <th>PRIM. SKILL</th>
                                <th>SKILL CODES</th>
                                <th>DOT</th>
                                <th>ATA</th>
                                <th>CUST. CARD</th>
                                <th>TASK CODE</th>
                                <th>ORDER TYPE</th>
                                <th>CONTRACT</th>
                                <th>CONTRACT DESCRIPTION</th>
                                <th>MPD/NRC MHRS</th>
                                <th>APPR. TIME</th>
                                <th>BILL. TIME</th>
                                <th>REM. EST.</th>
                                <th>REM. APPR.</th>
                                <th>APPL. TIME</th>
                                <th>AVG. TIME</th>
                                <th>ACT. TIME</th>
                                <th>APP.USER</th>
                                <th>AIRCRAFT LOCATION</th>
                                <th>MILESTONE</th>
                                <th>INDEPENDENT INSPECTOR NUMBER</th>
                                <th>INSPECTOR</th>
                                <th>INSPECTOR NAME</th>
                                <th>CREATED BY</th>
                                <th>CREATED BY NAME</th>
                                <th>PERFORMED BY EMPLOYEE#</th>
                                <th>PERFORMED DATE</th>
                                <th>W/O DEPT</th>
                                <th>WORK ORDER DEPARTMENT NAME</th>
                                <th>SHOP</th>
                                <th>SHOP DESCRIPTION</th>
                                <th>DEPARTMENT</th>
                                <th>DEPARTMENT NAME</th>
                                <th>APPLICABLE STANDARD</th>
                                <th>FORM APPLICABLE STANDARD</th>
                                <th>FORM NUMBER</th>
                                <th>PANEL CODES</th>
                                <th>COMPONENT NUMBER</th>
                                <th>COMP. QTY</th>
                                <th>SERIAL NUMBER</th>
                                <th>SERVICES</th>
                                <th>PRINT COUNT</th>
                                <th>CHECK STATUS</th>
                                <th>CHECK BY EMPLOYEE NUMBER</th>
                                <th>CHECK BY EMPLOYEE NAME</th>
                                <th>CHECK DATE</th>
                                <th>DOCUMENTS</th>
                                <th>MANUFACTURER</th>
                                <th>ESTIMATOR COMMENT</th>
                                <th>REPRESENTATIVE COMMENT</th>
                                <th>CONTROLLER COMMENT</th>
                                <th>FINDINGS</th>
                                <th>CUSTOMER#</th>
                                <th>CUSTOMER</th>
                                <th>INSPECTION DATE</th>
                                <th>PART DESCRIPTION</th>
                                <th>AUTH. TYPE</th>
                                <th>CONDITION CODE</th>
                                <th>CONDITION</th>
                                <th>ETOPS</th>
                                <th>CRITICAL</th>
                                <th>ILS</th>
                                <th>RII</th>
                                <th>CDCCL</th>
                                <th>LEAK C.</th>
                                <th>OPEN</th>
                                <th>CLOSE</th>
                                <th>LUBE</th>
                                <th>SDR</th>
                                <th>STRUCTURAL</th>
                                <th>ENGINE RUN</th>
                                <th>ON FLOOR</th>
                                <th>MAJOR</th>
                                <th>ALTER</th>
                                <th>CPCP</th>
                                <th>LOGON</th>
                                <th>ONLY ASSIGNED</th>
                                <th>AIRCRAFT</th>
                                <th>GQAR</th>
                                <th>BILLABLE</th>
                                <th>LOCK</th>
                                <th>OPEN STEPS#</th>
                                <th>TOTAL STEPS#</th>
                                <th>MAINT.START DATE</th>
                                <th>CHILD CARD COUNT</th>
                                <th>GROUP CODE</th>
                                <th>POCKET#</th>
                                <th>PIN POCKET</th>
                                <th>HANDOVER</th>
                                <th>INCOMING DEFECT</th>
                                <th>MANDATORY</th>
                                <th>EST. MHRS</th>
                                <th>DMI DUE DATE</th>
                                <th>DMI REFERENCE</th>
                                <th>CMM REFERENCE</th>
                                <th>EXT NO</th>
                                <th>AC MSN</th>
                                <th>SERV. HRS.</th>
                                <th>BARCODE PRINT COUNT</th>
                                <th>COMPLETED TIME (UTC)</th>
                                <th>created_at</th>
                                <th>updated_at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                            <tr>
                                <td class="text-center"><input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input work-cards-row-cb"></td>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->project }}</td>
                                <td>{{ $row->project_type }}</td>
                                <td>{{ $row->aircraft_type }}</td>
                                <td>{{ $row->tail_number }}</td>
                                <td>{{ $row->bay }}</td>
                                <td>{{ $row->wo_station }}</td>
                                <td>{{ $row->work_order }}</td>
                                <td>{{ $row->zone }}</td>
                                <td>{{ $row->item }}</td>
                                <td>{{ $row->quality_code }}</td>
                                <td>{{ $row->zones }}</td>
                                <td>{{ $row->status }}</td>
                                <td>{{ $row->wip_status }}</td>
                                <td>{{ $row->reason }}</td>
                                <td>{{ $row->src_order }}</td>
                                <td>{{ $row->src_zone }}</td>
                                <td>{{ $row->src_item }}</td>
                                <td>{{ $row->src_cust_card }}</td>
                                <td>{{ $row->src_open_dt?->format('Y-m-d H:i') }}</td>
                                <td>{{ Str::limit($row->description, 50) }}</td>
                                <td>{{ Str::limit($row->corrective_action, 50) }}</td>
                                <td>{{ $row->open_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->close_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->planned_start?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->planned_finish_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->card_start_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->card_finish_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->ms_start_day }}</td>
                                <td>{{ $row->ms_finish_day }}</td>
                                <td>{{ $row->ms_start_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->ms_finish_date?->format('Y-m-d') }}</td>
                                <td>{{ Str::limit($row->ms_description, 30) }}</td>
                                <td>{{ $row->prim_skill }}</td>
                                <td>{{ $row->skill_codes }}</td>
                                <td>{{ $row->dot }}</td>
                                <td>{{ $row->ata }}</td>
                                <td>{{ $row->cust_card }}</td>
                                <td>{{ $row->task_code }}</td>
                                <td>{{ $row->order_type }}</td>
                                <td>{{ $row->contract }}</td>
                                <td>{{ Str::limit($row->contract_description, 30) }}</td>
                                <td>{{ $row->mpd_nrc_mhrs }}</td>
                                <td>{{ $row->appr_time }}</td>
                                <td>{{ $row->bill_time }}</td>
                                <td>{{ $row->rem_est }}</td>
                                <td>{{ $row->rem_appr }}</td>
                                <td>{{ $row->appl_time }}</td>
                                <td>{{ $row->avg_time }}</td>
                                <td>{{ $row->act_time }}</td>
                                <td>{{ $row->app_user }}</td>
                                <td>{{ $row->aircraft_location }}</td>
                                <td>{{ $row->milestone }}</td>
                                <td>{{ $row->independent_inspector_number }}</td>
                                <td>{{ $row->inspector }}</td>
                                <td>{{ $row->inspector_name }}</td>
                                <td>{{ $row->created_by }}</td>
                                <td>{{ $row->created_by_name }}</td>
                                <td>{{ $row->performed_by_employee_number }}</td>
                                <td>{{ $row->performed_date?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->wo_dept }}</td>
                                <td>{{ $row->work_order_department_name }}</td>
                                <td>{{ $row->shop }}</td>
                                <td>{{ $row->shop_description }}</td>
                                <td>{{ $row->department }}</td>
                                <td>{{ $row->department_name }}</td>
                                <td>{{ $row->applicable_standard }}</td>
                                <td>{{ $row->form_applicable_standard }}</td>
                                <td>{{ $row->form_number }}</td>
                                <td>{{ $row->panel_codes }}</td>
                                <td>{{ $row->component_number }}</td>
                                <td>{{ $row->comp_qty }}</td>
                                <td>{{ $row->serial_number }}</td>
                                <td>{{ Str::limit($row->services, 30) }}</td>
                                <td>{{ $row->print_count }}</td>
                                <td>{{ $row->check_status }}</td>
                                <td>{{ $row->check_by_employee_number }}</td>
                                <td>{{ $row->check_by_employee_name }}</td>
                                <td>{{ $row->check_date?->format('Y-m-d H:i') }}</td>
                                <td>{{ Str::limit($row->documents, 30) }}</td>
                                <td>{{ $row->manufacturer }}</td>
                                <td>{{ Str::limit($row->estimator_comment, 30) }}</td>
                                <td>{{ Str::limit($row->representative_comment, 30) }}</td>
                                <td>{{ Str::limit($row->controller_comment, 30) }}</td>
                                <td>{{ Str::limit($row->findings, 30) }}</td>
                                <td>{{ $row->customer_number }}</td>
                                <td>{{ $row->customer }}</td>
                                <td>{{ $row->inspection_date?->format('Y-m-d') }}</td>
                                <td>{{ Str::limit($row->part_description, 30) }}</td>
                                <td>{{ $row->auth_type }}</td>
                                <td>{{ $row->condition_code }}</td>
                                <td>{{ $row->condition }}</td>
                                <td>{{ $row->etops }}</td>
                                <td>{{ $row->critical }}</td>
                                <td>{{ $row->ils }}</td>
                                <td>{{ $row->rii }}</td>
                                <td>{{ $row->cdccl }}</td>
                                <td>{{ $row->leak_c }}</td>
                                <td>{{ $row->open }}</td>
                                <td>{{ $row->close }}</td>
                                <td>{{ $row->lube }}</td>
                                <td>{{ $row->sdr }}</td>
                                <td>{{ $row->structural }}</td>
                                <td>{{ $row->engine_run }}</td>
                                <td>{{ $row->on_floor }}</td>
                                <td>{{ $row->major }}</td>
                                <td>{{ $row->alter }}</td>
                                <td>{{ $row->cpcp }}</td>
                                <td>{{ $row->logon }}</td>
                                <td>{{ $row->only_assigned }}</td>
                                <td>{{ $row->aircraft }}</td>
                                <td>{{ $row->gqar }}</td>
                                <td>{{ $row->billable }}</td>
                                <td>{{ $row->lock }}</td>
                                <td>{{ $row->open_steps_number }}</td>
                                <td>{{ $row->total_steps_number }}</td>
                                <td>{{ $row->maint_start_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->child_card_count }}</td>
                                <td>{{ $row->group_code }}</td>
                                <td>{{ $row->pocket_number }}</td>
                                <td>{{ $row->pin_pocket }}</td>
                                <td>{{ $row->handover }}</td>
                                <td>{{ Str::limit($row->incoming_defect, 30) }}</td>
                                <td>{{ $row->mandatory }}</td>
                                <td>{{ $row->est_mhrs }}</td>
                                <td>{{ $row->dmi_due_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->dmi_reference }}</td>
                                <td>{{ $row->cmm_reference }}</td>
                                <td>{{ $row->ext_no }}</td>
                                <td>{{ $row->ac_msn }}</td>
                                <td>{{ $row->serv_hrs }}</td>
                                <td>{{ $row->barcode_print_count }}</td>
                                <td>{{ $row->completed_time_utc?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->updated_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="136" class="text-muted">No data. Upload CSV or XLSX.</td></tr>
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

<div class="modal fade" id="workCardsUploadModal" tabindex="-1" aria-labelledby="workCardsUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workCardsUploadModalLabel">Add from Excel / CSV</h5>
                <button type="button" class="btn-close" id="wc-modal-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-upload-work-cards-modal" action="{{ route('modules.reliability.settings.inspection.work-cards.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- Шаг 1: выбор файла --}}
                    <div id="wc-step-select">
                        {{-- Вкладки: Upload / С диска --}}
                        <ul class="nav nav-tabs mb-3" id="wc-upload-tabs">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" id="wc-tab-upload" onclick="wcSwitchTab('upload')">
                                    <i class="fas fa-upload me-1"></i>Upload file
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" id="wc-tab-local" onclick="wcSwitchTab('local')">
                                    <i class="fas fa-server me-1"></i>From server disk
                                </button>
                            </li>
                        </ul>

                        {{-- Панель: загрузка файла --}}
                        <div id="wc-panel-upload">
                            <input type="file" name="file" id="work-cards-upload-file" class="d-none" accept=".csv,.xlsx,.xls">
                            <div id="work-cards-upload-dropzone" class="inspection-upload-dropzone">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="mb-1">Drag file here</p>
                                <p class="small text-muted mb-0">or click to select file (CSV, XLSX, XLS)</p>
                            </div>
                        </div>

                        {{-- Панель: файл с диска --}}
                        <div id="wc-panel-local" class="d-none">
                            <p class="small text-muted mb-2">Enter file path relative to project root:</p>
                            <input type="text" id="wc-local-path" class="form-control form-control-sm font-monospace"
                                   placeholder="excel/GAES Data/PR_0059_Work Cards_Data.xlsx"
                                   value="excel/GAES Data/PR_0059_Work Cards_Data.xlsx">
                            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm mt-2" id="wc-local-check">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="wc-local-spin"></span>
                                Check and count rows
                            </button>
                        </div>

                        {{-- Информация о файле и кол-ве строк --}}
                        <div id="wc-file-info" class="mt-3 d-none">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-file-excel text-success"></i>
                                <span id="work-cards-upload-filename" class="small text-success fw-bold"></span>
                            </div>
                            <div id="wc-counting" class="small text-muted d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>Counting rows…
                            </div>
                            <div id="wc-count-result" class="d-none">
                                <span class="small text-muted">Rows to upload: </span>
                                <strong id="wc-total-rows" class="text-primary fs-6">—</strong>
                            </div>
                            <div id="wc-count-error" class="small text-danger mt-1 d-none"></div>
                        </div>
                    </div>
                    {{-- Шаг 2: прогресс загрузки --}}
                    <div id="wc-step-progress" class="d-none">
                        <div class="text-center mb-3">
                            <i class="fas fa-file-excel text-success fa-2x mb-2"></i>
                            <p class="mb-0 small text-muted" id="wc-prog-filename"></p>
                        </div>
                        <p class="mb-1 small">Uploading: <span id="wc-prog-processed" class="fw-bold">0</span> of <span id="wc-prog-total" class="fw-bold">—</span> rows</p>
                        <div class="progress mb-2" style="height:1.4rem; border-radius:.5rem;">
                            <div id="wc-progress-bar"
                                 class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                0%
                            </div>
                        </div>
                        <p id="wc-prog-error" class="small text-danger d-none mt-2"></p>
                        <p id="wc-prog-done" class="small text-success d-none mt-2">
                            <i class="fas fa-check-circle me-1"></i>Upload complete!
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="efds-actions mb-0">
                        <button type="button" class="btn efds-btn efds-btn--outline-primary" id="wc-upload-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="work-cards-upload-submit" class="btn efds-btn efds-btn--primary" disabled>Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('Modules.Reliability.inspection_settings.partials.table_styles')
<script>
(function() {
    var formDelete = document.getElementById('form-delete-work-cards');
    var checkboxes = document.querySelectorAll('.work-cards-row-cb');
    var selectAll = document.getElementById('work-cards-select-all');
    function updateDeleteVisibility() {
        var any = Array.prototype.some.call(checkboxes, function(cb) { return cb.checked; });
        formDelete.classList.toggle('d-none', !any);
    }
    function updateSelectAll() {
        if (!selectAll) return;
        var all = document.querySelectorAll('.work-cards-row-cb');
        var checked = document.querySelectorAll('.work-cards-row-cb:checked');
        selectAll.checked = all.length > 0 && checked.length === all.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
    }
    Array.prototype.forEach.call(checkboxes, function(cb) {
        cb.addEventListener('change', function() { updateDeleteVisibility(); updateSelectAll(); });
    });
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            Array.prototype.forEach.call(document.querySelectorAll('.work-cards-row-cb'), function(cb) { cb.checked = selectAll.checked; });
            updateDeleteVisibility();
        });
    }
    formDelete.addEventListener('submit', function(e) {
        e.preventDefault();
        var ids = [];
        document.querySelectorAll('.work-cards-row-cb:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        ids.forEach(function(id) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
            formDelete.appendChild(inp);
        });
        formDelete.submit();
    });

    var perPageSelect = document.getElementById('work-cards-per-page');
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
    // ===== Upload modal =====
    var uploadModal   = document.getElementById('workCardsUploadModal');
    var dropzone      = document.getElementById('work-cards-upload-dropzone');
    var fileInput     = document.getElementById('work-cards-upload-file');
    var submitBtn     = document.getElementById('work-cards-upload-submit');
    var cancelBtn     = document.getElementById('wc-upload-cancel-btn');
    var formUpload    = document.getElementById('form-upload-work-cards-modal');

    var stepSelect    = document.getElementById('wc-step-select');
    var fileInfoEl    = document.getElementById('wc-file-info');
    var filenameEl    = document.getElementById('work-cards-upload-filename');
    var countingEl    = document.getElementById('wc-counting');
    var countResultEl = document.getElementById('wc-count-result');
    var totalRowsEl   = document.getElementById('wc-total-rows');
    var countErrorEl  = document.getElementById('wc-count-error');

    var stepProgress  = document.getElementById('wc-step-progress');
    var progFilename  = document.getElementById('wc-prog-filename');
    var progProcessed = document.getElementById('wc-prog-processed');
    var progTotal     = document.getElementById('wc-prog-total');
    var progressBar   = document.getElementById('wc-progress-bar');
    var progError     = document.getElementById('wc-prog-error');
    var progDone      = document.getElementById('wc-prog-done');

    var knownTotal  = 0;
    var importing   = false;
    var activeTab   = 'upload'; // 'upload' | 'local'
    var localPathOk = false;

    window.wcSwitchTab = function(tab) {
        activeTab = tab;
        document.getElementById('wc-tab-upload').classList.toggle('active', tab === 'upload');
        document.getElementById('wc-tab-local').classList.toggle('active', tab === 'local');
        document.getElementById('wc-panel-upload').classList.toggle('d-none', tab !== 'upload');
        document.getElementById('wc-panel-local').classList.toggle('d-none', tab !== 'local');
        fileInfoEl.classList.add('d-none');
        submitBtn.disabled = true;
        knownTotal = 0;
        localPathOk = false;
    };

    function resetModal() {
        importing   = false;
        localPathOk = false;
        fileInput.value = '';
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
        wcSwitchTab('upload');
    }

    function setProgressBar(pct) {
        progressBar.style.width = pct + '%';
        progressBar.textContent = pct + '%';
        progressBar.setAttribute('aria-valuenow', pct);
    }

    // Читаем поток NDJSON и обновляем UI прогресса
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
        var reader  = response.body.getReader();
        var decoder = new TextDecoder();
        var buf     = '';
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
                                window.location.href = '{{ route("modules.reliability.settings.inspection.work-cards") }}?success=' + encodeURIComponent('Imported records: ' + (d.count || 0));
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
        fd.append('_token', document.querySelector('#form-upload-work-cards-modal [name=_token]').value);

        fetch('{{ route("modules.reliability.settings.inspection.work-cards.count") }}', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            countingEl.classList.add('d-none');
            if (data.error) { countErrorEl.textContent = data.error; countErrorEl.classList.remove('d-none'); return; }
            knownTotal = data.total || 0;
            totalRowsEl.textContent = knownTotal.toLocaleString();
            countResultEl.classList.remove('d-none');
            submitBtn.disabled = false;
        }).catch(function(err) {
            countingEl.classList.add('d-none');
            countErrorEl.textContent = 'Count error: ' + err.message;
            countErrorEl.classList.remove('d-none');
            submitBtn.disabled = false;
        });
    }

    // Кнопка «Проверить» для локального файла
    var localCheckBtn = document.getElementById('wc-local-check');
    var localSpin     = document.getElementById('wc-local-spin');

    function showLocalError(msg) {
        localSpin.classList.add('d-none');
        localCheckBtn.disabled = false;
        fileInfoEl.classList.remove('d-none');
        countingEl.classList.add('d-none');
        countResultEl.classList.add('d-none');
        countErrorEl.textContent = msg;
        countErrorEl.classList.remove('d-none');
    }

    if (localCheckBtn) {
        localCheckBtn.addEventListener('click', function() {
            var path = document.getElementById('wc-local-path').value.trim();
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

            var csrfToken = document.querySelector('#form-upload-work-cards-modal [name=_token]').value;
            var fd2 = new FormData();
            fd2.append('_token', csrfToken);
            fd2.append('local_path', path);

            fetch('{{ route("modules.reliability.settings.inspection.work-cards.import-local") }}', {
                method: 'POST',
                body: fd2,
                headers: { 'X-WC-Count-Only': '1', 'Accept': 'application/x-ndjson' }
            }).then(function(r) {
                // Ошибки до стриминга приходят как обычный JSON
                if (!r.ok) {
                    return r.text().then(function(t) {
                        try { var e = JSON.parse(t); throw new Error(e.error || 'HTTP ' + r.status); }
                        catch(pe) { throw new Error('HTTP ' + r.status + (pe.message && pe.message.indexOf('{') < 0 ? ': ' + pe.message : '')); }
                    });
                }
                // Успешный ответ — NDJSON поток, читаем первую строку
                var reader2 = r.body.getReader();
                var dec = new TextDecoder();
                var buf2 = '';
                function readFirst() {
                    return reader2.read().then(function(chunk) {
                        buf2 += dec.decode(chunk.value || new Uint8Array(), { stream: !chunk.done });
                        var nl = buf2.indexOf('\n');
                        if (nl < 0 && !chunk.done) return readFirst(); // ещё нет полной строки
                        var firstLine = (nl >= 0 ? buf2.substring(0, nl) : buf2).trim();
                        reader2.cancel();
                        var d = JSON.parse(firstLine);
                        if (d.error) throw new Error(d.error);
                        localSpin.classList.add('d-none');
                        localCheckBtn.disabled = false;
                        knownTotal = d.total || 0;
                        totalRowsEl.textContent = knownTotal.toLocaleString();
                        countResultEl.classList.remove('d-none');
                        countErrorEl.classList.add('d-none');
                        countingEl.classList.add('d-none');
                        localPathOk = true;
                        submitBtn.disabled = false;
                    });
                }
                return readFirst();
            }).catch(function(err) {
                showLocalError(err.message || 'Connection error');
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
                // Импорт с диска
                var path = document.getElementById('wc-local-path').value.trim();
                if (!path || !localPathOk) return;
                var fd3 = new FormData();
                fd3.append('_token', document.querySelector('#form-upload-work-cards-modal [name=_token]').value);
                fd3.append('path', path);
                fetch('{{ route("modules.reliability.settings.inspection.work-cards.import-local") }}', {
                    method: 'POST', body: fd3, headers: { 'Accept': 'application/x-ndjson' }
                }).then(function(r) { return readNdjsonStream(r, path.split('/').pop()); })
                .catch(function(err) {
                    progError.textContent = err.message || 'Error';
                    progError.classList.remove('d-none');
                    importing = false;
                    cancelBtn.setAttribute('data-bs-dismiss', 'modal');
                });
            } else {
                // Загрузка файла с компьютера
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
