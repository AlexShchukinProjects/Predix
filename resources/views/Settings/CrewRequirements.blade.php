@extends("layout.main")

@section('content')

<style>
    .requirements-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
    }

    .requirements-table th,
    .requirements-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
        vertical-align: middle;
    }

    .requirements-table th {
        background-color: #f5f7fa;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .requirements-table th:first-child {
        position: sticky;
        left: 0;
        z-index: 20;
        background-color: #f5f7fa;
        min-width: 300px;
        text-align: left;
    }

    .btn-left {
        font-size: 16px;
        font-weight: 600;
    }

    .requirements-table td:first-child {
        position: sticky;
        left: 0;
        z-index: 15;
        background-color: white;
        text-align: left;
        font-weight: 500;
    }

    .category-header {
        background-color: #e3f2fd !important;
        font-weight: bold;
        text-align: left !important;
        padding: 12px 8px !important;
    }

    .requirement-row {
        background-color: white;
    }

    .requirement-row:hover {
        background-color: #f8f9fa;
    }

    /* Ensure the sticky first cell also highlights with the row */
    .requirement-row:hover td:first-child {
        background-color: #f8f9fa;
    }

    /* Requirement name hover to blue */
    .requirement-name:hover {
        color: #0d6efd;
    }

    .checkbox-cell {
        text-align: center;
        width: 110px;
    }

    .checkbox-cell input[type="checkbox"] {
        transform: scale(1.2);
        cursor: pointer;
    }

    .form-control{
        width: 100%;
    }

    .position-header {
        background-color: #f5f7fa;
        font-weight: bold;
        text-align: center;
        min-width: 110px;
        max-width: 250px;
        word-wrap: break-word;
    }

    .save-button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    .save-button:hover {
        background-color: #218838;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .table-container {
        overflow-x: auto;
        max-width: 100%;
        margin-top: 20px;
    }

    .page-header {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .page-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }

    .page-description {
        color: #666;
        font-size: 16px;
    }

    .alert {
        padding: 12px 16px;
        margin-bottom: 20px;
        border-radius: 4px;
        border: 1px solid transparent;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #545b62;
    }

    .btn-info {
        background-color: #17a2b8;
        color: white;
    }

    .btn-info:hover {
        background-color: #117a8b;
    }

    /* Кнопки как в форме "Добавление маршрута" */
    .btn-left {
        font-size: 16px;
        font-weight: 600;
    }

    /* Липкий футер модалки как на форме маршрута */
    .sticky-modal-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        padding-top: 10px;
        border-top: 1px solid #e9ecef;
        z-index: 5;
    }

    /* Явно выровнять кнопки в модалке добавления требования по левому краю */
    #addRequirementModal .modal-footer {
        display: flex !important;
        justify-content: flex-start !important;
        gap: 10px;
    }

    /* Затемнение модалки редактирования при подтверждении удаления */
    #editRequirementModal .modal-content { position: relative; }
    body.confirming-delete #editRequirementModal .modal-content::after {
        content: "";
        position: absolute;
        left: 0; top: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        border-radius: .3rem;
        pointer-events: auto;
        z-index: 1000;
    }
</style>

