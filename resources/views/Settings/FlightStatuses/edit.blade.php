@extends('layout.main')

@section('content')
<div class="content_center">
    <h2>Редактировать статус рейса</h2>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert" style="margin:10px 0;">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success" role="alert" style="margin:10px 0;">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('settings.flight-statuses.update', $flightStatus) }}" style="max-width:520px;">
        @csrf
        @method('PATCH')
        <div class="mb-3">
            <label class="form-label">Статус</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name', $flightStatus->name) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Цвет</label>
            <div style="display:flex;gap:8px;align-items:center;">
                <input type="color" id="color" class="form-control form-control-color" value="{{ old('color', $flightStatus->color ?? '#E8F5E9') }}" oninput="document.getElementById('colorHex').value=this.value">
                <input type="text" id="colorHex" name="color" class="form-control" placeholder="#RRGGBB" value="{{ old('color', $flightStatus->color ?? '#E8F5E9') }}" oninput="document.getElementById('color').value=this.value">
            </div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="active" id="active" class="form-check-input" value="1" {{ old('active', $flightStatus->active) ? 'checked' : '' }}>
            <label for="active" class="form-check-label">Активно</label>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="efds-actions">
                <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                <a href="{{ route('settings.flight-statuses.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
            </div>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteStatusModal">
                Удалить
            </button>
        </div>
    </form>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteStatusModal" tabindex="-1" aria-labelledby="deleteStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteStatusModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить статус <strong>{{ $flightStatus->name }}</strong>?</p>
                <p class="text-danger mb-0">Это действие нельзя отменить.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form action="{{ route('settings.flight-statuses.destroy', $flightStatus) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


