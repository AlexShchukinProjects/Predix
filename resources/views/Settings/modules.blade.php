@extends('layout.main')

@section('content')
<div class="settings-container">
    <div class="row justify-content-center">
        <div class="col-auto">
            <div class="d-flex justify-content-start align-items-center mb-4">
                <a href="{{ route('settings.index') }}" class="back-button">
                    <i class="fas fa-arrow-left me-2"></i>Назад к настройкам
                </a>
            </div>
            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif
            <p class="text-muted small mb-3">Отметьте модули, которые включены в системе для всей компании. На главной странице отображаются только выбранные модули. Если активен только один модуль, при открытии главной выполняется переход в него.</p>
            <form action="{{ route('settings.modules.update') }}" method="POST">
                @csrf
                <div class="modules-list border rounded p-3" style="max-width: 500px;">
                    @foreach($modules as $module)
                        <label class="d-flex align-items-center py-2 module-check-item {{ !$loop->last ? 'border-bottom' : '' }}" style="cursor: pointer;">
                            <input type="checkbox" name="modules[]" value="{{ $module['id'] }}" class="me-3"
                                @if(in_array($module['id'], $enabledIds)) checked @endif>
                            <span>{{ $module['name'] }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="efds-actions mt-3">
                    <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                    <a href="{{ route('settings.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>

<style>
/* Кнопка "Назад" как в других модулях */
.back-button {
    color: #007bff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 500;
    transition: color 0.3s ease;
    border: none;
    background: none;
    padding: 8px 0;
}

.back-button:hover {
    color: #0056b3;
    text-decoration: none;
}

.back-button i {
    font-size: 14px;
}
</style>
@endsection
