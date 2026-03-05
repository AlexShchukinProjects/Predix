@extends('layout.main')

@section('content')
<div class="container-fluid">
    <h1 class="h4 mb-3">Редактировать воздушное судно</h1>
    

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4" style="width: 1300px;">
        <div class="card-header">
            <h5 class="mb-0">Загрузить коды Тип ВС / Модификация из файла</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">В файле Excel (.xls, .xlsx) должны быть колонки: <strong>Бортовой № ВС</strong> (или Рег. №), <strong>Тип ВС (код)</strong>, <strong>Модификация (код)</strong>. По регистрационному номеру будут обновлены коды для всех подходящих ВС в парке.</p>
            <form action="{{ route('fleet.upload-type-codes') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-end gap-3">
                @csrf
                <div class="flex-grow-1">
                    <input type="file" name="file" class="form-control form-control-sm" accept=".xls,.xlsx" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Загрузить коды</button>
            </form>
        </div>
    </div>

    <div class="card" style="width: 1300px;">
        <form method="POST" action="{{ route('fleet.update', $aircraft->id) }}" class="card-body">
            @csrf @method('PATCH')
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Регистрационный номер (RegN) <span class="text-danger">*</span></label>
                    <input type="text" name="RegN" class="form-control @error('RegN') is-invalid @enderror" value="{{ old('RegN', $aircraft->RegN) }}">
                    @error('RegN')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Владелец (Owner)</label>
                    <select name="Owner" class="form-control @error('Owner') is-invalid @enderror">
                        <option value="">Выберите владельца</option>
                        <option value="JSC PREMIER AVIA" {{ old('Owner', $aircraft->Owner ?? '') == 'JSC PREMIER AVIA' ? 'selected' : '' }}>JSC PREMIER AVIA</option>
                        <option value="JSC JET AIR GROUP" {{ old('Owner', $aircraft->Owner ?? '') == 'JSC JET AIR GROUP' ? 'selected' : '' }}>JSC JET AIR GROUP</option>
                    </select>
                    @error('Owner')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Заводской номер ВС</label>
                    <input type="text" name="FactoryNumber" class="form-control @error('FactoryNumber') is-invalid @enderror" value="{{ old('FactoryNumber', $aircraft->FactoryNumber ?? '') }}">
                    @error('FactoryNumber')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Тип (Type) <span class="text-danger">*</span></label>
                    <select name="Type" class="form-control @error('Type') is-invalid @enderror">
                        <option value="">Выберите тип ВС</option>
                        @foreach($aircraftTypes ?? [] as $aircraftType)
                            <option value="{{ $aircraftType->icao }}" {{ old('Type', $aircraft->Type) == $aircraftType->icao ? 'selected' : '' }}>
                                {{ $aircraftType->name_rus ?? $aircraftType->icao }}
                            </option>
                        @endforeach
                    </select>
                    @error('Type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Тип ВС (код)</label>
                    <input type="text" name="type_code" class="form-control @error('type_code') is-invalid @enderror" value="{{ old('type_code', $aircraft->type_code ?? '') }}" maxlength="50">
                    @error('type_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Модификация (код)</label>
                    <input type="text" name="modification_code" class="form-control @error('modification_code') is-invalid @enderror" value="{{ old('modification_code', $aircraft->modification_code ?? '') }}" maxlength="50">
                    @error('modification_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Класс (Class)</label>
                    <input type="text" name="Class" class="form-control @error('Class') is-invalid @enderror" value="{{ old('Class', $aircraft->Class) }}">
                    @error('Class')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Пассажиров (Pax_number)</label>
                    <input type="number" name="Pax_number" class="form-control @error('Pax_number') is-invalid @enderror" value="{{ old('Pax_number', $aircraft->Pax_number) }}">
                    @error('Pax_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">База (Airport_base)</label>
                    <input type="text" name="Airport_base" class="form-control @error('Airport_base') is-invalid @enderror" value="{{ old('Airport_base', $aircraft->Airport_base) }}">
                    @error('Airport_base')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Дата выпуска (Date_manufacture)</label>
                    <input type="date" name="Date_manufacture" class="form-control @error('Date_manufacture') is-invalid @enderror" value="{{ old('Date_manufacture', $aircraft->Date_manufacture) }}">
                    @error('Date_manufacture')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ремонт (Repair)</label>
                    <input type="text" name="Repair" class="form-control @error('Repair') is-invalid @enderror" value="{{ old('Repair', $aircraft->Repair) }}">
                    @error('Repair')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Высота (Height), м</label>
                    <input type="number" step="0.01" name="Height" class="form-control @error('Height') is-invalid @enderror" value="{{ old('Height', $aircraft->Height) }}">
                    @error('Height')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Длина (Length), м</label>
                    <input type="number" step="0.01" name="Length" class="form-control @error('Length') is-invalid @enderror" value="{{ old('Length', $aircraft->Length) }}">
                    @error('Length')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Размах крыла (Wing), м</label>
                    <input type="number" step="0.01" name="Wing" class="form-control @error('Wing') is-invalid @enderror" value="{{ old('Wing', $aircraft->Wing) }}">
                    @error('Wing')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Крейсерская скорость (Cruise_speed), км/ч</label>
                    <input type="number" name="Cruise_speed" class="form-control @error('Cruise_speed') is-invalid @enderror" value="{{ old('Cruise_speed', $aircraft->Cruise_speed) }}">
                    @error('Cruise_speed')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Дальность (Range), км</label>
                    <input type="number" name="Range" class="form-control @error('Range') is-invalid @enderror" value="{{ old('Range', $aircraft->Range) }}">
                    @error('Range')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Максимальная взлетная масса (MWM), кг</label>
                    <input type="number" step="0.01" name="MWM" class="form-control @error('MWM') is-invalid @enderror" value="{{ old('MWM', $aircraft->MWM) }}">
                    @error('MWM')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Комментарий</label>
                    <textarea style="width: 1160px;" name="Description" class="form-control @error('Description') is-invalid @enderror" rows="3">{{ old('Description', $aircraft->Description) }}</textarea>
                    @error('Description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="btnsave-delete sticky-modal-footer">
                <div class="btnsave-delete-left">
                    <div class="">
                        <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                    </div>
                    <div class="btncancel btn-left">
                        <a href="{{ route('fleet.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
                    </div>
                </div>
                <div class="btn-delete">
                    <button type="button" class="btn efds-btn efds-btn--danger" data-bs-toggle="modal" data-bs-target="#deleteAircraftModal">
                        Удалить
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteAircraftModal" tabindex="-1" aria-labelledby="deleteAircraftModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAircraftModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить воздушное судно <strong>{{ $aircraft->RegN ?? $aircraft->aircraft_number }}</strong>?</p>
                <p class="text-danger mb-0">Это действие нельзя отменить.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn efds-btn efds-btn--outline-primary" data-bs-dismiss="modal">Отмена</button>
                <form action="{{ route('fleet.destroy', $aircraft->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn efds-btn efds-btn--danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
.form-control, .form-select {
    background-color: #ffffff !important;
}

.form-control:focus, .form-select:focus {
    background-color: #ffffff !important;
}

.form-control:disabled, .form-select:disabled {
    background-color: #f8f9fa !important;
}

/* Стили кнопок как в форме добавления маршрута */
.btnsave-delete {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: auto;
    align-items: center;
}

.btnsave-delete-left {
    display: flex;
    gap: 20px;
    align-items: center;
}



.btn-left {
    font-size: 16px !important;
    font-weight: 600 !important;
}

/* Дополнительные стили для кнопок */
.btn-left.btn-success,
.btn-left.btn-primary,
.btn-left.btn-outline-primary {
    font-size: 16px !important;
    font-weight: 600 !important;
    padding: 8px 16px;
    border-radius: 4px;
}

.btncancel {
    margin-left: 20px;
}

.btn-delete {
    margin-left: 20px;
}

.sticky-modal-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}
</style>
