@extends('layout.main')

@section('content')
<style>
    .form-control {
        background-color: white;
        border-color: #ced4da;
    }
</style>

<div class="container-fluid">
    <h1 class="h4 mb-3">Добавить тип ВС</h1>
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
        <form method="POST" action="{{ route('settings.fleet.aircraft-types.store') }}" class="card-body">
            @csrf
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label">ICAO</label>
                    <input type="text" name="icao" class="form-control" value="{{ old('icao') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">IATA</label>
                    <input type="text" name="iata" class="form-control" value="{{ old('iata') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Название (RU)</label>
                    <input type="text" name="name_rus" class="form-control" value="{{ old('name_rus') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Название (EN)</label>
                    <input type="text" name="name_eng" class="form-control" value="{{ old('name_eng') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Группа</label>
                    <input type="text" name="group" class="form-control" value="{{ old('group') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Экипаж 1</label>
                    <input type="number" name="crew1" class="form-control" value="{{ old('crew1') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Экипаж 2</label>
                    <input type="number" name="crew2" class="form-control" value="{{ old('crew2') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Цвет</label>
                    <input type="color" name="color" class="form-control form-control-color" value="{{ old('color', '#007bff') }}">
                </div>
                <div class="col-12 col-md-6 d-flex align-items-end gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="helicopter" value="1" id="helicopter" @checked(old('helicopter'))>
                        <label class="form-check-label" for="helicopter">Вертолет</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="active" @checked(old('active', true))>
                        <label class="form-check-label" for="active">Активен</label>
                    </div>
                </div>
            </div>
            <div style="margin-top: 30px !important;" class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <button style="font-size: 16px; font-weight: 500;" class="btn btn-primary">Сохранить</button>
                    <a href="{{ route('settings.fleet.aircraft-types.index') }}" style="font-size: 16px; font-weight: 500; margin-left: 10px;" class="btn btn-outline-primary">Отмена</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection


