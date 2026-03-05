@extends('layout.main')

@section('content')
<div class="container-fluid">
    <h1 class="h4 mb-3">Добавить пользователя</h1>

    <div class="card user-form" style="width: 850px;">
        <form action="{{ route('admin.users.store') }}" method="POST" class="card-body">
            @csrf
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-6 left">
                    <div class="d-flex flex-column">
                        <div class="field mb-3">
                            <label class="form-label">Статус</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Активен</option>
                                <option value="blocked">Заблокирован</option>
                            </select>
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Логин</label>
                            <input type="text" name="login" class="form-control" value="{{ old('login') }}">
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">ФИО</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Подразделение</label>
                            <select name="department_id" class="form-select">
                                <option value="">—</option>
                                @foreach(\App\Models\Department::orderBy('name')->get() as $dep)
                                    <option value="{{ $dep->id }}" @selected(old('department_id') == $dep->id)>{{ $dep->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Должность</label>
                            <input type="text" name="position" class="form-control" value="{{ old('position') }}">
                        </div>
                        
                        <div class="field mb-3">
                            <label class="form-label">Табельный номер</label>
                            <input type="text" name="personnel_number" class="form-control" value="{{ old('personnel_number') }}">
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Мобильный телефон</label>
                            <input type="text" name="mobile_phone" class="form-control" value="{{ old('mobile_phone') }}">
                        </div>
                        <div class="field mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Пароль</label>
                                <button type="button" id="generate_password_btn" class="password-generate-btn">
                                    Сгенерировать
                                </button>
                            </div>
                            <input type="text" name="password" class="form-control" id="password_input" required autocomplete="off">
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Подтверждение пароля</label>
                            <input type="text" name="password_confirmation" class="form-control" required autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Роли</label>
                    @php $oldRoles = old('roles', []); @endphp
                    
                    @php $canAssignSuperAdmin = auth()->user()?->isSuperAdmin(); @endphp
                    <div class="roles-checkboxes">
                        @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                            @php
                                $isSuperAdminRole = $role->name === \App\Models\Role::SUPER_ADMIN_NAME;
                                $isDisabled = $isSuperAdminRole && !$canAssignSuperAdmin;
                            @endphp
                            <div class="role-checkbox-item {{ $isDisabled ? 'role-checkbox-item--locked' : '' }}"
                                 @if($isDisabled) title="Назначить роль «{{ $role->name }}» может только суперадминистратор" @endif>
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                       id="role_{{ $role->id }}" class="form-check-input"
                                       @checked(in_array($role->id, $oldRoles))
                                       @disabled($isDisabled)>
                                <label for="role_{{ $role->id }}" class="form-check-label">
                                    {{ $role->name }}
                                    @if($isDisabled)
                                        <i class="fas fa-lock ms-1 text-muted" style="font-size: 11px;" title="Только суперадминистратор может назначать эту роль"></i>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    
                    @if(\App\Models\Role::count() === 0)
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Роли не найдены
                        </div>
                    @endif
                </div>
            </div>
            <div class="efds-actions">
                <button type="submit" class="btn efds-btn efds-btn--primary">
                   
                    Создать
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn efds-btn efds-btn--outline-primary">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.user-form .left .form-control,
.user-form .left .form-select { 
    width: 400px !important;
    max-width: 400px !important; 
    background: #fff !important; 
}

/* Выпадающий список с множественным выбором */
.dropdown-multiselect {
    position: relative;
    width: 200px;
    max-width: 200px;
}

.dropdown-multiselect-button {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.375rem 0.75rem;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 38px;
}

.dropdown-multiselect-button:hover {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.dropdown-multiselect-button.active {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.dropdown-text {
    flex: 1;
    color: #495057;
    font-size: 1rem;
}

.dropdown-arrow {
    transition: transform 0.2s ease;
    color: #6c757d;
}

.dropdown-multiselect-button.active .dropdown-arrow {
    transform: rotate(180deg);
}

.dropdown-multiselect-content {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
    z-index: 1000;
    display: none;
    max-height: 300px;
    overflow: hidden;
}

.dropdown-multiselect-content.show {
    display: block;
}

.dropdown-multiselect-options {
    max-height: 250px;
    overflow-y: auto;
    padding: 0.5rem 0;
}

.dropdown-option {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    transition: background-color 0.15s ease;
    margin: 0;
    user-select: none;
}

.dropdown-option:hover {
    background-color: #f8f9fa;
}

.dropdown-option input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #dee2e6;
    border-radius: 3px;
    margin-right: 0.5rem;
    position: relative;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.dropdown-option input[type="checkbox"]:checked + .checkmark {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.dropdown-option input[type="checkbox"]:checked + .checkmark::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.option-text {
    flex: 1;
    font-size: 0.9rem;
    color: #495057;
}

.dropdown-option input[type="checkbox"]:checked + .checkmark + .option-text {
    font-weight: 500;
    color: #0d6efd;
}

/* Стили для чекбоксов ролей */
.roles-checkboxes {
    max-height: 350px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.75rem;
    background-color: #f8f9fa;
}

.role-checkbox-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    padding: 0.25rem 0;
}

.role-checkbox-item:last-child {
    margin-bottom: 0;
}

.role-checkbox-item .form-check-input {
    margin-right: 0.5rem;
    margin-top: 0;
    width: 1.1em;
    height: 1.1em;
    cursor: pointer;
}

.role-checkbox-item .form-check-label {
    cursor: pointer;
    font-size: 0.9rem;
    line-height: 1.4;
    color: #495057;
    margin-bottom: 0;
    flex: 1;
}

.role-checkbox-item .form-check-input:checked + .form-check-label {
    font-weight: 500;
    color: #0d6efd;
}

.role-checkbox-item--locked {
    opacity: 0.55;
    cursor: not-allowed;
}

.role-checkbox-item--locked:hover {
    background-color: transparent;
}

.role-checkbox-item--locked .form-check-input,
.role-checkbox-item--locked .form-check-label {
    cursor: not-allowed;
}

.role-checkbox-item:hover {
    background-color: rgba(13, 110, 253, 0.05);
    border-radius: 0.25rem;
    padding: 0.25rem 0.5rem;
    margin-left: -0.5rem;
    margin-right: -0.5rem;
}

.roles-checkboxes::-webkit-scrollbar {
    width: 6px;
}

.roles-checkboxes::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.roles-checkboxes::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.roles-checkboxes::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.field {
    margin-bottom: 1rem;
}

.btn-group-sm > .btn, .btn-sm {
    font-size: 0.775rem;
}

.password-generate-btn {
    border: none;
    background: transparent;
    padding: 0;
    margin-bottom: 4px;
    color: #0d6efd;
    cursor: pointer;
    font-size: 0.9rem;
}
</style>

<script>
function generateSecurePassword(length = 12) {
    const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const lower = 'abcdefghijkmnopqrstuvwxyz';
    const digits = '0123456789';
    const special = '!@#$%^&*()-_=+[]{}';

    // Гарантируем наличие всех типов символов
    let passwordChars = [
        upper[Math.floor(Math.random() * upper.length)],
        lower[Math.floor(Math.random() * lower.length)],
        digits[Math.floor(Math.random() * digits.length)],
        special[Math.floor(Math.random() * special.length)],
    ];

    const all = upper + lower + digits + special;

    for (let i = passwordChars.length; i < length; i++) {
        passwordChars.push(all[Math.floor(Math.random() * all.length)]);
    }

    // Перемешиваем символы
    for (let i = passwordChars.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [passwordChars[i], passwordChars[j]] = [passwordChars[j], passwordChars[i]];
    }

    return passwordChars.join('');
}

document.addEventListener('DOMContentLoaded', function() {
    const dropdownButton = document.getElementById('aircraft_types_dropdown');
    const dropdownContent = document.getElementById('aircraft_types_content');
    if (dropdownButton && dropdownContent) {
    const dropdownText = dropdownButton.querySelector('.dropdown-text');
    const checkboxes = dropdownContent.querySelectorAll('input[type="checkbox"]');
    
    dropdownButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleDropdown();
    });
    
    // Закрытие при клике вне списка
    document.addEventListener('click', function(e) {
        if (!dropdownButton.contains(e.target) && !dropdownContent.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Предотвращение закрытия при клике внутри списка
    dropdownContent.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    function toggleDropdown() {
        const isOpen = dropdownContent.classList.contains('show');
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }
    
    function openDropdown() {
        dropdownContent.classList.add('show');
        dropdownButton.classList.add('active');
    }
    
    function closeDropdown() {
        dropdownContent.classList.remove('show');
        dropdownButton.classList.remove('active');
    }
    
    // Обновление текста кнопки
    function updateDropdownText() {
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        if (checkedCount === 0) {
            dropdownText.textContent = 'Выберите типы ВС';
        } else if (checkedCount === 1) {
            const checkedCheckbox = Array.from(checkboxes).find(cb => cb.checked);
            const optionText = checkedCheckbox.parentElement.querySelector('.option-text').textContent;
            dropdownText.textContent = optionText;
        } else {
            dropdownText.textContent = `Выбрано: ${checkedCount} типов ВС`;
        }
    }
    
    // Обработка изменения чекбоксов
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateDropdownText();
        });
    });
    
    updateDropdownText();
    }

    // Генерация пароля
    const generateBtn = document.getElementById('generate_password_btn');
    const passwordInput = document.getElementById('password_input');
    const passwordConfirmInput = document.querySelector('input[name="password_confirmation"]');

    if (generateBtn && passwordInput) {
        generateBtn.addEventListener('click', function() {
            const newPassword = generateSecurePassword(12);
            passwordInput.value = newPassword;
            if (passwordConfirmInput) {
                passwordConfirmInput.value = newPassword;
            }
        });
    }
});
</script>
@endsection