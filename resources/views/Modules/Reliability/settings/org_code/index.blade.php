@extends('layout.main')

@section('content')
<div class="container-fluid mt-3" style="max-width: 600px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('modules.reliability.settings.index') }}" class="back-link" style="color: #007bff; text-decoration: none; font-size: 16px;">
                ← Back to reliability settings
            </a>
            <h2 class="mb-0 mt-2" style="font-weight: 600; color: #2d3748; font-size: 24px;">Organization code</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('modules.reliability.settings.org-code.update') }}">
                @csrf

                <div class="mb-3">
                    <label for="org_code" class="form-label fw-semibold">Organization code</label>
                    <input type="text"
                           class="form-control"
                           id="org_code"
                           name="org_code"
                           value="{{ old('org_code', $orgCode) }}"
                           placeholder="E.g.: 0130"
                           maxlength="100"
                           style="max-width: 300px;">
                    <div class="form-text text-muted">The code is used in Reliability module reports and exports.</div>
                </div>

                <button type="submit" class="btn efds-btn efds-btn--primary">
                    <i class="fas fa-save me-1"></i>Save
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
