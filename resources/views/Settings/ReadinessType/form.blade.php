@extends('layout.main')

@section('content')
<div class="container" style="width: 500px">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="">
                <div style="margin-bottom: 10px">
                    <h5 class="mb-0">{{ isset($readinessType) ? 'Редактировать тип готовности' : 'Создать тип готовности' }}</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ isset($readinessType) ? route('ReadinessType.update', $readinessType->id) : route('ReadinessType.store') }}">
                        @csrf
                        @if(isset($readinessType))
                            @method('PATCH')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Название</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $readinessType->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="active" value="0">
                                <input type="checkbox" class="form-check-input" id="active" name="active" value="1"
                                       {{ old('active', $readinessType->active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Активен</label>
                            </div>
                        </div>

                        <div class="efds-actions">
                            <button type="submit" class="btn efds-btn efds-btn--primary">
                                {{ isset($readinessType) ? 'Сохранить' : 'Сохранить' }}
                            </button>
                            <a href="{{ route('ReadinessType.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 