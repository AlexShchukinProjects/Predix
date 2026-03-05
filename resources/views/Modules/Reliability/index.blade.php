@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
            
            </div>
        </div>
    </div>

    <!-- Панель фильтров -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('modules.reliability.index') }}" id="filtersForm">
                        <input type="hidden" name="tab" id="filterTab" value="{{ request('tab', 'failures') }}">
                        <div class="row g-3">
                            <!-- Первая строка фильтров -->
                            <div class="col-md-2">
                                <label class="form-label">Дата</label>
                                <input type="date" class="form-control form-control-sm filter-date-input" name="date_from" value="{{ request('date_from', \Carbon\Carbon::now()->subYear()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <input type="date" class="form-control form-control-sm filter-date-input" name="date_to" value="{{ request('date_to', \Carbon\Carbon::now()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">ID</label>
                                <input type="text" class="form-control form-control-sm" name="id" value="{{ request('id') }}" placeholder="">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Описание</label>
                                <input type="text" class="form-control form-control-sm" name="description" value="{{ request('description') }}" placeholder="">
                            </div>
                            @php
                                $aircraftTypesOptions = ($aircraftTypesForFilter ?? collect())->map(fn($t) => ['value' => $t, 'label' => $t])->values()->all();
                                $aircraftNumberOptions = collect($aircraftList ?? [])->map(fn($ac) => ['value' => $ac->RegN, 'label' => $ac->RegN, 'data_type' => $ac->Type ?? ''])->all();
                            @endphp
                            <div class="col-md-2">
                                <label class="form-label">Типы ВС</label>
                                <div class="filter-multiselect-wrap">
                                    <div class="filter-multiselect-trigger form-control form-control-sm" data-name="aircraft_type" data-placeholder="Все" tabindex="0" role="button" id="trigger_aircraft_type">
                                        <span class="filter-multiselect-label">{{ count((array)request('aircraft_type', [])) ? 'Выбрано: ' . count((array)request('aircraft_type', [])) : 'Все' }}</span>
                                    </div>
                                    <div class="filter-multiselect-values" data-name="aircraft_type">
                                        @foreach((array)request('aircraft_type', []) as $val)
                                            @if($val !== '')
                                                <input type="hidden" name="aircraft_type[]" value="{{ $val }}">
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <script type="application/json" id="options_aircraft_type">@json($aircraftTypesOptions)</script>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Номера ВС</label>
                                <div class="filter-multiselect-wrap">
                                    <div class="filter-multiselect-trigger form-control form-control-sm" data-name="aircraft_number" data-placeholder="Все" tabindex="0" role="button" id="trigger_aircraft_number">
                                        <span class="filter-multiselect-label">{{ count((array)request('aircraft_number', [])) ? 'Выбрано: ' . count((array)request('aircraft_number', [])) : 'Все' }}</span>
                                    </div>
                                    <div class="filter-multiselect-values" data-name="aircraft_number">
                                        @foreach((array)request('aircraft_number', []) as $val)
                                            @if($val !== '')
                                                <input type="hidden" name="aircraft_number[]" value="{{ $val }}">
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <script type="application/json" id="options_aircraft_number">@json($aircraftNumberOptions)</script>
                            </div>
                            
                            <!-- Вторая строка фильтров -->
                            @php
                                $systemOptions = ($failureSystems ?? collect())->map(fn($s) => ['value' => $s->system_name, 'label' => $s->system_name])->all();
                                $subsystemsFilter = collect($failureSubsystems ?? [])->unique(fn($i) => $i->system_name . '||' . $i->subsystem_name)->sortBy('subsystem_name')->values();
                                $subsystemOptions = $subsystemsFilter->map(fn($s) => ['value' => $s->subsystem_name, 'label' => $s->subsystem_name, 'data_system' => $s->system_name])->all();
                                $aggregateOptions = ($aggregateTypes ?? collect())->map(fn($a) => ['value' => $a->name, 'label' => $a->name])->all();
                                $detectionStageOptions = ($detectionStages ?? collect())->map(fn($s) => ['value' => (string)$s->id, 'label' => $s->name])->all();
                                $engineTypeOptions = ($engineTypes ?? collect())->map(fn($e) => ['value' => (string)$e->id, 'label' => ($e->code ? $e->code . ' - ' : '') . $e->name])->all();
                                $engineNumberOptions = ($engineNumbers ?? collect())->map(fn($e) => ['value' => (string)$e->id, 'label' => $e->number . ($e->engineType ? ' (' . $e->engineType->name . ')' : '')])->all();
                            @endphp
                            <div class="col-md-2">
                                <label class="form-label">Системы</label>
                                <div class="filter-multiselect-wrap">
                                    <div class="filter-multiselect-trigger form-control form-control-sm" data-name="system" data-placeholder="Все" tabindex="0" role="button">
                                        <span class="filter-multiselect-label">{{ count((array)request('system', [])) ? 'Выбрано: ' . count((array)request('system', [])) : 'Все' }}</span>
                                    </div>
                                    <div class="filter-multiselect-values" data-name="system">
                                        @foreach((array)request('system', []) as $val)
                                            @if($val !== '')<input type="hidden" name="system[]" value="{{ $val }}">@endif
                                        @endforeach
                                    </div>
                                </div>
                                <script type="application/json" id="options_system">@json($systemOptions)</script>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Подсистемы</label>
                                <div class="filter-multiselect-wrap">
                                    <div class="filter-multiselect-trigger form-control form-control-sm" data-name="subsystem" data-placeholder="Все" tabindex="0" role="button">
                                        <span class="filter-multiselect-label">{{ count((array)request('subsystem', [])) ? 'Выбрано: ' . count((array)request('subsystem', [])) : 'Все' }}</span>
                                    </div>
                                    <div class="filter-multiselect-values" data-name="subsystem">
                                        @foreach((array)request('subsystem', []) as $val)
                                            @if($val !== '')<input type="hidden" name="subsystem[]" value="{{ $val }}">@endif
                                        @endforeach
                                    </div>
                                </div>
                                <script type="application/json" id="options_subsystem">@json($subsystemOptions)</script>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Тип агрегата</label>
                                <div class="filter-multiselect-wrap">
                                    <div class="filter-multiselect-trigger form-control form-control-sm" data-name="aggregate_type" data-placeholder="Все" data-options-ajax="1" tabindex="0" role="button">
                                        <span class="filter-multiselect-label">{{ count((array)request('aggregate_type', [])) ? 'Выбрано: ' . count((array)request('aggregate_type', [])) : 'Все' }}</span>
                                    </div>
                                    <div class="filter-multiselect-values" data-name="aggregate_type">
                                        @foreach((array)request('aggregate_type', []) as $val)
                                            @if($val !== '')<input type="hidden" name="aggregate_type[]" value="{{ $val }}">@endif
                                        @endforeach
                                    </div>
                                </div>
                                <script type="application/json" id="options_aggregate_type">@json($aggregateOptions)</script>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Этап обнаружения</label>
                                <div class="filter-multiselect-wrap">
                                    <div class="filter-multiselect-trigger form-control form-control-sm" data-name="detection_stage" data-placeholder="Все" tabindex="0" role="button">
                                        <span class="filter-multiselect-label">{{ count((array)request('detection_stage', [])) ? 'Выбрано: ' . count((array)request('detection_stage', [])) : 'Все' }}</span>
                                    </div>
                                    <div class="filter-multiselect-values" data-name="detection_stage">
                                        @foreach((array)request('detection_stage', []) as $val)
                                            @if($val !== '')<input type="hidden" name="detection_stage[]" value="{{ $val }}">@endif
                                        @endforeach
                                    </div>
                                </div>
                                <script type="application/json" id="options_detection_stage">@json($detectionStageOptions)</script>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Типы двигателей</label>
                                <div class="filter-multiselect-wrap">
                                    <div class="filter-multiselect-trigger form-control form-control-sm" data-name="engine_type" data-placeholder="Все" tabindex="0" role="button">
                                        <span class="filter-multiselect-label">{{ count((array)request('engine_type', [])) ? 'Выбрано: ' . count((array)request('engine_type', [])) : 'Все' }}</span>
                                    </div>
                                    <div class="filter-multiselect-values" data-name="engine_type">
                                        @foreach((array)request('engine_type', []) as $val)
                                            @if($val !== '')<input type="hidden" name="engine_type[]" value="{{ $val }}">@endif
                                        @endforeach
                                    </div>
                                </div>
                                <script type="application/json" id="options_engine_type">@json($engineTypeOptions)</script>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Двигатели</label>
                                <div class="filter-multiselect-wrap">
                                    <div class="filter-multiselect-trigger form-control form-control-sm" data-name="engine" data-placeholder="Все" tabindex="0" role="button">
                                        <span class="filter-multiselect-label">{{ count((array)request('engine', [])) ? 'Выбрано: ' . count((array)request('engine', [])) : 'Все' }}</span>
                                    </div>
                                    <div class="filter-multiselect-values" data-name="engine">
                                        @foreach((array)request('engine', []) as $val)
                                            @if($val !== '')<input type="hidden" name="engine[]" value="{{ $val }}">@endif
                                        @endforeach
                                    </div>
                                </div>
                                <script type="application/json" id="options_engine">@json($engineNumberOptions)</script>
                            </div>
                            
                            <!-- Кнопка сброса фильтров -->
                            <div class="col-12">
                                <div>
                                    <button type="button" style="border:none; box-shadow:none; color:gray;" class="btn btn-outline-primary btn-sm rel-reset-filters-btn" onclick="resetFilters()">Сбросить</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Модальное окно множественного выбора для фильтров -->
                    <div class="modal fade" id="filterMultiSelectModal" tabindex="-1" aria-labelledby="filterMultiSelectModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filterMultiSelectModalLabel">Выбор</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="filter-multiselect-search mb-2">
                                        <input type="text" class="form-control form-control-sm" placeholder="Поиск..." id="filterMultiSelectSearch">
                                    </div>
                                    <div id="filterMultiSelectCheckboxes" class="filter-multiselect-list"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn efds-btn efds-btn--primary" id="filterMultiSelectApply">Применить</button>
                                    <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Отмена</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Переключалка табов в стиле nav-tabs (синяя полоска снизу) -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="doc-mode-tabs">
                @php
                    $tabsVisibility = $tabsVisibility ?? [
                        'failures' => true,
                        'defects' => true,
                        'monitoring' => true,
                        'aging_aircraft' => true,
                        'aging_components' => true,
                        'systems' => true,
                    ];
                @endphp
                <ul class="nav nav-tabs" role="tablist">
                    @if($tabsVisibility['failures'] ?? true)
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                    class="nav-link {{ $activeTab === 'failures' ? 'active' : '' }}"
                                    data-tab="failures"
                                    role="tab"
                                    onclick="switchReliabilityTab('failures')">
                                Отказы
                            </button>
                        </li>
                    @endif
                    @if($tabsVisibility['defects'] ?? true)
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                    class="nav-link {{ $activeTab === 'defects' ? 'active' : '' }}"
                                    data-tab="defects"
                                    role="tab"
                                    onclick="switchReliabilityTab('defects')">
                                Дефекты
                            </button>
                        </li>
                    @endif
                    @if($tabsVisibility['monitoring'] ?? true)
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                    class="nav-link {{ $activeTab === 'monitoring' ? 'active' : '' }}"
                                    data-tab="monitoring"
                                    role="tab"
                                    onclick="switchReliabilityTab('monitoring')">
                                Мониторинг
                            </button>
                        </li>
                    @endif
                    @if($tabsVisibility['aging_aircraft'] ?? true)
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                    class="nav-link {{ $activeTab === 'aging_aircraft' ? 'active' : '' }}"
                                    data-tab="aging_aircraft"
                                    role="tab"
                                    onclick="switchReliabilityTab('aging_aircraft')">
                                Старение ВС
                            </button>
                        </li>
                    @endif
                    @if($tabsVisibility['aging_components'] ?? true)
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                    class="nav-link {{ $activeTab === 'aging_components' ? 'active' : '' }}"
                                    data-tab="aging_components"
                                    role="tab"
                                    onclick="switchReliabilityTab('aging_components')">
                                Старение КИ
                            </button>
                        </li>
                    @endif
                    @if($tabsVisibility['systems'] ?? true)
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                    class="nav-link {{ $activeTab === 'systems' ? 'active' : '' }}"
                                    data-tab="systems"
                                    role="tab"
                                    onclick="switchReliabilityTab('systems')">
                                Системы
                            </button>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Контент табов -->
    <div class="row">
        <div class="col-12">
            <div class="tab-content" id="reliabilityTabContent">
                @if($tabsVisibility['failures'] ?? true)
                    <!-- Таб Отказы -->
                    <div class="tab-pane fade {{ $activeTab === 'failures' ? 'show active' : '' }}" id="failures" role="tabpanel" aria-labelledby="failures-tab">
                        @include('Modules.Reliability.tabs.failures')
                    </div>
                @endif

                @if($tabsVisibility['defects'] ?? true)
                    <!-- Таб Дефекты -->
                    <div class="tab-pane fade {{ $activeTab === 'defects' ? 'show active' : '' }}" id="defects" role="tabpanel" aria-labelledby="defects-tab">
                        @include('Modules.Reliability.tabs.defects')
                    </div>
                @endif

                @if($tabsVisibility['monitoring'] ?? true)
                    <!-- Таб Мониторинг -->
                    <div class="tab-pane fade {{ $activeTab === 'monitoring' ? 'show active' : '' }}" id="monitoring" role="tabpanel" aria-labelledby="monitoring-tab">
                        @include('Modules.Reliability.tabs.monitoring')
                    </div>
                @endif

                @if($tabsVisibility['aging_aircraft'] ?? true)
                    <!-- Таб Старение ВС -->
                    <div class="tab-pane fade {{ $activeTab === 'aging_aircraft' ? 'show active' : '' }}" id="aging-aircraft" role="tabpanel" aria-labelledby="aging-aircraft-tab">
                        @include('Modules.Reliability.tabs.aging-aircraft')
                    </div>
                @endif

                @if($tabsVisibility['aging_components'] ?? true)
                    <!-- Таб Старение КИ -->
                    <div class="tab-pane fade {{ $activeTab === 'aging_components' ? 'show active' : '' }}" id="aging-components" role="tabpanel" aria-labelledby="aging-components-tab">
                        @include('Modules.Reliability.tabs.aging-components')
                    </div>
                @endif

                @if($tabsVisibility['systems'] ?? true)
                    <!-- Таб Системы -->
                    <div class="tab-pane fade {{ $activeTab === 'systems' ? 'show active' : '' }}" id="systems" role="tabpanel" aria-labelledby="systems-tab">
                        @include('Modules.Reliability.tabs.systems')
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function resetFilters() {
    // Полный сброс всех фильтров через очистку query-параметров
    const url = new URL(window.location.href);
    url.search = '';
    url.searchParams.set('tab', 'failures');
    window.location.href = url.toString();
}

// Переключение табов (используется в nav-tabs)
function switchReliabilityTab(tabValue) {
    // Подсветка активной вкладки (синяя полоска снизу)
    document.querySelectorAll('.doc-mode-tabs .nav-link').forEach(function(btn) {
        btn.classList.remove('active');
    });
    var activeBtn = document.querySelector('.doc-mode-tabs .nav-link[data-tab="' + tabValue + '"]');
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Обновляем скрытое поле формы фильтров, если оно есть
    var filterTabInput = document.getElementById('filterTab');
    if (filterTabInput) {
        filterTabInput.value = tabValue;
    }

    // Переключаем контент табов на странице (без перезагрузки)
    var tabMapping = {
        'failures': 'failures',
        'defects': 'defects',
        'monitoring': 'monitoring',
        'aging_aircraft': 'aging-aircraft',
        'aging_components': 'aging-components',
        'systems': 'systems'
    };
    var targetId = tabMapping[tabValue];
    if (targetId) {
        document.querySelectorAll('.tab-pane').forEach(function(pane) {
            pane.classList.remove('show', 'active');
        });
        var targetPane = document.getElementById(targetId);
        if (targetPane) {
            targetPane.classList.add('show', 'active');
        }
    }

    // Инициализация графика мониторинга при переключении на вкладку "Мониторинг"
    if (tabValue === 'monitoring' && typeof window.initMonitoringChart === 'function') {
        if (typeof waitForChartConfig !== 'undefined') {
            waitForChartConfig(function () {
                if (!window.monitoringChartInstance) {
                    window.initMonitoringChart();
                    window.monitoringChartInstance = true;
                }
            });
        } else {
            if (!window.monitoringChartInstance) {
                window.initMonitoringChart();
                window.monitoringChartInstance = true;
            }
        }
    }

    // Обновляем URL
    var url = new URL(window.location.href);
    url.searchParams.set('tab', tabValue);
    window.history.pushState({ tab: tabValue }, '', url.toString());
}

// Логика переключения табов без перезагрузки страницы
document.addEventListener('DOMContentLoaded', function() {
    // Маппинг значений табов на их ID элементов
    const tabMapping = {
        'failures': 'failures',
        'defects': 'defects',
        'monitoring': 'monitoring',
        'aging_aircraft': 'aging-aircraft',
        'aging_components': 'aging-components',
        'systems': 'systems'
    };

    // Функция переключения таба
    function switchTab(tabValue) {
        // Скрываем все табы
        document.querySelectorAll('.tab-pane').forEach(function(pane) {
            pane.classList.remove('show', 'active');
        });

        // Показываем выбранный таб
        const targetTabId = tabMapping[tabValue];
        if (targetTabId) {
            const targetPane = document.getElementById(targetTabId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
                
                // Инициализируем графики для табов с графиками
                if (tabValue === 'monitoring' && typeof window.initMonitoringChart === 'function') {
                    const canvas = document.getElementById('monitoringChart');
                    if (canvas) {
                        // Проверяем, не инициализирован ли уже график
                        if (!window.monitoringChartInstance) {
                            if (typeof waitForChartConfig !== 'undefined') {
                                waitForChartConfig(function() {
                                    window.initMonitoringChart();
                                    window.monitoringChartInstance = true;
                                });
                            }
                        }
                    }
                } else if (tabValue === 'aging_aircraft' && typeof window.initAgingAircraftChart === 'function') {
                    const canvas = document.getElementById('agingAircraftChart');
                    if (canvas) {
                        if (!window.agingAircraftChartInstance) {
                            if (typeof waitForChartConfig !== 'undefined') {
                                waitForChartConfig(function() {
                                    window.initAgingAircraftChart();
                                    window.agingAircraftChartInstance = true;
                                });
                            }
                        }
                    }
                } else if (tabValue === 'aging_components' && typeof window.initAgingComponentsChart === 'function') {
                    const canvas = document.getElementById('agingComponentsChart');
                    if (canvas) {
                        if (!window.agingComponentsChartInstance) {
                            if (typeof waitForChartConfig !== 'undefined') {
                                waitForChartConfig(function() {
                                    window.initAgingComponentsChart();
                                    window.agingComponentsChartInstance = true;
                                });
                            }
                        }
                    }
                }
            }
        }

        // Обновляем URL без перезагрузки страницы
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabValue);
        window.history.pushState({ tab: tabValue }, '', url.toString());
    }

    // Обработчики для radio кнопок табов
    document.querySelectorAll('input[name="tab"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.checked) {
                switchTab(this.value);
            }
        });
    });

    // Обработка кнопок "Назад/Вперед" браузера
    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'failures';
        const radio = document.querySelector(`input[name="tab"][value="${tab}"]`);
        if (radio) {
            radio.checked = true;
            switchTab(tab);
        }
    });
});

