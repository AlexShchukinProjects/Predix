@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Добавить аэропорт</h1>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="table table-blue">
                </div>
                <div class="card-body">
                    <form action="{{ route('airports.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="NameRus" class="form-label">Название (RU) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('NameRus') is-invalid @enderror" 
                                       id="NameRus" name="NameRus" value="{{ old('NameRus') }}" required>
                                @error('NameRus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="NameEng" class="form-label">Название (EN)</label>
                                <input type="text" class="form-control @error('NameEng') is-invalid @enderror" 
                                       id="NameEng" name="NameEng" value="{{ old('NameEng') }}">
                                @error('NameEng')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="iata" class="form-label">IATA код <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('iata') is-invalid @enderror" 
                                       id="iata" name="iata" value="{{ old('iata') }}" 
                                       placeholder="Например: SVO" maxlength="3" required>
                                @error('iata')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="icao" class="form-label">ICAO код</label>
                                <input type="text" class="form-control @error('icao') is-invalid @enderror" 
                                       id="icao" name="icao" value="{{ old('icao') }}" 
                                       placeholder="Например: UUEE" maxlength="4">
                                @error('icao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="City" class="form-label">Город</label>
                                <input type="text" class="form-control @error('City') is-invalid @enderror" 
                                       id="City" name="City" value="{{ old('City') }}">
                                @error('City')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="Country" class="form-label">Страна</label>
                                <input type="text" class="form-control @error('Country') is-invalid @enderror" 
                                       id="Country" name="Country" value="{{ old('Country') }}">
                                @error('Country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="SummurUTC" class="form-label">Летнее время UTC</label>
                                <input type="text" class="form-control @error('SummurUTC') is-invalid @enderror" 
                                       id="SummurUTC" name="SummurUTC" value="{{ old('SummurUTC') }}"
                                       placeholder="Например: 4, 4:30 или -5:30">
                                @error('SummurUTC')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="WinterUTC" class="form-label">Зимнее время UTC</label>
                                <input type="text" class="form-control @error('WinterUTC') is-invalid @enderror" 
                                       id="WinterUTC" name="WinterUTC" value="{{ old('WinterUTC') }}"
                                       placeholder="Например: 4, 4:30 или -5:30">
                                @error('WinterUTC')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="Reglament" class="form-label">Регламент</label>
                                <textarea style="width: 100%;" class="form-control @error('Reglament') is-invalid @enderror" 
                                       id="Reglament" name="Reglament" rows="3">{{ old('Reglament') }}</textarea>
                                @error('Reglament')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="PDSP_ADP" class="form-label">Контакты ПДСП/АДП</label>
                                <textarea style="width: 100%;" class="form-control @error('PDSP_ADP') is-invalid @enderror" 
                                       id="PDSP_ADP" name="PDSP_ADP" rows="3">{{ old('PDSP_ADP') }}</textarea>
                                @error('PDSP_ADP')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="vip" class="form-label">Контакты VIP</label>
                                <textarea style="width: 100%;" class="form-control @error('vip') is-invalid @enderror" 
                                       id="vip" name="vip" rows="3">{{ old('vip') }}</textarea>
                                @error('vip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="Comments" class="form-label">Комментарий</label>
                                <textarea style="width: 100%;" class="form-control @error('Comments') is-invalid @enderror" 
                                       id="Comments" name="Comments" rows="3">{{ old('Comments') }}</textarea>
                                @error('Comments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="Checked" class="form-label">Проверено</label>
                                <input type="date" class="form-control @error('Checked') is-invalid @enderror" 
                                       id="Checked" name="Checked" value="{{ old('Checked') }}">
                                @error('Checked')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="efds-actions">
                            <button type="submit" class="btn efds-btn efds-btn--primary">
                                Сохранить
                            </button>
                            <a href="{{ route('airports.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.form-control {
    background-color: white;
    border-color: #ced4da;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.text-danger {
    color: #dc3545 !important;
}

.btn {
    border-radius: 0.375rem;
}

.invalid-feedback {
    display: block;
}

.main_screen {
    margin: 0 auto;
    width: 800px;
}
</style>
@endsection
