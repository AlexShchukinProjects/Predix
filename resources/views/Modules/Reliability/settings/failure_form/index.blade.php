@extends('layout.main')

@section('content')
<div class="container-fluid mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('modules.reliability.settings.index') }}" class="back-link" style="color: #007bff; text-decoration: none; font-size: 16px;">
                ← Назад к настройкам надежности
            </a>
            <h2 class="mb-0 mt-2" style="font-weight: 600; color: #2d3748; font-size: 24px;">Форма отказа</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <p class="text-muted small mb-3">
        Отметьте поля, которые должны отображаться в формах «Добавить отказ» и «Редактировать отказ». Снятая галочка скрывает поле в обеих формах.
    </p>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('modules.reliability.settings.failure-form.update') }}">
                @csrf
                <div class="row">
                    <div class="col-md-8 col-lg-6">
                        <div class="list-group list-group-flush">
                            @foreach($fields as $key => $label)
                                <div class="list-group-item d-flex align-items-center py-2 px-3">
                                    <div class="form-check flex-grow-1 mb-0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="visible[{{ $key }}]"
                                               value="1"
                                               id="visible_{{ $key }}"
                                               @checked($visibility[$key] ?? true)>
                                        <label class="form-check-label" for="visible_{{ $key }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="efds-actions mt-3">
                    <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                    <a href="{{ route('modules.reliability.settings.index') }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