// Логика фильтров: множественный выбор через модальное окно
document.addEventListener('DOMContentLoaded', function() {
    const filtersForm = document.getElementById('filtersForm');
    const filterTabInput = document.getElementById('filterTab');
    const modalEl = document.getElementById('filterMultiSelectModal');
    const modalTitle = document.getElementById('filterMultiSelectModalLabel');
    const checkboxesContainer = document.getElementById('filterMultiSelectCheckboxes');
    const searchInput = document.getElementById('filterMultiSelectSearch');
    const applyBtn = document.getElementById('filterMultiSelectApply');

    let currentFilterName = null;
    let currentTrigger = null;
    let currentOptions = [];

    function getSelectedValues(name) {
        const container = document.querySelector('.filter-multiselect-values[data-name="' + name + '"]');
        if (!container) return [];
        return Array.from(container.querySelectorAll('input[type="hidden"]')).map(function(inp) { return inp.value; });
    }

    function getOptionsForFilter(name) {
        if (name === 'aggregate_type' && document.querySelector('.filter-multiselect-trigger[data-name="aggregate_type"][data-options-ajax="1"]')) {
            return null;
        }
        const scriptEl = document.getElementById('options_' + name);
        if (!scriptEl || !scriptEl.textContent) return [];
        try {
            return JSON.parse(scriptEl.textContent);
        } catch (e) {
            return [];
        }
    }

    function openModal(trigger) {
        currentTrigger = trigger;
        currentFilterName = trigger.getAttribute('data-name');
        const placeholder = trigger.getAttribute('data-placeholder') || 'Все';
        var col = trigger.closest('.col-md-2');
        modalTitle.textContent = col && col.querySelector('.form-label') ? col.querySelector('.form-label').textContent : 'Выбор';

        let options = getOptionsForFilter(currentFilterName);
        if (options === null && currentFilterName === 'aggregate_type') {
            const systemVals = getSelectedValues('system');
            const subsystemVals = getSelectedValues('subsystem');
            const systemFirst = systemVals[0] || '';
            const subsystemFirst = subsystemVals[0] || '';
            if (!systemFirst || !subsystemFirst) {
                options = [];
            } else {
                var url = new URL('{{ route('modules.reliability.aggregates') }}', window.location.origin);
                url.searchParams.set('system', systemFirst);
                url.searchParams.set('subsystem', subsystemFirst);
                fetch(url.toString(), { method: 'GET', headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        options = (data && data.success && Array.isArray(data.aggregates)) ? data.aggregates.map(function(a) { return { value: a.name, label: a.name }; }) : [];
                        renderCheckboxes(options);
                        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    });
                return;
            }
        }
        currentOptions = options || [];
        renderCheckboxes(currentOptions);
        if (searchInput) searchInput.value = '';
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function renderCheckboxes(options) {
        currentOptions = options || [];
        const selected = currentFilterName ? getSelectedValues(currentFilterName) : [];
        checkboxesContainer.innerHTML = '';
        if (currentOptions.length === 0) {
            var empty = document.createElement('p');
            empty.className = 'text-muted mb-0';
            empty.textContent = 'Нет вариантов для выбора';
            checkboxesContainer.appendChild(empty);
            return;
        }
        currentOptions.forEach(function(opt, index) {
            const value = String(opt.value);
            const label = (opt.label !== undefined && opt.label !== null) ? String(opt.label) : value;
            const div = document.createElement('div');
            div.className = 'form-check filter-multiselect-item';
            div.setAttribute('data-value', value);
            div.setAttribute('data-label', label);

            const input = document.createElement('input');
            input.className = 'form-check-input';
            input.type = 'checkbox';
            input.value = value;
            input.id = 'filter_cb_' + currentFilterName + '_' + index;

            const labelEl = document.createElement('label');
            labelEl.className = 'form-check-label';
            labelEl.setAttribute('for', input.id);
            labelEl.textContent = label;

            div.appendChild(input);
            div.appendChild(labelEl);
            if (selected.indexOf(value) !== -1) {
                input.checked = true;
            }
            checkboxesContainer.appendChild(div);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            checkboxesContainer.querySelectorAll('.filter-multiselect-item').forEach(function(div) {
                const label = (div.getAttribute('data-label') || '').toLowerCase();
                div.style.display = (!q || label.indexOf(q) !== -1) ? '' : 'none';
            });
        });
    }

    if (applyBtn && modalEl) {
        applyBtn.addEventListener('click', function() {
            if (!currentFilterName || !currentTrigger) return;
            const container = document.querySelector('.filter-multiselect-values[data-name="' + currentFilterName + '"]');
            const labelSpan = currentTrigger.querySelector('.filter-multiselect-label');
            const placeholder = currentTrigger.getAttribute('data-placeholder') || 'Все';
            const checked = checkboxesContainer.querySelectorAll('input:checked');
            const values = Array.from(checked).map(function(cb) { return cb.value; });
            container.innerHTML = '';
            values.forEach(function(v) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = currentFilterName + '[]';
                inp.value = v;
                container.appendChild(inp);
            });
            if (labelSpan) {
                labelSpan.textContent = values.length ? ('Выбрано: ' + values.length) : placeholder;
            }
            bootstrap.Modal.getInstance(modalEl).hide();
            currentFilterName = null;
            currentTrigger = null;
            if (filtersForm) {
                var tabRadio = document.querySelector('input[name="tab"]:checked');
                if (tabRadio && filterTabInput) filterTabInput.value = tabRadio.value;
                filtersForm.submit();
            }
        });
    }

    document.querySelectorAll('.filter-multiselect-trigger').forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(this);
        });
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openModal(this);
            }
        });
    });

    // Отправка формы при изменении селектов и мультиселектов (не при вводе в текстовых/дата полях)
    filtersForm.addEventListener('change', function(e) {
        if (e.target && e.target.classList && e.target.classList.contains('filter-date-input')) {
            return;
        }
        var tabRadio = document.querySelector('input[name="tab"]:checked');
        if (tabRadio && filterTabInput) filterTabInput.value = tabRadio.value;
        filtersForm.submit();
    });

    // Поля даты: применяем фильтр только по blur (клик вне поля) или Enter
    function submitFiltersForm() {
        var tabRadio = document.querySelector('input[name="tab"]:checked');
        if (tabRadio && filterTabInput) filterTabInput.value = tabRadio.value;
        filtersForm.submit();
    }
    filtersForm.querySelectorAll('.filter-date-input').forEach(function(input) {
        input.addEventListener('blur', submitFiltersForm);
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            }
        });
    });
});
</script>

