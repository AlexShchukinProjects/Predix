@extends("layout.main")

@section('content')

<div class="settings-container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="module-header mb-4">
                <h2 class="module-title">{{ $config['title'] }}</h2>
                <p class="module-subtitle">Управление e-mail уведомлениями</p>
            </div>
            
            @foreach($config['notifications'] as $key => $notification)
            <div style="margin-bottom: 15px;">
                <a href="{{ route('notification.template.edit', ['module' => $module, 'template' => $key]) }}" style="text-decoration:none;color:inherit;">
                    <div class="module-notification-tile">
                        <div class="module-notification-content">
                            <div class="module-notification-info">
                                <h6 class="module-notification-title">{{ $notification['name'] }}</h6>
                                <div class="module-notification-status">
                                    <span class="status-badge status-{{ $notification['status'] }}">
                                        {{ $notification['status'] === 'active' ? 'Активно' : 'Неактивно' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
.module-header {
    text-align: center;
    margin-bottom: 30px;
}

.module-title {
    font-size: 28px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 8px;
}

.module-subtitle {
    font-size: 16px;
    color: #6c757d;
    margin-bottom: 0;
}

.module-notification-tile {
    cursor: pointer;
    border-radius: 12px;
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    height: 80px;
    width: 600px;
    display: flex;
    align-items: center;
}

.module-notification-tile:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
    background: #ffffff;
    border-color: #d1d9e6;
}

.module-notification-content {
    width: 100%;
    display: flex;
    align-items: center;
}

.module-notification-info {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.module-notification-title {
    font-size: 16px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0;
    line-height: 1.3;
    letter-spacing: -0.02em;
    flex: 1;
}

.module-notification-status {
    display: flex;
    align-items: center;
    margin-left: 15px;
}

.status-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-inactive {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.module-notification-tile:hover .module-notification-title {
    color: #1a202c;
}

/* Адаптивность */
@media (max-width: 768px) {
    .module-notification-tile {
        height: 70px;
        width: 100%;
        padding: 16px;
    }
    
    .module-title {
        font-size: 24px;
    }
    
    .module-subtitle {
        font-size: 14px;
    }
    
    .module-notification-title {
        font-size: 15px;
    }
}

@media (max-width: 576px) {
    .module-notification-tile {
        height: 65px;
        width: 100%;
        padding: 14px;
    }
    
    .module-title {
        font-size: 20px;
    }
    
    .module-subtitle {
        font-size: 13px;
    }
    
    .module-notification-title {
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
