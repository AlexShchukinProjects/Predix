@extends('layout.main')

@section('content')
<div class="container mt-4">
    <h2>Редактировать тип технического обслуживания</h2>
    
    <form action="{{ route('maintenance-types.update', $maintenanceType) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Название *</label>
            <input style="background-color: white;border-color: #ced4da; width: 300px" type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $maintenanceType->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $maintenanceType->active) ? 'checked' : '' }}>
                <label class="form-check-label" for="active">
                    Активно
                </label>
            </div>
        </div>
        
        <div class="efds-actions">
            <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
            <a href="{{ route('maintenance-types.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
        </div>
    </form>
</div>
@endsection
