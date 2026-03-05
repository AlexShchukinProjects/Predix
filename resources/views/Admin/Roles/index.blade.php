@extends('layout.main')

@section('content')
<style>
    /* Сетка колонок */
    .roles-layout { display: flex; flex-wrap: nowrap; gap: 1rem; }
    .roles-left-col { width: 400px; min-width: 400px; flex-shrink: 0; }
    .roles-right-col { min-width: 0; flex: 1; max-width: 65%; }
    /* Стили как на странице safety-reporting/messages (заголовки, кнопки, карточки) */
    .admin-roles-page .page-title { font-size: 18px; font-weight: 600; color: #1f2937; margin-bottom: 1rem; }
    .admin-roles-page .cardNew { background: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #e8ecf1; overflow: hidden; }
    .admin-roles-page .cardNew .card-header { background: #1E64D4; color: #ffffff !important; font-weight: 600; font-size: 14px; padding: 15px 20px; border: none; }
    .admin-roles-page .cardNew .card-header .fw-semibold { color: #ffffff !important; }
    .admin-roles-page .cardNew .card-header .btn-light { background: rgba(255,255,255,0.2); color: #fff; border: none; }
    .admin-roles-page .cardNew .card-header .btn-light:hover { background: rgba(255,255,255,0.35); color: #fff; }
    .admin-roles-page .cardNew .card-body { padding: 20px; }
    .admin-roles-page .table-group-header td { background: #1E64D4 !important; color: #ffffff !important; font-weight: 600; font-size: 16px; padding: 15px 20px; border: none; }
    .admin-roles-page .table-group-header .group-title { color: #ffffff !important; }
    .admin-roles-page .table-group-header .toggle-group { color: #ffffff !important; }
    .admin-roles-page .table-parent-header td { background: #1E64D4 !important; color: #ffffff !important; font-weight: 600; font-size: 16px; padding: 15px 20px; border: none; }
    .admin-roles-page .table-parent-header .parent-title { color: #ffffff !important; }
    .admin-roles-page .table-parent-header .toggle-parent { color: #ffffff !important; }
    .admin-roles-page .btn-primary { background-color: #1E64D4; border-color: #1E64D4; font-weight: 500; }
    .admin-roles-page .btn-primary:hover { background-color: #1a56bb; border-color: #1a56bb; }
    .admin-roles-page .btn-outline-primary { color: #1E64D4; border-color: #1E64D4; font-weight: 500; }
    .admin-roles-page .btn-outline-primary:hover { background-color: #1E64D4; color: #fff; border-color: #1E64D4; }
    .admin-roles-page .btn-outline-secondary { color: #6b7280; border-color: #d1d5db; }
    .admin-roles-page .btn-outline-secondary:hover { background-color: #f3f4f6; color: #374151; border-color: #d1d5db; }
    .admin-roles-page .roles-right-col .card-header .btn-outline-secondary { background-color: #ffffff; color: #374151; }
    .admin-roles-page .roles-right-col .card-header .btn-outline-secondary:hover { background-color: #f8f9fa; color: #1f2937; border-color: #d1d5db; }
    .admin-roles-page .table thead.table-light th { background: #f8f9fa; font-weight: 600; color: #374151; font-size: 14px; padding: 12px; }
    .admin-roles-page .form-label { font-weight: 600; color: #495057; }
    .admin-roles-page .modal .modal-header { background: #1E64D4; color: #ffffff; border-bottom: none; }
    .admin-roles-page .modal .modal-header .modal-title { color: #ffffff; font-weight: 600; font-size: 16px; }
    .admin-roles-page .modal .modal-header .btn-close { filter: invert(1); }
    .admin-roles-page .admin-roles-modal-footer { justify-content: flex-start; }
    .admin-roles-page .admin-roles-modal-footer .efds-actions { margin-top: 0; display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .admin-roles-page .admin-roles-modal-footer .efds-actions .btn { height: 40px; display: inline-flex; align-items: center; }
    .admin-roles-page .role-actions .role-edit { border: none !important; box-shadow: none !important; text-decoration: none !important; color: #6b7280 !important; margin-right: 0.5rem; }
    .admin-roles-page .role-actions .role-edit:hover { border: none !important; box-shadow: none !important; opacity: 0.8; color: #4b5563 !important; }
    .admin-roles-page .role-delete { border: none !important; box-shadow: none !important; text-decoration: none !important; }
    .admin-roles-page .role-delete:hover { border: none !important; box-shadow: none !important; opacity: 0.8; }
    /* Активная роль в списке */
    .admin-roles-page .list-group-item.active {
        background-color: lightslategray;
        border-color: lightslategray;
        color: #ffffff;
    }
    .admin-roles-page .role-item .role-drag-handle { cursor: grab; color: #6b7280; padding: 0 4px; }
    .admin-roles-page .role-item .role-drag-handle:active { cursor: grabbing; }
    .admin-roles-page .list-group-roles.sortable-ghost { opacity: 0.5; }
</style>
<div class="container-fluid admin-roles-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title mb-0">Управление ролями</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
        </div>
    @endif

    <div class="row g-3 roles-layout">
        {{-- Левая колонка: роли (фиксированная ширина) --}}
        <div class="roles-left-col">
            <div class="card cardNew h-100">
                <div class="card-header">
                    <span class="fw-semibold">РОЛИ</span>
                </div>
                <div class="card-body p-0 d-flex flex-column">
                    <ul class="list-group list-group-flush list-group-roles flex-grow-1" id="rolesSortableList">
                        @forelse($roles as $role)
                            <li class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3 role-item" data-role-id="{{ $role->id }}" data-role-name="{{ e($role->name) }}" data-permission-ids="{{ $role->permissions->pluck('id')->join(',') }}">
                                <span class="role-drag-handle me-2" title="Перетащите для изменения порядка"><i class="fas fa-grip-vertical"></i></span>
                                <a href="#" class="text-decoration-none text-dark flex-grow-1 role-link">{{ $role->name }}</a>
                                @if($role->name !== ($superAdminRoleName ?? ''))
                                <div class="btn-group btn-group-sm role-actions">
                                    <a href="#" class="btn btn-link btn-sm p-1 role-edit" data-role-id="{{ $role->id }}" data-role-name="{{ e($role->name) }}" title="Изменить название"><i class="fas fa-pen"></i></a>
                                    <a href="#" class="btn btn-link btn-sm p-1 text-danger role-delete" data-role-id="{{ $role->id }}" data-role-name="{{ e($role->name) }}" title="Удалить"><i class="fas fa-times"></i></a>
                                </div>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center py-3">Нет ролей.</li>
                        @endforelse
                    </ul>
                    <div class="p-3 border-top">
                        <button type="button" class="btn btn-primary w-100" id="btnAddRole">
                            <i class="fas fa-plus me-1"></i>Добавить роль
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Правая колонка: страницы (действия) по модулям с галочками (фиксированная ширина) --}}
        <div class="roles-right-col">
            <div class="card cardNew h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">ДЕЙСТВИЕ / ОПИСАНИЕ ДЕЙСТВИЯ / АКТИВНОСТЬ</span>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnExpandCollapse">Развернуть\Свернуть</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnExpandChecked">Развернуть с галочками</button>
                    </div>
                </div>
                <div class="card-body" id="permissionsPanel">
                    <div id="noRoleSelected" class="text-muted text-center py-5">
                        Выберите роль в левой колонке, чтобы настроить видимые страницы.
                    </div>
                    <div id="permissionsContent" style="display: none;">
                        <form method="POST" action="{{ route('admin.roles.update-permissions') }}" id="formPermissions">
                            @csrf
                            <input type="hidden" name="role_id" id="inputRoleId" value="">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Действие</th>
                                            <th style="width: 45%;">Описание действия</th>
                                            <th style="width: 25%;" class="text-center">Активность</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($permissionTree as $node)
                                            @if($node['parent'])
                                                {{-- Папка «Планирование»: заголовок папки, внутри — разделы ПДС, Летная служба, ИАС, Отчеты --}}
                                                <tr class="table-parent-header" data-parent="{{ e($node['parent']) }}" data-groups="{{ implode(',', array_map('e', array_keys($node['children']))) }}">
                                                    <td colspan="3" class="fw-semibold py-2">
                                                        <i class="fas fa-folder-open me-2 toggle-parent"></i>
                                                        <span class="parent-title">{{ $node['parent'] }}</span>
                                                    </td>
                                                </tr>
                                            @endif
                                            @foreach($node['children'] as $group => $items)
                                                <tr class="table-group-header" data-group="{{ e($group ?? '') }}" @if($node['parent']) data-parent="{{ e($node['parent']) }}" @endif>
                                                    <td colspan="3" class="bg-light fw-semibold py-2 {{ $node['parent'] ? 'ps-4' : '' }}">
                                                        <i class="fas fa-folder-open me-2 toggle-group"></i>
                                                        <span class="group-title">{{ $group ?: 'Общие страницы' }}</span>
                                                    </td>
                                                </tr>
                                                @foreach($items as $perm)
                                                    <tr class="permission-row" data-group="{{ e($group ?? '') }}">
                                                        <td class="{{ $node['parent'] ? 'ps-5' : 'ps-4' }}">{{ $perm->name }}</td>
                                                        <td>{{ $perm->slug ?? '—' }}</td>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="form-check-input permission-cb">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">Нет прав.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary">Сохранить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Модальное окно: добавить роль --}}
<div class="modal fade" id="modalAddRole" tabindex="-1" aria-labelledby="modalAddRoleLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAddRoleLabel">Добавить роль</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Название роли</label>
                        <input type="text" class="form-control w-100" id="roleName" name="name" required placeholder="Напр. Менеджер">
                    </div>
                </div>
                <div class="modal-footer admin-roles-modal-footer">
                    <div class="efds-actions">
                        <button type="submit" class="btn efds-btn efds-btn--primary">Добавить</button>
                        <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Отмена</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Модальное окно: изменить название роли --}}
<div class="modal fade" id="modalEditRole" tabindex="-1" aria-labelledby="modalEditRoleLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEditRole">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditRoleLabel">Изменить название роли</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editRoleName" class="form-label">Название роли</label>
                        <input type="text" class="form-control w-100" id="editRoleName" name="name" required placeholder="Напр. Менеджер">
                    </div>
                </div>
                <div class="modal-footer admin-roles-modal-footer">
                    <div class="efds-actions">
                        <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                        <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Отмена</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Модальное окно: подтверждение удаления роли --}}
<div class="modal fade" id="modalDeleteRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удаление роли</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <p>Удалить роль «<span id="deleteRoleName"></span>»?</p>
            </div>
            <div class="modal-footer admin-roles-modal-footer">
                <div class="efds-actions">
                    <form id="formDeleteRole" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn efds-btn efds-btn--danger">Удалить</button>
                    </form>
                    <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Сортировка ролей (drag-and-drop)
    var rolesList = document.getElementById('rolesSortableList');
    if (rolesList && typeof Sortable !== 'undefined') {
        new Sortable(rolesList, {
            handle: '.role-drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            filter: '.list-group-item:not(.role-item)',
            onEnd: function() {
                var order = [];
                rolesList.querySelectorAll('.role-item').forEach(function(el) {
                    var id = el.getAttribute('data-role-id');
                    if (id) order.push(parseInt(id, 10));
                });
                if (order.length === 0) return;
                fetch('{{ route("admin.roles.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ order: order })
                }).then(function(r) {
                    if (!r.ok) throw new Error('Ошибка сохранения порядка');
                }).catch(function() {
                    alert('Не удалось сохранить порядок ролей.');
                });
            }
        });
    }

    const noRoleSelected = document.getElementById('noRoleSelected');
    const permissionsContent = document.getElementById('permissionsContent');
    const inputRoleId = document.getElementById('inputRoleId');
    const formPermissions = document.getElementById('formPermissions');
    const permissionRows = document.querySelectorAll('.permission-row');
    const groupHeaders = document.querySelectorAll('.table-group-header');
    const parentHeaders = document.querySelectorAll('.table-parent-header');

    // Выбор роли в левой колонке
    document.querySelectorAll('.role-item').forEach(function(item) {
        const link = item.querySelector('.role-link');
        if (!link) return;
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const roleId = item.getAttribute('data-role-id');
            const permissionIds = (item.getAttribute('data-permission-ids') || '').split(',').filter(Boolean);
            document.querySelectorAll('.role-item').forEach(function(r) { r.classList.remove('active'); });
            item.classList.add('active');
            inputRoleId.value = roleId;
            noRoleSelected.style.display = 'none';
            permissionsContent.style.display = 'block';
            document.querySelectorAll('.permission-cb').forEach(function(cb) {
                cb.checked = permissionIds.indexOf(String(cb.value)) !== -1;
            });
            expandAllGroups();
        });
    });

    // Кнопка редактирования названия роли
    document.querySelectorAll('.role-edit').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var roleId = btn.getAttribute('data-role-id');
            var roleName = btn.getAttribute('data-role-name');
            document.getElementById('editRoleName').value = roleName;
            document.getElementById('formEditRole').action = '{{ url("admin/roles") }}/' + roleId;
            new bootstrap.Modal(document.getElementById('modalEditRole')).show();
        });
    });

    // Крестик удаления роли (не переходить по ссылке)
    document.querySelectorAll('.role-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const roleId = btn.getAttribute('data-role-id');
            const roleName = btn.getAttribute('data-role-name');
            document.getElementById('deleteRoleName').textContent = roleName;
            document.getElementById('formDeleteRole').action = '{{ url("admin/roles") }}/' + roleId;
            new bootstrap.Modal(document.getElementById('modalDeleteRole')).show();
        });
    });

    // Кнопка "Добавить" — открыть модалку
    document.getElementById('btnAddRole').addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('modalAddRole')).show();
    });

    // Показать/скрыть дочерние строки родительской папки (Планирование)
    function setParentChildrenVisible(parentRow, visible) {
        var parentName = parentRow.getAttribute('data-parent');
        var groupsStr = parentRow.getAttribute('data-groups') || '';
        document.querySelectorAll('.table-group-header[data-parent="' + parentName + '"]').forEach(function(r) { r.style.display = visible ? '' : 'none'; });
        groupsStr.split(',').forEach(function(g) {
            g = g.trim();
            if (!g) return;
            document.querySelectorAll('.permission-row[data-group="' + g + '"]').forEach(function(r) { r.style.display = visible ? '' : 'none'; });
        });
        var icon = parentRow.querySelector('.toggle-parent');
        if (icon) icon.className = (visible ? 'fas fa-folder-open' : 'fas fa-folder') + ' me-2 toggle-parent';
    }
    function isParentCollapsed(parentRow) {
        var parentName = parentRow.getAttribute('data-parent');
        var first = document.querySelector('.table-group-header[data-parent="' + parentName + '"]');
        return first && first.style.display === 'none';
    }

    function expandAllGroups() {
        parentHeaders.forEach(function(ph) { setParentChildrenVisible(ph, true); });
        groupHeaders.forEach(function(h) {
            if (h.style.display === 'none') return;
            var g = h.getAttribute('data-group');
            document.querySelectorAll('.permission-row[data-group="' + g + '"]').forEach(function(r) { r.style.display = ''; });
            var icon = h.querySelector('.toggle-group');
            if (icon) icon.className = 'fas fa-folder-open me-2 toggle-group';
        });
    }
    function collapseAllGroups() {
        groupHeaders.forEach(function(h) {
            var g = h.getAttribute('data-group');
            document.querySelectorAll('.permission-row[data-group="' + g + '"]').forEach(function(r) { r.style.display = 'none'; });
            var icon = h.querySelector('.toggle-group');
            if (icon) icon.className = 'fas fa-folder me-2 toggle-group';
        });
        parentHeaders.forEach(function(ph) { setParentChildrenVisible(ph, false); });
    }
    parentHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            var visible = isParentCollapsed(header);
            setParentChildrenVisible(header, visible);
        });
    });
    groupHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            var g = header.getAttribute('data-group');
            var rows = document.querySelectorAll('.permission-row[data-group="' + g + '"]');
            var isHidden = rows.length && rows[0].style.display === 'none';
            var icon = header.querySelector('.toggle-group');
            rows.forEach(function(r) { r.style.display = isHidden ? '' : 'none'; });
            if (icon) icon.className = (isHidden ? 'fas fa-folder-open' : 'fas fa-folder') + ' me-2 toggle-group';
        });
    });

    document.getElementById('btnExpandCollapse').addEventListener('click', function() {
        var anyCollapsed = false;
        parentHeaders.forEach(function(ph) { if (isParentCollapsed(ph)) anyCollapsed = true; });
        groupHeaders.forEach(function(h) {
            if (h.style.display === 'none') return;
            var g = h.getAttribute('data-group');
            var rows = document.querySelectorAll('.permission-row[data-group="' + g + '"]');
            if (rows.length && rows[0].style.display === 'none') anyCollapsed = true;
        });
        if (anyCollapsed) expandAllGroups(); else collapseAllGroups();
    });
    document.getElementById('btnExpandChecked').addEventListener('click', function() {
        collapseAllGroups();
        document.querySelectorAll('.permission-cb:checked').forEach(function(cb) {
            var row = cb.closest('.permission-row');
            if (row) row.style.display = '';
            var g = row.getAttribute('data-group');
            var h = document.querySelector('.table-group-header[data-group="' + g + '"]');
            if (h) {
                h.style.display = '';
                var icon = h.querySelector('.toggle-group');
                if (icon) icon.className = 'fas fa-folder-open me-2 toggle-group';
                var parentName = h.getAttribute('data-parent');
                if (parentName) {
                    var ph = document.querySelector('.table-parent-header[data-parent="' + parentName + '"]');
                    if (ph) setParentChildrenVisible(ph, true);
                }
            }
        });
        document.querySelectorAll('.permission-row').forEach(function(r) {
            var g = r.getAttribute('data-group');
            var header = document.querySelector('.table-group-header[data-group="' + g + '"]');
            if (header && header.style.display !== 'none') r.style.display = '';
        });
    });
});
</script>
@endsection
