@extends('layout.main')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="{{ route('modules.reliability.settings.index') }}" class="back-button"><i class="fas fa-arrow-left me-2"></i>Настройки</a>
    </div>
    @if(session('success') || request('success'))
        <div class="alert alert-success">{{ session('success') ?? request('success') }}</div>
    @endif

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span class="me-2">На странице:</span>
            <select class="form-select form-select-sm" id="eef-registry-per-page" aria-label="Записей на странице">
                @php $currentPerPage = (int) request('per_page', $perPage ?? 50); @endphp
                <option value="10" {{ $currentPerPage === 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $currentPerPage === 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $currentPerPage === 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $currentPerPage === 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="ms-2">Всего записей: {{ $items->total() }}</span>
        </div>
        <div class="efds-table-header__actions">
            <button type="button" class="btn efds-btn efds-btn--outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#eefRegistryUploadModal"><i class="fas fa-file-excel me-1"></i>Добавить из Excel / CSV</button>
            <a href="#" class="btn efds-btn efds-btn--primary btn-sm"><i class="fas fa-plus me-1"></i>Добавить</a>
            <form id="form-delete-eef-registry" action="{{ route('modules.reliability.settings.inspection.eef-registry.delete') }}" method="post" class="d-none">
                @csrf
                <button type="submit" class="btn efds-btn efds-btn--danger btn-sm">Удалить выбранные</button>
            </form>
        </div>
    </div>
    <div class="inspection-settings-table-card">
    <div class="card">
        <div class="card-body p-0">
            <form id="form-eef-registry-table">
                @csrf
            <div class="reliability-table-scroll-wrap">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 2.5rem;"><input type="checkbox" id="eef-registry-select-all" class="form-check-input" title="Выбрать все на странице"></th>
                                <th>id</th>
                                <th>EEF Number</th>
                                <th>NRC Number</th>
                                <th>AC Type</th>
                                <th>ATA</th>
                                <th>Project No.</th>
                                <th>Subject</th>
                                <th>Remarks</th>
                                <th>Location</th>
                                <th>EEF Status</th>
                                <th>Link</th>
                                <th>Link Path</th>
                                <th>Man Hours</th>
                                <th>Chargeable to Customer?</th>
                                <th>Customer Name</th>
                                <th>Inspection Source Task</th>
                                <th>RC#</th>
                                <th>Open Date</th>
                                <th>Assigned Engineering Engineer</th>
                                <th>OPEN/Continuation Raised by Production Dates</th>
                                <th>Answer provided by Engineering Dates</th>
                                <th>OEM Communication Reference</th>
                                <th>GAES EO</th>
                                <th>Manual limits (OUT / WITHIN)</th>
                                <th>Back-up Engineer</th>
                                <th>Project Status</th>
                                <th>EEF Priority</th>
                                <th>Latest Processing</th>
                                <th>Project Status2</th>
                                <th>created_at</th>
                                <th>updated_at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $row)
                            <tr>
                                <td class="text-center"><input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input eef-registry-row-cb"></td>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->eef_number }}</td>
                                <td>{{ $row->nrc_number }}</td>
                                <td>{{ $row->ac_type }}</td>
                                <td>{{ $row->ata }}</td>
                                <td>{{ $row->project_no }}</td>
                                <td>{{ $row->subject }}</td>
                                <td>{{ $row->remarks }}</td>
                                <td>{{ $row->location }}</td>
                                <td>{{ $row->eef_status }}</td>
                                <td>{{ $row->link }}</td>
                                <td>{{ $row->link_path }}</td>
                                <td>{{ $row->man_hours }}</td>
                                <td>{{ $row->chargeable_to_customer }}</td>
                                <td>{{ $row->customer_name }}</td>
                                <td>{{ $row->inspection_source_task }}</td>
                                <td>{{ $row->rc_number }}</td>
                                <td>{{ $row->open_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->assigned_engineering_engineer }}</td>
                                <td>{{ $row->open_continuation_raised_by_production_dates }}</td>
                                <td>{{ $row->answer_provided_by_engineering_dates }}</td>
                                <td>{{ $row->oem_communication_reference }}</td>
                                <td>{{ $row->gaes_eo }}</td>
                                <td>{{ $row->manual_limits_out_within }}</td>
                                <td>{{ $row->backup_engineer }}</td>
                                <td>{{ $row->project_status }}</td>
                                <td>{{ $row->eef_priority }}</td>
                                <td>{{ $row->latest_processing }}</td>
                                <td>{{ $row->project_status2 }}</td>
                                <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->updated_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="33" class="text-muted">Нет данных. Загрузите CSV или XLSX.</td></tr>
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

<div class="modal fade" id="eefRegistryUploadModal" tabindex="-1" aria-labelledby="eefRegistryUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eefRegistryUploadModalLabel">Добавить из Excel / CSV</h5>
                <button type="button" class="btn-close" id="eef-modal-close-btn" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <form id="form-upload-eef-registry-modal" action="{{ route('modules.reliability.settings.inspection.eef-registry.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="eef-step-select">
                        <ul class="nav nav-tabs mb-3" id="eef-upload-tabs">
                            <li class="nav-item"><button type="button" class="nav-link active" id="eef-tab-upload" onclick="eefSwitchTab('upload')"><i class="fas fa-upload me-1"></i>Загрузить файл</button></li>
                            <li class="nav-item"><button type="button" class="nav-link" id="eef-tab-local" onclick="eefSwitchTab('local')"><i class="fas fa-server me-1"></i>С диска сервера</button></li>
                        </ul>
                        <div id="eef-panel-upload">
                            <input type="file" name="file" id="eef-registry-upload-file" class="d-none" accept=".csv,.xlsx,.xls">
                            <div id="eef-registry-upload-dropzone" class="inspection-upload-dropzone">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="mb-1">Перетащите файл сюда</p>
                                <p class="small text-muted mb-0">или нажмите, чтобы выбрать файл (CSV, XLSX, XLS)</p>
                            </div>
                        </div>
                        <div id="eef-panel-local" class="d-none">
                            <p class="small text-muted mb-2">Путь относительно корня проекта:</p>
                            <input type="text" id="eef-local-path" class="form-control form-control-sm font-monospace" placeholder="excel/GAES Data/EEF — копия.xlsx" value="excel/GAES Data/EEF — копия.xlsx">
                        </div>
                        <div id="eef-file-info" class="mt-3 d-none">
                            <div class="d-flex align-items-center gap-2 mb-1"><i class="fas fa-file-excel text-success"></i><span id="eef-registry-upload-filename" class="small text-success fw-bold"></span></div>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="eef-clear-before-upload" checked>
                            <label class="form-check-label small" for="eef-clear-before-upload">Очистить таблицу перед загрузкой</label>
                        </div>
                    </div>
                    <div id="eef-step-progress" class="d-none">
                        <p class="small text-muted mb-1"><span id="eef-prog-filename"></span></p>
                        <p class="small mb-1">Обработано: <span id="eef-prog-processed">0</span><span id="eef-prog-total-wrap"> из <span id="eef-prog-total">—</span></span></p>
                        <div class="progress mb-2"><div class="progress-bar progress-bar-striped progress-bar-animated" id="eef-progress-bar" role="progressbar" style="width: 100%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>
                        <div id="eef-prog-error" class="small text-danger d-none"></div>
                        <div id="eef-prog-done" class="small text-success d-none">Готово.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="efds-actions mb-0">
                        <button type="button" class="btn efds-btn efds-btn--outline-primary" id="eef-upload-cancel-btn" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" id="eef-registry-upload-submit" class="btn efds-btn efds-btn--primary" disabled>Загрузить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('Modules.Reliability.inspection_settings.partials.table_styles')
<script>
(function() {
    var formDelete = document.getElementById('form-delete-eef-registry');
    var checkboxes = document.querySelectorAll('.eef-registry-row-cb');
    var selectAll = document.getElementById('eef-registry-select-all');
    function updateDeleteVisibility() {
        var any = Array.prototype.some.call(checkboxes, function(cb) { return cb.checked; });
        formDelete.classList.toggle('d-none', !any);
    }
    function updateSelectAll() {
        if (!selectAll) return;
        var all = document.querySelectorAll('.eef-registry-row-cb');
        var checked = document.querySelectorAll('.eef-registry-row-cb:checked');
        selectAll.checked = all.length > 0 && checked.length === all.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
    }
    Array.prototype.forEach.call(checkboxes, function(cb) {
        cb.addEventListener('change', function() { updateDeleteVisibility(); updateSelectAll(); });
    });
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            Array.prototype.forEach.call(document.querySelectorAll('.eef-registry-row-cb'), function(cb) { cb.checked = selectAll.checked; });
            updateDeleteVisibility();
        });
    }
    formDelete.addEventListener('submit', function(e) {
        e.preventDefault();
        var ids = [];
        document.querySelectorAll('.eef-registry-row-cb:checked').forEach(function(cb) { ids.push(cb.value); });
        if (ids.length === 0) return;
        ids.forEach(function(id) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
            formDelete.appendChild(inp);
        });
        formDelete.submit();
    });

    var perPageSelect = document.getElementById('eef-registry-per-page');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('per_page', this.value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    var uploadModal = document.getElementById('eefRegistryUploadModal');
    var dropzone = document.getElementById('eef-registry-upload-dropzone');
    var fileInput = document.getElementById('eef-registry-upload-file');
    var filenameEl = document.getElementById('eef-registry-upload-filename');
    var submitBtn = document.getElementById('eef-registry-upload-submit');
    var stepSelect = document.getElementById('eef-step-select');
    var stepProgress = document.getElementById('eef-step-progress');
    var fileInfoEl = document.getElementById('eef-file-info');
    var progFilename = document.getElementById('eef-prog-filename');
    var progTotalWrap = document.getElementById('eef-prog-total-wrap');
    var progProcessed = document.getElementById('eef-prog-processed');
    var progTotal = document.getElementById('eef-prog-total');
    var progressBar = document.getElementById('eef-progress-bar');
    var progError = document.getElementById('eef-prog-error');
    var progDone = document.getElementById('eef-prog-done');
    var cancelBtn = document.getElementById('eef-upload-cancel-btn');
    var formUpload = document.getElementById('form-upload-eef-registry-modal');

    var knownTotal = 0;
    var importing = false;
    var activeTab = 'upload';
    var localPathOk = false;

    window.eefSwitchTab = function(tab) {
        activeTab = tab;
        document.getElementById('eef-tab-upload').classList.toggle('active', tab === 'upload');
        document.getElementById('eef-tab-local').classList.toggle('active', tab === 'local');
        document.getElementById('eef-panel-upload').classList.toggle('d-none', tab !== 'upload');
        document.getElementById('eef-panel-local').classList.toggle('d-none', tab !== 'local');
        fileInfoEl.classList.add('d-none');
        knownTotal = 0;
        if (tab === 'upload') {
            submitBtn.disabled = !(fileInput.files && fileInput.files[0]);
        } else {
            var pathInput = document.getElementById('eef-local-path');
            localPathOk = pathInput && pathInput.value.trim().length > 0;
            submitBtn.disabled = !localPathOk;
            if (localPathOk && pathInput) { filenameEl.textContent = pathInput.value.trim().split('/').pop(); fileInfoEl.classList.remove('d-none'); }
        }
    };

    function resetUploadModal() {
        importing = false;
        localPathOk = false;
        fileInput.value = '';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Загрузить';
        cancelBtn.setAttribute('data-bs-dismiss', 'modal');
        stepSelect.classList.remove('d-none');
        stepProgress.classList.add('d-none');
        fileInfoEl.classList.add('d-none');
        progError.classList.add('d-none');
        progDone.classList.add('d-none');
        knownTotal = 0;
        eefSwitchTab('upload');
    }

    function setProgressBar(pct, noTotal) {
        if (noTotal) {
            progressBar.style.width = '100%';
            progressBar.textContent = '';
        } else {
            progressBar.style.width = pct + '%';
            progressBar.textContent = pct + '%';
        }
        progressBar.setAttribute('aria-valuenow', noTotal ? 0 : pct);
    }

    function readNdjsonStream(response, displayName) {
        stepSelect.classList.add('d-none');
        stepProgress.classList.remove('d-none');
        progFilename.textContent = displayName;
        progProcessed.textContent = '0';
        var noTotal = true;
        if (progTotalWrap) progTotalWrap.style.display = 'none';
        setProgressBar(0, true);
        progError.classList.add('d-none');
        progDone.classList.add('d-none');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Загружается…';
        cancelBtn.removeAttribute('data-bs-dismiss');
        if (!response.ok) throw new Error('HTTP ' + response.status);
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
                        if (d.total > 0 && noTotal) { noTotal = false; if (progTotalWrap) { progTotalWrap.style.display = ''; } document.getElementById('eef-prog-total').textContent = d.total.toLocaleString(); }
                        if (d.error) { progError.textContent = d.error; progError.classList.remove('d-none'); importing = false; cancelBtn.setAttribute('data-bs-dismiss', 'modal'); return; }
                        if (typeof d.processed !== 'undefined') {
                            progProcessed.textContent = d.processed.toLocaleString();
                            if (!noTotal && d.total > 0) { document.getElementById('eef-prog-total').textContent = d.total.toLocaleString(); setProgressBar(Math.min(100, Math.round(100 * d.processed / d.total)), false); }
                            else { setProgressBar(0, true); }
                        }
                        if (d.done) {
                            setProgressBar(100, false);
                            progProcessed.textContent = (d.count || 0).toLocaleString();
                            progressBar.classList.remove('progress-bar-animated');
                            progDone.classList.remove('d-none');
                            importing = false;
                            cancelBtn.setAttribute('data-bs-dismiss', 'modal');
                            submitBtn.textContent = 'Готово';
                            setTimeout(function() { window.location.href = '{{ route("modules.reliability.settings.inspection.eef-registry") }}?success=' + encodeURIComponent('Импортировано записей: ' + (d.count || 0)); }, 1200);
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
            if (this.files && this.files[0]) {
                filenameEl.textContent = this.files[0].name;
                fileInfoEl.classList.remove('d-none');
                submitBtn.disabled = false;
            }
        });
        dropzone.addEventListener('dragover', function(e) { e.preventDefault(); e.stopPropagation(); dropzone.classList.add('drag-over'); });
        dropzone.addEventListener('dragleave', function(e) { e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('drag-over'); });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault(); e.stopPropagation();
            dropzone.classList.remove('drag-over');
            var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) { var dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files; filenameEl.textContent = file.name; fileInfoEl.classList.remove('d-none'); submitBtn.disabled = false; }
        });
    }

    var pathInput = document.getElementById('eef-local-path');
    if (pathInput) {
        pathInput.addEventListener('input', function() {
            if (activeTab !== 'local') return;
            var p = this.value.trim();
            localPathOk = p.length > 0;
            submitBtn.disabled = !localPathOk;
            if (localPathOk) { filenameEl.textContent = p.split('/').pop(); fileInfoEl.classList.remove('d-none'); }
        });
    }

    if (formUpload) {
        formUpload.addEventListener('submit', function(e) {
            e.preventDefault();
            importing = true;
            var clearCheck = document.getElementById('eef-clear-before-upload');
            function doUpload() {
                if (activeTab === 'local') {
                    var path = document.getElementById('eef-local-path').value.trim();
                    if (!path) return;
                    var fd3 = new FormData();
                    fd3.append('_token', formUpload.querySelector('[name=_token]').value);
                    fd3.append('path', path);
                    fetch('{{ route("modules.reliability.settings.inspection.eef-registry.import-local") }}', { method: 'POST', body: fd3, headers: { 'Accept': 'application/x-ndjson' } })
                    .then(function(r) { return readNdjsonStream(r, path.split('/').pop()); })
                    .catch(function(err) { progError.textContent = err.message || 'Ошибка'; progError.classList.remove('d-none'); importing = false; cancelBtn.setAttribute('data-bs-dismiss', 'modal'); });
                } else {
                    if (!fileInput.files || !fileInput.files[0]) return;
                    var fd = new FormData(formUpload);
                    fetch(formUpload.action, { method: 'POST', body: fd, headers: { 'X-EEF-Stream': '1', 'Accept': 'application/x-ndjson' } })
                    .then(function(r) { return readNdjsonStream(r, fileInput.files[0].name); })
                    .catch(function(err) { progError.textContent = err.message || 'Ошибка'; progError.classList.remove('d-none'); importing = false; cancelBtn.setAttribute('data-bs-dismiss', 'modal'); submitBtn.disabled = false; submitBtn.textContent = 'Загрузить'; });
                }
            }
            if (clearCheck && clearCheck.checked) {
                var fdClear = new FormData();
                fdClear.append('_token', formUpload.querySelector('[name=_token]').value);
                fetch('{{ route("modules.reliability.settings.inspection.eef-registry.clear") }}', { method: 'POST', body: fdClear })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.error) throw new Error(data.error);
                    doUpload();
                })
                .catch(function(err) { progError.textContent = err.message || 'Ошибка очистки'; progError.classList.remove('d-none'); importing = false; cancelBtn.setAttribute('data-bs-dismiss', 'modal'); });
            } else {
                doUpload();
            }
        });
    }
})();
</script>
@endsection
