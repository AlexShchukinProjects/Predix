@extends('layout.main')

@section('content')
<div class="container mt-4" style="width: 500px">
    <div class="mb-3">
        <h2 class="mb-0">Статусы рейсов</h2>
    </div>

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span>Всего записей: {{ $statuses->total() }}</span>
        </div>
        <div class="efds-table-header__actions">
            <a href="{{ route('settings.flight-statuses.create') }}" class="btn efds-btn efds-btn--primary">Добавить статус</a>
        </div>
    </div>

    <table class="table table-blue table-bordered">
        <thead>
            <tr>
                <th>Статус</th>
                <th>Цвет</th>
                <th style="text-align: center;">Активно</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statuses as $status)
                <tr class="fs-row" data-href="{{ route('settings.flight-statuses.edit', $status) }}" style="cursor:pointer;">
                    <td>{{ $status->name }}</td>
                    <td>
                        @php $c = $status->color ?? '#cccccc'; @endphp
                        <span style="display:inline-block;width:24px;height:24px;border:1px solid #ddd;background: {{ $c }};"></span>
                        <span style="margin-left:8px;">{{ $status->color }}</span>
                    </td>
                    <td style="text-align: center;">
                        @if($status->active)
                            <span style="color: #28a745; font-size: 20px; font-weight: bold;">✓</span>
                        @else
                            <span style="color: #dc3545; font-size: 20px; font-weight: bold;">✗</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $statuses->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mt-4 p-3 border rounded" style="background-color: #f8f9fa;">
        <h5 class="mb-3">Настройка цвета "Взлетел"</h5>
        <form method="POST" action="{{ route('settings.flight-statuses.update-special-color') }}">
            @csrf
            <input type="hidden" name="status_name" value="Взлетел">
            <div class="mb-3">
                <label class="form-label">Цвет "Взлетел"</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="color" id="takenOffColor" class="form-control form-control-color" value="{{ old('color', $takenOffColor ?? '#4CAF50') }}" oninput="document.getElementById('takenOffColorHex').value=this.value">
                    <input type="text" id="takenOffColorHex" name="color" class="form-control" placeholder="#RRGGBB" value="{{ old('color', $takenOffColor ?? '#4CAF50') }}" oninput="document.getElementById('takenOffColor').value=this.value" pattern="^#[0-9A-Fa-f]{6}$" required>
                </div>
                @error('color')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-success">Сохранить цвет</button>
        </form>
    </div>

    <div class="mt-4 p-3 border rounded" style="background-color: #f8f9fa;">
        <h5 class="mb-3">Настройка цвета "Тех обслуживание"</h5>
        <form method="POST" action="{{ route('settings.flight-statuses.update-special-color') }}">
            @csrf
            <input type="hidden" name="status_name" value="Тех обслуживание">
            <div class="mb-3">
                <label class="form-label">Цвет "Тех обслуживание"</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="color" id="maintenanceColor" class="form-control form-control-color" value="{{ old('color', $maintenanceColor ?? '#FF9800') }}" oninput="document.getElementById('maintenanceColorHex').value=this.value">
                    <input type="text" id="maintenanceColorHex" name="color" class="form-control" placeholder="#RRGGBB" value="{{ old('color', $maintenanceColor ?? '#FF9800') }}" oninput="document.getElementById('maintenanceColor').value=this.value" pattern="^#[0-9A-Fa-f]{6}$" required>
                </div>
                @error('color')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-success">Сохранить цвет</button>
        </form>
    </div>

    <div class="mt-4 p-3 border rounded" style="background-color: #f8f9fa;">
        <h5 class="mb-3">Настройка цвета "Фактическое время"</h5>
        <form method="POST" action="{{ route('settings.flight-statuses.update-special-color') }}">
            @csrf
            <input type="hidden" name="status_name" value="Фактическое время">
            <div class="mb-3">
                <label class="form-label">Цвет "Фактическое время"</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="color" id="actualTimeColor" class="form-control form-control-color" value="{{ old('color', $actualTimeColor ?? '#2196F3') }}" oninput="document.getElementById('actualTimeColorHex').value=this.value">
                    <input type="text" id="actualTimeColorHex" name="color" class="form-control" placeholder="#RRGGBB" value="{{ old('color', $actualTimeColor ?? '#2196F3') }}" oninput="document.getElementById('actualTimeColor').value=this.value" pattern="^#[0-9A-Fa-f]{6}$" required>
                </div>
                @error('color')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-success">Сохранить цвет</button>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.fs-row').forEach(function(row){
    row.addEventListener('click', function(){
      const href = this.getAttribute('data-href');
      if (href) window.location.href = href;
    });
  });
});
</script>
@endsection


