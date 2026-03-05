@extends("layout.main")

@section('content')

<div class="preview-container">
    <div class="row">
        <div class="col-12">
            <!-- Заголовок страницы -->
            <div class="page-header mb-4">
                <h2 class="page-title">Предпросмотр шаблона</h2>
                <p class="page-subtitle">{{ $previewData['name'] }}</p>
            </div>

            <!-- Предпросмотр e-mail -->
            <div class="email-preview">
                <div class="email-header">
                    <div class="email-field">
                        <label class="email-label">Тема:</label>
                        <div class="email-value">{{ $previewData['subject'] }}</div>
                    </div>
                </div>
                
                <div class="email-body">
                    <div class="email-content">
                        {!! nl2br(e($previewData['content'])) !!}
                    </div>
                </div>
            </div>

            <!-- Кнопка закрытия -->
            <div class="preview-actions">
                <button type="button" class="btn btn-secondary" onclick="window.close()">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.preview-container {
    background: white;
    min-height: calc(100vh - 80px);
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.page-title {
    font-size: 24px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 8px;
}

.page-subtitle {
    font-size: 16px;
    color: #6c757d;
    margin-bottom: 0;
}

.email-preview {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 30px;
}

.email-header {
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
    background: white;
    border-radius: 8px 8px 0 0;
}

.email-field {
    display: flex;
    align-items: center;
    gap: 15px;
}

.email-label {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    min-width: 60px;
}

.email-value {
    font-size: 16px;
    color: #2d3748;
    font-weight: 500;
}

.email-body {
    padding: 20px;
}

.email-content {
    font-size: 14px;
    line-height: 1.6;
    color: #2d3748;
    white-space: pre-wrap;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.preview-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    text-decoration: none;
    display: inline-block;
    border: 1px solid transparent;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

/* Увеличенный main_screen для окна предпросмотра */
.main_screen {
    max-width: 1000px  !important;
    width: 1000px !important;
    margin: 0 auto !important;
}

/* Адаптивность */
@media (max-width: 768px) {
    .preview-container {
        padding: 15px;
    }
    
    .page-title {
        font-size: 20px;
    }
    
    .page-subtitle {
        font-size: 14px;
    }
    
    .email-header,
    .email-body {
        padding: 15px;
    }
    
    .email-field {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .email-label {
        min-width: auto;
    }
}
</style>

@endsection
