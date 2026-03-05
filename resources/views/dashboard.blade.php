@extends('layout.main')

@section('content')
<div class="container-fluid dashboard-container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
             
          
            </div>
        </div>
    </div>

    <div class="dashboard-single-column">
        @foreach($modules as $module)
            <div class="mb-3">
                <a href="{{ route($module['route']) }}" style="text-decoration:none;color:inherit;">
                    <div class="module-tile">
                        <div class="module-content">
                            <h6 class="module-title">{{ $module['name'] }}</h6>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

</div>

<style>
.module-tile {
    cursor: pointer;
    border-radius: 8px;
    padding: 15px 20px;
    background: #f5f7fa;
    border: 1px solid #e8ecf1;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    height: 70px;
    width: 100%;
    display: flex;
    align-items: center;
}

.module-tile:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    background: #ffffff;
    border-color: #d1d9e6;
}


.module-content {
    width: 100%;
    position: relative;
}

.module-title {
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0;
    line-height: 1.3;
    letter-spacing: -0.02em;
}

.module-tile:hover .module-title {
    color: #1a202c;
}

/* Адаптивность */
@media (max-width: 768px) {
    .module-tile {
        height: 60px;
        padding: 12px 16px;
    }
    
    .module-title {
        font-size: 15px;
    }
}

@media (max-width: 576px) {
    .module-tile {
        height: 55px;
        padding: 10px 16px;
    }
    
    .module-title {
        font-size: 14px;
    }
}

/* Дополнительные корпоративные стили */
.dashboard-container {
    background: white;
    min-height: calc(100vh - 80px);
    padding-top: 20px;
}

/* Одна колонка по центру */
.dashboard-single-column {
    max-width: 400px;
    margin: 0 auto;
}

/* Убеждаемся, что header остается компактным */
body .container_header {
    padding: 8px 0 !important;
}

body .header {
    min-height: 60px !important;
}

body .navbar-nav {
    margin: 0 !important;
    padding: 0 !important;
}

/* Выравнивание контейнеров для центрирования плиток */
html body .container_main {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 auto !important;
    padding-left: 15px !important;
    padding-right: 15px !important;
    display: block !important;
}

html body .main_screen {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 auto !important;
    padding: 0 !important;
    display: block !important;
    box-sizing: border-box !important;
}

.row {
    margin: 0 -8px;
}

.col-lg-3, .col-md-4, .col-sm-6 {
    padding: 0 8px;
}
</style>
@endsection