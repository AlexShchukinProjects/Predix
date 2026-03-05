@extends('layout.main')

@section('content')
<div class="container mt-4">
    <h2>{{ isset($event) ? 'Редактировать мероприятие' : 'Добавить мероприятие' }}</h2>
    <form method="POST" action="{{ isset($event) ? route('events.update', $event) : route('events.store') }}">
        @csrf
        @if(isset($event))
            @method('PATCH')
        @endif
        <div class="mb-3">
            <label for="name" class="form-label">Название</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $event->name ?? '') }}" required>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $event->active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">Активно</label>
        </div>
        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="{{ route('events.index') }}" class="btn btn-secondary">Отмена</a>
    </form>
</div>
@endsection 