@extends('layout.main')

@section('content')
<div class="settings-container">
    <div class="row justify-content-center">
        <div class="col-auto">
            @php
                $tiles = [
                    ['name' => 'Master data', 'route' => route('modules.reliability.settings.master-data.index')],
                    ['name' => 'Projects', 'route' => route('modules.reliability.settings.inspection.projects')],
                    ['name' => 'Aircrafts', 'route' => route('modules.reliability.settings.inspection.aircrafts')],
                    ['name' => 'Work cards', 'route' => route('modules.reliability.settings.inspection.work-cards')],
                    ['name' => 'EEF registry', 'route' => route('modules.reliability.settings.inspection.eef-registry')],
                    ['name' => 'Work card materials', 'route' => route('modules.reliability.settings.inspection.work-card-materials')],
                    ['name' => 'Source card refs', 'route' => route('modules.reliability.settings.inspection.source-card-refs')],
                    ['name' => 'Case analyses', 'route' => route('modules.reliability.settings.inspection.case-analyses')],
                ];
            @endphp
            @foreach($tiles as $tile)
            <div style="margin-bottom: 10px;">
                <a href="{{ $tile['route'] }}" style="text-decoration:none;color:inherit;">
                    <div class="module-tile">
                        <div class="module-content">
                            <h6 class="module-title">{{ $tile['name'] }}</h6>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
<style>
.module-tile { cursor: pointer; border-radius: 8px; padding: 15px 20px; background: #f5f7fa; border: 1px solid #e8ecf1; transition: all 0.3s; height: 70px; width: 400px; display: flex; align-items: center; }
.module-tile:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); background: #fff; border-color: #d1d9e6; }
.module-title { font-size: 16px; font-weight: 600; color: #2d3748; margin: 0; }
.settings-container { background: white; min-height: calc(100vh - 80px); padding-top: 20px; }
</style>
@endsection
