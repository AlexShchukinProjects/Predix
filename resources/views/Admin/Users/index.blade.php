@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="mb-3">
                <h2 class="mb-0">Пользователи</h2>
            </div>
        </div>
    </div>

    <!-- Фильтры в стиле страницы рейсов -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="filter-bar-wrap">
                <div class="filter-bar">
                    <span class="filter-bar__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                    </span>
                    <form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar__form" id="usersFilterForm">
                        <div class="filter-bar__fields">
                            <input 
                                type="text"
                                class="filter-bar__input filter-bar__input--search"
                                name="q"
                                placeholder="Имя или email"
                                value="{{ $filters['q'] }}"
                                aria-label="Поиск по имени или email"
                            >
                            <select name="status" class="filter-bar__select" aria-label="Статус">
                                <option value="">Все статусы</option>
                                <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Активные</option>
                                <option value="blocked" {{ $filters['status'] === 'blocked' ? 'selected' : '' }}>Заблокированные</option>
                            </select>
                            @php
                                $roles = \App\Models\Role::orderBy('name')->get();
                            @endphp
                            <select name="role" class="filter-bar__select" aria-label="Роль">
                                <option value="">Все роли</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected($filters['role'] === (string) $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="per_page" value="{{ request()->get('per_page', $users->perPage()) }}">
                    </form>
                    <button 
                        type="button"
                        class="filter-bar__clear"
                        onclick="window.location.href='{{ route('admin.users.index') }}'"
                        aria-label="Сбросить фильтры"
                        title="Сбросить все фильтры"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица пользователей -->
    <div class="row">
        <div class="col-12">
            <div class="efds-table-header">
                <div class="efds-table-header__stats text-muted">
                    <span class="me-2">На странице:</span>
                    @php $currentPerPage = request()->get('per_page', $users->perPage()); @endphp
                    <select class="form-select form-select-sm d-inline-block" style="width: auto;" name="per_page_selector" onchange="updateUsersPerPage(this.value)" aria-label="Записей на странице">
                        <option value="10" {{ $currentPerPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="ms-2">Всего записей: {{ $users->total() }}</span>
                </div>
                <div class="efds-table-header__actions">
                    <button type="button" class="btn efds-btn efds-btn--danger me-2" id="bulkDeleteUsersBtn" style="display: none;" title="Удалить выбранных">
                        Удалить
                    </button>
                    <a href="{{ route('admin.users.create') }}" class="btn efds-btn efds-btn--primary">
                        <i class="fas fa-plus me-1"></i>
                        Добавить пользователя
                    </a>
                </div>
            </div>

            <div class="card-body" id="users-table-container">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-blue">
                                    <tr>
                                        <th scope="col" class="user-checkbox-col text-center" style="width: 40px;">
                                            <input type="checkbox" id="selectAllUsers" class="form-check-input" aria-label="Выбрать всех">
                                        </th>
                                        <th>ID</th>
                                        <th>Имя</th>
                                        <th>Email</th>
                                        <th>Логин</th>
                                        <th>Статус</th>
                                        <th>Должность</th>
                                        <th>Телефон</th>
                                        <th>Создан</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr class="user-row" data-href="{{ route('admin.users.edit', $user) }}" style="cursor:pointer;">
                                        <td class="user-checkbox-col text-center" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="form-check-input user-checkbox" name="user_ids[]" value="{{ $user->id }}" aria-label="Выбрать пользователя {{ $user->id }}">
                                        </td>
                                        <td class="fw-bold">{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->login ?? '-' }}</td>
                                        <td>
                                            @if($user->status === 'blocked')
                                                <span class="badge bg-danger">Заблокирован</span>
                                            @else
                                                <span class="badge bg-success">Активен</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->position ?? '-' }}</td>
                                        <td>{{ $user->mobile_phone ?? '-' }}</td>
                                        <td>{{ optional($user->created_at)->format('d.m.Y') ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div style="margin-top: 20px;">
                            {{ $users->onEachSide(1)->links('vendor.pagination.safety-reporting') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="text-muted mb-3">
                                <i class="fas fa-users fa-3x"></i>
                            </div>
                            <h5>Пользователи не найдены</h5>
                            <p class="text-muted">Попробуйте изменить параметры поиска или добавьте нового пользователя</p>
                            <a href="{{ route('admin.users.create') }}" class="btn efds-btn efds-btn--primary">
                                <i class="fas fa-plus me-1"></i>
                                Добавить пользователя
                            </a>
                        </div>
                    @endif
            </div>
        </div>
    </div>

    <form id="bulkDeleteUsersForm" action="{{ route('admin.users.bulk-destroy') }}" method="POST" style="display: none;">
        @csrf
        <div id="bulkDeleteUsersFormIds"></div>
    </form>

    <div id="bulkDeleteUsersModal" class="bulk-delete-modal" role="dialog" aria-labelledby="bulkDeleteUsersModalTitle" aria-modal="true" style="display: none;">
        <div class="bulk-delete-modal__backdrop"></div>
        <div class="bulk-delete-modal__dialog">
            <div class="bulk-delete-modal__content">
                <h5 class="bulk-delete-modal__title" id="bulkDeleteUsersModalTitle">Удаление пользователей</h5>
                <p class="bulk-delete-modal__text" id="bulkDeleteUsersModalText">Удалить выбранных пользователей?</p>
                <div class="bulk-delete-modal__actions">
                    <button type="button" class="btn efds-btn efds-btn--primary" id="bulkDeleteUsersModalCancel">Отмена</button>
                    <button type="button" class="btn efds-btn efds-btn--danger" id="bulkDeleteUsersModalConfirm">Удалить</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #5a5c69;
        border-bottom: 2px solid #e3e6f0;
    }

    
   
    
    .table td {
        font-size: 14px;
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .badge {
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .card-header {
        background-color: white;
        border-bottom: 1px solid #e3e6f0;
        color: white;
    }
    .form-control, .form-select {
        background-color: white;
        border-color: #ced4da;
    }

    .table .user-checkbox-col { width: 40px; min-width: 40px; }

    .bulk-delete-modal {
        position: fixed;
        inset: 0;
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .bulk-delete-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
    }
    .bulk-delete-modal__dialog {
        position: relative;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        min-width: 320px;
        max-width: 90vw;
    }
    .bulk-delete-modal__content { padding: 24px; }
    .bulk-delete-modal__title { margin: 0 0 12px 0; font-size: 18px; font-weight: 600; }
    .bulk-delete-modal__text { margin: 0 0 20px 0; font-size: 15px; color: #333; }
    .bulk-delete-modal__actions { display: flex; gap: 12px; justify-content: flex-end; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.user-row').forEach(function(row){
        row.addEventListener('click', function(){
            const href = this.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });

    var bulkDeleteUsersBtn = document.getElementById('bulkDeleteUsersBtn');
    var bulkDeleteUsersForm = document.getElementById('bulkDeleteUsersForm');
    var bulkDeleteUsersFormIds = document.getElementById('bulkDeleteUsersFormIds');
    var bulkDeleteUsersModal = document.getElementById('bulkDeleteUsersModal');
    var bulkDeleteUsersModalText = document.getElementById('bulkDeleteUsersModalText');
    var bulkDeleteUsersModalCancel = document.getElementById('bulkDeleteUsersModalCancel');
    var bulkDeleteUsersModalConfirm = document.getElementById('bulkDeleteUsersModalConfirm');

    function updateBulkDeleteUsersButtonVisibility() {
        var container = document.getElementById('users-table-container');
        if (!container) return;
        var checked = container.querySelectorAll('.user-checkbox:checked');
        if (bulkDeleteUsersBtn) bulkDeleteUsersBtn.style.display = checked.length ? 'inline-block' : 'none';
        var selectAll = document.getElementById('selectAllUsers');
        if (selectAll) {
            var all = container.querySelectorAll('.user-checkbox');
            selectAll.checked = all.length > 0 && all.length === checked.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
        }
    }

    function toggleSelectAllUsers() {
        var selectAll = document.getElementById('selectAllUsers');
        var container = document.getElementById('users-table-container');
        if (!selectAll || !container) return;
        container.querySelectorAll('.user-checkbox').forEach(function(cb) { cb.checked = selectAll.checked; });
        updateBulkDeleteUsersButtonVisibility();
    }

    document.addEventListener('change', function(e) {
        if (e.target.closest('#users-table-container') && e.target.classList.contains('user-checkbox')) updateBulkDeleteUsersButtonVisibility();
        if (e.target.id === 'selectAllUsers') toggleSelectAllUsers();
    });

    if (bulkDeleteUsersBtn && bulkDeleteUsersForm && bulkDeleteUsersFormIds && bulkDeleteUsersModal) {
        bulkDeleteUsersBtn.addEventListener('click', function() {
            var container = document.getElementById('users-table-container');
            if (!container) return;
            var ids = [];
            container.querySelectorAll('.user-checkbox:checked').forEach(function(cb) {
                if (cb.value) ids.push(cb.value);
            });
            if (ids.length === 0) return;
            if (bulkDeleteUsersModalText) bulkDeleteUsersModalText.textContent = 'Удалить выбранных пользователей (' + ids.length + ')?';
            bulkDeleteUsersModal.style.display = 'flex';
            bulkDeleteUsersModal.setAttribute('data-pending-ids', ids.join(','));
        });
        function closeBulkDeleteUsersModal() {
            if (bulkDeleteUsersModal) bulkDeleteUsersModal.style.display = 'none';
        }
        if (bulkDeleteUsersModalCancel) bulkDeleteUsersModalCancel.addEventListener('click', closeBulkDeleteUsersModal);
        if (bulkDeleteUsersModal.querySelector('.bulk-delete-modal__backdrop')) {
            bulkDeleteUsersModal.querySelector('.bulk-delete-modal__backdrop').addEventListener('click', closeBulkDeleteUsersModal);
        }
        if (bulkDeleteUsersModalConfirm) bulkDeleteUsersModalConfirm.addEventListener('click', function() {
            var idsStr = bulkDeleteUsersModal.getAttribute('data-pending-ids') || '';
            var ids = idsStr ? idsStr.split(',').filter(Boolean) : [];
            closeBulkDeleteUsersModal();
            if (ids.length === 0) return;
            bulkDeleteUsersFormIds.innerHTML = ids.map(function(id) {
                return '<input type="hidden" name="ids[]" value="' + id + '">';
            }).join('');
            bulkDeleteUsersForm.submit();
        });
    }

    // Обновление пер_page через селектор
    window.updateUsersPerPage = function(perPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', '1'); // при смене количества записей возвращаемся на первую страницу
        window.location.href = url.toString();
    };

    // Применение фильтров
    const filterForm = document.getElementById('usersFilterForm');
    if (filterForm) {
        const qInput = filterForm.querySelector('input[name="q"]');
        const statusSelect = filterForm.querySelector('select[name="status"]');
        const roleSelect = filterForm.querySelector('select[name="role"]');

        const submitForm = () => filterForm.submit();

        if (qInput) {
            qInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitForm();
                }
            });
        }
        if (statusSelect) {
            statusSelect.addEventListener('change', submitForm);
        }
        if (roleSelect) {
            roleSelect.addEventListener('change', submitForm);
        }
    }
});
</script>
@endsection
