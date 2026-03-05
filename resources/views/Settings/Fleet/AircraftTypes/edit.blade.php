@extends('layout.main')

@section('content')
<style>
    .form-control {
        background-color: white;
        border-color: #ced4da;
    }
</style>

<div class="container-fluid">
    <h1 class="h4 mb-3">Редактировать тип ВС</h1>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card">
        <form method="POST" action="{{ route('settings.fleet.aircraft-types.update', $aircraftType->id) }}" class="card-body">
            @csrf @method('PATCH')
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label">ICAO</label>
                    <input type="text" name="icao" class="form-control" value="{{ old('icao', $aircraftType->icao) }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">IATA</label>
                    <input type="text" name="iata" class="form-control" value="{{ old('iata', $aircraftType->iata) }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Название (RU)</label>
                    <input type="text" name="name_rus" class="form-control" value="{{ old('name_rus', $aircraftType->name_rus) }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Название (EN)</label>
                    <input type="text" name="name_eng" class="form-control" value="{{ old('name_eng', $aircraftType->name_eng) }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Группа</label>
                    <input type="text" name="group" class="form-control" value="{{ old('group', $aircraftType->group) }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Экипаж 1</label>
                    <input type="number" name="crew1" class="form-control" value="{{ old('crew1', $aircraftType->crew1) }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Экипаж 2</label>
                    <input type="number" name="crew2" class="form-control" value="{{ old('crew2', $aircraftType->crew2) }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Цвет</label>
                    <input type="color" name="color" class="form-control form-control-color" value="{{ old('color', $aircraftType->color ?? '#007bff') }}">
                </div>
                <div class="col-12 col-md-6 d-flex align-items-end gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="helicopter" value="1" id="helicopter" @checked($aircraftType->helicopter)>
                        <label class="form-check-label" for="helicopter">Вертолет</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked($aircraftType->active)>
                        <label class="form-check-label" for="active">Активен</label>
                    </div>
                </div>
            </div>
            <div style="margin-top: 30px !important;" class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <button style="font-size: 16px; font-weight: 500;" class="btn btn-primary">Сохранить</button>
                    <a href="{{ route('settings.fleet.aircraft-types.index') }}" style="font-size: 16px; font-weight: 500; margin-left: 10px;" class="btn btn-outline-primary">Отмена</a>
                </div>
                <button style="color: white; font-weight: 500; font-size: 16px; margin-right: 10px;" type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAircraftTypeModal" style="font-size: 16px; font-weight: 500;">
                    Удалить
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteAircraftTypeModal" tabindex="-1" aria-labelledby="deleteAircraftTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAircraftTypeModalLabel">Удаление типа ВС</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Удалить тип ВС <strong>{{ $aircraftType->name_rus ?? $aircraftType->icao }}</strong>?</p>
                <p class="text-danger"><strong>Внимание:</strong> Это действие удалит все данные по типу ВС и не может быть отменено.</p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('settings.fleet.aircraft-types.destroy', $aircraftType->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn efds-btn efds-btn--danger">Удалить</button>
                </form>
                <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
@endsection