<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">Требования к подготовке сотрудников</h1>
        <p class="page-description">
            Установите требования к подготовке, документам и допускам для каждой должности. 
            Отметьте чекбоксы для тех требований, которые обязательны для конкретной должности.
        </p>
        
        <!-- Management buttons -->
        <div class="management-buttons" style="margin-top: 20px;">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addRequirementModal">
                <i class="fas fa-plus"></i> Добавить требование
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div style="margin-bottom: 20px; text-align: center;">
        <button type="button" class="btn btn-primary" onclick="selectAll()" style="margin-right: 10px;">
            <i class="fas fa-check-square"></i> Выбрать все
        </button>
        <button type="button" class="btn btn-secondary" onclick="clearAll()" style="margin-right: 10px;">
            <i class="fas fa-square"></i> Очистить все
        </button>
        <button type="button" class="btn btn-info" onclick="toggleAll()">
            <i class="fas fa-exchange-alt"></i> Инвертировать выбор
        </button>
    </div>

    <div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>Статистика:</strong>
                <span id="selectedCount">0</span> из <span id="totalCount">0</span> требований выбрано
            </div>
            <div>
                <strong>Должностей:</strong> <span id="positionCount">{{ count($positions) }}</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('crew-requirements.update') }}">
        @csrf
        
        <div class="table-container">
            <table class="requirements-table">
                <thead>
                    <tr>
                        <th>Требования</th>
                        <th style="min-width:120px;">Для всех типов ВС</th>
                        <th style="min-width:120px;">Предупреждение</th>
                        @foreach($positions as $positionName => $positionShort)
                            <th class="position-header">
                                <div>{{ $positionName }}</div>
                                <div style="font-size: 0.9em; font-weight: normal; color: #666;">({{ $positionShort }})</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($requirementTypes as $requirementType)
                        <tr>
                            <td class="category-header" colspan="{{ count($positions) + 3 }}">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>{{ $requirementType->name === 'Летные проверки' ? 'Летная проверка' : $requirementType->name }}</span>
                                </div>
                            </td>
                        </tr>
                        @foreach($requirementType->requirements as $requirement)
                            <tr class="requirement-row" draggable="true" 
                                data-id="{{ $requirement->id }}" 
                                data-short-name="{{ $requirement->short_name ?? '' }}"
                                data-name="{{ $requirement->name }}"
                                data-description="{{ $requirement->description }}"
                                data-type-id="{{ $requirement->requirement_type_id }}"
                                data-validity="{{ $requirement->validity_period_months ?? '' }}"
                                data-control-days="{{ $requirement->control_level_days ?? '' }}"
                                data-warning-days="{{ $requirement->warning_level_days ?? '' }}"
                                data-for-all="{{ $requirement->for_all_aircraft_types ? 1 : 0 }}">
                                <td>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span class="requirement-name" style="cursor:pointer;" onclick="openRequirementFromRow(this)">{{ $requirement->name }}</span>
                                    </div>
                                </td>
                                <td class="checkbox-cell" style="text-align: center;">
                                    @if($requirement->for_all_aircraft_types)
                                        <i class="fas fa-check-circle" style="color: #0d6efd; font-size: 18px;" title="Применимо для всех типов ВС"></i>
                                    @else
                                        <i class="fas fa-times-circle" style="color: #6c757d; font-size: 18px;" title="Применимо только для конкретных типов ВС"></i>
                                    @endif
                                </td>
                                <td class="checkbox-cell">
                                    {{ is_null($requirement->control_level_days) && is_null($requirement->warning_level_days) ? '-' : ((string)($requirement->control_level_days ?? '-')) . '/' . ((string)($requirement->warning_level_days ?? '-')) }}
                                </td>
                                @foreach($positions as $positionName => $positionShort)
                                    <td class="checkbox-cell">
                                        @php
                                            // Используем requirement_id вместо имени для избежания проблем с кодировкой
                                            $checkboxName = 'req_' . $requirement->id . '_' . $positionShort;
                                            $searchKey = $positionShort . '_' . $requirement->name;
                                            $isChecked = $savedRequirements->get($searchKey) && $savedRequirements->get($searchKey)->required;
                                        @endphp
                                        <input type="checkbox" 
                                               name="{{ $checkboxName }}"
                                               id="{{ $checkboxName }}"
                                               value="on"
                                               {{ $isChecked ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="save-button">
                <i class="fas fa-save"></i> Сохранить требования
            </button>
        </div>
    </form>
</div>

<!-- Add Requirement Modal -->
<div class="modal fade" id="addRequirementModal" tabindex="-1" aria-labelledby="addRequirementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRequirementModalLabel">Добавить требование</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('crew-requirements.requirements.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="requirement_type_id" class="form-label">Тип требования</label>
                        <select class="form-control" id="requirement_type_id" name="requirement_type_id" required>
                            <option value="">Выберите тип</option>
                            @foreach($requirementTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="requirement_name" class="form-label">Название требования</label>
                        <input type="text" class="form-control" id="requirement_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="requirement_short_name" class="form-label">Краткое название требования</label>
                        <input type="text" class="form-control" id="requirement_short_name" name="short_name" placeholder="Напр.: ВЛЭК">
                    </div>
                    <div class="mb-3">
                        <label for="requirement_description" class="form-label">Описание</label>
                        <textarea class="form-control" id="requirement_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="validity_period_months" class="form-label">Срок действия</label>
                        <select class="form-control" id="validity_period_months" name="validity_period_months">
                            <option value="">Не указано</option>
                            <option value="0">Бессрочно</option>
                            <option value="1">1 месяц</option>
                            <option value="2">2 месяца</option>
                            <option value="3">3 месяца</option>
                            <option value="4">4 месяца</option>
                            <option value="5">5 месяцев</option>
                            <option value="6">6 месяцев</option>
                            <option value="7">7 месяцев</option>
                            <option value="8">8 месяцев</option>
                            <option value="9">9 месяцев</option>
                            <option value="10">10 месяцев</option>
                            <option value="11">11 месяцев</option>
                            <option value="12">12 месяцев</option>
                            <option value="24">24 месяца</option>
                            <option value="36">36 месяцев</option>
                            <option value="48">48 месяцев</option>
                            <option value="60">60 месяцев</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="control_level_days" class="form-label">Контрольный уровень, дней до окончания действия</label>
                        <input type="number" class="form-control" id="control_level_days" name="control_level_days" min="0" step="1">
                    </div>
                    <div class="mb-3">
                        <label for="warning_level_days" class="form-label">Предупредительный уровень, дней до окончания действия</label>
                        <input type="number" class="form-control" id="warning_level_days" name="warning_level_days" min="0" step="1">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="hidden" name="for_all_aircraft_types" value="0">
                            <input class="form-check-input" type="checkbox" id="for_all_aircraft_types" name="for_all_aircraft_types" value="1" checked>
                            <label class="form-check-label" for="for_all_aircraft_types">
                                Для всех типов ВС?
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer sticky-modal-footer" style="display:flex; justify-content: flex-start; gap: 10px; align-items: center;">
                    <button type="submit" style="background-color: #1E64D4; border-color: #1E64D4;" class="btn btn-primary btn-left">Добавить</button>
                    <button type="button" style="color: #1E64D4; border: 1px solid #1E64D4;" class="btn btn-outline-primary btn-left" data-bs-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit Requirement Modal -->
<div class="modal fade" id="editRequirementModal" tabindex="-1" aria-labelledby="editRequirementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRequirementModalLabel">Редактировать требование</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRequirementForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_requirement_type_id" class="form-label">Тип требования</label>
                        <select class="form-control" id="edit_requirement_type_id" name="requirement_type_id" required>
                            @foreach($requirementTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_requirement_name" class="form-label">Название требования</label>
                        <input type="text" class="form-control" id="edit_requirement_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_requirement_short_name" class="form-label">Краткое название требования</label>
                        <input type="text" class="form-control" id="edit_requirement_short_name" name="short_name" placeholder="Напр.: ВЛЭК">
                    </div>
                    <div class="mb-3">
                        <label for="edit_requirement_description" class="form-label">Описание</label>
                        <textarea class="form-control" id="edit_requirement_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_validity_period_months" class="form-label">Срок действия</label>
                        <select class="form-control" id="edit_validity_period_months" name="validity_period_months">
                            <option value="">Не указано</option>
                            <option value="0">Бессрочно</option>
                            <option value="1">1 месяц</option>
                            <option value="2">2 месяца</option>
                            <option value="3">3 месяца</option>
                            <option value="4">4 месяца</option>
                            <option value="5">5 месяцев</option>
                            <option value="6">6 месяцев</option>
                            <option value="7">7 месяцев</option>
                            <option value="8">8 месяцев</option>
                            <option value="9">9 месяцев</option>
                            <option value="10">10 месяцев</option>
                            <option value="11">11 месяцев</option>
                            <option value="12">12 месяцев</option>
                            <option value="24">24 месяца</option>
                            <option value="36">36 месяцев</option>
                            <option value="48">48 месяцев</option>
                            <option value="60">60 месяцев</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_control_level_days" class="form-label">Контрольный уровень, дней до окончания действия</label>
                        <input type="number" class="form-control" id="edit_control_level_days" name="control_level_days" min="0" step="1">
                    </div>
                    <div class="mb-3">
                        <label for="edit_warning_level_days" class="form-label">Предупредительный уровень, дней до окончания действия</label>
                        <input type="number" class="form-control" id="edit_warning_level_days" name="warning_level_days" min="0" step="1">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="hidden" name="for_all_aircraft_types" value="0">
                            <input class="form-check-input" type="checkbox" id="edit_for_all_aircraft_types" name="for_all_aircraft_types" value="1">
                            <label class="form-check-label" for="edit_for_all_aircraft_types">
                                Для всех типов ВС?
                            </label>
                        </div>
                    </div>
                </div>
            </form>
                <div class="modal-footer sticky-modal-footer" style="display:flex; justify-content: space-between; align-items: center;">
                    <div>
                        <button type="submit" class="btn btn-primary btn-left" form="editRequirementForm">
                            Сохранить
                        </button>
                        <button type="button" style="color: #1E64D4; border: 1px solid #1E64D4;" class="btn btn-outline-primary btn-left" data-bs-dismiss="modal">Отмена</button>
                    </div>
                    <form id="deleteRequirementForm" method="POST" onsubmit="return confirmDeleteRequirement(event)">
                        @csrf
                        @method('DELETE')
                        <button style="background-color: #dc3545; border-color: #dc3545; color: white; font-weight: 500; font-size: 16px;" type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
                    </form>
                </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Для данного требования заведены данные у сотрудников: <strong id="impactCount">0</strong>.
                После удаления они станут недоступны. Удалить требование?
            </div>
            <div class="modal-footer" style="display:flex; justify-content: flex-end; gap: 10px;">
                <button style="color: #1E64D4; border-color: #1E64D4;" type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Отмена</button>
                <button style="background-color: #dc3545; border-color: #dc3545; color: white; font-weight: 500; font-size: 16px;" type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Удалить
                </button>
            </div>
        </div>
    </div>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Добавляем обработчик для сохранения состояния чекбоксов
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    
    // Обновляем статистику при загрузке
    updateStatistics();
    
    checkboxes.forEach(checkbox => {
        // Сохраняем состояние при изменении
        checkbox.addEventListener('change', function() {
            localStorage.setItem(this.name, this.checked);
            updateStatistics();
        });
    });

    // Функция для обновления статистики
    function updateStatistics() {
        const totalCheckboxes = checkboxes.length;
        const selectedCheckboxes = Array.from(checkboxes).filter(cb => cb.checked).length;
        
        document.getElementById('totalCount').textContent = totalCheckboxes;
        document.getElementById('selectedCount').textContent = selectedCheckboxes;
    }

    // Добавляем функциональность для массового выбора по категориям
    const categoryHeaders = document.querySelectorAll('.category-header');
    
    categoryHeaders.forEach(header => {
        const categoryRow = header.parentElement;
        const requirementRows = [];
        let currentRow = categoryRow.nextElementSibling;
        
        // Собираем все строки требований для этой категории
        while (currentRow && !currentRow.querySelector('.category-header')) {
            if (currentRow.querySelector('.requirement-row')) {
                requirementRows.push(currentRow);
            }
            currentRow = currentRow.nextElementSibling;
        }
        
        // Добавляем обработчик клика для выбора всех требований категории
        header.addEventListener('click', function() {
            const checkboxes = requirementRows.flatMap(row => 
                Array.from(row.querySelectorAll('input[type="checkbox"]'))
            );
            
            // Проверяем, все ли чекбоксы отмечены
            const allChecked = checkboxes.every(cb => cb.checked);
            
            // Переключаем состояние всех чекбоксов
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
                localStorage.setItem(cb.name, cb.checked);
            });
            
            updateStatistics();
        });
        
        // Добавляем подсказку
        header.style.cursor = 'pointer';
        header.title = 'Кликните для выбора/отмены всех требований этой категории';
    });

    // Добавляем функциональность для массового выбора по должностям
    const positionHeaders = document.querySelectorAll('.position-header');
    
    positionHeaders.forEach(header => {
        const positionIndex = Array.from(header.parentElement.children).indexOf(header);
        const checkboxes = Array.from(document.querySelectorAll(`td:nth-child(${positionIndex + 1}) input[type="checkbox"]`));
        
        header.addEventListener('click', function() {
            const allChecked = checkboxes.every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
                localStorage.setItem(cb.name, cb.checked);
            });
            
            updateStatistics();
        });
        
        header.style.cursor = 'pointer';
        header.title = 'Кликните для выбора/отмены всех требований для этой должности';
    });
    // Drag & Drop reordering
    const rows = document.querySelectorAll('tr.requirement-row');
    rows.forEach(row => row.setAttribute('draggable', 'true'));

    let dragSrcEl = null;
    let dragSectionHeader = null;

    document.addEventListener('dragstart', function (e) {
        const row = e.target.closest('tr.requirement-row');
        if (!row) return;
        dragSrcEl = row;
        dragSectionHeader = findSectionHeader(row);
        e.dataTransfer.effectAllowed = 'move';
    });

    document.addEventListener('dragover', function (e) {
        if (e.target.closest('tr.requirement-row')) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        }
    });

    document.addEventListener('drop', function (e) {
        const targetRow = e.target.closest('tr.requirement-row');
        if (!dragSrcEl || !targetRow) return;
        e.preventDefault();
        const sameSection = findSectionHeader(targetRow) === dragSectionHeader;
        if (!sameSection) return;
        const tbody = targetRow.parentElement;
        const rect = targetRow.getBoundingClientRect();
        const before = (e.clientY - rect.top) < rect.height / 2;
        if (before) tbody.insertBefore(dragSrcEl, targetRow); else tbody.insertBefore(dragSrcEl, targetRow.nextSibling);
        persistOrder(targetRow);
    });

    function findSectionHeader(row) {
        let p = row.previousElementSibling;
        while (p && !p.querySelector('.category-header')) p = p.previousElementSibling;
        return p;
    }

    function persistOrder(anyRowInSection) {
        const header = findSectionHeader(anyRowInSection);
        if (!header) return;
        const typeName = header.querySelector('.category-header span')?.textContent?.trim();
        const typeMap = {
            @foreach($requirementTypes as $type)
            "{{ $type->name === 'Летные проверки' ? 'Летная проверка' : $type->name }}": {{ $type->id }},
            @endforeach
        };
        const requirement_type_id = typeMap[typeName];
        if (!requirement_type_id) return;
        const order = [];
        let cur = header.nextElementSibling;
        while (cur && !cur.querySelector('.category-header')) {
            if (cur.classList.contains('requirement-row')) {
                const id = cur.getAttribute('data-id');
                if (id) order.push(parseInt(id));
            }
            cur = cur.nextElementSibling;
        }

        fetch("{{ route('crew-requirements.requirements.reorder') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ requirement_type_id, order })
        }).catch(() => {});
    }
});

