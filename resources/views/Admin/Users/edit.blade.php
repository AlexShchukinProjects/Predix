@extends('layout.main')

@section('content')
<div class="container-fluid">
    <h1 class="h4 mb-3">Изменение пользователя</h1>

    <div class="card user-form" style="width: 850px;">
        @if(auth()->id() !== $user->id)
            <div class="card-body pb-0 d-flex justify-content-between align-items-center">
                <form action="{{ route('admin.users.login-as', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите авторизоваться под пользователем {{ $user->name }}?');">
                    @csrf
                    <button type="submit" class="btn efds-btn efds-btn--outline-primary user-actions-btn">
                        Авторизоваться под пользователем
                    </button>
                </form>

                <button type="button" class="btn efds-btn efds-btn--danger user-actions-btn" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                    Удалить пользователя
                </button>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="card-body">
            @csrf @method('PUT')
            
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
                                <option value="active" @selected($user->status==='active')>Активен</option>
                                <option value="blocked" @selected($user->status==='blocked')>Заблокирован</option>
                            </select>
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Логин</label>
                            <input type="text" name="login" class="form-control" value="{{ old('login', $user->login) }}">
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn efds-btn efds-btn--ghost" id="openChangePasswordModal">
                                <i class="fas fa-key me-1"></i>
                                Сменить пароль
                            </button>
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">ФИО</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Подразделение</label>
                            <select name="department_id" class="form-select">
                                <option value="">—</option>
                                @foreach(\App\Models\Department::orderBy('name')->get() as $dep)
                                    <option value="{{ $dep->id }}" @selected($user->department_id==$dep->id)>{{ $dep->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Должность</label>
                            <input type="text" name="position" class="form-control" value="{{ old('position', $user->position) }}">
                        </div>
                        
                        <div class="field mb-3">
                            <label class="form-label">Табельный номер</label>
                            <input type="text" name="personnel_number" class="form-control" value="{{ old('personnel_number', $user->personnel_number) }}">
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="field mb-3">
                            <label class="form-label">Мобильный телефон</label>
                            <input type="text" name="mobile_phone" class="form-control" value="{{ old('mobile_phone', $user->mobile_phone) }}">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">Роли</label>
                    @php $currentRoles = $user->roles()->pluck('id')->toArray(); @endphp
                    
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
                                       @checked(in_array($role->id, $currentRoles))
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
                
                    Сохранить
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn efds-btn efds-btn--outline-primary">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>

@if(auth()->id() !== $user->id)
<!-- Модальное окно подтверждения удаления пользователя -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Удаление пользователя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <p class="mb-1">Удалить пользователя <strong>{{ $user->name }}</strong>?</p>
                        <p class="text-danger small mb-0">Внимание: это действие нельзя отменить. Все данные пользователя будут удалены.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="efds-actions">
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn efds-btn efds-btn--danger">
                                                      Да, удалить
                        </button>
                    </form>   
                
                <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">
                        Отмена
                    </button>
                   
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<style>
.user-form .left .form-control,
.user-form .left .form-select { 
    width: 400px !important;
    max-width: 400px !important; 
    background: #fff !important; 
}

.user-actions-btn {
    height: 40px;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    margin-right: 0px;
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

.role-checkbox-item:hover {
    background-color: rgba(13, 110, 253, 0.05);
    border-radius: 0.25rem;
    padding: 0.25rem 0.5rem;
    margin-left: -0.5rem;
    margin-right: -0.5rem;
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

#deleteUserModal .modal-footer {
    justify-content: flex-start;
}

#deleteUserModal .efds-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

#deleteUserModal .efds-actions .btn {
    height: 40px;
    display: inline-flex;
    align-items: center;
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

.input-group {

    width: 400px;
    padding-left: 20px;

}
.form-label {
   
    padding-left: 20px;
}

/* В модальном окне смены пароля выравниваем подписи по левому краю, как у кнопок */
#changePasswordModal .form-label {
    padding-left: 0;
}

/* И инпуты в модалке без дополнительного отступа и фиксированной ширины */
#changePasswordModal .input-group {
    width: 100%;
    padding-left: 0;
}

</style>

<script>
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
    // Генератор пароля
    const genBtn = document.getElementById('generatePasswordBtn');
    const copyBtn = document.getElementById('copyPasswordBtn');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleConfirmBtn = document.getElementById('togglePasswordConfirmBtn');
    if (genBtn) {
        genBtn.addEventListener('click', function() {
            const pwd = generateStrongPassword();
            const p1 = document.getElementById('password');
            const p2 = document.getElementById('password_confirmation');
            if (p1) p1.value = pwd;
            if (p2) p2.value = pwd;
            updateRulesState(pwd);
        });
    }

    // Клиентская валидация совпадения паролей
    const form = document.getElementById('passwordResetForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const p1 = document.getElementById('password');
            const p2 = document.getElementById('password_confirmation');
            // Если пользователь вводит пароль вручную — проверяем совпадение
            if (p1 && p1.value.length > 0) {
                if (!p2 || p1.value !== p2.value) {
                    e.preventDefault();
                    markFieldInvalid(p1);
                    markFieldInvalid(p2);
                    showInlineError(p2, 'Пароль и подтверждение не совпадают');
                    return false;
                }
                // Проверим правила сложности на клиенте
                if (!validateStrength(p1.value)) {
                    e.preventDefault();
                    markFieldInvalid(p1);
                    const rules = document.getElementById('passwordRules');
                    if (rules) rules.classList.add('invalid');
                    return false;
                }
            }
            // Очистка стилей при корректной отправке
            clearInvalid(p1); clearInvalid(p2);
        });
    }

    if (copyBtn) {
        copyBtn.addEventListener('click', async function() {
            const p1 = document.getElementById('password');
            if (p1 && p1.value) {
                try {
                    await navigator.clipboard.writeText(p1.value);
                    showTempTooltip(copyBtn, 'Скопировано');
                } catch (e) {
                    alert('Не удалось скопировать пароль');
                }
            }
        });
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            togglePasswordVisibility('password', toggleBtn);
        });
    }
    if (toggleConfirmBtn) {
        toggleConfirmBtn.addEventListener('click', function() {
            togglePasswordVisibility('password_confirmation', toggleConfirmBtn);
        });
    }

    function generateStrongPassword() {
        const length = 12; // генерируем 12+, что удовлетворяет минимуму 8
        const lowers = 'abcdefghijklmnopqrstuvwxyz';
        const uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const digits = '0123456789';
        const symbols = '!@#$%^&*()_+{}[]<>?';

        // Гарантируем наличие каждого типа
        let pwd = [
            pick(lowers),
            pick(uppers),
            pick(digits),
            pick(symbols)
        ];
        const all = lowers + uppers + digits + symbols;
        for (let i = pwd.length; i < length; i++) pwd.push(pick(all));
        // Перемешиваем
        for (let i = pwd.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [pwd[i], pwd[j]] = [pwd[j], pwd[i]];
        }
        return pwd.join('');
    }

    function pick(str) { return str[Math.floor(Math.random() * str.length)]; }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    }

    function showTempTooltip(el, text) {
        const tip = document.createElement('div');
        tip.textContent = text;
        tip.style.position = 'absolute';
        tip.style.background = '#333';
        tip.style.color = '#fff';
        tip.style.padding = '4px 8px';
        tip.style.borderRadius = '4px';
        tip.style.fontSize = '12px';
        tip.style.transform = 'translate(-50%, -150%)';
        tip.style.whiteSpace = 'nowrap';
        tip.style.zIndex = '10000';
        const rect = el.getBoundingClientRect();
        tip.style.left = (rect.left + rect.width / 2 + window.scrollX) + 'px';
        tip.style.top = (rect.top + window.scrollY) + 'px';
        document.body.appendChild(tip);
        setTimeout(() => tip.remove(), 1200);
    }

    // Подсветка правил при вводе
    const pwdInput = document.getElementById('password');
    if (pwdInput) {
        pwdInput.addEventListener('input', function(){
            updateRulesState(pwdInput.value);
        });
        updateRulesState(pwdInput.value || '');
    }

    function updateRulesState(val) {
        const hasInput = val.length > 0;
        const hasLen = val.length >= 8;
        const hasU = /[A-Z]/.test(val);
        const hasL = /[a-z]/.test(val);
        const hasN = /[0-9]/.test(val);
        const hasS = /[^A-Za-z0-9]/.test(val);
        const box = document.getElementById('passwordRules');
        const allOk = hasLen && hasU && hasL && hasN && hasS;
        if (box) box.style.display = !hasInput ? 'none' : (allOk ? 'none' : 'block');

        toggleRule('rule-length', hasLen);
        toggleRule('rule-upper', hasU);
        toggleRule('rule-lower', hasL);
        toggleRule('rule-number', hasN);
        toggleRule('rule-special', hasS);
        if (box) box.style.color = allOk ? '#198754' : '#dc3545';
    }

    function toggleRule(cls, ok) {
        const el = document.querySelector('.' + cls);
        if (!el) return;
        // Показываем только несоответствующие правила
        el.style.display = ok ? 'none' : 'list-item';
    }

    function validateStrength(val) {
        return val.length >= 8 && /[A-Z]/.test(val) && /[a-z]/.test(val) && /[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val);
    }

    function markFieldInvalid(input) {
        if (!input) return;
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
    }
    function clearInvalid(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
    function showInlineError(input, message) {
        if (!input) return;
        let feedback = input.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentElement.appendChild(feedback);
        }
        feedback.textContent = message;
    }

    // Копирование сгенерированного пароля
    const copyGeneratedBtn = document.getElementById('copyGeneratedBtn');
    if (copyGeneratedBtn) {
        copyGeneratedBtn.addEventListener('click', async function() {
            const el = document.getElementById('generatedPasswordValue');
            if (el) {
                try {
                    await navigator.clipboard.writeText(el.textContent);
                    showTempTooltip(copyGeneratedBtn, 'Скопировано');
                } catch (e) {}
            }
        });
    }
});
</script>
<style>
/* Карточка области смены пароля как на макете */
.pwd-card{
    background:#fff;
    border-radius:12px;
    padding:24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
}
.pwd-input .form-control{
    border-radius: 10px 0 0 10px;
    background:#f9fafb;
}
.pwd-input .btn{
    border-radius:0;
}
.pwd-input .btn:last-child{
    border-radius:0 10px 10px 0;
}
.pwd-submit{
    height: 44px;
    border-radius:10px;
    background: linear-gradient(90deg, #2575fc 0%, #1366ef 100%);
    border: none;
}
.pwd-submit:hover{ opacity:.95; }
.pwd-rules{ list-style: none; padding-left: 0; }
.pwd-rules .rule{ position: relative; padding-left: 18px; margin: 4px 0;}
.pwd-rules .rule:before{
    content: '\2022';
    position: absolute; left:0; top:0; color:#dc3545;
}
.password-hint{ border-left:3px solid #dc3545; padding-left:10px; }
</style>
<!-- Modal: Change Password -->
<div id="changePasswordModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1050;">
  <div class="modal-dialog" style="max-width:560px; margin:5% auto;">
    <div class="modal-content" style="border-radius:12px; overflow:hidden;">
      <div class="modal-header" style="background:#1E64D4; color:#fff; padding: 12px 16px; display:flex; align-items:center; justify-content:space-between;">
        <h5 class="modal-title">Смена пароля</h5>
        <button type="button" class="btn-close" id="closeChangePasswordModal"></button>
      </div>
      <div style="background-color: white;" class="modal-body">
        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" id="passwordResetFormModal" novalidate>
          @csrf
          <div class="mb-3">
            <label class="form-label">Новый пароль</label>
            <div class="input-group pwd-input">
              <input type="password" name="password" id="m_password" class="form-control" placeholder="Введите новый пароль" autocomplete="new-password">
              <button class="btn efds-btn efds-btn--outline-primary" type="button" id="m_togglePasswordBtn" title="Показать/скрыть"><i class="fas fa-eye"></i></button>
              <button class="btn efds-btn efds-btn--outline-primary" type="button" id="m_copyPasswordBtn" title="Скопировать"><i class="fas fa-copy"></i></button>
              <button class="btn efds-btn efds-btn--outline-primary" type="button" id="m_generatePasswordBtn">Сгенерировать</button>
            </div>
            <div class="password-hint invalid mt-2" id="m_passwordRules" style="display:none; font-size:12px; color:#dc3545;">
              <div>Пожалуйста, добавьте все необходимые символы, чтобы создать безопасный пароль.</div>
              <ul class="mt-2 mb-0 pwd-rules">
                <li class="rule m_rule-length">Минимум символов: 8</li>
                <li class="rule m_rule-upper">Одна заглавная буква</li>
                <li class="rule m_rule-lower">Одна строчная буква</li>
                <li class="rule m_rule-special">Один специальный символ</li>
                <li class="rule m_rule-number">Одна цифра</li>
              </ul>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Подтвердите новый пароль</label>
            <div class="input-group pwd-input">
              <input type="password" name="password_confirmation" id="m_password_confirmation" class="form-control" placeholder="Введите подтверждение нового пароля" autocomplete="new-password">
              <button class="btn efds-btn efds-btn--outline-primary" type="button" id="m_togglePasswordConfirmBtn" title="Показать/скрыть"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="efds-actions">
            <button type="submit" class="btn efds-btn efds-btn--primary">
              <i class="fas fa-key me-1"></i>
              Сменить пароль
            </button>
            <button type="button" class="btn efds-btn efds-btn--outline-primary" id="cancelChangePasswordModal">
              Отмена
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function(){
  const modal = document.getElementById('changePasswordModal');
  const openBtn = document.getElementById('openChangePasswordModal');
  const closeBtn = document.getElementById('closeChangePasswordModal');
  const cancelBtn = document.getElementById('cancelChangePasswordModal');
  const form = document.getElementById('passwordResetFormModal');
  const p1 = document.getElementById('m_password');
  const p2 = document.getElementById('m_password_confirmation');
  const rules = document.getElementById('m_passwordRules');

  // Fallback: ensure helpers exist in global scope
  if (typeof window.generateStrongPassword !== 'function') {
    window.generateStrongPassword = function(){
      const length = 12;
      const lowers = 'abcdefghijklmnopqrstuvwxyz';
      const uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      const digits = '0123456789';
      const symbols = '!@#$%^&*()_+{}[]<>?';
      function pick(str){ return str[Math.floor(Math.random()*str.length)]; }
      let pwd = [pick(lowers), pick(uppers), pick(digits), pick(symbols)];
      const all = lowers + uppers + digits + symbols;
      for (let i = pwd.length; i < length; i++) pwd.push(pick(all));
      for (let i = pwd.length - 1; i > 0; i--) { const j = Math.floor(Math.random()*(i+1)); [pwd[i], pwd[j]] = [pwd[j], pwd[i]]; }
      return pwd.join('');
    }
  }
  if (typeof window.togglePasswordVisibility !== 'function') {
    window.togglePasswordVisibility = function(inputId, btn){
      const input = document.getElementById(inputId);
      if (!input) return;
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      const icon = btn.querySelector('i');
      if (icon) { icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash'); }
    }
  }

  function show(){ modal.style.display='block'; }
  function hide(){ modal.style.display='none'; }
  openBtn?.addEventListener('click', show);
  closeBtn?.addEventListener('click', hide);
  cancelBtn?.addEventListener('click', hide);
  modal?.addEventListener('click', function(e){ if(e.target===modal) hide(); });

  // Генерация/копирование/переключение видимости
  document.getElementById('m_generatePasswordBtn')?.addEventListener('click', function(){
    const pwd = window.generateStrongPassword();
    p1.value = pwd; p2.value = pwd; updateModalRules(pwd);
  });
  document.getElementById('m_copyPasswordBtn')?.addEventListener('click', async function(){
    if(p1.value){ try{ await navigator.clipboard.writeText(p1.value);}catch(e){} }
  });
  document.getElementById('m_togglePasswordBtn')?.addEventListener('click', function(){ window.togglePasswordVisibility('m_password', this); });
  document.getElementById('m_togglePasswordConfirmBtn')?.addEventListener('click', function(){ window.togglePasswordVisibility('m_password_confirmation', this); });

  p1?.addEventListener('input', function(){ updateModalRules(p1.value); });
  updateModalRules(p1?.value || '');

  form?.addEventListener('submit', function(e){
    if(p1.value.length>0){
      if(p1.value!==p2.value){ e.preventDefault(); markFieldInvalid(p1); markFieldInvalid(p2); return false; }
      if(!validateStrength(p1.value)){ e.preventDefault(); markFieldInvalid(p1); return false; }
    }
  });

  function updateModalRules(val){
    const hasInput = val.length>0;
    const hasLen = val.length>=8;
    const hasU = /[A-Z]/.test(val);
    const hasL = /[a-z]/.test(val);
    const hasN = /[0-9]/.test(val);
    const hasS = /[^A-Za-z0-9]/.test(val);
    const allOk = hasLen && hasU && hasL && hasN && hasS;
    if(rules) rules.style.display = !hasInput ? 'none' : (allOk ? 'none' : 'block');
    toggleModalRule('m_rule-length', hasLen);
    toggleModalRule('m_rule-upper', hasU);
    toggleModalRule('m_rule-lower', hasL);
    toggleModalRule('m_rule-number', hasN);
    toggleModalRule('m_rule-special', hasS);
  }
  function toggleModalRule(cls, ok){ const el = document.querySelector('.'+cls); if(el) el.style.display = ok? 'none':'list-item'; }
});
</script>