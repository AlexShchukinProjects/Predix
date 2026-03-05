/**
 * Асинхронное сохранение форм документов
 */
class AsyncFormSaver {
    constructor(formSelector, saveUrl, options = {}) {
        this.form = document.querySelector(formSelector);
        this.saveUrl = saveUrl;
        this.options = {
            debounceDelay: 1000, // Задержка перед сохранением (мс)
            showNotifications: true,
            autoSave: true,
            ...options
        };
        
        this.debounceTimer = null;
        this.isSaving = false;
        this.lastSavedData = null;
        
        this.init();
    }
    
    init() {
        if (!this.form) return;
        
        // Добавляем индикатор сохранения
        this.addSaveIndicator();
        
        // Обработчики событий
        this.bindEvents();
        
        // Автосохранение при загрузке страницы
        if (this.options.autoSave) {
            setTimeout(() => this.saveForm(), 500);
        }
    }
    
    addSaveIndicator() {
        // Создаем индикатор сохранения
        const indicator = document.createElement('div');
        indicator.id = 'save-indicator';
        indicator.className = 'save-indicator';
        indicator.innerHTML = `
            <div class="save-indicator-content">
                <span class="save-status" id="save-status">Готов к сохранению</span>
                <div class="save-progress" id="save-progress" style="display: none;">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    <span>Сохранение...</span>
                </div>
            </div>
        `;
        
        // Добавляем стили
        const style = document.createElement('style');
        style.textContent = `
            .save-indicator {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1050;
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 10px 15px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                font-size: 14px;
                min-width: 200px;
            }
            .save-indicator.saving {
                border-color: #007bff;
                background: #f8f9fa;
            }
            .save-indicator.saved {
                border-color: #28a745;
                background: #f8fff9;
            }
            .save-indicator.error {
                border-color: #dc3545;
                background: #fff8f8;
            }
            .save-indicator-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .save-status {
                color: #6c757d;
            }
            .save-indicator.saving .save-status {
                color: #007bff;
            }
            .save-indicator.saved .save-status {
                color: #28a745;
            }
            .save-indicator.error .save-status {
                color: #dc3545;
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(indicator);
    }
    
    bindEvents() {
        // Обработчики для всех полей формы
        const inputs = this.form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            // Пропускаем скрытые поля и кнопки
            if (input.type === 'hidden' || input.type === 'submit' || input.type === 'button') {
                return;
            }
            
            // События для разных типов полей
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.addEventListener('change', () => this.debouncedSave());
            } else {
                input.addEventListener('input', () => this.debouncedSave());
                input.addEventListener('change', () => this.debouncedSave());
            }
        });
        
        // Обработчик для файлов
        const fileInputs = this.form.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', () => this.debouncedSave());
        });
    }
    
    debouncedSave() {
        // Очищаем предыдущий таймер
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        
        // Устанавливаем новый таймер
        this.debounceTimer = setTimeout(() => {
            this.saveForm();
        }, this.options.debounceDelay);
    }
    
    async saveForm() {
        if (this.isSaving) return;
        
        const formData = this.getFormData();
        
        // Проверяем, изменились ли данные
        if (this.lastSavedData && JSON.stringify(formData) === JSON.stringify(this.lastSavedData)) {
            return;
        }
        
        this.isSaving = true;
        this.updateIndicator('saving', 'Сохранение...');
        
        try {
            const response = await fetch(this.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateIndicator('saved', 'Сохранено');
                this.lastSavedData = formData;
                
                // Скрываем индикатор через 2 секунды
                setTimeout(() => {
                    this.updateIndicator('ready', 'Готов к сохранению');
                }, 2000);
            } else {
                throw new Error(result.message || 'Ошибка сохранения');
            }
        } catch (error) {
            console.error('Ошибка сохранения:', error);
            this.updateIndicator('error', 'Ошибка сохранения');
            
            // Скрываем индикатор ошибки через 3 секунды
            setTimeout(() => {
                this.updateIndicator('ready', 'Готов к сохранению');
            }, 3000);
        } finally {
            this.isSaving = false;
        }
    }
    
    getFormData() {
        const formData = new FormData(this.form);
        const data = {};
        
        // Преобразуем FormData в обычный объект
        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                // Если ключ уже существует, делаем массив
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        
        return data;
    }
    
    updateIndicator(status, message) {
        const indicator = document.getElementById('save-indicator');
        const statusElement = document.getElementById('save-status');
        const progressElement = document.getElementById('save-progress');
        
        if (!indicator) return;
        
        // Убираем все классы статуса
        indicator.classList.remove('saving', 'saved', 'error', 'ready');
        
        // Добавляем новый класс
        if (status !== 'ready') {
            indicator.classList.add(status);
        }
        
        // Обновляем сообщение
        if (statusElement) {
            statusElement.textContent = message;
        }
        
        // Показываем/скрываем прогресс
        if (progressElement) {
            progressElement.style.display = status === 'saving' ? 'flex' : 'none';
        }
    }
    
    // Публичный метод для ручного сохранения
    async manualSave() {
        await this.saveForm();
    }
}

// Инициализация для разных типов форм
document.addEventListener('DOMContentLoaded', function() {
    // Определяем тип формы по URL или другим признакам
    const path = window.location.pathname;
    
    if (path.includes('/Training/') || path.includes('/training')) {
        // Форма подготовки
        new AsyncFormSaver('form', '/planning/training/async-save');
    } else if (path.includes('/Permission/') || path.includes('/permission')) {
        // Форма допуска
        new AsyncFormSaver('form', '/planning/permission/async-save');
    } else if (path.includes('/FlightDoc/') || path.includes('/flightdoc')) {
        // Форма летного документа
        new AsyncFormSaver('form', '/planning/flightdoc/async-save');
    } else if (path.includes('/FlightCheck/') || path.includes('/flightcheck')) {
        // Форма летной проверки
        new AsyncFormSaver('form', '/planning/flightcheck/async-save');
    }
});