<!-- Chart.js загружаем один раз для всех табов -->
<script>
// Функция ожидания загрузки ChartConfig
window.waitForChartConfig = function(callback) {
    if (typeof ChartConfig !== 'undefined' && typeof chartColors !== 'undefined') {
        callback();
        return;
    }
    
    // Проверяем, загружается ли уже
    if (window.chartJsLoading) {
        const checkInterval = setInterval(function() {
            if (typeof ChartConfig !== 'undefined' && typeof chartColors !== 'undefined') {
                clearInterval(checkInterval);
                callback();
            }
        }, 50);
        return;
    }
    
    // Загружаем Chart.js и ChartConfig
    window.chartJsLoading = true;
    
    const loadChartConfig = function() {
        if (typeof ChartConfig === 'undefined' && !document.querySelector('script[src*="chart-config.js"]')) {
            const configScript = document.createElement('script');
            configScript.src = '{{ asset('js/chart-config.js') }}';
            configScript.onload = function() {
                window.chartJsLoaded = true;
                window.chartJsLoading = false;
                callback();
            };
            configScript.onerror = function() {
                window.chartJsLoading = false;
                console.error('Failed to load chart-config.js');
            };
            document.head.appendChild(configScript);
        } else {
            // Ждем, пока ChartConfig загрузится
            const checkInterval = setInterval(function() {
                if (typeof ChartConfig !== 'undefined' && typeof chartColors !== 'undefined') {
                    clearInterval(checkInterval);
                    window.chartJsLoaded = true;
                    window.chartJsLoading = false;
                    callback();
                }
            }, 50);
        }
    };
    
    if (typeof Chart === 'undefined') {
        const chartScript = document.createElement('script');
        chartScript.src = '{{ asset('js/chart.min.js') }}';
        chartScript.onload = loadChartConfig;
        chartScript.onerror = function() {
            window.chartJsLoading = false;
            console.error('Failed to load chart.min.js');
        };
        document.head.appendChild(chartScript);
    } else {
        loadChartConfig();
    }
};