function selectAll() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        localStorage.setItem(checkbox.name, true);
    });
    updateStatistics();
}

function clearAll() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        localStorage.setItem(checkbox.name, false);
    });
    updateStatistics();
}

function toggleAll() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = !checkbox.checked;
        localStorage.setItem(checkbox.name, checkbox.checked);
    });
    updateStatistics();
}

// CRUD functions (types отключены)
// Новая функция для безопасного открытия требования из строки таблицы
function openRequirementFromRow(element) {
    // Находим родительскую строку таблицы
    const row = element.closest('tr.requirement-row');
    if (!row) return;
    
    // Читаем данные из data-атрибутов
    const id = row.getAttribute('data-id');
    const name = row.getAttribute('data-name') || '';
    const description = row.getAttribute('data-description') || '';
    const typeId = row.getAttribute('data-type-id');
    
    // Для validity_period_months нужна особая обработка: пустая строка означает "Не указано"
    const validityAttr = row.getAttribute('data-validity');
    const validityPeriodMonths = validityAttr !== null && validityAttr !== '' ? validityAttr : '';
    
    const controlLevelDays = row.getAttribute('data-control-days') || '';
    const warningLevelDays = row.getAttribute('data-warning-days') || '';
    const forAllAircraftTypes = row.getAttribute('data-for-all') || 0;
    
    console.log('Opening requirement:', {id, validityAttr, validityPeriodMonths});
    
    // Вызываем оригинальную функцию
    openRequirement(id, name, description, typeId, validityPeriodMonths, controlLevelDays, warningLevelDays, forAllAircraftTypes);
}

