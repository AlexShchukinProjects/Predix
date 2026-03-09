@extends('layout.main')

@section('content')
@php
    $f = $failure ?? null;
    $isEdit = $f !== null;
@endphp
@if($isEdit)
<style>
.modern-file-upload { border: 2px dashed #d1d5db; border-radius: 8px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); overflow: hidden; transition: all 0.3s ease; margin-bottom: 16px; }
.modern-file-upload:hover { border-color: #3b82f6; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); }
.file-upload-header { display: flex; align-items: center; justify-content: center; padding: 1rem 0.75rem; cursor: pointer; user-select: none; }
.file-upload-text h4 { margin: 0 0 0.125rem 0; font-size: 0.875rem; font-weight: 600; color: #1f2937; }
.file-upload-text p { margin: 0; font-size: 0.75rem; color: #6b7280; }
.file-upload-content { padding: 0.75rem; background: #fff; min-height: 60px; display: flex; flex-direction: column; align-items: stretch; }
.file-upload-placeholder { text-align: center; color: #9ca3af; display: flex; flex-direction: column; align-items: center; gap: 0.25rem; }
.file-upload-placeholder svg { width: 1.5rem; height: 1.5rem; }
.file-preview-item { display: flex; align-items: center; padding: 0.5rem; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 0.375rem; width: 100%; cursor: pointer; }
.file-preview-item:hover { background: #f1f5f9; border-color: #3b82f6; }
.file-preview-icon { width: 1.75rem; height: 1.75rem; margin-right: 0.5rem; display: flex; align-items: center; justify-content: center; background: #fff; border-radius: 4px; border: 1px solid #e5e7eb; flex: 0 0 auto; overflow: hidden; }
.file-preview-icon svg { width: 1.25rem; height: 1.25rem; color: #4b5563; }
.file-preview-icon img { width: 100%; height: 100%; object-fit: contain; }
.file-preview-icon img.file-preview-thumb { object-fit: cover; }
.file-preview-info { flex: 1; min-width: 0; }
.file-preview-name { font-size: 0.75rem; font-weight: 500; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-preview-size { font-size: 0.625rem; color: #6b7280; }
.file-preview-remove { margin-left: 0.5rem; background: transparent; color: #dc2626; border: none; padding: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
.file-preview-remove:hover { color: #b91c1c; }
.multi-file-upload .file-upload-content { min-height: 80px; }
.modern-file-upload.drag-over { border-color: #1E64D4; background: #dbeafe; }


</style>
@endif
<div class="container-fluid">
    <div class="d-flex justify-content-start align-items-center mb-2">
        <a href="{{ route('modules.reliability.index', ['tab' => 'failures']) }}" class="back-button">
            <i class="fas fa-arrow-left me-2"></i>К списку отказов
        </a>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            {{ $isEdit ? 'Редактирование отказа' : 'Добавить отказ' }}
        </h1>
   {{--
   @if($isEdit)
         <a href="{{ route('modules.reliability.failures.export-card', $f?->id) }}" class="btn efds-btn efds-btn--primary" target="_blank">
                <i class="fas fa-download me-1"></i>Выгрузить карту
            </a>
        @endif
   --}}
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <form id="failureForm" method="POST" action="{{ $isEdit ? route('modules.reliability.failures.update', $f) : route('modules.reliability.failures.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="card-body">
                @if(!$isEdit)
                <input type="hidden" name="failure_date" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="aircraft_number" value="">
                @endif
                <div class="row mb-3">
                    <div class="col-4">
                        <label class="form-label mb-0">TASK CARD</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="work_order_number" value="{{ old('work_order_number', $f?->work_order_number ?? '') }}" placeholder="">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">
                        <label class="form-label mb-0">TASK CARD DESCRIPTION</label>
                    </div>
                    <div class="col-8">
                        <textarea class="form-control" name="aircraft_malfunction" rows="3" placeholder="">{{ old('aircraft_malfunction', $f?->aircraft_malfunction ?? '') }}</textarea>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">
                        <label class="form-label mb-0">MPD</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="mpd" value="{{ old('mpd', $f?->mpd ?? '') }}" placeholder="">
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="efds-actions mb-0">
                    <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                    <a href="{{ route('modules.reliability.index', ['tab' => 'failures']) }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var hiddenFormFields = @json($hiddenFormFields ?? []);
    hiddenFormFields.forEach(function(key) {
        document.querySelectorAll('[data-form-field="' + key + '"]').forEach(function(el) { el.classList.add('d-none'); });
    });

    var acSelect = document.getElementById('failure_aircraft_number');
    var typeInput = document.getElementById('failure_aircraft_type');
    var typeCodeInput = document.getElementById('failure_type_code');
    var modificationCodeInput = document.getElementById('failure_modification_code');
    var serialInput = document.getElementById('failure_aircraft_serial');
    var manufactureInput = document.getElementById('failure_aircraft_manufacture_date');

    if (acSelect && typeInput && typeCodeInput && modificationCodeInput && serialInput && manufactureInput) {
        acSelect.addEventListener('change', function() {
            var option = acSelect.selectedOptions[0];
            if (!option || !option.value) {
                typeInput.value = '';
                typeCodeInput.value = '';
                modificationCodeInput.value = '';
                serialInput.value = '';
                manufactureInput.value = '';
                return;
            }
            typeInput.value = option.getAttribute('data-type') || '';
            typeCodeInput.value = option.getAttribute('data-type-code') || '';
            modificationCodeInput.value = option.getAttribute('data-modification-code') || '';
            serialInput.value = option.getAttribute('data-serial') || '';
            manufactureInput.value = option.getAttribute('data-manufacture') || '';
        });
        acSelect.dispatchEvent(new Event('change'));
    }

    (function initAggregateModal() {
        var modalEl = document.getElementById('aggregateSelectModal');
        var aggInput = document.getElementById('failure_aggregate_type');
        var sysSelect = document.getElementById('agg_modal_system');
        var subSelect = document.getElementById('agg_modal_subsystem');
        var aircraftTypeSelect = document.getElementById('agg_modal_aircraft_type');
        var searchInput = document.getElementById('agg_modal_search');
        var tbody = document.getElementById('agg_modal_tbody');
        var placeholder = document.getElementById('agg_modal_placeholder');
        var tableWrap = document.getElementById('agg_modal_table');
        var addForm = document.getElementById('agg_modal_add_form');
        var btnAdd = document.getElementById('agg_modal_btn_add');
        var addCancel = document.getElementById('agg_add_cancel');
        var addSave = document.getElementById('agg_add_save');
        var addCode = document.getElementById('agg_add_code');
        var addName = document.getElementById('agg_add_name');
        var addAircraftType = document.getElementById('agg_add_aircraft_type');
        var addSystem = document.getElementById('agg_add_system');
        var addSubsystem = document.getElementById('agg_add_subsystem');
        var addMessage = document.getElementById('agg_add_message');
        if (!modalEl || !aggInput || !sysSelect || !subSelect || !searchInput || !tbody || !tableWrap || !placeholder) return;

        var allSubOpts = Array.from(subSelect.querySelectorAll('option[data-system]'));
        var allAddSubOpts = addSubsystem ? Array.from(addSubsystem.querySelectorAll('option[data-system]')) : [];
        var searchTimeout = null;
        var modalApiBase = '{{ url()->route('modules.reliability.aggregates.modal') }}';
        var storeFromModalUrl = '{{ route('modules.reliability.aggregates.store-from-modal') }}';
        var csrfToken = document.querySelector('input[name="_token"]') && document.querySelector('input[name="_token"]').value;

        function buildSubsystemOptions(systemVal, targetSelect, opts) {
            if (!targetSelect) return;
            targetSelect.innerHTML = '<option value="">Выберите подсистему</option>';
            if (!systemVal || systemVal === '__free__') {
                targetSelect.disabled = true;
                return;
            }
            targetSelect.disabled = false;
            (opts || allSubOpts).forEach(function(opt) {
                if (opt.getAttribute('data-system') === systemVal) {
                    targetSelect.appendChild(opt.cloneNode(true));
                }
            });
        }

        function loadModalAggregates() {
            var system = sysSelect.value;
            var subsystem = subSelect.value;
            var search = searchInput.value.trim();
            var aircraftTypeId = aircraftTypeSelect ? aircraftTypeSelect.value : '';
            var canLoad = system === '' || system === '__free__' || (system && subsystem);
            if (!canLoad) {
                tableWrap.style.display = 'none';
                placeholder.style.display = 'block';
                placeholder.textContent = 'Выберите систему и подсистему для фильтра';
                return;
            }
            tableWrap.style.display = 'table';
            placeholder.style.display = 'none';
            tbody.innerHTML = '<tr><td colspan="2" class="text-muted small">Загрузка...</td></tr>';
            var url = modalApiBase + '?system=' + encodeURIComponent(system || '') + '&subsystem=' + encodeURIComponent(subsystem || '') + '&search=' + encodeURIComponent(search);
            if (aircraftTypeId) url += '&aircraft_type_id=' + encodeURIComponent(aircraftTypeId);
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function(data) {
                    var items = (data && data.aggregates) ? data.aggregates : [];
                    if (items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="2" class="text-muted small">Ничего не найдено</td></tr>';
                        return;
                    }
                    tbody.innerHTML = '';
                    items.forEach(function(a, idx) {
                        var tr = document.createElement('tr');
                        tr.style.cursor = 'pointer';
                        tr.dataset.name = (a && a.name) ? String(a.name) : '';
                        tr.innerHTML = '<td>' + (idx + 1) + '</td><td>' + escapeHtml(tr.dataset.name) + '</td>';
                        tr.addEventListener('click', function() {
                            aggInput.value = this.dataset.name || '';
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        });
                        tbody.appendChild(tr);
                    });
                })
                .catch(function(err) {
                    tbody.innerHTML = '<tr><td colspan="2" class="text-danger small">Ошибка загрузки</td></tr>';
                });
        }
        function escapeHtml(s) {
            var div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }

        if (btnAdd && addForm) {
            btnAdd.addEventListener('click', function() {
                addForm.style.display = addForm.style.display === 'none' ? 'block' : 'none';
                if (addForm.style.display === 'block') {
                    addMessage.style.display = 'none';
                    addCode.value = '';
                    addName.value = '';
                    if (addAircraftType) addAircraftType.value = '';
                    if (addSystem) addSystem.value = '';
                    if (addSubsystem) { addSubsystem.innerHTML = '<option value="">Выберите систему</option>'; addSubsystem.disabled = true; }
                }
            });
        }
        if (addCancel && addForm) {
            addCancel.addEventListener('click', function() {
                addForm.style.display = 'none';
                addMessage.style.display = 'none';
            });
        }
        if (addSystem && addSubsystem && allAddSubOpts.length) {
            addSystem.addEventListener('change', function() {
                buildSubsystemOptions(this.value, addSubsystem, allAddSubOpts);
                addSubsystem.value = '';
            });
        }
        if (addSave && storeFromModalUrl && csrfToken) {
            addSave.addEventListener('click', function() {
                var name = (addName && addName.value) ? addName.value.trim() : '';
                if (!name) {
                    if (addMessage) { addMessage.style.display = 'block'; addMessage.className = 'small mt-1 text-danger'; addMessage.textContent = 'Укажите наименование.'; }
                    return;
                }
                var payload = {
                    aggregate_code: (addCode && addCode.value) ? addCode.value.trim() : '',
                    aggregate_name_display: name,
                    aircraft_type_id: (addAircraftType && addAircraftType.value) ? parseInt(addAircraftType.value, 10) : null,
                    failure_system_id: (addSubsystem && addSubsystem.value) ? parseInt(addSubsystem.value, 10) : null
                };
                if (addMessage) { addMessage.style.display = 'none'; addMessage.textContent = ''; }
                addSave.disabled = true;
                fetch(storeFromModalUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(payload),
                    credentials: 'same-origin'
                })
                    .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
                    .then(function(res) {
                        if (res.ok && res.data && res.data.success) {
                            if (addForm) addForm.style.display = 'none';
                            if (addName) addName.value = '';
                            if (addCode) addCode.value = '';
                            if (aggInput && res.data.aggregate && res.data.aggregate.name) aggInput.value = res.data.aggregate.name;
                            loadModalAggregates();
                            if (addMessage) { addMessage.style.display = 'block'; addMessage.className = 'small mt-1 text-success'; addMessage.textContent = res.data.message || 'Агрегат добавлен.'; setTimeout(function() { addMessage.style.display = 'none'; }, 2000); }
                        } else {
                            var msg = (res.data && res.data.message) ? res.data.message : (res.data && res.data.errors) ? Object.values(res.data.errors).flat().join(' ') : 'Ошибка сохранения';
                            if (addMessage) { addMessage.style.display = 'block'; addMessage.className = 'small mt-1 text-danger'; addMessage.textContent = msg; }
                        }
                    })
                    .catch(function() {
                        if (addMessage) { addMessage.style.display = 'block'; addMessage.className = 'small mt-1 text-danger'; addMessage.textContent = 'Ошибка сети'; }
                    })
                    .finally(function() { addSave.disabled = false; });
            });
        }

        sysSelect.addEventListener('change', function() {
            buildSubsystemOptions(this.value, subSelect, allSubOpts);
            subSelect.value = '';
            loadModalAggregates();
        });
        subSelect.addEventListener('change', loadModalAggregates);
        if (aircraftTypeSelect) aircraftTypeSelect.addEventListener('change', loadModalAggregates);
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadModalAggregates, 300);
        });

        modalEl.addEventListener('show.bs.modal', function() {
            searchInput.value = '';
            if (addForm) addForm.style.display = 'none';
            buildSubsystemOptions(sysSelect.value, subSelect, allSubOpts);
            loadModalAggregates();
        });

        setTimeout(function() { loadModalAggregates(); }, 100);
    })();

    var engineTypeSelect = document.getElementById('failure_engine_type');
    var engineNumberSelect = document.getElementById('failure_engine_number');
    if (engineTypeSelect && engineNumberSelect) {
        var allEngineNumberOptions = Array.from(engineNumberSelect.options);
        engineTypeSelect.addEventListener('change', function() {
            var typeId = this.value;
            engineNumberSelect.innerHTML = '<option value="">Выберите номер двигателя</option>';
            allEngineNumberOptions.forEach(function(opt) {
                if (opt.value === '') return;
                var optTypeId = opt.getAttribute('data-engine-type-id');
                if (!optTypeId || !typeId || optTypeId === typeId) {
                    engineNumberSelect.appendChild(opt.cloneNode(true));
                }
            });
        });
    }

    @if($isEdit && $f)
    (function failureAttachmentsInit() {
        var failureId = {{ (int) $f->id }};
        var uploadUrl = @json(route('modules.reliability.failures.attachments.upload', $f));
        var deleteUrl = @json(route('modules.reliability.failures.attachments.delete', $f));
        var serveBase = @json(url()->route('modules.reliability.failures.attachments.serve', $f));
        var downloadBase = @json(url()->route('modules.reliability.failures.attachments.download', $f));
        var csrf = (document.querySelector('input[name="_token"]') || {}).value || '';
        var listEl = document.getElementById('failure-attachments-list');
        var uploadBlock = document.getElementById('failure-attachments-upload');
        var previewEl = document.getElementById('failure-attachments-preview');
        var inputEl = document.getElementById('failure-attachments-input');
        var placeholderHtml = '<div class="file-upload-placeholder"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg><span>Перетащите файлы сюда или нажмите для выбора</span></div>';

        function formatSize(bytes) {
            if (!bytes) return '—';
            var u = ['B','KB','MB','GB'], i = 0, s = bytes;
            while (s >= 1024 && i < u.length - 1) { s /= 1024; i++; }
            return s.toFixed(1) + ' ' + u[i];
        }

        if (!uploadBlock || !previewEl || !inputEl) return;

        listEl && listEl.querySelectorAll('.file-preview-item').forEach(function(item) {
            var path = item.getAttribute('data-file-path');
            var name = item.getAttribute('data-file-name') || '';
            var type = (item.getAttribute('data-file-type') || '').toLowerCase();
            var fileId = item.getAttribute('data-file-id');
            var serveUrl = serveBase + (path ? '?path=' + encodeURIComponent(path) : '');
            var downloadUrl = downloadBase + (path ? '?path=' + encodeURIComponent(path) : '');
            item.addEventListener('click', function(e) {
                if (e.target.closest('.file-preview-remove')) return;
                if (type.indexOf('image/') === 0) { window.open(serveUrl, '_blank'); return; }
                window.open(downloadUrl, '_blank');
            });
            item.querySelector('.file-preview-remove').addEventListener('click', function(e) {
                e.stopPropagation();
                if (!fileId || !confirm('Удалить этот файл?')) return;
                fetch(deleteUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ file_id: fileId }) })
                    .then(function(r) { if (r.ok) item.remove(); });
            });
        });

        function addFileToPreview(file, fileId, path, name, size, type) {
            var wrap = document.createElement('div');
            wrap.className = 'file-preview-item';
            if (fileId) wrap.setAttribute('data-file-id', fileId);
            if (path) wrap.setAttribute('data-file-path', path);
            wrap.setAttribute('data-file-name', name || '');
            wrap.setAttribute('data-file-type', type || '');
            wrap.innerHTML = '<div class="file-preview-icon"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div><div class="file-preview-info"><div class="file-preview-name" title="' + (name || '').replace(/"/g, '&quot;') + '">' + (name || 'файл') + '</div><div class="file-preview-size">' + formatSize(size) + '</div></div><button type="button" class="file-preview-remove" title="Удалить"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>';
            var serveUrl = serveBase + (path ? '?path=' + encodeURIComponent(path) : '');
            var downloadUrl = downloadBase + (path ? '?path=' + encodeURIComponent(path) : '');
            wrap.addEventListener('click', function(e) { if (e.target.closest('.file-preview-remove')) return; window.open(path ? downloadUrl : serveUrl, '_blank'); });
            wrap.querySelector('.file-preview-remove').addEventListener('click', function(e) {
                e.stopPropagation();
                if (!fileId) { wrap.remove(); if (previewEl.children.length === 0) previewEl.innerHTML = placeholderHtml; return; }
                if (!confirm('Удалить этот файл?')) return;
                fetch(deleteUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ file_id: fileId }) }).then(function(r) { if (r.ok) { wrap.remove(); if (previewEl.children.length === 0) previewEl.innerHTML = placeholderHtml; } });
            });
            if (listEl) listEl.appendChild(wrap); else previewEl.appendChild(wrap);
        }

        uploadBlock.querySelector('.file-upload-header') && uploadBlock.querySelector('.file-upload-header').addEventListener('click', function() { inputEl.click(); });
        uploadBlock.addEventListener('dragover', function(e) { e.preventDefault(); uploadBlock.classList.add('drag-over'); });
        uploadBlock.addEventListener('dragleave', function(e) { e.preventDefault(); uploadBlock.classList.remove('drag-over'); });
        uploadBlock.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadBlock.classList.remove('drag-over');
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) { inputEl.files = e.dataTransfer.files; inputEl.dispatchEvent(new Event('change', { bubbles: true })); }
        });
        inputEl.addEventListener('change', function() {
            var files = inputEl.files;
            if (!files || !files.length) return;
            for (var i = 0; i < files.length; i++) {
                (function(file) {
                    var item = document.createElement('div');
                    item.className = 'file-preview-item';
                    item.innerHTML = '<div class="file-preview-icon"><span class="spinner-border spinner-border-sm text-primary"></span></div><div class="file-preview-info"><div class="file-preview-name">' + (file.name || '').replace(/</g, '&lt;') + '</div><div class="file-preview-size">' + formatSize(file.size) + '</div></div>';
                    if (previewEl.querySelector('.file-upload-placeholder')) previewEl.innerHTML = '';
                    previewEl.appendChild(item);
                    var fd = new FormData();
                    fd.append('file', file);
                    fetch(uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: fd })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            item.remove();
                            if (data.success && data.file) {
                                addFileToPreview(null, data.file.id, data.file.path, data.file.name, data.file.size, data.file.type);
                            }
                        })
                        .catch(function() { item.remove(); if (previewEl.children.length === 0) previewEl.innerHTML = placeholderHtml; });
                })(files[i]);
            }
            inputEl.value = '';
        });
    })();
    @endif
});
</script>

<style>
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


.main_screen
    {width:1000px;
    background-color: #fff !important;
  
}
    .form-control {
        width:100% !important;
        background-color: #fff !important;
        }


.back-button:hover {
    color: #0056b3;
    text-decoration: none;
}
.back-button i {
    font-size: 14px;
}
</style>
@endsection
