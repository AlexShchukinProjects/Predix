@extends('layout.main')

@section('content')
<div class="container" style="width: 500px">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="">
                <div style="margin-bottom: 10px">
                    <h5 class="mb-0">{{ isset($position) ? 'Редактировать должность' : 'Создать должность' }}</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ isset($position) ? route('Position.update', $position->id) : route('Position.store') }}">
                        @csrf
                        @if(isset($position))
                            @method('PATCH')
                        @endif

                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Название</label>
                                <input style="width: 100%;" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $position->Name ?? '') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="short_name" class="form-label">Сокращение</label>
                                <input style="width: 100%;" type="text" class="form-control @error('short_name') is-invalid @enderror" 
                                       id="short_name" name="short_name" value="{{ old('short_name', $position->short_name ?? '') }}" required>
                                @error('short_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-12">
                                <label for="crew_type" class="form-label">Тип экипажа</label>
                                <select style="width: 100%;" class="form-control @error('crew_type') is-invalid @enderror" 
                                        id="crew_type" name="crew_type">
                                    <option value="">-- Выберите тип экипажа --</option>
                                    <option value="Летный экипаж" {{ old('crew_type', $position->crew_type ?? '') == 'Летный экипаж' ? 'selected' : '' }}>Летный экипаж</option>
                                    <option value="Кабинный экипаж" {{ old('crew_type', $position->crew_type ?? '') == 'Кабинный экипаж' ? 'selected' : '' }}>Кабинный экипаж</option>
                                    <option value="ИТП" {{ old('crew_type', $position->crew_type ?? '') == 'ИТП' ? 'selected' : '' }}>ИТП</option>
                                </select>
                                @error('crew_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Порядок сортировки</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $position->sort_order ?? 0) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">Активен</label>
                                <div class="form-check">
                                    <input type="hidden" name="active" value="0">
                                    <input type="checkbox" class="form-check-input" id="active" name="active" value="1"
                                           {{ old('active', $position->Active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">Активен</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button style="font-size: 16px; font-weight: 500;" type="submit" class="btn btn-primary">
                                    {{ isset($position) ? 'Сохранить' : 'Создать' }}
                                </button>
                                <a href="{{ route('Position.index') }}" style="font-size: 16px; font-weight: 500; margin-left: 10px;" class="btn btn-outline-primary">Отмена</a>
                            </div>
                            <div>
                                @if(isset($position))
                                    <button type="button" style="color: white; font-weight: 500; font-size: 16px;" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletePositionModal">
                                        Удалить
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($position))
<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deletePositionModal" tabindex="-1" aria-labelledby="deletePositionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePositionModalLabel">Удаление должности</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Удалить должность <strong>{{ $position->Name }}</strong>?</p>
                <p class="text-danger"><strong>Внимание:</strong> Это действие удалит все данные по должности и не может быть отменено.</p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('Position.destroy', $position->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="color: white; font-weight: 500; font-size: 16px;" class="btn btn-danger">Да, удалить</button>
                </form>
                <button type="button" style="font-size: 16px; font-weight: 500; margin-left: 10px;" class="btn btn-outline-primary" data-bs-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