function openRequirement(id, name, description, typeId, validityPeriodMonths, controlLevelDays, warningLevelDays, forAllAircraftTypes) {
    console.log('openRequirement called with validityPeriodMonths:', validityPeriodMonths, 'type:', typeof validityPeriodMonths);
    
    document.getElementById('edit_requirement_name').value = name;
    document.getElementById('edit_requirement_description').value = description || '';
    document.getElementById('edit_requirement_type_id').value = typeId;
    
    // Устанавливаем значение срока действия
    // Пустая строка = "Не указано", null = "Не указано", 0 = "Бессрочно", числа = месяцы
    const validitySelect = document.getElementById('edit_validity_period_months');
    if (validityPeriodMonths === '' || validityPeriodMonths === null || validityPeriodMonths === 'null') {
        validitySelect.value = ''; // Выбираем "Не указано"
    } else {
        validitySelect.value = validityPeriodMonths;
    }
    console.log('Set validity select to:', validitySelect.value);
    
    document.getElementById('edit_control_level_days').value = controlLevelDays || '';
    document.getElementById('edit_warning_level_days').value = warningLevelDays || '';
    document.getElementById('edit_for_all_aircraft_types').checked = forAllAircraftTypes == 1;
    
    // Заполняем краткое название из data-атрибута
    const row = document.querySelector(`tr.requirement-row[data-id="${id}"]`);
    if (row) {
        const shortName = row.getAttribute('data-short-name') || '';
        const input = document.getElementById('edit_requirement_short_name');
        if (input) input.value = shortName;
    }
    
    document.getElementById('editRequirementForm').action = `/settings/crew-requirements/requirements/${id}`;
    // set delete form action too
    const delForm = document.getElementById('deleteRequirementForm');
    if (delForm) delForm.action = `/settings/crew-requirements/requirements/${id}`;
    
    const modal = new bootstrap.Modal(document.getElementById('editRequirementModal'));
    modal.show();
}

