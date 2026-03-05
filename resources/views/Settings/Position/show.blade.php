@extends("layout.main")

@section('content')
<div class="container" style="width: 500px">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="">
                <div style="margin-bottom: 10px">
                    <h5 class="mb-0">Просмотр должности</h5>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <p class="form-control-plaintext">{{ $position->Name }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Сокращение</label>
                        <p class="form-control-plaintext">{{ $position->short_name }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Тип экипажа</label>
                        <p class="form-control-plaintext">{{ $position->crew_type ?? 'Не указан' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Порядок сортировки</label>
                        <p class="form-control-plaintext">{{ $position->sort_order }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Активность</label>
                        <p class="form-control-plaintext">
                            <span class="badge {{ $position->Active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $position->Active ? 'Активен' : 'Неактивен' }}
                            </span>
                        </p>
                    </div>

                    <div class="">
                        <a href="{{ route('Position.edit', $position->id) }}" class="btn btn-primary">Редактировать</a>
                        <a href="{{ route('Position.index') }}" class="btn btn-secondary">Назад</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
