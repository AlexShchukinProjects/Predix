<style>
    .custom-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        border-left: 4px solid #007AFF;
    }

    .custom-notification.show {
        transform: translateX(0);
    }

    .custom-notification-success {
        border-left-color: #28a745;
    }

    .custom-notification-error {
        border-left-color: #dc3545;
    }

    .custom-notification-warning {
        border-left-color: #ffc107;
    }

    .custom-notification-info {
        border-left-color: #17a2b8;
    }

    .notification-content {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .notification-message {
        flex: 1;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        line-height: 1.4;
    }

    .notification-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #666;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .notification-close:hover {
        background-color: #f8f9fa;
        color: #333;
    }

    @media (max-width: 768px) {
        .custom-notification {
            top: 10px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }
</style>

<script>
window.showNotification = function(message, type) {
    type = type || 'info';
    var existing = document.querySelectorAll('.custom-notification');
    existing.forEach(function(n) { n.remove(); });

    var notification = document.createElement('div');
    notification.className = 'custom-notification custom-notification-' + type;
    notification.innerHTML =
        '<div class="notification-content">' +
            '<span class="notification-message">' + message + '</span>' +
            '<button class="notification-close">&times;</button>' +
        '</div>';

    document.body.appendChild(notification);

    setTimeout(function() {
        notification.classList.add('show');
    }, 100);

    setTimeout(function() {
        notification.classList.remove('show');
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 5000);

    var closeBtn = notification.querySelector('.notification-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            notification.classList.remove('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        });
    }
};
</script>
