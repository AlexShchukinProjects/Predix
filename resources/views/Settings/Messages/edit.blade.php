@extends('layout.main')

@section('content')
<div class="container main_screen">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Редактировать сообщение</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('messages.update', $message) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="service">Название услуги *</label>
                            <input type="text" class="form-control @error('service') is-invalid @enderror" 
                                   id="service" name="service" value="{{ old('service', $message->Service) }}" required>
                            @error('service')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="group">Группа</label>
                            <input type="text" class="form-control @error('group') is-invalid @enderror" 
                                   id="group" name="group" value="{{ old('group', $message->Group) }}">
                            @error('group')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="template">Шаблон сообщения</label>
                            <textarea class="form-control @error('template') is-invalid @enderror" 
                                      id="template" name="template" rows="10" 
                                      placeholder="Введите шаблон сообщения...">{{ old('template', $message->Template) }}</textarea>
                            @error('template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="efds-actions">
                            <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                            <a href="{{ route('messages.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.main_screen {
    margin: 0 auto;
    width: 1000px;
    max-width: 100%;
}
.main_screen .form-control {
    width: 100%;
    background-color: #fff;
}
.main_screen textarea.form-control {
    width: 100%;
    box-sizing: border-box;
    background-color: #fff;
}
</style>
@endsection 