// delete handled by deleteRequirementForm submit

let pendingDeleteForm = null;
let deleteModalInstance = null;

function confirmDeleteRequirement(e) {
    e.preventDefault();
    pendingDeleteForm = e.target;
    const action = pendingDeleteForm.action;
    const match = action.match(/requirements\/(\d+)/);
    const id = match ? match[1] : null;
    const modalEl = document.getElementById('deleteConfirmModal');
    if (!deleteModalInstance) deleteModalInstance = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
    if (!id) {
        document.getElementById('impactCount').textContent = '0';
        deleteModalInstance.show();
        return false;
    }
    document.body.classList.add('confirming-delete');
    fetch(`/settings/crew-requirements/requirements/${id}/impact`)
        .then(r => r.json())
        .then(data => {
            const count = data.count ?? 0;
            document.getElementById('impactCount').textContent = count;
            deleteModalInstance.show();
        })
        .catch(() => {
            document.getElementById('impactCount').textContent = '0';
            deleteModalInstance.show();
        });
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('confirmDeleteBtn');
    if (btn) {
        btn.addEventListener('click', function() {
            if (pendingDeleteForm) {
                // Hide modal before submit to avoid flicker
                if (deleteModalInstance) deleteModalInstance.hide();
                pendingDeleteForm.submit();
                pendingDeleteForm = null;
                document.body.classList.remove('confirming-delete');
            }
        });
    }
    const delModalEl = document.getElementById('deleteConfirmModal');
    delModalEl.addEventListener('hidden.bs.modal', function() {
        document.body.classList.remove('confirming-delete');
    });
});

</script>

@endsection
