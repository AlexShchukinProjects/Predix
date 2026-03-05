// Общие функции для модального окна ответа за мероприятие

// Проверка пустоты текстового поля и подсветка
function checkTextareaEmpty(textarea) {
    if (!textarea) return;
    
    const isEmpty = !textarea.value.trim();
    if (isEmpty) {
        textarea.style.borderColor = '#dc3545';
        textarea.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
    } else {
        // Сбросить стили по умолчанию
        textarea.style.borderColor = '#ced4da';
        textarea.style.boxShadow = '';
    }
}

// Заполнение модального окна данными мероприятия
function populateActionModal(action) {
    // Заполнить описание задачи
    const taskDescription = document.querySelector('#actionResponseModal .task-description');
    if (taskDescription) {
        taskDescription.innerHTML = `<p>${escapeHtml(action.description)}</p>`;
    }
    
    // Заполнить срок исполнения
    const dueDateInput = document.querySelector('#actionResponseModal input[type="date"]');
    if (dueDateInput) {
        dueDateInput.value = action.due_date;
    }
    
    // Заполнить ответственного
    const responsibleInput = document.getElementById('actionResponseResponsible');
    if (responsibleInput && action.responsible) {
        responsibleInput.value = action.responsible.name;
    }
    
    // Заполнить подтверждающего
    const confirmingInput = document.getElementById('actionResponseConfirming');
    if (confirmingInput && action.confirming_user) {
        confirmingInput.value = action.confirming_user.name;
    }
    
    // Заполнить фактически выполненный объем работ
    const actualWorkTextarea = document.querySelector('#actionResponseModal textarea[placeholder*="фактически выполненный"]');
    if (actualWorkTextarea) {
        actualWorkTextarea.value = action.actual_work_volume || '';
        // Проверить и подсветить если пусто
        checkTextareaEmpty(actualWorkTextarea);
    }
    
    // Показать/скрыть кнопки в зависимости от статуса
    const saveFooter = document.getElementById('actionResponseFooterSave');
    const confirmFooter = document.getElementById('actionResponseFooterConfirm');
    
    if (action.status === 'pending_confirmation') {
        saveFooter.classList.add('d-none');
        confirmFooter.classList.remove('d-none');
    } else {
        saveFooter.classList.remove('d-none');
        confirmFooter.classList.add('d-none');
    }
}

// Вспомогательная функция для экранирования HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Инициализация обработчиков для модального окна
function initActionResponseModal(messageId, currentActionId = null, currentMessageId = null) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Обработка кнопки "Сохранить"
    const saveActionResponseBtn = document.getElementById('saveActionResponseBtn');
    if (saveActionResponseBtn) {
        saveActionResponseBtn.addEventListener('click', async function() {
            if (!currentActionId || !currentMessageId) return;
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Сохранение...';
            
            try {
                const actualWorkVolume = document.querySelector('#actionResponseModal textarea[placeholder*="фактически выполненный"]')?.value || '';
                const comment = document.getElementById('actionResponseComment')?.value?.trim() || '';
                
                const response = await fetch(`/modules/safety-reporting/messages/${currentMessageId}/actions/${currentActionId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        actual_work_volume: actualWorkVolume,
                        comment: comment
                    })
                });
                
                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error('Ошибка сохранения:', error);
            } finally {
                this.disabled = false;
                this.innerHTML = 'Сохранить';
            }
        });
    }

    // Обработка кнопки "Сохранить и завершить"
    const saveAndCompleteActionBtn = document.getElementById('saveAndCompleteActionBtn');
    if (saveAndCompleteActionBtn) {
        saveAndCompleteActionBtn.addEventListener('click', async function() {
            if (!currentActionId || !currentMessageId) return;
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Сохранение...';
            
            try {
                const actualWorkVolume = document.querySelector('#actionResponseModal textarea[placeholder*="фактически выполненный"]')?.value || '';
                const comment = document.getElementById('actionResponseComment')?.value?.trim() || '';
                
                // Получить текущую дату в формате YYYY-MM-DD
                const today = new Date().toISOString().split('T')[0];
                
                const response = await fetch(`/modules/safety-reporting/messages/${currentMessageId}/actions/${currentActionId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        actual_work_volume: actualWorkVolume,
                        actual_due_date: today, // Устанавливаем фактическую дату завершения
                        status: 'completed', // Меняем статус на "Выполнено"
                        comment: comment
                    })
                });
                
                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error('Ошибка сохранения и завершения:', error);
            } finally {
                this.disabled = false;
                this.innerHTML = 'Сохранить и завершить';
            }
        });
    }
}

// Добавить обработчик для текстового поля
document.addEventListener('input', function(e) {
    if (e.target.matches('textarea[placeholder*="фактически выполненный"]')) {
        checkTextareaEmpty(e.target);
    }
});