// Загружаем Chart.js только если еще не загружен
(function() {
    // Используем глобальную переменную для отслеживания загрузки
    if (window.chartJsLoading || window.chartJsLoaded) {
        return; // Уже загружается или загружен
    }
    
    // Проверяем, не загружен ли уже chart-config.js
    const existingConfigScript = document.querySelector('script[src*="chart-config.js"]');
    if (existingConfigScript || (typeof ChartConfig !== 'undefined' && typeof chartColors !== 'undefined')) {
        window.chartJsLoaded = true;
        return; // Уже загружен
    }
    
    window.chartJsLoading = true;
    
    if (typeof Chart === 'undefined') {
        const chartScript = document.createElement('script');
        chartScript.src = '{{ asset('js/chart.min.js') }}';
        chartScript.onload = function() {
            // Загружаем chart-config.js только если Chart загружен и ChartConfig еще не определен
            if (typeof ChartConfig === 'undefined' && !document.querySelector('script[src*="chart-config.js"]')) {
                const configScript = document.createElement('script');
                configScript.src = '{{ asset('js/chart-config.js') }}';
                configScript.onload = function() {
                    window.chartJsLoaded = true;
                    window.chartJsLoading = false;
                };
                document.head.appendChild(configScript);
            } else {
                window.chartJsLoaded = true;
                window.chartJsLoading = false;
            }
        };
        document.head.appendChild(chartScript);
    } else if (typeof ChartConfig === 'undefined' && !document.querySelector('script[src*="chart-config.js"]')) {
        // Chart уже загружен, но ChartConfig нет
        const configScript = document.createElement('script');
        configScript.src = '{{ asset('js/chart-config.js') }}';
        configScript.onload = function() {
            window.chartJsLoaded = true;
            window.chartJsLoading = false;
        };
        document.head.appendChild(configScript);
    } else {
        window.chartJsLoaded = true;
        window.chartJsLoading = false;
    }
})();
</script>

