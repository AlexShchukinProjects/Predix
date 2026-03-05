@extends('layout.main')

@section('content')
<div class="settings-container">
    <div class="row justify-content-center">
        <div class="col-auto">
            @php
                $settingsModules = [
                    [
                        'name' => 'ФОРМА ОТКАЗА',
                        'route' => route('modules.reliability.settings.failure-form.index'),
                    ],
                    [
                        'name' => 'ВКЛАДКИ',
                        'route' => route('modules.reliability.settings.tabs.index'),
                    ],
                    [
                        'name' => 'ЭТАП ОБНАРУЖЕНИЯ ОТКАЗА',
                        'route' => route('modules.reliability.settings.detection-stages.index'),
                    ],
                    [
                        'name' => 'СТРУКТУРА ОТЧЁТА BUF',
                        'route' => route('modules.reliability.settings.report-structure-buf.index'),
                    ],
                    [
                        'name' => 'ПОСЛЕДСТВИЯ',
                        'route' => route('modules.reliability.settings.consequences.index'),
                    ],
                    [
                        'name' => 'СТАТУС WO',
                        'route' => route('modules.reliability.settings.wo-statuses.index'),
                    ],
                    [
                        'name' => 'СИСТЕМЫ / ПОДСИСТЕМЫ',
                        'route' => route('modules.reliability.settings.systems.index'),
                    ],
                    [
                        'name' => 'ТИПЫ ДВИГАТЕЛЕЙ',
                        'route' => route('modules.reliability.settings.engine-types.index'),
                    ],
                    [
                        'name' => 'НОМЕРА ДВИГАТЕЛЕЙ',
                        'route' => route('modules.reliability.settings.engine-numbers.index'),
                    ],
                    [
                        'name' => 'КОДЫ ТИПА ВС',
                        'route' => route('modules.reliability.settings.aircraft-type-codes.index'),
                    ],
                    [
                        'name' => 'ПРИНЯТЫЕ МЕРЫ',
                        'route' => route('modules.reliability.settings.taken-measures.index'),
                    ],
                    [
                        'name' => 'КОД ОРГАНИЗАЦИИ',
                        'route' => route('modules.reliability.settings.org-code.index'),
                    ],
                ];
            @endphp

            @foreach($settingsModules as $module)
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

.settings-container {
    background: white;
    min-height: calc(100vh - 80px);
    padding-top: 20px;
}
</style>
@endsection


