@extends('layout.main')

@section('content')
<style>
    .main_screen {
        width: 600px;
    }
    .card {
        border: none;
    }
</style>
<div class="container-fluid">
    <div class="mb-3">
        <h2 class="mb-0">Воздушные суда</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="efds-table-header">
        <div class="efds-table-header__stats text-muted">
            <span class="me-2">На странице:</span>
            @php $currentPerPage = request()->get('per_page', $aircrafts->perPage()); @endphp
            <select class="form-select form-select-sm d-inline-block" style="width: auto;" name="per_page_selector" onchange="updateFleetPerPage(this.value)" aria-label="Записей на странице">
                <option value="10" {{ $currentPerPage == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
            </select>
            <span class="ms-2">Всего записей: {{ $aircrafts->total() }}</span>
        </div>
        <div class="efds-table-header__actions">
            <a href="{{ route('fleet.create') }}" class="btn efds-btn efds-btn--primary">Добавить ВС</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-blue">
                    <tr>
                        <th>Бортовой номер</th>
                        <th>Тип</th>
                        <th>База</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @foreach($aircrafts as $aircraft)
                        <tr class="aircraft-row" data-href="{{ route('fleet.edit', $aircraft->id) }}" style="cursor:pointer;">
                            <td>{{ $aircraft->RegN ?? $aircraft->aircraft_number ?? $aircraft->number ?? $aircraft->reg ?? '' }}</td>
                            <td>{{ $aircraft->Type ?? $aircraft->type ?? '' }}</td>
                            <td>{{ $aircraft->Airport_base ?? $aircraft->base ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($aircrafts, 'links'))
            <div style="margin-top: 20px;">
                {{ $aircrafts->onEachSide(1)->links('vendor.pagination.safety-reporting') }}
            </div>
        @endif
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.aircraft-row').forEach(function(row){
        row.addEventListener('click', function(){
            const href = this.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });
});
window.updateFleetPerPage = function(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
};
</script>
@endsection

<style>
    .card, .card-custom {
     background-color: white !important;
    }


</style>