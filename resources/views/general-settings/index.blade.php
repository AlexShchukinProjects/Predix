@extends('layout.main')

@section('content')
<div class="container-fluid mt-3" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ url()->previous() }}" class="back-link" style="color: #007bff; text-decoration: none; font-size: 16px;">
                ← Назад
            </a>
            <h2 class="mb-0 mt-2" style="font-weight: 600; color: #2d3748; font-size: 24px;">Общие настройки</h2>
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
            <h5 class="mb-4" style="font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px;">
                <i class="fas fa-image me-2" style="color: #1E64D4;"></i>Логотип компании
            </h5>

            {{-- Текущий логотип --}}
            @if($logoUrl)
                <div class="mb-4">
                    <div class="text-muted small mb-2">Текущий логотип:</div>
                    <div style="background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; display: inline-block;">
                        <img src="{{ asset($logoUrl) }}" alt="Логотип" style="max-height: 60px; max-width: 300px; object-fit: contain;">
                    </div>
                </div>
            @else
                <div class="mb-4">
                    <div class="text-muted small mb-2">Текущий логотип:</div>
                    <div style="background: #f8f9fa; border: 1px dashed #d1d5db; border-radius: 8px; padding: 16px; display: inline-flex; align-items: center; color: #9ca3af;">
                        <i class="fas fa-image me-2"></i> Логотип не установлен (используется стандартный)
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('general-settings.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Загрузить новый логотип</label>
                    <input type="file" name="logo" id="logoInput" class="form-control" accept="image/*">
                    <div class="form-text">Поддерживаемые форматы: JPG, PNG, SVG, WebP. Максимальный размер: 2 МБ.</div>
                    <div class="form-text text-muted">Рекомендуемый размер: ширина ~200px, высота ~35–50px.</div>
                </div>

                {{-- Предпросмотр --}}
                <div id="logoPreviewWrap" class="mb-3" style="display:none;">
                    <div class="text-muted small mb-1">Предпросмотр:</div>
                    <div style="background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; display: inline-block;">
                        <img id="logoPreview" src="" alt="Preview" style="max-height: 60px; max-width: 300px; object-fit: contain;">
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center">
                    <button type="submit" class="btn efds-btn efds-btn--primary">
                        <i class="fas fa-save me-1"></i>Сохранить
                    </button>

                    @if($logoUrl)
                        <button type="submit" name="remove_logo" value="1"
                                class="btn btn-outline-danger btn-sm"
                                onclick="return confirm('Удалить логотип и вернуть стандартный?')">
                            <i class="fas fa-trash me-1"></i>Удалить логотип
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('logoInput').addEventListener('change', function () {
    const file = this.files[0];
    const wrap = document.getElementById('logoPreviewWrap');
    const preview = document.getElementById('logoPreview');
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            wrap.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        wrap.style.display = 'none';
    }
});
</script>
@endsection
