@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Реестр воздушных судов</h1>
        <a href="{{ route('fleet.create') }}" class="btn btn-primary">Добавить ВС</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successNotification">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-blue">
                    <tr>
                        <th>Бортовой номер</th>
                        <th>Тип</th>
                        <th>База</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aircrafts as $aircraft)
                        <tr>
                            <td>{{ $aircraft->aircraft_number }}</td>
                            <td>{{ $aircraft->Type }}</td>
                            <td>{{ $aircraft->Airport_base }}</td>
                            <td class="text-end">
                                <a href="{{ route('fleet.edit', $aircraft->id) }}" class="btn btn-sm btn-outline-primary">Редактировать</a>
                                <form action="{{ route('fleet.destroy', $aircraft->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить запись?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($aircrafts, 'links'))
            <div class="card-footer">{{ $aircrafts->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Автоматически скрыть уведомление через 5 секунд
    document.addEventListener('DOMContentLoaded', function() {
        const notification = document.getElementById('successNotification');
        if (notification) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(notification);
                bsAlert.close();
            }, 5000);
        }
    });
</script>
@endpush
@endsection

