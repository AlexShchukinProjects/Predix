@extends('layout.main')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="{{ route('modules.reliability.settings.index') }}" class="back-button"><i class="fas fa-arrow-left me-2"></i>Настройки</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span class="me-2">На странице:</span>
            <select class="form-select form-select-sm" id="work-card-materials-per-page" aria-label="Записей на странице">
                @php $currentPerPage = (int) request('per_page', $perPage ?? 50); @endphp
                <option value="10" {{ $currentPerPage === 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $currentPerPage === 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $currentPerPage === 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $currentPerPage === 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="ms-2">Всего записей: {{ $items->total() }}</span>
        </div>
        <div class="efds-table-header__actions">
            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#workCardMaterialsUploadModal"><i class="fas fa-file-excel me-1"></i>Добавить из Excel / CSV</button>
            <a href="#" class="btn efds-btn efds-btn--primary btn-sm"><i class="fas fa-plus me-1"></i>Добавить</a>
            <form id="form-delete-work-card-materials" action="{{ route('modules.reliability.settings.inspection.work-card-materials.delete') }}" method="post" class="d-none">
                @csrf
                <button type="submit" class="btn efds-btn efds-btn--danger btn-sm">Удалить выбранные</button>
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
                                <th class="text-center" style="width: 2.5rem;"><input type="checkbox" id="work-card-materials-select-all" class="form-check-input" title="Выбрать все на странице"></th>
                                <th>id</th>
                                <th>PROJECT #</th>
                                <th>WORK ORDER #</th>
                                <th>ZONE #</th>
                                <th>ITEM #</th>
                                <th>WIP STATUS</th>
                                <th>CARD DESCRIPTION</th>
                                <th>CUSTOMER WORK CARD</th>
                                <th>SOURCE CARD #</th>
                                <th>SOURCE CUSTOMER CARD</th>
                                <th>TAIL #</th>
                                <th>EST. TIME</th>
                                <th>TAG #</th>
                                <th>PART #</th>
                                <th>DESCRIPTION</th>
                                <th>OEM SPEC. #</th>
                                <th>GROUP CODE</th>
                                <th>EXPIRE DT.</th>
                                <th>CSP</th>
                                <th>ORDER #</th>
                                <th>REQ. DT.</th>
                                <th>REQ. DUE DT.</th>
                                <th>REQ. QTY.</th>
                                <th>REQ. LINE INTERNAL COMMENT</th>
                                <th>LOCATION</th>
                                <th>ORDER #</th>
                                <th>ORDER DT.</th>
                                <th>ORDER DUE DT.</th>
                                <th>ORDER QTY.</th>
                                <th>RECEIPT DT.</th>
                                <th>WAYBILL</th>
                                <th>ETA DT.</th>
                                <th>STATUS</th>
                                <th>REASON</th>
                                <th>ALLOC. QTY.</th>
                                <th>UNIT COST</th>
                                <th>ITEM LIST PRICE</th>
                                <th>ORDER UNIT COST</th>
                                <th>CURRENCY</th>
                                <th>created_at</th>
                                <th>updated_at</th>
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
                            <tr><td colspan="42" class="text-muted">Нет данных. Загрузите CSV или XLSX (формат IC_0097).</td></tr>
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
                <h5 class="modal-title" id="workCardMaterialsUploadModalLabel">Добавить из Excel / CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <form id="form-upload-work-card-materials-modal" action="{{ route('modules.reliability.settings.inspection.work-card-materials.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="file" name="file" id="work-card-materials-upload-file" class="d-none" accept=".csv,.xlsx,.xls" required>
                    <div id="work-card-materials-upload-dropzone" class="inspection-upload-dropzone">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-1">Перетащите файл сюда</p>
                        <p class="small text-muted mb-0">или нажмите, чтобы выбрать файл (CSV, XLSX, XLS)</p>
                        <p id="work-card-materials-upload-filename" class="mt-2 mb-0 small text-success fw-bold d-none"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="efds-actions mb-0">
                        <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" id="work-card-materials-upload-submit" class="btn efds-btn efds-btn--primary" disabled>Загрузить</button>
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
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('per_page', this.value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }
    var uploadModal = document.getElementById('workCardMaterialsUploadModal');
    var dropzone = document.getElementById('work-card-materials-upload-dropzone');
    var fileInput = document.getElementById('work-card-materials-upload-file');
    var filenameEl = document.getElementById('work-card-materials-upload-filename');
    var submitBtn = document.getElementById('work-card-materials-upload-submit');
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
