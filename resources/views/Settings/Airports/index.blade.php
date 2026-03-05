@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <a href="{{ url()->previous() }}" class="back-button d-inline-block mb-2">
                    <i class="fas fa-arrow-left me-2"></i>Назад
                </a>
                <h1 class="h3 mb-0 text-gray-800">Справочник аэропортов</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Фильтры в стиле дизайн-системы -->
                    <div class="filter-bar-wrap mb-3">
                        <div class="filter-bar">
                            <span class="filter-bar__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                                </svg>
                            </span>
                            <div class="filter-bar__form">
                                <div class="filter-bar__fields">
                                    <input type="text"
                                           class="filter-bar__input filter-bar__input--search"
                                           id="search-input"
                                           value="{{ request('search') }}"
                                           placeholder="Поиск по названию, IATA, ICAO коду..."
                                           aria-label="Поиск">
                                </div>
                            </div>
                            <button type="button" class="filter-bar__clear" id="clear-search" style="display: none;" aria-label="Сбросить фильтры" title="Сбросить">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 6L6 18M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Хидер над таблицей (дизайн-система) -->
                    <div class="efds-table-header">
                        <div class="efds-table-header__stats text-muted">
                            <span class="me-2">На странице:</span>
                            <select id="per-page-select" class="form-select form-select-sm d-inline-block" style="width: auto;" aria-label="Записей на странице">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <span class="ms-2">Всего записей: {{ $airports->total() }}</span>
                        </div>
                        <div class="efds-table-header__actions">
                            <a href="{{ route('airports.create') }}" class="btn efds-btn efds-btn--primary">
                                <i class="fas fa-plus me-1"></i>Добавить аэропорт
                            </a>
                        </div>
                    </div>

                    <!-- Индикатор загрузки -->
                    <div id="loading-indicator" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-2 text-muted">Поиск...</p>
                    </div>

                    <!-- Контейнер для таблицы и пагинации -->
                    <div id="table-container">
                        @include('Settings.Airports.partials.table')
                        @include('Settings.Airports.partials.pagination')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.back-button {
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.back-button:hover {
    color: #0056b3;
    text-decoration: none;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.badge {
    font-size: 0.75em;
}

.airports-card {
    background-color: #ffffff !important;
    border: 1px solid #ffffff !important;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
.airports-add-row {
    width: 100%;
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
    padding-bottom: 0.5rem;
}
.airports-add-row .btn {
    margin-right: 0;
}

.airports-pagination-wrap .pagination-links {
    flex-direction: column;
    align-items: flex-start;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.input-group .btn {
    border-radius: 0 0.375rem 0.375rem 0;
}

.input-group .btn:last-child {
    border-radius: 0 0.375rem 0.375rem 0;
    margin-left: 2px;
}

.pagination {
    margin-bottom: 0;
}

.page-link {
    color: #495057;
    border-color: #dee2e6;
}

.page-link:hover {
    color: #0056b3;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.text-muted {
    font-size: 0.875rem;
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}

#table-container {
    transition: opacity 0.3s ease;
}

#loading-indicator {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
}

.card-body {
    position: relative;
}

.input-group .btn {
    transition: all 0.2s ease;
}

.input-group .btn:hover {
    transform: translateY(-1px);
}

#search-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    border-color: #80bdff;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('clear-search');
    const perPageSelect = document.getElementById('per-page-select');
    const loadingIndicator = document.getElementById('loading-indicator');
    const tableContainer = document.getElementById('table-container');
    let currentSort = new URLSearchParams(window.location.search).get('sort') || 'NameRus';
    let currentDirection = new URLSearchParams(window.location.search).get('direction') || 'asc';
    
    let searchTimeout;
    
    // Функция для выполнения поиска (page — номер страницы для пагинации)
    function performSearch(page) {
        const searchTerm = searchInput.value.trim();
        const perPage = perPageSelect.value;
        
        // Скрываем/показываем кнопку очистки
        clearBtn.style.display = searchTerm ? 'block' : 'none';
        
        // Подготавливаем параметры
        const params = new URLSearchParams();
        if (searchTerm) params.append('search', searchTerm);
        if (perPage) params.append('per_page', perPage);
        if (currentSort) params.append('sort', currentSort);
        if (currentDirection) params.append('direction', currentDirection);
        if (page) params.append('page', page);
        
        // Показываем индикатор загрузки только если есть поисковый запрос
        if (searchTerm) {
            loadingIndicator.style.display = 'block';
            tableContainer.style.opacity = '0.5';
        }
        
        // Выполняем AJAX запрос
        fetch(`{{ route('airports.search') }}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            // Плавно обновляем содержимое
            tableContainer.style.transition = 'opacity 0.3s ease';
            tableContainer.innerHTML = data.html + data.pagination;
            
            // Скрываем индикатор загрузки и восстанавливаем прозрачность
            loadingIndicator.style.display = 'none';
            tableContainer.style.opacity = '1';
        })
        .catch(error => {
            console.error('Ошибка поиска:', error);
            loadingIndicator.style.display = 'none';
            tableContainer.style.opacity = '1';
        });
    }
    
    // Поиск при вводе с задержкой
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = searchInput.value.trim();
        
        // Если поле пустое, сразу показываем все результаты
        if (!searchTerm) {
            performSearch();
            return;
        }
        
        // Для непустого поиска используем задержку
        searchTimeout = setTimeout(performSearch, 300); // 300ms задержка
    });
    
    // Поиск при нажатии Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            performSearch();
        }
    });
    
    // Очистка поиска
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        performSearch();
    });
    
    // Изменение количества строк на странице
    perPageSelect.addEventListener('change', function() {
        performSearch();
    });
    
    // Обработка кликов по пагинации — используем search-маршрут с параметром page
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink) {
            e.preventDefault();
            const href = paginationLink.getAttribute('href');
            if (!href) return;
            const urlParams = new URL(href, window.location.origin).searchParams;
            const page = urlParams.get('page');
            if (page) {
                loadingIndicator.style.display = 'block';
                tableContainer.style.transition = 'opacity 0.2s ease';
                tableContainer.style.opacity = '0.5';
                performSearch(page);
            }
        }
        // Клик по сортировке
        if (e.target.closest('.sort-link')) {
            e.preventDefault();
            const link = e.target.closest('.sort-link');
            const sortField = link.getAttribute('data-sort');
            if (currentSort === sortField) {
                currentDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort = sortField;
                currentDirection = 'asc';
            }
            updateSortIndicators();
            performSearch();
        }
    });

    function updateSortIndicators() {
        document.querySelectorAll('.sort-indicator').forEach(span => {
            const field = span.getAttribute('data-for');
            if (field === currentSort) {
                span.innerHTML = currentDirection === 'asc' ? ' ▲' : ' ▼';
            } else {
                span.innerHTML = '';
            }
        });
    }

    // Инициализация индикаторов сортировки при загрузке
    updateSortIndicators();

    // Делать строки кликабельными после динамической подгрузки
    document.addEventListener('click', function(e){
        const row = e.target.closest('.airport-row');
        if (row) {
            const href = row.getAttribute('data-href');
            if (href) window.location.href = href;
        }
    });
});
</script>
@endsection
