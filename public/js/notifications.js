// Глобальная функция для показа красивых уведомлений
window.showNotification = function(message, type = 'info') {
    // Удаляем существующие уведомления
    $('.custom-notification').remove();
    
    const notification = $(`
        <div class="custom-notification custom-notification-${type}">
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    
    // Показываем уведомление с анимацией
    setTimeout(() => {
        notification.addClass('show');
    }, 100);
    
    // Автоматически скрываем через 5 секунд
    setTimeout(() => {
        notification.removeClass('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
    
    // Обработчик закрытия
    notification.find('.notification-close').on('click', function() {
        notification.removeClass('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
};