<style>

.card {
    box-shadow: none;
    border: 1px solid #e3e6f0;
    background-color:#f5f7fa
}




.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #5a5c69;
    margin-bottom: 0.25rem;
}

.form-control-sm, .form-select-sm {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.rel-reset-filters-btn:hover,
.rel-reset-filters-btn:focus {
    background-color: transparent !important;
}

/* Стили для селектора табов в стиле вкладок */
.btn-group {
    border-radius: 8px;
    background: none;
    margin-bottom: 0;
    padding: 0;
}

.btn-group .btn,
.btn-group .btn-outline-primary {
    border: none !important;
    border-radius: 8px !important;
    color: #6c757d !important;
    background-color: transparent !important;
    margin-right: 8px;
    font-size: 16px;
    font-weight: 500;
    padding: 8px 16px;
    position: relative;
    height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: none !important;
    transition: none !important;
}


.btn-group .btn:hover,
.btn-group .btn-outline-primary:hover {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    border: none !important;
}

.btn-group .btn-check:checked + .btn,
.btn-group .btn-check:checked + .btn-outline-primary {
    color: #495057 !important;
    font-weight: 600 !important;
    background-color: #f5f7fa !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    border: none !important;
}

.btn-group .btn-check:focus + .btn,
.btn-group .btn-check:focus + .btn-outline-primary {
    box-shadow: none !important;
    border: none !important;
}

.btn-group .btn-check:active + .btn,
.btn-group .btn-check:active + .btn-outline-primary {
    background-color: #f5f7fa !important;
    color: #495057 !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

.btn-group .btn:last-child,
.btn-group .btn-outline-primary:last-child {
    margin-right: 0;
}

/* Принудительно применяем закругления ко всем кнопкам */
.btn-group .btn:first-child,
.btn-group .btn-outline-primary:first-child {
    border-top-left-radius: 8px !important;
    border-bottom-left-radius: 8px !important;
    border-top-right-radius: 8px !important;
    border-bottom-right-radius: 8px !important;
}

.btn-group .btn:not(:first-child):not(:last-child),
.btn-group .btn-outline-primary:not(:first-child):not(:last-child) {
    border-top-left-radius: 8px !important;
    border-bottom-left-radius: 8px !important;
    border-top-right-radius: 8px !important;
    border-bottom-right-radius: 8px !important;
}

 .btn-group .btn:last-child,
 .btn-group .btn-outline-primary:last-child {
     border-top-left-radius: 8px !important;
     border-bottom-left-radius: 8px !important;
     border-top-right-radius: 8px !important;
     border-bottom-right-radius: 8px !important;
 }

/* Вкладки режима (как в документации / реестре рисков) */
.doc-mode-tabs {
    margin-bottom: 0;
}

.doc-mode-tabs .nav-tabs {
    border-bottom: 2px solid #dee2e6;
}

.doc-mode-tabs .nav-link {
    color: #495057;
    font-weight: 500;
    font-size: 14px;
    padding: 12px 24px;
    border: none;
    border-bottom: 3px solid transparent;
    background: transparent;
}

.doc-mode-tabs .nav-link:hover {
    border-color: transparent;
    color: #1E64D4;
}

.doc-mode-tabs .nav-link.active {
    color: #1E64D4;
    border-bottom-color: #1E64D4;
    background: transparent;
    font-weight: 600;
}

/* Таблица отказов: фиксированная раскладка, текст переносится по словам, строки растягиваются */
.reliability-failures-table {
    table-layout: fixed;
    width: 1600px;
}


.reliability-failures-table th,
.reliability-failures-table td {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
    vertical-align: top;
    height: auto;
    overflow: visible;
}
.reliability-failures-table tr {
    height: auto;
}

/* Зона таблицы отказов: обе полосы прокрутки в зоне видимости (горизонтальная — внизу видимой области) */
.reliability-table-scroll-wrap {
    height: calc(100vh - 320px);
    min-height: 300px;
    display: flex;
    flex-direction: column;
}
.reliability-table-scroll-wrap .table-responsive {
    flex: 1;
    min-height: 0;
    overflow: scroll;
    overflow-x: scroll;
    overflow-y: scroll;
}

.form-control {
    width: 100%;
}

.filter-multiselect-trigger {
    cursor: pointer;
    user-select: none;
    background-color:white;
}
.filter-multiselect-trigger:hover {
    background-color: #f8f9fa;
}

.form-control{
    background-color: white; 
    box-shadow:none;
}


.form-control, .form-select {
    height: 40px;
   
   
    border: 1px solid #dadce0;
    border-radius: 4px;
    background: #fff;
    color: #5f6368;
  
}



.filter-multiselect-list {
    max-height: 320px;
    min-height: 120px;
    overflow-y: auto;
}
.filter-multiselect-list .form-check {
    padding: 8px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.filter-multiselect-list .form-check-input {
    width: 1.1em;
    height: 1.1em;
    margin: 0;
    flex-shrink: 0;
    cursor: pointer;
}
.filter-multiselect-list .form-check-label {
    cursor: pointer;
    flex: 1;
}

.btn:disabled, .btn-custom:disabled, .btn.disabled, .disabled.btn-custom, fieldset:disabled .btn, fieldset:disabled .btn-custom
 {
opacity:100;
 }
</style>
@endsection

