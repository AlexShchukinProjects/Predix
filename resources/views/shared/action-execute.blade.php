@extends('layout.main')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<style>
    .action-execute-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 16px 60px;
    }
    .action-execute-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #1E64D4;
        text-decoration: none;
        font-size: 15px;
        margin-bottom: 18px;
    }

    .main_screen{
        width: 100%;
    }

    textarea.form-control, textarea.form-control-custom {
    resize: vertical;
    min-height: 80px;
    width: 100%;
    background-color: white;
    }

   .form-control, .form-control-custom {
    
    background-color: white;
    }

    .action-execute-back:hover { text-decoration: underline; }
    .action-execute-title {
        font-size: 22px;
        font-weight: 700;
        color: #1E64D4;
        margin-bottom: 24px;
    }
    .ae-section {
        background: #f5f7fa;
       
      
        margin-bottom: 20px;
        overflow: hidden;
    }
    .ae-section-header {
      
    padding: 14px 20px;
    font-size: 20px;
    font-weight: 700;
    color: white;
    border-bottom: 1px solid #e5e7eb;
    background-color: #1E64D4;

    }
    .ae-section-body {
        padding: 20px;
    }
    .ae-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding: 12px 24px;
        display: flex;
        align-items: center;
        z-index: 1040;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
    }
    .ae-footer .btn { font-size: 15px; }
    .ae-status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .ae-status-pending { background: #fef3c7; color: #92400e; }
    .ae-status-in_progress { background: #dbeafe; color: #1e40af; }
    .ae-status-pending_confirmation { background: #ede9fe; color: #5b21b6; }
    .ae-status-completed { background: #d1fae5; color: #065f46; }
    .ae-status-cancelled { background: #f3f4f6; color: #6b7280; }
    .ae-status-overdue { background: #fee2e2; color: #991b1b; }

    .ae-employee-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 1070; display: none; }
    .ae-employee-modal.show { display: block; }
    .ae-employee-modal-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); }
    .ae-employee-modal-content { position: relative; margin: 2rem auto; max-width: 480px; background: #fff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); max-height: calc(100vh - 4rem); display: flex; flex-direction: column; }
    .ae-employee-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; background-color: #1E64D4; color: white; border-radius: 8px 8px 0 0; }
    .ae-employee-modal-title { margin: 0; font-size: 1.125rem; font-weight: 600; }
    .ae-employee-modal-close { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: rgba(255,255,255,0.2); border: none; border-radius: 6px; color: #fff; font-size: 1rem; cursor: pointer; transition: background 0.2s; }
    .ae-employee-modal-close:hover { background: rgba(255,255,255,0.3); }
    .ae-employee-modal-body { padding: 1.25rem; overflow-y: auto; flex: 1; min-height: 0; }
    .ae-employee-list { max-height: 320px; overflow-y: auto; }
    .ae-employee-item { padding: 0.75rem 1rem; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 0.5rem; cursor: pointer; transition: background 0.2s; }
    .ae-employee-item:hover { background: #f3f4f6; }
    .ae-employee-name { font-weight: 500; color: #111827; }
    .ae-employee-meta { font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem; }
    .ae-picker-trigger { cursor: pointer; background-color: #fff !important; }

    /* Кнопки файлов: современный вид */
    .ae-file-actions { display: flex; align-items: center; gap: 6px; }
    .ae-file-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border: none; border-radius: 8px;
        cursor: pointer; transition: background 0.2s, color 0.2s;
        color: #64748b; background: transparent;
        font-size: 14px; text-decoration: none;
    }
    .ae-file-btn:hover { background: #f1f5f9; color: #1E64D4; }
    .ae-file-btn--download:hover { color: #1E64D4; }
    .ae-file-btn--remove { color: #94a3b8; }
    .ae-file-btn--remove:hover { background: #fef2f2; color: #dc2626; }
    .ae-file-btn--remove:focus { outline: none; box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.3); }
</style>

<div class="action-execute-page">
    <a href="{{ $backUrl }}" class="action-execute-back">
        <i class="fas fa-arrow-left"></i> Назад к сообщению
    </a>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="action-execute-title mb-0">Выполнение поручения / мероприятия</h1>
        <span class="ae-status-badge ae-status-{{ $action->status }}">{{ $action->status_text }}</span>
    </div>

    <!-- ЗАДАЧА -->
    <div class="ae-section">
        <div class="ae-section-header">ЗАДАЧА</div>
        <div class="ae-section-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Мероприятие</label>
                <textarea class="form-control" id="aeTaskDescription" rows="4" @unless($canEditTask) disabled @endunless>{{ $action->description }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Срок исполнения</label>
                        <input type="date" class="form-control" id="aeDueDate" value="{{ $action->due_date?->format('Y-m-d') }}" @unless($canEditTask) disabled @endunless>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Ответственный исполнитель</label>
                        <input type="hidden" id="aeResponsibleId" value="{{ $action->responsible_user_id }}">
                        @if($canEditTask)
                            <input type="text" class="form-control ae-picker-trigger" id="aeResponsibleName" data-target-hidden="aeResponsibleId" readonly placeholder="Нажмите для выбора" value="{{ $action->responsible?->name }}">
                        @else
                            <input type="text" class="form-control" id="aeResponsibleName" value="{{ $action->responsible?->name ?? 'Не назначен' }}" disabled>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Подтверждающий исполнение</label>
                        <input type="hidden" id="aeConfirmingId" value="{{ $action->confirming_user_id }}">
                        @if($canEditTask)
                            <input type="text" class="form-control ae-picker-trigger" id="aeConfirmingName" data-target-hidden="aeConfirmingId" readonly placeholder="Нажмите для выбора" value="{{ $action->confirmingUser?->name }}">
                        @else
                            <input type="text" class="form-control" id="aeConfirmingName" value="{{ $action->confirmingUser?->name ?? 'Не назначен' }}" disabled>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ОТЧЕТ ОБ ИСПОЛНЕНИИ -->
    <div class="ae-section">
        <div class="ae-section-header">ОТЧЕТ ОБ ИСПОЛНЕНИИ</div>
        <div class="ae-section-body">
            <div class="mb-3">
                <label class="form-label">Фактически выполненный объем работ</label>
                <textarea class="form-control" id="aeActualWork" rows="5" placeholder="Опишите фактически выполненный объем работ...">{{ $action->actual_work_volume }}</textarea>
            </div>
            <div class="mb-3">
                <button type="button" class="btn efds-btn efds-btn--primary" id="aeAddFileBtn">
                    <i class="fas fa-plus me-1"></i>Добавить файл
                </button>
                <input type="file" id="aeFileInput" class="d-none" multiple>
                <div id="aeFilesList" class="mt-2"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Комментарий</label>
                <textarea class="form-control" id="aeComment" rows="2" placeholder="Комментарий...">{{ $action->comment }}</textarea>
            </div>
        </div>
    </div>
</div>

<!-- Фиксированный футер с кнопками -->
<div class="ae-footer">
    <div class="d-flex align-items-center" style="gap: 8px;">
        <div id="aeFooterSave" class="d-flex {{ $action->status === 'pending_confirmation' ? 'd-none' : '' }}" style="gap: 8px;">
            <button type="button" class="btn efds-btn efds-btn--primary" id="aeSaveAndCompleteBtn">Сохранить и завершить</button>
            <button type="button" class="btn efds-btn efds-btn--primary" id="aeSaveBtn">Сохранить</button>
        </div>
        <div id="aeFooterConfirm" class="d-flex {{ $action->status !== 'pending_confirmation' ? 'd-none' : '' }}" style="gap: 8px;">
            <button type="button" class="btn efds-btn efds-btn--primary" id="aeConfirmBtn">Подтвердить</button>
            <button type="button" class="btn efds-btn efds-btn--warning" id="aeRevisionBtn">Отправить на доработку</button>
        </div>
    </div>
    <button type="button" style="margin-right: 0px;" class="btn efds-btn efds-btn--danger ms-auto" id="aeDeleteBtn">Удалить</button>
</div>

<!-- Модальное окно выбора сотрудника -->
<div class="ae-employee-modal" id="aeEmployeeModal">
    <div class="ae-employee-modal-overlay"></div>
    <div class="ae-employee-modal-content">
        <div class="ae-employee-modal-header">
            <h5 class="ae-employee-modal-title">Выбор сотрудника</h5>
            <button type="button" class="ae-employee-modal-close" id="aeEmployeeModalClose">&times;</button>
        </div>
        <div class="ae-employee-modal-body">
            <input type="text" class="form-control mb-3" id="aeEmployeeSearch" placeholder="Поиск по имени, email, должности...">
            <div class="ae-employee-list" id="aeEmployeeList"></div>
        </div>
    </div>
</div>

<script>
(function() {
    const API_BASE = @json($apiBaseUrl);
    const BACK_URL = @json($backUrl);
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const ALL_EMPLOYEES = @json($allEmployees);
    const ACTION_ID = {{ $action->id }};
    const CAN_EDIT_TASK = @json($canEditTask);

    function escapeHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function notify(message, success = true) {
        const el = document.createElement('div');
        el.className = `alert alert-${success ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        el.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        el.innerHTML = `${escapeHtml(message)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(el);
        setTimeout(() => { if (el.parentNode) el.remove(); }, 3000);
    }

    // --- Файлы ---
    const existingFiles = @json($action->files ?? []);

    function renderFiles() {
        const list = document.getElementById('aeFilesList');
        if (!list) return;
        list.innerHTML = '';
        existingFiles.forEach(function(file) {
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-between align-items-center mb-2 p-2 border rounded';
            div.innerHTML = `
                <div>
                    <i class="fas fa-file me-2"></i>
                    <span>${escapeHtml(file.name)}</span>
                    <small class="text-muted ms-2">(${Math.round(file.size / 1024)} кБ)</small>
                </div>
                <div class="ae-file-actions">
                    <a href="${file.url}" target="_blank" class="ae-file-btn ae-file-btn--download" title="Скачать"><i class="fas fa-download"></i></a>
                    <button type="button" class="ae-file-btn ae-file-btn--remove ae-remove-file" data-path="${escapeHtml(file.path)}" title="Удалить" aria-label="Удалить файл"><i class="fas fa-times"></i></button>
                </div>
            `;
            list.appendChild(div);
        });
    }
    renderFiles();

    document.getElementById('aeAddFileBtn')?.addEventListener('click', function() {
        document.getElementById('aeFileInput')?.click();
    });

    document.getElementById('aeFileInput')?.addEventListener('change', function(e) {
        Array.from(e.target.files || []).forEach(async function(file) {
            const list = document.getElementById('aeFilesList');
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-between align-items-center mb-2 p-2 border rounded';
            div.innerHTML = `
                <div><i class="fas fa-file me-2"></i><span>${escapeHtml(file.name)}</span></div>
                <div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Загрузка...</span></div>
            `;
            list.appendChild(div);

            try {
                const form = new FormData();
                form.append('file', file);
                const resp = await fetch(API_BASE + '/upload', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                    body: form
                });
                const result = await resp.json();
                if (result.success) {
                    existingFiles.push(result.file);
                    renderFiles();
                } else {
                    throw new Error(result.message || 'Ошибка загрузки');
                }
            } catch (err) {
                div.innerHTML = `<div class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>${escapeHtml(file.name)}: ${escapeHtml(err.message)}</div>
                    <button type="button" class="ae-file-btn ae-file-btn--remove" onclick="this.closest('.d-flex').remove()" title="Убрать" aria-label="Убрать"><i class="fas fa-times"></i></button>`;
            }
        });
        e.target.value = '';
    });

    document.getElementById('aeFilesList')?.addEventListener('click', async function(e) {
        const btn = e.target.closest('.ae-remove-file');
        if (!btn) return;
        if (!confirm('Удалить этот файл?')) return;
        const path = btn.getAttribute('data-path');
        try {
            const resp = await fetch(API_BASE + '/files', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ file_path: path })
            });
            const data = await resp.json();
            if (data.success) {
                const idx = existingFiles.findIndex(f => f.path === path);
                if (idx !== -1) existingFiles.splice(idx, 1);
                renderFiles();
                notify('Файл удален');
            } else {
                notify('Ошибка удаления файла', false);
            }
        } catch (err) {
            notify('Ошибка удаления файла', false);
        }
    });

    // --- Выбор сотрудника ---
    let currentPickerInput = null;
    const modal = document.getElementById('aeEmployeeModal');
    const listEl = document.getElementById('aeEmployeeList');
    const searchEl = document.getElementById('aeEmployeeSearch');

    function renderEmployeeList(filter) {
        if (!listEl) return;
        const q = (filter || '').toLowerCase();
        const filtered = ALL_EMPLOYEES.filter(u =>
            (u.name || '').toLowerCase().includes(q) ||
            (u.email || '').toLowerCase().includes(q) ||
            (u.position || '').toLowerCase().includes(q)
        );
        listEl.innerHTML = filtered.length === 0
            ? '<div class="text-muted text-center py-3">Ничего не найдено</div>'
            : filtered.map(u => `
                <div class="ae-employee-item" data-id="${u.id}" data-name="${escapeHtml(u.name)}">
                    <div class="ae-employee-name">${escapeHtml(u.name)}</div>
                    <div class="ae-employee-meta">${escapeHtml(u.position || '')}${u.email ? ' &middot; ' + escapeHtml(u.email) : ''}</div>
                </div>
            `).join('');
    }

    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.ae-picker-trigger');
        if (!trigger) return;
        currentPickerInput = trigger;
        if (searchEl) searchEl.value = '';
        renderEmployeeList('');
        modal?.classList.add('show');
    });

    searchEl?.addEventListener('input', function() {
        renderEmployeeList(this.value);
    });

    listEl?.addEventListener('click', function(e) {
        const item = e.target.closest('.ae-employee-item');
        if (!item || !currentPickerInput) return;
        currentPickerInput.value = item.getAttribute('data-name');
        const hiddenId = currentPickerInput.getAttribute('data-target-hidden');
        if (hiddenId) {
            const hidden = document.getElementById(hiddenId);
            if (hidden) hidden.value = item.getAttribute('data-id');
        }
        modal?.classList.remove('show');
        currentPickerInput = null;
    });

    document.getElementById('aeEmployeeModalClose')?.addEventListener('click', function() {
        modal?.classList.remove('show');
        currentPickerInput = null;
    });
    document.querySelector('.ae-employee-modal-overlay')?.addEventListener('click', function() {
        modal?.classList.remove('show');
        currentPickerInput = null;
    });

    // --- Действия ---
    function getPayload() {
        const payload = {
            actual_work_volume: document.getElementById('aeActualWork')?.value || '',
            comment: document.getElementById('aeComment')?.value?.trim() || '',
            responsible_user_id: document.getElementById('aeResponsibleId')?.value || null,
            confirming_user_id: document.getElementById('aeConfirmingId')?.value || null
        };
        if (CAN_EDIT_TASK) {
            payload.description = document.getElementById('aeTaskDescription')?.value || '';
            payload.due_date = document.getElementById('aeDueDate')?.value || null;
        }
        return payload;
    }

    async function sendUpdate(payload, btn, originalText) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Сохранение...';
        try {
            const resp = await fetch(API_BASE, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (data.success) {
                notify(data.message || 'Сохранено');
                if (payload.status === 'pending_confirmation' || payload.status === 'completed' || payload.status === 'in_progress') {
                    setTimeout(() => { window.location.href = BACK_URL; }, 800);
                }
            } else {
                throw new Error(data.message || 'Ошибка');
            }
        } catch (err) {
            notify(err.message, false);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    document.getElementById('aeSaveBtn')?.addEventListener('click', function() {
        sendUpdate(getPayload(), this, 'Сохранить');
    });

    document.getElementById('aeSaveAndCompleteBtn')?.addEventListener('click', function() {
        const p = getPayload();
        p.status = 'pending_confirmation';
        sendUpdate(p, this, 'Сохранить и завершить');
    });

    document.getElementById('aeConfirmBtn')?.addEventListener('click', function() {
        const comment = document.getElementById('aeComment')?.value?.trim() || '';
        sendUpdate({ status: 'completed', comment: comment }, this, 'Подтвердить');
    });

    document.getElementById('aeRevisionBtn')?.addEventListener('click', function() {
        const comment = document.getElementById('aeComment')?.value?.trim() || '';
        sendUpdate({ status: 'in_progress', comment: comment }, this, 'Отправить на доработку');
    });

    document.getElementById('aeDeleteBtn')?.addEventListener('click', async function() {
        if (!confirm('Вы уверены, что хотите удалить это мероприятие?')) return;
        this.disabled = true;
        try {
            const resp = await fetch(API_BASE, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
            if (resp.ok) {
                notify('Мероприятие удалено');
                setTimeout(() => { window.location.href = BACK_URL; }, 800);
            } else {
                throw new Error('Ошибка удаления');
            }
        } catch (err) {
            notify(err.message, false);
            this.disabled = false;
        }
    });
})();
</script>
@endsection
