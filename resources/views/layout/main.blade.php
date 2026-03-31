<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-title" content="PredictiveMaintenance">
    <title>PredictiveMaintanence</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    
    <!-- iOS PWA Support -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Aviatix Chat">
    <link rel="apple-touch-icon" href="{{ asset('icons/LogoA.png') }}">
    
    <!-- Android PWA Support -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#1E64D4">
    @vite(['resources/sass/app.scss', 'resources/sass/style.css'])
    <script type="text/javascript" src="https://code.jquery.com/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ru.min.js"></script>
    <style>
        .nav_link_nav {
            position: relative;
            display: inline-block;
            z-index: 1003;
        }

        .dropdown-menu_nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            border: 1px solid #E5E5EA;
            border-radius: 8px;
            padding: 8px 0;
            min-width: 180px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 10001 !important;
           
            font-weight: 500;
            font-size: 14px;
            color: #333;


        }
       
 


        .nav_link_nav:hover .dropdown-menu_nav {
            display: block;
        }

        .dropdown-item {
            display: block;
            padding: 8px 16px;
            color: #1C1C1E;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .dropdown-item:hover {
            background-color: white;
            color: #007AFF;
            background-color: #f5f7fa;
        }

        .dropdown-item.active {
            color: #1E64D4;
            
        }

        /* Добавляем стили для навигации */
        .navbar-nav {
            position: relative;
            z-index: 1002;
        }

        .nav-ul {
            position: relative;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            z-index: 1002;
        }

        /* Добавляем стили для контейнера навигации */
        nav {
            position: relative;
            z-index: 1001;
        }

        /* Убеждаемся, что выпадающее меню не влияет на поток документа */
        .nav_link_nav:hover .dropdown-menu_nav {
            display: block;
        }

        /* Компактный header */
        .container_header {
            padding: 8px 0;
        
        }

        header {
            position: relative;
            z-index: 1000;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 60px;
            position: relative;
            z-index: 1000;
        }
        
        .header nav {
            display: flex;
            align-items: center;
            margin-left: auto;
            position: relative;
            z-index: 1001;
        }

        .navbar-nav {
            display: flex;
            flex-direction: row;
            margin: 0;
            padding: 0;
        }

        .nav-ul {
            margin-right: 20px;
        }
        
        .nav-ul:last-child {
            margin-right: 0;
        }

        .nav_link_nav {
            padding: 8px 12px;
            text-decoration: none;
            color: #495057 !important;
            font-weight:500;
            transition: color 0.2s ease;
            font-size: 16px;


         

            
        }

        .nav_link_nav:hover {
            color: #007bff;
        }

        /* Стили для активной вкладки */
        .nav_link_nav.active {
            color: #1E64D4 !important;
            font-weight: 500 !important;
        }
        
        /* Убираем лишние отступы у навигации */
        nav {
            margin: 0;
            padding: 0;
        }
        
        .container-fluid {
            padding: 0;
            margin: 0;
        }

        /* Стили для чата */
        .chat-container {
            display: flex;
            align-items: center;
        }
        
        .chat-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #6c757d;
            font-size: 16px;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .chat-link:hover {
            color: #007bff;
        }
        
        .chat-link i {
            margin-right: 8px;
            font-size: 18px;
        }
        
        .chat-link {
            position: relative;
        }
        
        .chat-unread-badge {
            position: absolute;
            top: -8px;
            right: -12px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            min-width: 20px;
            padding: 0 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .chat-unread-badge:empty {
            display: none;
        }
        
        .chat-unread-badge[data-count="0"] {
            display: none !important;
        }

        /* Стили для аватара пользователя */
        .user-avatar-container {
            position: relative;
            margin-left: 20px;
            margin-right: 10px;
        }

        .user-avatar {
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fbd6b2;
            border: 2px solid #e9ecef;
            text-transform: uppercase;
        }

        .user-avatar:hover {
            color: #007bff;
            border-color: #007bff;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 280px;
            z-index: 1000;
            margin-top: 5px;
        }

        .user-dropdown.show {
            display: block;
        }

        .user-info {
            padding: 16px;
        }

        .user-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.4;
        }

        .user-info-row:last-of-type {
            margin-bottom: 16px;
        }

        .user-info-row .label {
            font-weight: 600;
            color: #495057;
            min-width: 80px;
            padding: 0;
            margin: 0;
        }

        .user-info-row .value {
            color: #6c757d;
            text-align: right;
            flex: 1;
            margin-left: 10px;
            line-height: 1.4;
            padding: 0;
            margin-bottom: 0;
        }

        .status-active {
            color: #28a745 !important;
        }

        .status-blocked {
            color: #dc3545 !important;
        }

        .user-actions {
            border-top: 1px solid #e9ecef;
            padding-top: 12px;
            margin-top: 12px;
        }

        .logout-btn {
            width: 100%;
            padding: 8px 16px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .table thead th {
            background-color: #f5f7fa;
            color: #1E64D4;
            font-weight: 500;
            font-size: 14px;
        }

        /* ── iOS safe area (notch / Dynamic Island) ── */
        header {
            padding-top: env(safe-area-inset-top, 0px);
        }
        body {
            padding-left:  env(safe-area-inset-left, 0px);
            padding-right: env(safe-area-inset-right, 0px);
        }

        /* ── Inspection app pages: compact header for mobile ── */
        @media (max-width: 767.98px) {
            .header { min-height: 50px; }
            .container_header { padding: 6px 12px; }
            .header_logo_img { width: 130px !important; height: 28px !important; }
            .user-avatar { width: 34px; height: 34px; font-size: 13px; }
        }

    </style>
</head>
<body>

@if(session('impersonating_admin_id'))
<div style="position: fixed; top: 0; left: 0; right: 0; z-index: 10000; background: linear-gradient(90deg, #ff6b35 0%, #f7931e 100%); color: white; padding: 12px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-user-shield" style="font-size: 20px;"></i>
        <span style="font-weight: 600; font-size: 15px;">
            Logged in as: {{ auth()->user()->name }}
        </span>
    </div>
    <form action="{{ route('admin.users.stop-impersonating') }}" method="POST" style="margin: 0;">
        @csrf
        <button type="submit" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 6px 16px; border-radius: 6px; font-weight: 500; font-size: 14px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fas fa-sign-out-alt me-1"></i>
            Back to my account
        </button>
    </form>
</div>
<div style="height: 48px;"></div>
@endif


<header>
    <div class="container_header">
        <div class="header">
            @php
                $customLogo = \App\Models\SystemSetting::get('company_logo');
                $routeName = request()->route()->getName() ?? '';
                $path = request()->path();
                $isSettingsPage = $path === 'settings' || str_starts_with($path, 'settings/');
                $hideNavInHeader = $isSettingsPage
                    || $path === 'airports' || str_starts_with($path, 'airports/')
                    || $path === 'fleet' || str_starts_with($path, 'fleet/')
                    || $path === 'notification' || str_starts_with($path, 'notification/')
                    || $path === 'general-settings' || str_starts_with($path, 'general-settings/')
                    || $path === 'admin' || str_starts_with($path, 'admin/');
                $isInspectionAppPage = false;
                $isPersonalPage = false;
                $logoHref = '/';
            @endphp
            <a href="{{ $logoHref }}" class="header_logo" style="color: #1E64D4;">
                @if($customLogo)
                    <img src="{{ asset($customLogo) }}" alt="Логотип" class="header_logo_img" style="display:inline-block;width:200px;height:35px;object-fit:contain;">
                @else
                    <span class="header_logo_img" style="display:inline-block;width:200px;height:35px;background-color:currentColor;-webkit-mask:url('{{ asset('images/aviatix_traced.svg') }}') no-repeat center / contain;mask:url('{{ asset('images/aviatix_traced.svg') }}') no-repeat center / contain;" aria-label="Aviatix"></span>
                @endif
            </a>
            @if(!$isPersonalPage && !$hideNavInHeader && !$isInspectionAppPage)
            <nav class="">
                <div class="container-fluid">

                    <ul class="navbar-nav" id="nav-menu">

                        @php
                            $currentRoute = request()->route()->getName();
                            $currentModule = str_starts_with($currentRoute ?? '', 'modules.reliability') ? 'reliability' : '';
                            $can = function($route) use ($userAllowedRoutes) {
                                return ($userAllowedRoutes ?? null) === null || (is_array($userAllowedRoutes) && in_array($route, $userAllowedRoutes, true));
                            };
                        @endphp

                        @if($currentRoute === 'dashboard')
                            @if($can('settings.index'))
                            <li class="nav-ul nav-item">
                                <a href="/settings" class="nav_link_nav">SETTINGS</a>
                            </li>
                            @endif
                        @elseif($currentModule === 'reliability')
                                @if($can('modules.reliability.index'))<li class="nav-ul nav-item"><a href="{{ route('modules.reliability.dashboards') }}" class="nav_link_nav {{ $currentRoute === 'modules.reliability.dashboards' ? 'active' : '' }}">DASHBOARDS</a></li>@endif
                                @if($can('modules.reliability.index'))<li class="nav-ul nav-item"><a href="{{ route('modules.reliability.index') }}" class="nav_link_nav {{ $currentRoute === 'modules.reliability.index' ? 'active' : '' }}">ANALYSIS</a></li>@endif
                            @if($can('modules.reliability.settings.index'))<li class="nav-ul nav-item"><a href="{{ route('modules.reliability.settings.index') }}" class="nav_link_nav {{ str_starts_with($currentRoute, 'modules.reliability.settings') ? 'active' : '' }}">DATA</a></li>@endif
                            @if($can('modules.reliability.settings.index'))<li class="nav-ul nav-item"><a href="{{ route('modules.reliability.master-data-schema') }}" class="nav_link_nav {{ $currentRoute === 'modules.reliability.master-data-schema' ? 'active' : '' }}" style="display:inline-flex;align-items:center;gap:5px;">MASTER DATA <i class="fas fa-diagram-project" style="font-size:11px;opacity:.65;"></i></a></li>@endif
                        @endif





                    </ul>




                </div>

            </nav>
            @endif

            <div style="display:flex; align-items:center; {{ ($isPersonalPage || $isInspectionAppPage) ? 'margin-left:auto;' : '' }}">
            @if(false) {{-- чат в хидере пока скрыт --}}
            <!-- Чат -->
            <div class="chat-container" style="margin-right: 15px; margin-left: 15px;">
                <a href="{{ route('chat.index') }}" class="chat-link">
                    <i class="fas fa-comments"></i>
                    <span>CHAT</span>
                    <span class="chat-unread-badge" id="chatUnreadBadge" style="display: none;">0</span>
                </a>
            </div>
            @endif

            <!-- Аватар пользователя -->
            <div class="user-avatar-container">
                <div class="user-avatar" onclick="toggleUserMenu()">
                    @php
                        $user = Auth::user();
                        $initials = 'U';
                        
                        if ($user) {
                            $name = $user->name ?? '';
                            $parts = explode(' ', trim($name));
                            
                            if (count($parts) >= 2) {
                                // Если есть имя и фамилия - используем mb_substr для корректной работы с UTF-8
                                $initials = mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8') . mb_substr($parts[1], 0, 1, 'UTF-8'), 'UTF-8');
                            } elseif (count($parts) == 1 && !empty($parts[0])) {
                                // Если есть только одно имя
                                $initials = mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8'), 'UTF-8');
                            } else {
                                // Если нет имени, используем логин или email
                                $initials = mb_strtoupper(mb_substr($user->login ?? $user->email ?? 'U', 0, 1, 'UTF-8'), 'UTF-8');
                            }
                        }
                    @endphp
                    {{ $initials }}
                </div>
                
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-info">
                        <div class="user-info-row">
                            <span class="label">Name:</span>
                            <span class="value">{{ Auth::user()->name ?? 'Not set' }}</span>
                        </div>
                        <div class="user-info-row">
                            <span class="label">Email:</span>
                            <span class="value">{{ Auth::user()->email ?? 'Not set' }}</span>
                        </div>
                        <div class="user-info-row">
                            <span class="label">Login:</span>
                            <span class="value">{{ Auth::user()->login ?? Auth::user()->email ?? 'Not set' }}</span>
                        </div>
                        <div class="user-info-row">
                            <span class="label">Status:</span>
                            <span class="value status-{{ Auth::user()->status ?? 'active' }}">
                                {{ Auth::user()->status === 'active' ? 'Active' : 'Blocked' }}
                            </span>
                        </div>
                        <div class="user-info-row">
                            <span class="label">Position:</span>
                            <span class="value">{{ Auth::user()->position ?? 'Not set' }}</span>
                        </div>
                        <div class="user-info-row">
                            <span class="label">Roles:</span>
                            <span class="value">
                                @if(Auth::user() && Auth::user()->roles && Auth::user()->roles->count() > 0)
                                    {{ Auth::user()->roles->pluck('name')->join(', ') }}
                                @else
                                    Not assigned
                                @endif
                            </span>
                        </div>
                        <div class="user-actions">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="logout-btn">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            </div>{{-- /chat+avatar wrapper --}}




        </div>
    </div>



</header>





<div class="container_main">


    <div class="main_screen">


        @yield('content')


    </div>

</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('show');
}

