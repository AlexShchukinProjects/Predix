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

    @php
        $source = $source ?? 'rc';
        $sortColumn = $sortColumn ?? 'id';
        $sortDirection = $sortDirection ?? 'asc';
    @endphp

    <!-- Filters panel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('modules.reliability.settings.master-data.index') }}" id="masterDataFiltersForm">
                        <input type="hidden" name="source" value="{{ $source }}">
                        <input type="hidden" name="per_page" id="masterDataFilterPerPage" value="{{ request('per_page', $perPage ?? 50) }}">
                        <input type="hidden" name="sort" value="{{ request('sort', $sortColumn) }}">
                        <input type="hidden" name="dir" value="{{ request('dir', $sortDirection) }}">
                        <div class="row g-3 master-data-filters">
                            <div class="col-12 small text-muted">Filters match Work Card columns (import uses only these columns from CSV/XLSX).</div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="ID">ID</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="id" value="{{ request('id') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="PROJECT">PROJECT</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="project" value="{{ request('project') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="PROJECT TYPE">PROJECT TYPE</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="project_type" value="{{ request('project_type') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="AIRCRAFT TYPE">AIRCRAFT TYPE</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="aircraft_type" value="{{ request('aircraft_type') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="TAIL NUMBER">TAIL NUMBER</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="tail_number" value="{{ request('tail_number') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="WO STATION">WO STATION</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="wo_station" value="{{ request('wo_station') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="WORK ORDER">WORK ORDER</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="work_order" value="{{ request('work_order') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="ITEM">ITEM</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="item" value="{{ request('item') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="SRC. ORDER">SRC. ORDER</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="src_order" value="{{ request('src_order') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="SRC. ITEM">SRC. ITEM</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="src_item" value="{{ request('src_item') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="SRC. CUST. CARD">SRC. CUST. CARD</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="src_cust_card" value="{{ request('src_cust_card') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="DESCRIPTION">DESCRIPTION</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="description" value="{{ request('description') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="CORRECTIVE ACTION">CORRECTIVE ACTION</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="corrective_action" value="{{ request('corrective_action') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="ATA">ATA</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="ata" value="{{ request('ata') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="CUST. CARD">CUST. CARD</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="cust_card" value="{{ request('cust_card') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="ORDER TYPE">ORDER TYPE</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="order_type" value="{{ request('order_type') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="AVG. TIME">AVG. TIME</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="avg_time" value="{{ request('avg_time') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="ACT. TIME">ACT. TIME</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="act_time" value="{{ request('act_time') }}" placeholder="">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 min-w-0">
                                <label class="form-label text-truncate d-block mb-1" title="AIRCRAFT LOCATION">AIRCRAFT LOCATION</label>
                                <input type="text" class="form-control form-control-sm master-data-filter-input" name="aircraft_location" value="{{ request('aircraft_location') }}" placeholder="">
                            </div>
                            <div class="col-12 pt-1">
                                <button type="button" style="border:none; box-shadow:none; color:gray;" class="btn btn-outline-primary btn-sm" onclick="resetMasterDataFilters()">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="doc-mode-tabs mb-3">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="{{ route('modules.reliability.settings.master-data.index', ['source' => 'rc'] + request()->query()) }}"
                   class="nav-link {{ $source === 'rc' ? 'active' : '' }}"
                   role="tab">RC</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('modules.reliability.settings.master-data.index', ['source' => 'nrc'] + request()->query()) }}"
                   class="nav-link {{ $source === 'nrc' ? 'active' : '' }}"
                   role="tab">NRC</a>
            </li>
        </ul>
    </div>

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span class="me-2 d-none d-md-inline">Single table; NRC = ADDNRC or NONROUTINE with non-empty SRC. CUST. CARD; RC = all other rows.</span>
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
        <div class="efds-table-header__actions d-flex flex-wrap gap-2">
            <button type="button" class="btn efds-btn efds-btn--outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#masterDataDbSchemaModal" title="How table columns map to database tables">
                <i class="fas fa-project-diagram me-1"></i>Database schema
            </button>
            <a href="{{ route('modules.reliability.settings.master-data.export', request()->query()) }}" class="btn efds-btn efds-btn--outline-primary btn-sm"><i class="fas fa-download me-1"></i>CSV export</a>
            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#masterDataUploadModal"><i class="fas fa-file-excel me-1"></i>Add from Excel / CSV</button>
            <form id="form-delete-master-data" action="{{ route('modules.reliability.settings.master-data.delete') }}" method="post" class="d-none">
                @csrf
                <input type="hidden" name="source" value="{{ $source }}">
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
                                <th class="text-center" style="width: 40px; min-width: 40px; max-width: 40px;"><input type="checkbox" id="master-data-select-all" class="form-check-input" title="Select all on page"></th>
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'id', 'label' => 'ID', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'project', 'label' => 'PROJECT', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'project_type', 'label' => 'PROJECT TYPE', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'aircraft_type', 'label' => 'AIRCRAFT TYPE', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'tail_number', 'label' => 'TAIL NUMBER', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['label' => 'MSN', 'sortable' => false])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['label' => 'AGE', 'sortable' => false])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['label' => 'FC', 'sortable' => false])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['label' => 'FH', 'sortable' => false])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['label' => 'EEF#', 'sortable' => false])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['label' => 'DATA SOURCE', 'sortable' => false])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['label' => 'MATERIAL', 'sortable' => false])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['label' => 'EQUIPMENT', 'sortable' => false])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'wo_station', 'label' => 'WO STATION', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'work_order', 'label' => 'WORK ORDER', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'item', 'label' => 'ITEM', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'src_order', 'label' => 'SRC. ORDER', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'src_item', 'label' => 'SRC. ITEM', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'src_cust_card', 'label' => 'SRC. CUST. CARD', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'description', 'label' => 'DESCRIPTION', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'corrective_action', 'label' => 'CORRECTIVE ACTION', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'ata', 'label' => 'ATA', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'cust_card', 'label' => 'CUST. CARD', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['label' => 'CUST. CARD NORM', 'sortable' => false])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'order_type', 'label' => 'ORDER TYPE', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'avg_time', 'label' => 'AVG. TIME', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'act_time', 'label' => 'ACT. TIME', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                                @include('Modules.Reliability.settings.master_data.partials.sort_th', ['column' => 'aircraft_location', 'label' => 'AIRCRAFT LOCATION', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                            <tr>
                                <td class="text-center" style="width: 40px; min-width: 40px; max-width: 40px;"><input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input master-data-row-cb"></td>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->project }}</td>
                                <td>{{ $row->project_type }}</td>
                                <td>{{ $row->aircraft_type }}</td>
                                <td>{{ $row->tail_number }}</td>
                                <td>{{ $row->master_msn }}</td>
                                <td>{{ $row->master_age }}</td>
                                <td>{{ $row->master_fc }}</td>
                                <td>{{ $row->master_fh }}</td>
                                <td>{{ $row->master_eef }}</td>
                                <td>{{ Str::limit($row->master_data_source, 40) }}</td>
                                <td>{{ Str::limit($row->master_material, 50) }}</td>
                                <td>{{ $row->master_equipment }}</td>
                                <td>{{ $row->wo_station }}</td>
                                <td>{{ $row->work_order }}</td>
                                <td>{{ $row->item }}</td>
                                <td>{{ $row->src_order }}</td>
                                <td>{{ $row->src_item }}</td>
                                <td>{{ $row->src_cust_card }}</td>
                                <td>{{ Str::limit($row->description, 60) }}</td>
                                <td>{{ Str::limit($row->corrective_action, 60) }}</td>
                                <td>{{ $row->ata }}</td>
                                <td>{{ $row->cust_card }}</td>
                                <td>{{ $row->master_cust_card_norm ?: '—' }}</td>
                                <td>{{ $row->order_type }}</td>
                                <td>{{ $row->avg_time }}</td>
                                <td>{{ $row->act_time }}</td>
                                <td>{{ $row->aircraft_location }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="29" class="text-center py-4 text-muted">No records. Upload data from Excel / CSV.</td>
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
    <div class="efds-pagination-wrap mt-3 pt-2">
        {{ $items->onEachSide(1)->links('vendor.pagination.safety-reporting') }}
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
                <input type="hidden" name="source" value="{{ $source }}">
                <div class="modal-body">
                    <p class="small text-muted mb-2">Only these columns are imported: PROJECT, PROJECT TYPE, AIRCRAFT TYPE, TAIL NUMBER, WO STATION, WORK ORDER, ITEM, SRC. ORDER, SRC. ITEM, SRC. CUST. CARD, DESCRIPTION, CORRECTIVE ACTION, ATA, CUST. CARD, ORDER TYPE, AVG. TIME, ACT. TIME, AIRCRAFT LOCATION (all other columns are ignored).</p>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="clear_before" value="1" id="md-clear-before">
                        <label class="form-check-label" for="md-clear-before">Clear existing data in the database before upload</label>
                    </div>
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
                                   placeholder="excel/GAES Data/Work CardOLD.csv"
                                   value="excel/GAES Data/Work CardOLD.csv">
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

<div class="modal fade" id="masterDataDbSchemaModal" tabindex="-1" aria-labelledby="masterDataDbSchemaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered master-db-schema-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="masterDataDbSchemaModalLabel"><i class="fas fa-database me-2 text-primary"></i>Master Data — database schema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body master-db-schema-body">
                <div class="master-db-erd-hint mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Hover a column name for join hints. <span class="text-muted">Key icon = primary or join key used for lookup.</span>
                </div>

                <div class="master-db-erd-canvas">
                    <svg class="master-db-erd-wires" viewBox="0 0 1200 700" preserveAspectRatio="none" aria-hidden="true">
                        <path fill="none" stroke="#1e293b" stroke-width="1.35" d="M 210 195 L 210 248 L 340 248 L 340 278"/>
                        <path fill="none" stroke="#1e293b" stroke-width="1.35" d="M 990 195 L 990 248 L 860 248 L 860 278"/>
                        <path fill="none" stroke="#1e293b" stroke-width="1.35" d="M 260 575 L 260 518 L 400 518 L 400 455"/>
                        <path fill="none" stroke="#1e293b" stroke-width="1.35" d="M 940 575 L 940 518 L 800 518 L 800 455"/>
                    </svg>

                    <div class="master-db-erd-groups">
                        <div class="master-db-erd-group master-db-erd-group--fleet">
                            <span class="master-db-erd-group-label">Fleet &amp; project context</span>
                            <div class="master-db-erd-row master-db-erd-row--split">
                                @include('Modules.Reliability.settings.master_data.partials.erd_table', [
                                    'name' => 'aircrafts',
                                    'fields' => [
                                        ['name' => 'id', 'pk' => true, 'hint' => 'Table primary key'],
                                        ['name' => 'tail_number', 'pk' => true, 'hint' => 'Matched to work_cards_master.tail_number (trim, case)', 'note' => '→ join'],
                                        ['name' => 'serial_number', 'hint' => 'Shown as MSN', 'note' => '→ MSN'],
                                        ['name' => 'manufactured', 'hint' => 'Shown as AGE', 'note' => '→ AGE'],
                                        ['name' => 'engine_type', 'hint' => 'EQUIPMENT if project has no engine', 'note' => '→ EQUIPMENT'],
                                    ],
                                ])
                                @include('Modules.Reliability.settings.master_data.partials.erd_table', [
                                    'name' => 'projects',
                                    'fields' => [
                                        ['name' => 'id', 'pk' => true],
                                        ['name' => 'project_number + tail_number', 'pk' => true, 'hint' => 'TRIM(project_number) and tail match master.project + master.tail_number', 'note' => '→ composite join'],
                                        ['name' => 'aircraft_csn', 'note' => '→ FC'],
                                        ['name' => 'aircraft_tsn', 'note' => '→ FH'],
                                        ['name' => 'engine_type', 'note' => '→ EQUIPMENT (preferred)'],
                                    ],
                                ])
                            </div>
                        </div>

                        <div class="master-db-erd-group master-db-erd-group--master">
                            <span class="master-db-erd-group-label">Work card master (GAES import)</span>
                            @include('Modules.Reliability.settings.master_data.partials.erd_table', [
                                'name' => 'work_cards_master',
                                'wide' => true,
                                'fields' => [
                                    ['name' => 'id', 'pk' => true],
                                    ['name' => 'project', 'hint' => 'Filters EEF pool; join to projects', 'note' => '→ PROJECT'],
                                    ['name' => 'project_type', 'note' => '→ PROJECT TYPE'],
                                    ['name' => 'aircraft_type', 'note' => '→ AIRCRAFT TYPE'],
                                    ['name' => 'tail_number', 'hint' => 'Join to aircrafts & projects', 'note' => '→ TAIL #'],
                                    ['name' => 'wo_station', 'note' => '→ WO STATION'],
                                    ['name' => 'work_order', 'hint' => 'With item builds NRC for EEF', 'note' => '→ WORK ORDER'],
                                    ['name' => 'item', 'hint' => 'With work_order → NRC; join materials', 'note' => '→ ITEM'],
                                    ['name' => 'src_order', 'note' => '→ SRC. ORDER'],
                                    ['name' => 'src_item', 'note' => '→ SRC. ITEM'],
                                    ['name' => 'src_cust_card', 'note' => '→ SRC. CUST. CARD'],
                                    ['name' => 'description', 'note' => '→ DESCRIPTION'],
                                    ['name' => 'corrective_action', 'note' => '→ CORRECTIVE ACTION'],
                                    ['name' => 'ata', 'note' => '→ ATA'],
                                    ['name' => 'cust_card', 'note' => '→ CUST. CARD'],
                                    ['name' => 'order_type', 'hint' => 'RC/NRC tab filter with src_cust_card', 'note' => '→ ORDER TYPE'],
                                    ['name' => 'avg_time', 'note' => '→ AVG. TIME'],
                                    ['name' => 'act_time', 'note' => '→ ACT. TIME'],
                                    ['name' => 'aircraft_location', 'note' => '→ AIRCRAFT LOCATION'],
                                ],
                            ])
                        </div>

                        <div class="master-db-erd-group master-db-erd-group--refs">
                            <span class="master-db-erd-group-label">EEF registry &amp; materials</span>
                            <div class="master-db-erd-row master-db-erd-row--split">
                                @include('Modules.Reliability.settings.master_data.partials.erd_table', [
                                    'name' => 'eef_registry',
                                    'fields' => [
                                        ['name' => 'id', 'pk' => true],
                                        ['name' => 'project_no', 'hint' => 'Same as master.project (trim)', 'note' => '→ join'],
                                        ['name' => 'nrc_number', 'hint' => 'Must match WO + "-" + item (4-digit pad), e.g. 17766-0066', 'note' => '→ match'],
                                        ['name' => 'eef_number', 'note' => '→ EEF#'],
                                        ['name' => 'inspection_source_task', 'note' => '→ DATA SOURCE'],
                                    ],
                                ])
                                @include('Modules.Reliability.settings.master_data.partials.erd_table', [
                                    'name' => 'work_card_materials',
                                    'fields' => [
                                        ['name' => 'id', 'pk' => true],
                                        ['name' => 'project_number + work_order_number + item_number', 'pk' => true, 'hint' => 'Equals master.project, work_order, item', 'note' => '→ composite join'],
                                        ['name' => 'description', 'note' => '→ MATERIAL'],
                                        ['name' => 'part_number', 'note' => '→ MATERIAL'],
                                    ],
                                ])
                            </div>
                        </div>

                        <div class="master-db-erd-pipeline text-center small text-muted font-monospace py-2">
                            GAES import → <strong>work_cards_master</strong> → query + PHP joins → on-screen grid
                        </div>
                    </div>
                </div>

                <div class="master-db-schema-legend small text-muted mt-3 d-flex flex-wrap gap-3 justify-content-center">
                    <span><i class="fas fa-key text-warning me-1" style="font-size:0.65rem"></i> Primary / join key</span>
                    <span><span class="master-db-erd-legend-swatch master-db-erd-legend-swatch--fleet"></span> Fleet &amp; project</span>
                    <span><span class="master-db-erd-legend-swatch master-db-erd-legend-swatch--master"></span> Master row</span>
                    <span><span class="master-db-erd-legend-swatch master-db-erd-legend-swatch--refs"></span> EEF &amp; materials</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('Modules.Reliability.inspection_settings.partials.table_styles')
<style>
.doc-mode-tabs { margin-bottom: 0; }
.doc-mode-tabs .nav-tabs { border-bottom: 2px solid #dee2e6; }
.doc-mode-tabs .nav-link {
    color: #495057;
    font-weight: 500;
    font-size: 14px;
    padding: 12px 24px;
    border: none;
    border-bottom: 3px solid transparent;
    background: transparent;
}
.doc-mode-tabs .nav-link:hover {
    border-color: transparent;
    color: #1E64D4;
}
.doc-mode-tabs .nav-link.active {
    color: #1E64D4;
    border-bottom-color: #1E64D4;
    background: transparent;
    font-weight: 600;
}

.form-control{background-color:white;}
/* Filters: avoid cramming fields into one row */
.master-data-filters > [class*="col-"] {
    min-width: 0;
}
.master-data-filters .form-label {
    font-size: 0.8rem;
    font-weight: 500;
}
/* Same spacing as projects: below page numbers */
.efds-pagination-wrap {
    margin-top: 1rem !important;
    padding-top: 0.75rem;
}
.efds-table-header__stats {
    white-space: nowrap;
    flex-shrink: 0;
    min-width: 280px;
}
/* Database schema modal: 90% viewport width & height */
#masterDataDbSchemaModal .master-db-schema-modal-dialog {
    width: 90vw;
    max-width: 90vw;
    height: 90vh;
    max-height: 90vh;
    margin: 5vh auto;
}
#masterDataDbSchemaModal .modal-content {
    height: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}
#masterDataDbSchemaModal .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
}
#masterDataDbSchemaModal .modal-header,
#masterDataDbSchemaModal .modal-footer {
    flex-shrink: 0;
}
.master-db-schema-body { font-size: 0.9rem; }
.master-db-erd-hint {
    background: linear-gradient(180deg, #dbeafe 0%, #eff6ff 100%);
    border: 1px solid #93c5fd;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    color: #1e40af;
}
.master-db-erd-canvas {
    position: relative;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    min-height: 560px;
    overflow: hidden;
}
.master-db-erd-wires {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
    opacity: 0.85;
}
.master-db-erd-groups {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding: 1.25rem 1.5rem 1rem;
}
.master-db-erd-group {
    border-radius: 14px;
    padding: 1.85rem 1.25rem 1.25rem;
    position: relative;
}
.master-db-erd-group--fleet {
    background: rgba(45, 212, 191, 0.09);
    border: 1px dashed rgba(13, 148, 136, 0.38);
}
.master-db-erd-group--master {
    background: rgba(180, 140, 80, 0.11);
    border: 1px dashed rgba(120, 90, 55, 0.32);
}
.master-db-erd-group--refs {
    background: rgba(139, 92, 246, 0.07);
    border: 1px dashed rgba(124, 58, 237, 0.3);
}
.master-db-erd-group-label {
    position: absolute;
    top: 8px;
    left: 14px;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #57534e;
}
.master-db-erd-row--split {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem 2rem;
}
.master-db-erd-row--split > .master-db-erd-table {
    flex: 1 1 240px;
    max-width: 340px;
}
.master-db-erd-table {
    background: #fff;
    border-radius: 3px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.1), 0 0 0 1px rgba(15, 23, 42, 0.06);
    min-width: 200px;
}
.master-db-erd-table--wide {
    max-width: none;
    width: 100%;
}
.master-db-erd-table__head {
    background: linear-gradient(180deg, #4a8fd4 0%, #1e4d8f 100%);
    color: #fff;
    font-weight: 700;
    font-size: 0.68rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 0.5rem 0.65rem;
    text-shadow: 0 1px 0 rgba(0, 0, 0, 0.2);
    border-radius: 3px 3px 0 0;
}
.master-db-erd-table__body {
    font-family: ui-monospace, Consolas, "Courier New", monospace;
    font-size: 0.66rem;
    line-height: 1.4;
}
.master-db-erd-field {
    padding: 0.32rem 0.55rem 0.32rem 1.55rem;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
    color: #0f172a;
}
.master-db-erd-field:last-child {
    border-bottom: none;
}
.master-db-erd-field[title] {
    cursor: help;
}
.master-db-erd-field-name {
    word-break: break-word;
}
.master-db-erd-field--pk .master-db-erd-pk-icon {
    position: absolute;
    left: 7px;
    top: 50%;
    transform: translateY(-50%);
    color: #ca8a04;
    font-size: 0.55rem;
    filter: drop-shadow(0 0 1px rgba(202, 138, 4, 0.5));
}
.master-db-erd-note {
    display: block;
    font-size: 0.58rem;
    color: #64748b;
    font-family: system-ui, -apple-system, sans-serif;
    margin-top: 0.08rem;
}
.master-db-erd-pipeline {
    border-top: 1px dashed #e7e5e4;
    margin-top: 0.25rem;
}
.master-db-schema-legend .master-db-erd-legend-swatch {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 2px;
    margin-right: 0.3rem;
    vertical-align: middle;
}
.master-db-erd-legend-swatch--fleet {
    background: rgba(45, 212, 191, 0.2);
    border: 1px solid rgba(13, 148, 136, 0.45);
}
.master-db-erd-legend-swatch--master {
    background: rgba(180, 140, 80, 0.18);
    border: 1px solid rgba(120, 90, 55, 0.4);
}
.master-db-erd-legend-swatch--refs {
    background: rgba(139, 92, 246, 0.12);
    border: 1px solid rgba(124, 58, 237, 0.35);
}
@media (max-width: 767.98px) {
    .master-db-erd-wires { display: none; }
    .master-db-erd-row--split > .master-db-erd-table { max-width: none; }
}
</style>
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
            var perPageHidden = document.getElementById('masterDataFilterPerPage');
            if (perPageHidden) perPageHidden.value = val;
            var url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    var filtersForm = document.getElementById('masterDataFiltersForm');
    if (filtersForm) {
        function submitFiltersForm() {
            var perPageEl = document.getElementById('master-data-per-page');
            var hiddenPerPage = document.getElementById('masterDataFilterPerPage');
            if (hiddenPerPage && perPageEl) hiddenPerPage.value = perPageEl.value;
            filtersForm.submit();
        }
        filtersForm.querySelectorAll('.master-data-filter-input').forEach(function(input) {
            input.addEventListener('blur', submitFiltersForm);
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
            });
        });
    }

    window.resetMasterDataFilters = function() {
        var url = new URL('{{ route("modules.reliability.settings.master-data.index") }}', window.location.origin);
        url.searchParams.set('source', '{{ $source }}');
        var perPage = document.getElementById('master-data-per-page');
        if (perPage && perPage.value) url.searchParams.set('per_page', perPage.value);
        window.location.href = url.toString();
    };

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
                                window.location.href = '{{ route("modules.reliability.settings.master-data.index") }}?success=' + encodeURIComponent('Imported records: ' + (d.count || 0)) + '&source=' + encodeURIComponent('{{ $source }}');
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
                fd3.append('source', document.querySelector('#form-upload-master-data-modal input[name=source]').value || 'rc');
                var clearBeforeCb = document.getElementById('md-clear-before');
                if (clearBeforeCb && clearBeforeCb.checked) fd3.append('clear_before', '1');
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
