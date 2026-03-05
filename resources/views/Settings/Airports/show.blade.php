@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Информация об аэропорте</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('airports.edit', $airport) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>
                        Редактировать
                    </a>
                    <a href="{{ route('airports.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Назад к списку
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plane me-2"></i>
                        {{ $airport->NameRus ?? $airport->NameEng ?? 'Аэропорт' }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">ID:</td>
                                    <td>{{ $airport->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Название (RU):</td>
                                    <td>{{ $airport->NameRus ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Название (EN):</td>
                                    <td>{{ $airport->NameEng ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">IATA код:</td>
                                    <td>
                                        <span class="badge bg-primary fs-6">{{ $airport->iata ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">ICAO код:</td>
                                    <td>
                                        <span class="badge bg-info fs-6">{{ $airport->icao ?? '-' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Город:</td>
                                    <td>{{ $airport->City ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Страна:</td>
                                    <td>{{ $airport->Country ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Летнее время UTC:</td>
                                    <td>{{ $airport->SummurUTC ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Зимнее время UTC:</td>
                                    <td>{{ $airport->WinterUTC ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.table td {
    padding: 0.5rem 0.75rem;
    border: none;
}

.fw-bold {
    color: #495057;
    width: 40%;
}

.badge {
    font-size: 0.875em;
}

.btn {
    border-radius: 0.375rem;
}

iframe {
    border-radius: 0.375rem;
}

.main_screen {
    margin: 0 auto;
    width: 800px;
}
</style>
@endsection
