@extends('layout.main')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            {{ isset($item) ? 'Edit failure detection stage' : 'Add failure detection stage' }}
        </h1>
    </div>

    <div class="card" style="max-width: 800px;">
        <form method="POST" action="{{ isset($item)
            ? route('modules.reliability.settings.detection-stages.update', $item)
            : route('modules.reliability.settings.detection-stages.store') }}">
            @csrf
            @if(isset($item))
                @method('PATCH')
            @endif

            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $item->name ?? '') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Parent stage</label>
                    <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                        <option value="">— No parent —</option>
                        @foreach(($allStages ?? []) as $stageOption)
                            <option value="{{ $stageOption->id }}"
                                {{ (string) old('parent_id', $item->parent_id ?? '') === (string) $stageOption->id ? 'selected' : '' }}>
                                {{ $stageOption->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
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
                        Active
                    </label>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="efds-actions mb-0">
                    <button type="submit" class="btn efds-btn efds-btn--primary">Save</button>
                    <a href="{{ route('modules.reliability.settings.detection-stages.index') }}" class="btn efds-btn efds-btn--outline-primary">Cancel</a>
                </div>
            </div>
        </form>

        @if(isset($item))
            <div class="card-footer d-flex justify-content-end border-0 pt-0">
                <form method="POST" action="{{ route('modules.reliability.settings.detection-stages.destroy', $item) }}" onsubmit="return confirm('Delete record?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn efds-btn efds-btn--danger">Delete</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection

   

