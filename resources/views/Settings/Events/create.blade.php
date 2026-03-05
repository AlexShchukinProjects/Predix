@extends('layout.main')

@section('content')
<style>
.form-control-color {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.form-control-color:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
<div class="container mt-4">
    <h2>Добавить мероприятие</h2>
    <form action="{{ route('events.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Название</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="abbreviation" class="form-label">Сокращение <small class="text-muted">(максимум 10 символов)</small></label>
            <input type="text" class="form-control" id="abbreviation" name="abbreviation" value="{{ old('abbreviation') }}" maxlength="10" placeholder="Например: ТР, ПК, ОБ">
            @error('abbreviation')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="color" class="form-label">Цвет</label>
            <div class="d-flex align-items-center">
                <input type="color" class="form-control form-control-color me-2" id="color" name="color" value="{{ old('color', '#007bff') }}" style="width: 50px; height: 38px;">
                <input type="text" class="form-control" id="colorHex" placeholder="#007bff" value="{{ old('color', '#007bff') }}" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
            </div>
            <small class="text-muted">Выберите цвет или введите HEX код</small>
            @error('color')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="work_type" name="work_type" value="1" {{ old('work_type') ? 'checked' : '' }}>
            <label class="form-check-label" for="work_type">Рабочее</label>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="active" name="active" value="1" checked>
            <label class="form-check-label" for="active">Активно</label>
        </div>

        <div class="efds-actions">
            <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
            <a href="{{ route('events.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.getElementById('color');
    const colorHex = document.getElementById('colorHex');
    
    // Синхронизация color picker с текстовым полем
    colorPicker.addEventListener('input', function() {
        colorHex.value = this.value;
    });
    
    // Синхронизация текстового поля с color picker
    colorHex.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
            colorPicker.value = this.value;
        }
    });
    
    // Валидация при потере фокуса
    colorHex.addEventListener('blur', function() {
        if (!this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
            this.value = '#007bff';
            colorPicker.value = '#007bff';
        }
    });
});
</script>
@endsection 