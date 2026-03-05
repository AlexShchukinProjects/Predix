<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'eFlight') }}</title>

        <!-- Favicon (как в основном приложении) -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/sass/app.scss'])
    </head>
    <body class="bg-light">
        <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center py-4">
            <div class="mb-4">
                <a href="/" class="text-decoration-none">
                    <div class="text-center">
                    @php $customLogo = \App\Models\SystemSetting::get('company_logo'); @endphp
                    <a href="/" class="header_logo" style="color: #1E64D4;">
                        @if($customLogo)
                            <img src="{{ asset($customLogo) }}" alt="Логотип" class="header_logo_img" style="display:inline-block;width:200px;height:35px;object-fit:contain;">
                        @else
                            <span class="header_logo_img" style="display:inline-block;width:200px;height:35px;background-color:currentColor;-webkit-mask:url('{{ asset('images/aviatix_traced.svg') }}') no-repeat center / contain;mask:url('{{ asset('images/aviatix_traced.svg') }}') no-repeat center / contain;" aria-label="Aviatix"></span>
                        @endif
                    </a>
                    </div>
                </a>
            </div>

            <div class="w-100" style="max-width: 400px;">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
