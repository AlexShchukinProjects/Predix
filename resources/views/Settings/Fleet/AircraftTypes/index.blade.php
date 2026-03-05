@extends('layout.main')

@section('content')
<style>
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
        align-items: right;
    }
    .card.aircraft-types-card,
    .aircraft-types-card .table-responsive,
    .aircraft-types-card .aircraft-types-actions {
        background-color: #ffffff !important;
    }
    .card.aircraft-types-card {
        border: none;
    }
    .aircraft-types-actions {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        margin-top: 1rem;
        margin-right: 0px;
        padding: 0 0 1rem 0;
        background: #ffffff !important;
    }
    .aircraft-types-actions .btn {
        margin-right: 0px;
    }
</style>
<div class="container-fluid">
    <div class="mb-3">
        <h2 class="mb-0">Типы воздушных судов</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span class="me-2">На странице:</span>
            @php $currentPerPage = request()->get('per_page', $types->perPage()); @endphp
            <select class="form-select form-select-sm d-inline-block" style="width: auto;" name="per_page_selector" onchange="updateAircraftTypesPerPage(this.value)" aria-label="Записей на странице">
                <option value="10" {{ $currentPerPage == 10 ? 'selected' : '' }}>10</option>
                <option value="15" {{ $currentPerPage == 15 ? 'selected' : '' }}>15</option>
                <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="ms-2">Всего записей: {{ $types->total() }}</span>
        </div>
        <div class="efds-table-header__actions">
            <a href="{{ route('settings.fleet.aircraft-types.create') }}" class="btn efds-btn efds-btn--primary">Добавить тип</a>
        </div>
    </div>

    <div class="card aircraft-types-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-blue">
                    <tr>
                        <th>ICAO</th>
                        <th>IATA</th>
                        <th>Название (RU)</th>
                        <th>Название (EN)</th>
                        <th>Группа</th>
                        <th>Цвет</th>
                        <th>Активен</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse(($types ?? collect()) as $type)
                        <tr class="type-row" data-href="{{ route('settings.fleet.aircraft-types.edit', $type->id) }}" style="cursor:pointer;">
                            <td>{{ $type->icao }}</td>
                            <td>{{ $type->iata }}</td>
                            <td>{{ $type->name_rus }}</td>
                            <td>{{ $type->name_eng }}</td>
                            <td>{{ $type->group }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ $type->color ?? '#007bff' }}; color: white;">
                                    {{ $type->icao }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($type->active)
                                    <span style="color: #28a745; font-size: 20px; font-weight: bold;">✓</span>
                                @else
                                    <span style="color: #dc3545; font-size: 20px; font-weight: bold;">✗</span>
                                @endif
                            </td>
                            
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Нет данных</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($types, 'links'))
            <div style="margin-top: 20px;">
                {{ $types->onEachSide(1)->links('vendor.pagination.safety-reporting') }}
            </div>
        @endif
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.type-row').forEach(function(row){
        row.addEventListener('click', function(){
            const href = this.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });
});
window.updateAircraftTypesPerPage = function(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
};
</script>
@endsection


