@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            {{ isset($item) ? 'Редактировать систему / подсистему' : 'Добавить систему / подсистему' }}
        </h1>
    </div>

    <div class="card" style="max-width: 800px;">
        <form method="POST" action="{{ isset($item)
            ? route('modules.reliability.settings.systems.update', $item)
            : route('modules.reliability.settings.systems.store') }}">
            @csrf
            @if(isset($item))
                @method('PATCH')
            @endif

            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Система <span class="text-danger">*</span></label>
                    <input type="text" name="system_name" class="form-control @error('system_name') is-invalid @enderror"
                           value="{{ old('system_name', request('system', $item->system_name ?? '')) }}" required>
                    @error('system_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Подсистема</label>
                    <input type="text" name="subsystem_name" class="form-control @error('subsystem_name') is-invalid @enderror"
                           value="{{ old('subsystem_name', $item->subsystem_name ?? '') }}">
                    @error('subsystem_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $item->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="active" id="active"
                           value="1" {{ old('active', $item->active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="active">
                        Активна
                    </label>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="{{ route('modules.reliability.settings.systems.index') }}" class="btn btn-outline-primary">Отмена</a>
                </div>

                @if(isset($item))
                    <form method="POST" action="{{ route('modules.reliability.settings.systems.destroy', $item) }}" onsubmit="return confirm('Удалить запись?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection


