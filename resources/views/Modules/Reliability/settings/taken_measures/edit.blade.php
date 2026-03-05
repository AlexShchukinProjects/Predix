@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            {{ isset($item) ? 'Редактировать принятую меру' : 'Добавить принятую меру' }}
        </h1>
    </div>

    <div class="card" style="max-width: 800px;">
        <form id="taken-measure-form" method="POST" action="{{ isset($item)
            ? route('modules.reliability.settings.taken-measures.update', $item)
            : route('modules.reliability.settings.taken-measures.store') }}">
            @csrf
            @if(isset($item))
                @method('PATCH')
            @endif

            <div class="card-body">
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
                        Активна
                    </label>
                </div>
            </div>
        </form>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="efds-actions mb-0">
                <button form="taken-measure-form" type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                <a href="{{ route('modules.reliability.settings.taken-measures.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
            </div>
            @if(isset($item))
                <form method="POST" action="{{ route('modules.reliability.settings.taken-measures.destroy', $item) }}" onsubmit="return confirm('Удалить запись?');" class="mb-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn efds-btn efds-btn--danger">Удалить</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

<style>

    .main_screen
    {width:600px;}
    
    .form-control {
        width:100% !important;
        background-color: #fff !important;
        }

</style>    

