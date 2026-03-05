@extends('layout.main')

@section('content')
<div class="container-fluid mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('modules.reliability.settings.index') }}" class="back-link" style="color: #007bff; text-decoration: none; font-size: 16px;">
                ← Назад к настройкам надежности
            </a>
            <h2 class="mb-0 mt-2" style="font-weight: 600; color: #2d3748; font-size: 24px;">Коды Типа ВС</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <p class="text-muted small mb-3">
        Здесь можно задать <strong>код Типа ВС</strong> для типов из справочника «Типы воздушных судов» (таблица <code>aircrafts_types</code>).
        Код сохраняется в поле <code>rus</code> и может использоваться модулем «Надёжность» в отчётах и выгрузках.
    </p>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('modules.reliability.settings.aircraft-type-codes.update') }}">
                @csrf

                <div class="efds-table-header mb-2">
                    <div class="efds-table-header__stats text-muted">
                        <span class="me-2">На странице:</span>
                        @php $currentPerPage = request()->get('per_page', $perPage ?? 50); @endphp
                        <select class="form-select form-select-sm d-inline-block" style="width: auto;"
                                name="per_page_selector"
                                onchange="updateAircraftTypeCodesPerPage(this.value)"
                                aria-label="Записей на странице">
                            <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="ms-2">Всего типов: {{ $types->total() }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-blue">
                        <tr>
                            <th style="width: 40%;">Тип ВС (RU)</th>
                            <th style="width: 20%;">ICAO</th>
                            <th style="width: 40%;">Код типа ВС</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td>
                                    <strong>{{ $type->name_rus ?? $type->icao ?? '—' }}</strong>
                                </td>
                                <td>{{ $type->icao ?? '—' }}</td>
                                <td>
                                    <input type="text"
                                           name="codes[{{ $type->id }}]"
                                           class="form-control form-control-sm"
                                           maxlength="50"
                                           value="{{ old('codes.'.$type->id, $type->rus) }}"
                                           placeholder="Введите код типа ВС">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Типы ВС не найдены</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($types, 'links'))
                    <div class="mt-3">
                        {{ $types->onEachSide(1)->links('vendor.pagination.safety-reporting') }}
                    </div>
                @endif

                <div class="efds-actions mt-3">
                    <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                    <a href="{{ route('modules.reliability.settings.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateAircraftTypeCodesPerPage(perPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
</script>
@endsection