// Закрываем меню при клике вне его
document.addEventListener('click', function(event) {
    const avatarContainer = document.querySelector('.user-avatar-container');
    const dropdown = document.getElementById('userDropdown');
    
    if (!avatarContainer.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>

    <script>
        // Обновление счетчика непрочитанных сообщений в шапке
        window.updateChatUnreadBadge = function() {
            const badge = document.getElementById('chatUnreadBadge');
            if (!badge) return;
            
            fetch('{{ route("chat.unreadCount") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = data.unread_count || 0;
                    badge.textContent = count > 99 ? '99+' : count.toString();
                    badge.setAttribute('data-count', count);
                    
                    if (count > 0) {
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                    
                    // На мобильной странице чата — дублируем счётчик в фиксированный бейдж (header там скрыт)
                    const badgeMobile = document.getElementById('chatUnreadBadgeMobile');
                    if (badgeMobile) {
                        badgeMobile.textContent = count > 0 ? (count > 99 ? '99+' : count.toString()) : '';
                        badgeMobile.setAttribute('data-count', count);
                    }
                    
                    // Badging API (Chrome/Edge/Android) — на иконке вкладки/приложения; Safari/iOS не поддерживает
                    if ('setAppBadge' in navigator) {
                        try {
                            if (count > 0) {
                                navigator.setAppBadge(count > 99 ? 99 : count);
                            } else {
                                navigator.clearAppBadge();
                            }
                        } catch (e) {
                            console.debug('Badging API:', e);
                        }
                    } else {
                        // Fallback для iPhone/Safari: число непрочитанных в заголовке вкладки (видно в переключателе и при добавлении на домашний экран)
                        var baseTitle = document.querySelector('meta[name="app-title"]')?.getAttribute('content') || 'Авиатикс';
                        document.title = count > 0 ? '(' + (count > 99 ? '99+' : count) + ') ' + baseTitle : baseTitle;
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка при обновлении счетчика непрочитанных сообщений:', error);
            });
        };
        
        // Регистрация Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('Service Worker зарегистрирован:', registration.scope);
                    })
                    .catch(err => {
                        console.log('Ошибка регистрации Service Worker:', err);
                    });
            });
        }
        
        // Обновляем счетчик при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            updateChatUnreadBadge();
            
            // Обновляем счетчик каждые 30 секунд
            setInterval(updateChatUnreadBadge, 30000);
        });
        
        // Функция-алиас для совместимости
        function updateChatUnreadCount() {
            updateChatUnreadBadge();
        }
    </script>

    @include('components.datetime-mask')
    @include('components.notifications')

    <style>
        .bg-secondary,
        .badge.bg-secondary,
        .text-bg-secondary {
            color: #000 !important;
        }
    </style>

</body>
</html>
