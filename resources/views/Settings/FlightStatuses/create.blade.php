@extends('layout.main')

@section('content')
<div class="content_center">
    <h2>Добавить статус рейса</h2>
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
    <form method="POST" action="{{ route('settings.flight-statuses.store') }}" style="max-width:520px;">
        @csrf
        <div class="mb-3">
            <label class="form-label">Статус</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Цвет</label>
            <div style="display:flex;gap:8px;align-items:center;">
                <input type="color" id="color" class="form-control form-control-color" value="{{ old('color', '#E8F5E9') }}" oninput="document.getElementById('colorHex').value=this.value">
                <input type="text" id="colorHex" name="color" class="form-control" placeholder="#RRGGBB" value="{{ old('color', '#E8F5E9') }}" oninput="document.getElementById('color').value=this.value">
            </div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="active" id="active" class="form-check-input" value="1" {{ old('active', true) ? 'checked' : '' }}>
            <label for="active" class="form-check-label">Активно</label>
        </div>

        <div class="efds-actions">
            <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
            <a href="{{ route('settings.flight-statuses.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
        </div>
    </form>
</div>
@endsection


