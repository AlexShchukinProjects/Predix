@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            {{ isset($item) ? 'Редактировать статус WO' : 'Добавить статус WO' }}
        </h1>
    </div>

    <div class="card" style="max-width: 800px;">
        <form method="POST" action="{{ isset($item)
            ? route('modules.reliability.settings.wo-statuses.update', $item)
            : route('modules.reliability.settings.wo-statuses.store') }}">
            @csrf
            @if(isset($item))
                @method('PATCH')
            @endif

            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Код</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code', $item->code ?? '') }}">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Наименование <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $item->name ?? '') }}" required>
                    @error('name')
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
                        Активен
                    </label>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="{{ route('modules.reliability.settings.wo-statuses.index') }}" class="btn btn-outline-primary">Отмена</a>
                </div>

                @if(isset($item))
                    <form method="POST" action="{{ route('modules.reliability.settings.wo-statuses.destroy', $item) }}" onsubmit="return confirm('Удалить запись?');">
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


