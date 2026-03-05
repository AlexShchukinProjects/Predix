@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <h1 class="h4 mb-2">Системы / подсистемы</h1>
        <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 text-nowrap">Тип ВС</label>
            <select name="aircraft_type_id" id="aircraft_type_id" class="form-select form-select-sm" style="width: auto; min-width: 180px;" aria-label="Тип ВС">
                <option value="">— все —</option>
                @foreach($aircraftTypes ?? [] as $type)
                    <option value="{{ $type->id }}" {{ (int)($selectedAircraftTypeId ?? 0) === (int)$type->id ? 'selected' : '' }}>
                        {{ $type->name_rus ?? $type->icao ?? $type->id }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row rel-systems-four-columns">
        <!-- Левая колонка: список систем -->
        <div class="rel-systems-col">
            <div class="rel-systems-card">
                <div class="rel-systems-header">
                    <span class="rel-systems-title">Системы</span>
                </div>
                <div class="rel-systems-list">
                    @forelse($systems as $system)
                        @php
                            $isActive = $selectedSystem === $system->system_name;
                            $urlParams = ['system' => $system->system_name];
                            if (isset($selectedAircraftTypeId) && $selectedAircraftTypeId !== null && $selectedAircraftTypeId !== '') {
                                $urlParams['aircraft_type_id'] = $selectedAircraftTypeId;
                            }
                            $url = route('modules.reliability.settings.systems.index', $urlParams);
                        @endphp
                        <a href="{{ $url }}"
                           class="rel-system-item {{ $isActive ? 'active' : '' }}">
                            <span class="rel-system-name">{{ $system->system_name }}</span>
                            <button type="button"
                                    class="rel-system-edit-btn"
                                    data-system-id="{{ $system->id }}"
                                    data-system-name="{{ $system->system_name }}"
                                    data-rename-url="{{ route('modules.reliability.settings.systems.rename', $system->id) }}">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </a>
                    @empty
                        <div class="rel-system-item text-muted">Нет систем</div>
                    @endforelse
                </div>
                <div class="rel-systems-footer">
                    <button type="button"
                            class="btn-add-section-sidebar"
                            data-bs-toggle="modal"
                            data-bs-target="#createSystemModal">
                        + Добавить систему
                    </button>
                </div>
            </div>
        </div>

        <!-- Вторая колонка: подсистемы выбранной системы -->
        <div class="rel-systems-col">
            <div class="rel-subsystems-card">
                <div class="rel-subsystems-header">
                    <div class="rel-subsystems-title">
                        @if($selectedSystem)
                            {{ $selectedSystem }}
                        @else
                            Подсистемы
                        @endif
                    </div>
                </div>

                <div class="rel-subsystems-list">
                    @if($selectedSystem && $subsystems->count() > 0)
                        @foreach($subsystems as $item)
                            @php
                                $isActiveSubsystem = isset($selectedSubsystemId) && $selectedSubsystemId === $item->id;
                                $subUrlParams = ['system' => $selectedSystem, 'subsystem_id' => $item->id];
                                if (isset($selectedAircraftTypeId) && $selectedAircraftTypeId !== null && $selectedAircraftTypeId !== '') {
                                    $subUrlParams['aircraft_type_id'] = $selectedAircraftTypeId;
                                }
                                $subsystemUrl = route('modules.reliability.settings.systems.index', $subUrlParams);
                            @endphp
                            <a href="{{ $subsystemUrl }}" class="text-decoration-none text-reset">
                                <div class="rel-subsystem-row {{ $isActiveSubsystem ? 'rel-subsystem-row-active' : '' }}">
                                    <span class="rel-subsystem-drag"><i class="fas fa-grip-vertical"></i></span>
                                    <span class="rel-subsystem-label">
                                        {{ $item->subsystem_name ?: '—' }}
                                    </span>
                                    <button type="button"
                                            class="rel-subsystem-edit"
                                            data-subsystem-id="{{ $item->id }}"
                                            data-system-full="{{ $item->system_name }}"
                                            data-subsystem-full="{{ $item->subsystem_name }}"
                                            data-update-url="{{ route('modules.reliability.settings.systems.update', $item->id) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                </div>
                            </a>
                        @endforeach
                    @elseif(!$selectedSystem)
                        <div class="text-muted py-3 text-center rel-subsystems-list-placeholder">Выберите систему слева</div>
                    @else
                        <div class="text-muted py-3 text-center rel-subsystems-list-placeholder">Подсистем нет</div>
                    @endif
                </div>

                @if($selectedSystem)
                    <div class="rel-subsystems-footer">
                        <button type="button"
                                class="btn-add-section-sidebar"
                                data-system-full="{{ $selectedSystem }}"
                                data-bs-toggle="modal"
                                data-bs-target="#createSubsystemModal">
                            +  Добавить подсистему
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Третья колонка: агрегаты выбранной подсистемы -->
        <div class="rel-systems-col rel-column-aggregates">
            <div class="rel-subsystems-card">
                <div class="rel-subsystems-header">
                    <div class="rel-subsystems-title">
                        Агрегаты
                    </div>
                </div>
                <div class="rel-subsystems-list">
                    @if(isset($selectedSubsystemId) && $selectedSubsystemId && ($aggregates->count() > 0))
                        @foreach($aggregates as $agg)
                            <div class="rel-subsystem-row">
                                <span class="rel-subsystem-drag"><i class="fas fa-grip-vertical"></i></span>
                                <span class="rel-subsystem-label">
                                    {{ $agg->name }}
                                </span>
                                {{-- Карандаш на будущее, когда появится редактирование агрегатов --}}
                                <button type="button"
                                        class="rel-subsystem-edit"
                                        disabled>
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                            </div>
                        @endforeach
                    @elseif(isset($selectedSubsystemId) && $selectedSubsystemId)
                        <div class="text-muted py-3 text-center rel-subsystems-list-placeholder">Агрегатов нет</div>
                    @else
                        <div class="text-muted py-3 text-center rel-subsystems-list-placeholder">Выберите подсистему по центру</div>
                    @endif
                </div>

                <div class="rel-subsystems-footer">
                    @if(isset($selectedSubsystemId) && $selectedSubsystemId)
                        <button type="button"
                                class="btn-add-section-sidebar"
                                data-bs-toggle="modal"
                                data-bs-target="#createAggregateModal"
                                data-system-full="{{ $selectedSystem }}"
                                @php
                                $selectedSubsystem = ($subsystems ?? collect())->firstWhere('id', $selectedSubsystemId);
                                @endphp
                                data-subsystem-full="{{ $selectedSubsystem->subsystem_name ?? '' }}"
                                data-failure-system-id="{{ $selectedSubsystemId }}">
                            +  Добавить агрегат
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Четвёртая колонка: агрегаты вне систем -->
        <div class="rel-systems-col">
            <div class="rel-subsystems-card">
                <div class="rel-subsystems-header">
                    <div class="rel-subsystems-title">
                        Агрегаты вне систем
                    </div>
                </div>
                <div class="rel-subsystems-list">
                    @if(isset($freeAggregates) && $freeAggregates->count() > 0)
                        @foreach($freeAggregates as $agg)
                            <div class="rel-subsystem-row">
                                <span class="rel-subsystem-label">{{ $agg->name }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted py-3 text-center rel-subsystems-list-placeholder">Агрегатов нет</div>
                    @endif
                </div>
                <div class="rel-subsystems-footer">
                    <button type="button"
                            class="btn-add-section-sidebar"
                            data-bs-toggle="modal"
                            data-bs-target="#createAggregateModal"
                            data-system-full="Агрегаты вне систем"
                            data-subsystem-full=""
                            data-failure-system-id="">
                        + Добавить агрегат
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления системы -->
<div class="modal fade" id="createSystemModal" tabindex="-1" aria-labelledby="createSystemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSystemModalLabel">Добавить систему</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('modules.reliability.settings.systems.store') }}" id="createSystemForm" data-store-url="{{ route('modules.reliability.settings.systems.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="system_code" class="form-label">Код системы</label>
                        <input type="text" class="form-control" id="system_code" name="system_code" placeholder="01">
                    </div>
                    <div class="mb-3">
                        <label for="system_name_display" class="form-label">Название системы <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="system_name_display" name="system_name_display" required>
                    </div>
                    <!-- Скрытое поле, которое реально уходит в БД -->
                    <input type="hidden" name="system_name" id="createSystemHiddenName">
                    <input type="hidden" name="active" value="1">
                    <input type="hidden" name="aircraft_type_id" id="createSystemAircraftTypeId" value="{{ $selectedAircraftTypeId ?? '' }}">
                    <input type="hidden" name="_method" id="system_method" value="POST">
                </div>
                <div class="modal-footer">
                    <button type="submit" style="margin-left: 10px;" class="btn btn-primary">Сохранить</button>
                    <button type="button" style="margin-left: 10px;" class="btn btn-outline-primary" data-bs-dismiss="modal">Отмена</button>
              
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно добавления подсистемы -->
<div class="modal fade" id="createSubsystemModal" tabindex="-1" aria-labelledby="createSubsystemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSubsystemModalLabel">Добавить подсистему</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST"
                  action="{{ route('modules.reliability.settings.systems.store') }}"
                  id="createSubsystemForm"
                  data-store-url="{{ route('modules.reliability.settings.systems.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Система</label>
                        <input type="text"
                               class="form-control"
                               id="subsystem_system_display"
                               readonly>
                    </div>
                    <div class="mb-3">
                        <label for="subsystem_code" class="form-label">Код подсистемы</label>
                        <input type="text" class="form-control" id="subsystem_code" name="subsystem_code" placeholder="01">
                    </div>
                    <div class="mb-3">
                        <label for="subsystem_name_display" class="form-label">Название подсистемы <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subsystem_name_display" name="subsystem_name_display" required>
                    </div>
                    <!-- реальные поля для контроллера -->
                    <input type="hidden" name="system_name" id="subsystem_system_name">
                    <input type="hidden" name="subsystem_name" id="subsystem_hidden_name">
                    <input type="hidden" name="aircraft_type_id" id="createSubsystemAircraftTypeId" value="{{ $selectedAircraftTypeId ?? '' }}">
                    <input type="hidden" name="active" value="1">
                    <input type="hidden" name="_method" id="subsystem_form_method" value="POST">
                </div>
                <div class="modal-footer">
                <button type="submit" style="margin-left: 10px;" class="btn btn-primary">Сохранить</button>    
                <button type="button" style="margin-left: 10px;" class="btn btn-outline-primary" data-bs-dismiss="modal">Отмена</button>
                  
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно добавления агрегата -->
<div class="modal fade" id="createAggregateModal" tabindex="-1" aria-labelledby="createAggregateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAggregateModalLabel">Добавить агрегат</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST"
                  action="{{ route('modules.reliability.settings.aggregates.store') }}"
                  id="createAggregateForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Система</label>
                        <input type="text"
                               class="form-control"
                               id="aggregate_system_display"
                               readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Подсистема</label>
                        <input type="text"
                               class="form-control"
                               id="aggregate_subsystem_display"
                               readonly>
                    </div>
                    <div class="mb-3">
                        <label for="aggregate_code" class="form-label">Код агрегата</label>
                        <input type="text" class="form-control" id="aggregate_code" name="aggregate_code" placeholder="01">
                    </div>
                    <div class="mb-3">
                        <label for="aggregate_name_display" class="form-label">Название агрегата <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="aggregate_name_display" name="aggregate_name_display" required>
                    </div>
                    <input type="hidden" name="failure_system_id" id="aggregate_failure_system_id">
                    <input type="hidden" name="aircraft_type_id" id="createAggregateAircraftTypeId" value="{{ $selectedAircraftTypeId ?? '' }}">
                </div>
                <div class="modal-footer">
                    <button type="submit" style="margin-left: 10px;" class="btn btn-primary">Сохранить</button>
                    <button type="button" style="margin-left: 10px;" class="btn btn-outline-primary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>

  
.rel-systems-card {
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
}


.btn-add-section-sidebar {
    background: #10b981;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;

}


.rel-systems-header {
    padding: 10px 16px;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    font-size: 14px;
}

.rel-systems-list {
    padding: 8px;
    max-height: 520px;
    overflow-y: auto;
}

.rel-system-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    margin-bottom: 6px;
    border-radius: 6px;
    border: 1px solid transparent;
    background-color: #f5f7fa;
    color: #1f2933;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.15s ease;
}

.rel-system-item:hover {
    background-color: #e5edff;
}

.rel-system-item.active {
    background-color: #1E64D4;
    color: #ffffff;
}

.rel-system-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rel-system-edit-btn {
    border: none;
    background: transparent;
    color: inherit;
    padding: 0;
    margin-left: 8px;
    cursor: pointer;
}

.rel-system-edit-btn i {
    font-size: 13px;
}

.rel-systems-footer {
    margin-top: auto;
    padding: 10px;
    border-top: 1px solid #e5e7eb;
}

/* Строка из 4 колонок: жёсткая grid-сетка, контент не выходит за границы */
.rel-systems-four-columns {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
    width: 100%;
}

.rel-systems-col {
    min-width: 0;
}

.rel-subsystems-card {
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 16px 18px;
    width: 100%;
    min-width: 0;
    max-width: 100%;
    box-sizing: border-box;
}

/* Подсказки в списках: не растягивать по ширине, держать в границах колонки */
.rel-subsystems-list-placeholder {
    flex-shrink: 0;
    min-height: 2.5rem;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.rel-column-aggregates .rel-subsystems-list {
    min-height: 120px;
}

.main_screen {
    width: 90%;
}

.rel-subsystems-header {
    margin-bottom: 12px;
}

.rel-subsystems-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2933;
}

.rel-subsystems-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
    max-width: 100%;
}

.rel-subsystem-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    background-color: #f9fafb;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.rel-subsystem-row-active {
    background-color: #1E64D4;
    color: #ffffff;
}

.rel-subsystem-drag {
    color: #9aa5b1;
    cursor: grab;
    width: 18px;
    text-align: center;
}

.rel-subsystem-edit {
    color: #9aa5b1;
    text-decoration: none;
    padding: 4px;
    border: none;
    background: transparent;
    cursor: pointer;
    outline: none;
}

.rel-subsystem-edit:hover {
    color: #1E64D4;
}

.rel-subsystem-edit:focus {
    outline: none;
    box-shadow: none;
}

.rel-subsystems-footer {
    margin-top: 14px;
    text-align: left;
}

.rel-subsystem-add-btn {
    padding: 6px 18px;
    font-size: 13px;
}

.rel-subsystem-label {
    flex: 1;
    font-size: 13px;
    color: #1f2933;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // При смене типа ВС — переход на ту же страницу с выбранным фильтром
    const aircraftTypeSelect = document.getElementById('aircraft_type_id');
    if (aircraftTypeSelect) {
        aircraftTypeSelect.addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            const val = this.value;
            if (val) {
                params.set('aircraft_type_id', val);
            } else {
                params.delete('aircraft_type_id');
            }
            window.location.search = params.toString();
        });
    }

    const createSystemForm = document.getElementById('createSystemForm');
    const addSystemBtn = document.querySelector('.rel-systems-footer button[data-bs-target="#createSystemModal"]');
    const createSubsystemForm = document.getElementById('createSubsystemForm');
    // Кнопка "Добавить подсистему" теперь имеет класс btn-add-section-sidebar,
    // поэтому ищем её по data-bs-target, а не по старому классу.
    const addSubsystemBtn = document.querySelector('.rel-subsystems-footer button[data-bs-target="#createSubsystemModal"]');
        const createAggregateForm = document.getElementById('createAggregateForm');
        const addAggregateButtons = document.querySelectorAll('button[data-bs-target="#createAggregateModal"]');

    const codeInput = document.getElementById('system_code');
    const nameInput = document.getElementById('system_name_display');
    const hiddenName = document.getElementById('createSystemHiddenName');
    const modalTitle = document.getElementById('createSystemModalLabel');

    if (createSystemForm) {
        // Сохранение (создание или переименование)
        createSystemForm.addEventListener('submit', function (e) {
            const code = (codeInput.value || '').trim();
            const name = (nameInput.value || '').trim();

            if (!name) {
                e.preventDefault();
                nameInput.focus();
                return;
            }

            hiddenName.value = code ? `${code} - ${name}` : name;
        });
    }

    // Режим создания новой системы
    if (addSystemBtn && createSystemForm) {
        addSystemBtn.addEventListener('click', function () {
            createSystemForm.action = createSystemForm.getAttribute('data-store-url') || createSystemForm.action;
            if (modalTitle) {
                modalTitle.textContent = 'Добавить систему';
            }
            codeInput.value = '';
            nameInput.value = '';
            hiddenName.value = '';
            const systemAircraftTypeInput = document.getElementById('createSystemAircraftTypeId');
            if (systemAircraftTypeInput && aircraftTypeSelect) {
                systemAircraftTypeInput.value = aircraftTypeSelect.value || '';
            }

            // Для создания системы используем обычный POST
            const methodInput = createSystemForm.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.value = 'POST';
            }
        });
    }

    // Режим добавления подсистемы
    if (addSubsystemBtn && createSubsystemForm) {
        const systemDisplay = document.getElementById('subsystem_system_display');
        const systemHidden = document.getElementById('subsystem_system_name');
        const subCodeInput = document.getElementById('subsystem_code');
        const subNameInput = document.getElementById('subsystem_name_display');
        const subHiddenName = document.getElementById('subsystem_hidden_name');
        const subMethodInput = document.getElementById('subsystem_form_method');

        addSubsystemBtn.addEventListener('click', function () {
            const fullSystem = this.getAttribute('data-system-full') || '';
            systemDisplay.value = fullSystem;
            systemHidden.value = fullSystem;
            subCodeInput.value = '';
            subNameInput.value = '';
            subHiddenName.value = '';
            const subAircraftTypeInput = document.getElementById('createSubsystemAircraftTypeId');
            if (subAircraftTypeInput && aircraftTypeSelect) {
                subAircraftTypeInput.value = aircraftTypeSelect.value || '';
            }
            if (subMethodInput) {
                subMethodInput.value = 'POST';
            }
            createSubsystemForm.action = createSubsystemForm.getAttribute('data-store-url') || createSubsystemForm.action;
        });

        createSubsystemForm.addEventListener('submit', function (e) {
            const code = (subCodeInput.value || '').trim();
            const name = (subNameInput.value || '').trim();

            if (!name) {
                e.preventDefault();
                subNameInput.focus();
                return;
            }

            subHiddenName.value = code ? `${code} - ${name}` : name;
        });
    }

    // Режим добавления агрегата (как для агрегатов подсистем, так и "не в составе систем")
    if (addAggregateButtons.length > 0 && createAggregateForm) {
        const aggSystemDisplay = document.getElementById('aggregate_system_display');
        const aggSubsystemDisplay = document.getElementById('aggregate_subsystem_display');
        const aggCodeInput = document.getElementById('aggregate_code');
        const aggNameInput = document.getElementById('aggregate_name_display');
        const aggFailureSystemId = document.getElementById('aggregate_failure_system_id');

        const aggAircraftTypeInput = document.getElementById('createAggregateAircraftTypeId');
        addAggregateButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const systemFull = this.getAttribute('data-system-full') || '';
                const subsystemFull = this.getAttribute('data-subsystem-full') || '';
                const failureSystemId = this.getAttribute('data-failure-system-id') || '';

                aggSystemDisplay.value = systemFull;
                aggSubsystemDisplay.value = subsystemFull;
                aggCodeInput.value = '';
                aggNameInput.value = '';
                aggFailureSystemId.value = failureSystemId;
                if (aggAircraftTypeInput && aircraftTypeSelect) {
                    aggAircraftTypeInput.value = aircraftTypeSelect.value || '';
                }
            });
        });

        createAggregateForm.addEventListener('submit', function (e) {
            const name = (aggNameInput.value || '').trim();
            if (!name) {
                e.preventDefault();
                aggNameInput.focus();
            }
        });
    }

    // Режим редактирования подсистемы (карандаш в правой колонке)
    const editSubsystemButtons = document.querySelectorAll('.rel-subsystem-edit');
    editSubsystemButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (!createSubsystemForm) return;

            const fullSystem = this.getAttribute('data-system-full') || '';
            const fullSubsystem = this.getAttribute('data-subsystem-full') || '';
            const updateUrl = this.getAttribute('data-update-url');

            const systemDisplay = document.getElementById('subsystem_system_display');
            const systemHidden = document.getElementById('subsystem_system_name');
            const subCodeInput = document.getElementById('subsystem_code');
            const subNameInput = document.getElementById('subsystem_name_display');
            const subHiddenName = document.getElementById('subsystem_hidden_name');
            const subMethodInput = document.getElementById('subsystem_form_method');

            // Разбираем код/название подсистемы
            let code = '';
            let name = fullSubsystem;
            const parts = (fullSubsystem || '').split(' - ');
            if (parts.length > 1) {
                code = parts[0];
                name = parts.slice(1).join(' - ');
            }

            systemDisplay.value = fullSystem;
            systemHidden.value = fullSystem;
            subCodeInput.value = code;
            subNameInput.value = name;
            subHiddenName.value = fullSubsystem;

            if (subMethodInput) {
                subMethodInput.value = 'PATCH';
            }
            if (updateUrl) {
                createSubsystemForm.action = updateUrl;
            }

            const modalEl = document.getElementById('createSubsystemModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    });

    // Режим редактирования системы (карандаш)
    const editButtons = document.querySelectorAll('.rel-system-edit-btn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const fullName = this.getAttribute('data-system-name') || '';
            const renameUrl = this.getAttribute('data-rename-url');

            let code = '';
            let name = fullName;
            const parts = fullName.split(' - ');
            if (parts.length > 1) {
                code = parts[0];
                name = parts.slice(1).join(' - ');
            }

            codeInput.value = code;
            nameInput.value = name;
            hiddenName.value = fullName;

            if (renameUrl) {
                createSystemForm.action = renameUrl;
            }

            const systemAircraftTypeInput = document.getElementById('createSystemAircraftTypeId');
            if (systemAircraftTypeInput && aircraftTypeSelect) {
                systemAircraftTypeInput.value = aircraftTypeSelect.value || '';
            }

            // Для переименования системы нужен PATCH, чтобы совпасть с роутом
            const methodInput = createSystemForm.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.value = 'PATCH';
            }

            if (modalTitle) {
                modalTitle.textContent = 'Редактировать систему';
            }

            const modalEl = document.getElementById('createSystemModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    });
});
</script>
@endsection
