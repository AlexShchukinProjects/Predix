@extends("layout.main")

@section('content')

<div class="settings-container">
    <div class="row justify-content-center">
        <div class="col-auto">
            @php
                $notificationModules = [
                    [
                        'name' => 'ПЛАНИРОВАНИЕ',
                        'description' => 'Уведомления по планированию полетов и экипажей',
                        'route' => '/notification/planning',
                        'icon' => 'fas fa-calendar-alt',
                        'color' => '#1E64D4'
                    ],
                    [
                        'name' => 'УПРАВЛЕНИЕ РИСКАМИ',
                        'description' => 'Уведомления по оценке и управлению рисками',
                        'route' => '/notification/risk-management',
                        'icon' => 'fas fa-shield-alt',
                        'color' => '#dc3545'
                    ],
                    [
                        'name' => 'СИСТЕМА СООБЩЕНИЙ / БДАС',
                        'description' => 'Уведомления по безопасности полетов и базе данных: назначение ответственных, события, мероприятия',
                        'route' => '/notification/safety-database',
                        'icon' => 'fas fa-database',
                        'color' => '#17a2b8'
                    ],
                    [
                        'name' => 'НАДЕЖНОСТЬ',
                        'description' => 'Уведомления по модулю Надежность (отказы агрегатов и аналитика)',
                        'route' => '/notification/reliability',
                        'icon' => 'fas fa-tools',
                        'color' => '#fd7e14'
                    ],
                    [
                        'name' => 'ПОКАЗАТЕЛИ БП',
                        'description' => 'Уведомления по показателям безопасности полетов (SPI)',
                        'route' => '/notification/spi',
                        'icon' => 'fas fa-chart-line',
                        'color' => '#20c997'
                    ],
                    [
                        'name' => 'ОБУЧЕНИЕ',
                        'description' => 'Уведомления по обучению и сертификации',
                        'route' => '/notification/training',
                        'icon' => 'fas fa-graduation-cap',
                        'color' => '#28a745'
                    ],
                    [
                        'name' => 'ДОКУМЕНТАЦИЯ',
                        'description' => 'Уведомления по документам и согласованию',
                        'route' => '/notification/documentation',
                        'icon' => 'fas fa-file-alt',
                        'color' => '#6610f2'
                    ],
                    [
                        'name' => 'АУДИТЫ / ИНСПЕКЦИИ',
                        'description' => 'Уведомления по согласованию и утверждению аудитов и инспекций',
                        'route' => '/notification/inspections',
                        'icon' => 'fas fa-clipboard-check',
                        'color' => '#e83e8c'
                    ],
                    [
                        'name' => 'ИСПОЛНИТЕЛЬСКАЯ ДИСЦИПЛИНА',
                        'description' => 'Назначение ответственным за выполнение задачи, подтверждение и уведомления по задачам',
                        'route' => '/notification/executive-discipline',
                        'icon' => 'fas fa-tasks',
                        'color' => '#e83e8c'
                    ],
                    [
                        'name' => 'СИСТЕМНЫЕ УВЕДОМЛЕНИЯ',
                        'description' => 'Общие системные уведомления и алерты',
                        'route' => '/notification/system',
                        'icon' => 'fas fa-cog',
                        'color' => '#6c757d'
                    ],
                    [
                        'name' => 'ЛОГИ ОТПРАВКИ СООБЩЕНИЙ',
                        'description' => 'Просмотр отправленных e-mail сообщений',
                        'route' => route('notification.logs'),
                        'icon' => 'fas fa-envelope-open-text',
                        'color' => '#343a40'
                    ]
                ];
            @endphp
            
            @foreach($notificationModules as $module)
            <div style="margin-bottom: 15px;">
                <a href="{{ $module['route'] }}" style="text-decoration:none;color:inherit;">
                    <div class="notification-module-tile">
                        <div class="notification-module-content">
                            <div class="notification-module-icon">
                                <i class="{{ $module['icon'] }}"></i>
                            </div>
                            <div class="notification-module-info">
                                <h6 class="notification-module-title">{{ $module['name'] }}</h6>
                                <p class="notification-module-description">{{ $module['description'] }}</p>
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
.notification-module-tile {
    cursor: pointer;
    border-radius: 12px;
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    height: 90px;
    width: 500px;
    display: flex;
    align-items: center;
}

.notification-module-tile:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
    background: #ffffff;
    border-color: #d1d9e6;
}

.notification-module-content {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 15px;
}

.notification-module-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    color: #1E64D4;
    background-color: rgba(30, 100, 212, 0.08);
}

.notification-module-info {
    flex: 1;
    min-width: 0;
}

.notification-module-title {
    font-size: 16px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 4px;
    line-height: 1.3;
    letter-spacing: -0.02em;
}

.notification-module-description {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 0;
    line-height: 1.4;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.notification-module-tile:hover .notification-module-title {
    color: #1a202c;
}

.notification-module-tile:hover .notification-module-description {
    color: #495057;
}

/* Адаптивность */
@media (max-width: 768px) {
    .notification-module-tile {
        height: 80px;
        width: 100%;
        padding: 16px;
    }
    
    .notification-module-icon {
        width: 45px;
        height: 45px;
        font-size: 18px;
    }
    
    .notification-module-title {
        font-size: 15px;
    }
    
    .notification-module-description {
        font-size: 12px;
    }
}

@media (max-width: 576px) {
    .notification-module-tile {
        height: 75px;
        width: 100%;
        padding: 14px;
    }
    
    .notification-module-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .notification-module-title {
        font-size: 14px;
    }
    
    .notification-module-description {
        font-size: 11px;
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
