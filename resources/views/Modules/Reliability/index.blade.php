@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
            
            </div>
        </div>
    </div>

    <!-- Filters panel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('modules.reliability.index') }}" id="filtersForm">
                        <input type="hidden" name="tab" id="filterTab" value="{{ request('tab', 'failures') }}">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Date from</label>
                                <input type="date" class="form-control form-control-sm filter-date-input" name="date_from" value="{{ request('date_from', \Carbon\Carbon::now()->subYear()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date to</label>
                                <input type="date" class="form-control form-control-sm filter-date-input" name="date_to" value="{{ request('date_to', \Carbon\Carbon::now()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">SEQ / ID</label>
                                <input type="text" class="form-control form-control-sm" name="id" value="{{ request('id') }}" placeholder="">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">TASK CARD</label>
                                <input type="text" class="form-control form-control-sm" name="task_card" value="{{ request('task_card') }}" placeholder="">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">TASK CARD DESCRIPTION</label>
                                <input type="text" class="form-control form-control-sm" name="task_card_description" value="{{ request('task_card_description') }}" placeholder="">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Task</label>
                                <input type="text" class="form-control form-control-sm" name="mpd" value="{{ request('mpd') }}" placeholder="">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label"># of RC</label>
                                <input type="text" class="form-control form-control-sm" name="num_rc" value="{{ request('num_rc') }}" placeholder="">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Hours on RC</label>
                                <input type="text" class="form-control form-control-sm" name="max_hours_rc" value="{{ request('max_hours_rc') }}" placeholder="">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label"># of STR NRCs</label>
                                <input type="text" class="form-control form-control-sm" name="num_str_nrcs" value="{{ request('num_str_nrcs') }}" placeholder="">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">REF</label>
                                <input type="text" class="form-control form-control-sm" name="ref" value="{{ request('ref') }}" placeholder="">
                            </div>
                            <div class="col-12">
                                <button type="button" style="border:none; box-shadow:none; color:gray;" class="btn btn-outline-primary btn-sm rel-reset-filters-btn" onclick="resetFilters()">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab switcher (nav-tabs style) -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="doc-mode-tabs">
                @php
                    $tabsVisibility = ['failures' => true];
                @endphp
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button"
                                class="nav-link active"
                                data-tab="failures"
                                role="tab"
                                onclick="switchReliabilityTab('failures')">
                            Task
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Tab content -->
    <div class="row">
        <div class="col-12">
            <div class="tab-content" id="reliabilityTabContent">
                <!-- Tab Failures -->
                <div class="tab-pane fade show active" id="failures" role="tabpanel" aria-labelledby="failures-tab">
                    @include('Modules.Reliability.tabs.failures')
                </div>
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

    var targetId = tabValue === 'failures' ? 'failures' : null;
    if (targetId) {
        document.querySelectorAll('.tab-pane').forEach(function(pane) {
            pane.classList.remove('show', 'active');
        });
        var targetPane = document.getElementById(targetId);
        if (targetPane) {
            targetPane.classList.add('show', 'active');
        }
    }

    // Обновляем URL
    var url = new URL(window.location.href);
    url.searchParams.set('tab', tabValue);
    window.history.pushState({ tab: tabValue }, '', url.toString());
}

// Логика переключения табов без перезагрузки страницы
document.addEventListener('DOMContentLoaded', function() {
    function switchTab(tabValue) {
        document.querySelectorAll('.tab-pane').forEach(function(pane) {
            pane.classList.remove('show', 'active');
        });
        const targetPane = document.getElementById('failures');
        if (targetPane) {
            targetPane.classList.add('show', 'active');
        }
        const url = new URL(window.location.href);
        url.searchParams.set('tab', 'failures');
        window.history.pushState({ tab: 'failures' }, '', url.toString());
    }

    // Обработчики для radio кнопок табов
    document.querySelectorAll('input[name="tab"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.checked) {
                switchTab(this.value);
            }
        });
    });

    // Browser back/forward handling
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

document.addEventListener('DOMContentLoaded', function() {
    var filtersForm = document.getElementById('filtersForm');
    var filterTabInput = document.getElementById('filterTab');
    if (!filtersForm) return;
    function submitFiltersForm() {
        if (filterTabInput) filterTabInput.value = 'failures';
        filtersForm.submit();
    }
    filtersForm.querySelectorAll('.filter-date-input').forEach(function(input) {
        input.addEventListener('blur', submitFiltersForm);
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
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

