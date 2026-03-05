@extends("layout.main")

@section('content')

<div class="settings-container">
    <div class="row justify-content-center">
        <div class="col-auto">
            @php
                $isSuperAdmin = auth()->user()?->isSuperAdmin();
                $settingsModules = [
                    [
                        'name' => 'МОДУЛИ',
                        'route' => route('settings.modules'),
                        'superadmin_only' => true,
                    ],
                    [
                        'name' => 'СПРАВОЧНИКИ',
                        'route' => '/settings/directory'
                    ],
                    [
                        'name' => 'ПОЛЬЗОВАТЕЛИ',
                        'route' => '/admin/users'
                    ],
                    [
                        'name' => 'РОЛИ',
                        'route' => '/admin/roles'
                    ],
                    [
                        'name' => 'УВЕДОМЛЕНИЯ',
                        'route' => '/notification'
                    ],
                    [
                        'name' => 'ОБЩИЕ НАСТРОЙКИ',
                        'route' => route('general-settings.index')
                    ],
                ];
            @endphp
            
            @foreach($settingsModules as $module)
                @if(!empty($module['superadmin_only']) && !$isSuperAdmin)
                    @continue
                @endif
                <div style="margin-bottom: 10px;">
                    <a href="{{ $module['route'] }}" style="text-decoration:none;color:inherit;">
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
    width: 400px;
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
        width: 100%;
        padding: 12px 16px;
    }
    
    .module-title {
        font-size: 15px;
    }
}

@media (max-width: 576px) {
    .module-tile {
        height: 55px;
        width: 100%;
        padding: 10px 16px;
    }
    
    .module-title {
        font-size: 14px;
    }
}

/* Стили для страницы */
.settings-container {
    background: white;
    min-height: calc(100vh - 80px);
    padding-top: 20px;
}

.row {
    margin: 0;
}

.col-auto {
    padding: 0;
}

/* Выравнивание контейнеров для центрирования плиток */
.container_main {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 auto !important;
    padding-left: 15px !important;
    padding-right: 15px !important;
}

.main_screen {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 auto !important;
}
</style>

@endsection