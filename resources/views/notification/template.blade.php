@extends("layout.main")

@section('content')

<div class="template-container">
    <div class="row">
        <div class="col-12">
            <!-- Заголовок страницы -->
            <div class="page-header mb-4">
                <h2 class="page-title">{{ $config['title'] }}</h2>
                <p class="page-subtitle">Редактирование шаблона e-mail уведомления</p>
            </div>

            <!-- Сообщения об успехе/ошибке -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(!empty($variableHints))
            <!-- Подсказка по переменным для подстановки в теме и тексте письма -->
            <div class="alert alert-info mb-4" role="alert">
                <strong><i class="fas fa-info-circle me-2"></i>Доступные переменные для подстановки</strong>
                <p class="mb-1 mt-2">В теме и в тексте письма можно использовать:</p>
                <ul class="mb-0 mt-1">
                    @foreach($variableHints as $hint)
                    <li><code>{{ $hint['var'] }}</code> — {{ $hint['label'] }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Форма редактирования шаблона -->
            <form action="{{ route('notification.template.update', ['module' => $module, 'template' => $template]) }}" method="POST" id="templateForm">
                @csrf
                @method('PUT')

                <div class="template-form">
                    <!-- Левая колонка - лейблы -->
                    <div class="form-labels">
                        <div class="form-group">
                            <label class="form-label">Название</label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Тема</label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Активность</label>
                        </div>
                        <div class="form-group form-group-textarea">
                            <label class="form-label form-label-textarea">Содержимое письма</label>
                        </div>
                    </div>

                    <!-- Правая колонка - поля ввода -->
                    <div class="form-fields">
                        <div class="form-group">
                            <input type="text" class="form-control" name="name" value="{{ $config['name'] }}" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="subject" value="{{ $config['subject'] }}" required>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" class="form-check-input" name="active" value="1" {{ $config['active'] ? 'checked' : '' }}>
                                <span class="checkbox-label">Активность</span>
                            </div>
                        </div>
                        <div class="form-group form-group-textarea">
                            <div class="textarea-header">
                                <button style="margin-right:0px;"      type="button" class="btn btn-outline-primary preview-btn" onclick="previewTemplate()">
                                    Предпросмотр
                                </button>
                            </div>
                            <textarea class="form-control email-content" name="content" rows="12" required>{{ $config['content'] }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="form-actions">
                    <button style="margin-right:0px;" type="submit" class="btn btn-primary save-btn">
                        Сохранить
                    </button>
                </div>
                <!-- Скрытые поля для сохранения технических настроек уведомлений -->
                <input type="hidden" name="notify_days" value="{{ $config['notify_days'] }}">
                <input type="hidden" name="notify_frequency" value="{{ $config['notify_frequency'] }}">

            </form>
        </div>
    </div>
</div>

<style>
.template-container {
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

.template-form {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.form-labels {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.main_screen {
   
    width: 1000px;
}

.form-fields {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    align-items: center;
    min-height: 45px;
}

.form-group-textarea {
    align-items: flex-start;
    min-height: 300px;
    flex-direction: column;
}

.form-label {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    height: 40px;
}

.form-label-textarea {
    height: auto;
    align-items: flex-start;
    padding-top: 10px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    background-color: white;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    border-color: #1E64D4;
    box-shadow: 0 0 0 0.2rem rgba(30, 100, 212, 0.25);
    outline: none;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    height: 40px;
}

.form-check-input {
    width: 18px;
    height: 18px;
    border: 2px solid #dc3545;
    border-radius: 3px;
    background-color: white;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

.form-check-input:checked::after {
    content: '✓';
    color: white;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.checkbox-label {
    font-size: 14px;
    color: #495057;
    cursor: pointer;
}

.notify-days-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    height: 40px;
}

.notify-days-input {
    width: 80px;
    text-align: center;
}

.notify-days-text {
    font-size: 14px;
    color: #6c757d;
}

.email-content {
    resize: vertical;
    min-height: 200px;
    font-family: 'Courier New', monospace;
    line-height: 1.5;
}

.notify-frequency {
    width: 120px;
}

.textarea-header {
    width: 100%;
    display: flex;
    justify-content: flex-end;
    margin-bottom: 8px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.preview-btn {
    padding: 10px 20px;
    border: 1px solid #1E64D4;
    background-color: white;
    color: #1E64D4;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

.preview-btn:hover {
    background-color: #1E64D4;
    color: white;
}

.save-btn {
    padding: 10px 20px;
    background-color: #1E64D4;
    color: white;
    border: 1px solid #1E64D4;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

.save-btn:hover {
    background-color: #1557b0;
    border-color: #1557b0;
}

/* Адаптивность */
@media (max-width: 768px) {
    .template-form {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-labels,
    .form-fields {
        gap: 15px;
    }
    
    .page-title {
        font-size: 20px;
    }
    
    .page-subtitle {
        font-size: 14px;
    }
}

@media (max-width: 576px) {
    .template-container {
        padding: 15px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .preview-btn,
    .save-btn {
        width: 100%;
    }
}
</style>

<script>
function previewTemplate() {
    const form = document.getElementById('templateForm');
    const formData = new FormData(form);
    
    // Создаем временную форму для предпросмотра
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '{{ route("notification.template.preview", ["module" => $module, "template" => $template]) }}';
    tempForm.target = '_blank';
    
    // Добавляем CSRF токен
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    tempForm.appendChild(csrfToken);
    
    // Добавляем данные формы, кроме spoof-поля _method (иначе Laravel
    // будет интерпретировать запрос как PUT, а для предпросмотра нужен POST)
    for (let [key, value] of formData.entries()) {
        if (key === '_method') {
            continue;
        }
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        tempForm.appendChild(input);
    }
    
    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
}
</script>

@endsection
