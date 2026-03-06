@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            {{ isset($item) ? 'Edit engine number' : 'Add engine number' }}
        </h1>
    </div>

    <div class="card" style="max-width: 800px;">
        <form method="POST" action="{{ isset($item)
            ? route('modules.reliability.settings.engine-numbers.update', $item)
            : route('modules.reliability.settings.engine-numbers.store') }}">
            @csrf
            @if(isset($item))
                @method('PATCH')
            @endif

            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Номер <span class="text-danger">*</span></label>
                    <input type="text" name="number" class="form-control @error('number') is-invalid @enderror"
                           value="{{ old('number', $item->number ?? '') }}" required>
                    @error('number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Тип двигателя</label>
                    <select name="engine_type_id" class="form-select @error('engine_type_id') is-invalid @enderror">
                        <option value="">Не указан</option>
                        @foreach($engineTypes as $type)
                            <option value="{{ $type->id }}"
                                {{ (int) old('engine_type_id', $item->engine_type_id ?? null) === $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('engine_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $item->description ?? '') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="active" id="active"
                           value="1" {{ old('active', $item->active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="active">
                        Активен
                    </label>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('modules.reliability.settings.engine-numbers.index') }}" class="btn btn-outline-primary">Cancel</a>
                </div>

                @if(isset($item))
                    <form method="POST" action="{{ route('modules.reliability.settings.engine-numbers.destroy', $item) }}" onsubmit="return confirm('Delete record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection


