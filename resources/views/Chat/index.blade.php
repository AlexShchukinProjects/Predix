@extends('layout.main')

@section('content')

<style>
    .chat-container {
        display: flex;
        height: calc(100vh - 95px);
        min-height: 0;
    }
    .container_main {
       
        padding-bottom: 0;
        height: calc(100vh - 95px);
        display: block;
        overflow: hidden;
    }

    /* Левая колонка - список чатов */
    .chat-sidebar {
        width: 35%;
        background: white;
        border-right: 1px solid #e5e5ea;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        height:100%
    }

    .chat-sidebar-header {
        padding: 16px;
        border-bottom: 1px solid #e5e5ea;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-search {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid #e5e5ea;
        border-radius: 20px;
        font-size: 14px;
        outline: none;
    }

    .chat-search:focus {
        border-color: #1E64D4;
    }

    .btn-create-group {
        padding: 8px 16px;
        background: #1E64D4;
        color: white;
        border: none;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-create-group:hover {
        background: #1557b0;
    }

    .chat-list {
        flex: 1;
        overflow-y: auto;
        padding: 0;
        margin: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        align-content: flex-start;
    }

    .chat-list-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .chat-list-item:hover {
        background: #f8f9fa;
    }

    .chat-list-item.active {
        background: #e8f0fe;
        border-left: 3px solid #1E64D4;
    }

    .chat-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #fbd6b2;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .chat-info {
        flex: 1;
        min-width: 0;
    }

    .chat-name {
        font-weight: 600;
        font-size: 15px;
        color: #1c1c1e;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-last-message {
        font-size: 13px;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }

    .chat-time {
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
    }

    .chat-unread {
        background: #dc3545;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 600;
        min-width: 18px;
        height: 18px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    /* Правая колонка - область переписки */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
        position: relative;
        min-height: 0;
        height: 100%;
    }

    .chat-main-empty {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 16px;
    }

    .chat-header {
        padding: 16px;
        border-bottom: 1px solid #e5e5ea;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background-color:#1E64D4;
        color:white;
    }

    .chat-header-name {
        font-weight: 600;
        font-size: 16px;
        color: white;
    }

    .chat-header-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .chat-header-participants-count {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.8);
        cursor: pointer;
        transition: color 0.2s;
    }

    .chat-header-participants-count:hover {
        color: rgba(255, 255, 255, 1);
        text-decoration: underline;
    }

    .chat-header-menu {
        position: relative;
        margin-left: auto;
    }

    .chat-header-menu-btn {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
    }

    .chat-header-menu-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .chat-header-menu-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 180px;
        z-index: 1000;
        display: none;
        overflow: hidden;
    }

    .chat-header-menu-dropdown.show {
        display: block;
    }

    .chat-header-menu-item {
        padding: 12px 16px;
        cursor: pointer;
        transition: background-color 0.2s;
        color: #333;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chat-header-menu-item:hover {
        background-color: #f5f5f5;
    }

    .chat-header-menu-item.danger {
        color: #dc3545;
    }

    .chat-header-menu-item.danger:hover {
        background-color: #fff5f5;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 16px;
        background: #f5f7fa;
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 0;
    }

    .message {
        display: flex;
        flex-direction: column;
        max-width: 70%;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message.own {
        align-self: flex-end;
        align-items: flex-end;
    }

    .message.other {
        align-self: flex-start;
        align-items: flex-start;
    }

    .message-bubble {
        padding: 10px 14px;
        border-radius: 18px;
        word-wrap: break-word;
        font-size: 14px;
        line-height: 1.4;
        display: flex;
        flex-direction: column;
    }

    /* Блок ответа внутри сообщения */
    .message-reply-block {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 8px;
        padding-bottom: 8px;
    }

    .message.own .message-reply-block {
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    }

    .message.other .message-reply-block {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .message-reply-stripe {
        width: 4px;
        background: #1E64D4;
        border-radius: 2px;
        flex-shrink: 0;
        min-height: 40px;
    }

    .message.own .message-reply-stripe {
        background: rgba(255, 255, 255, 0.8);
    }

    .message-reply-content {
        flex: 1;
        min-width: 0;
    }

    .message-reply-sender {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 2px;
    }

    .message.own .message-reply-sender {
        color: rgba(255, 255, 255, 0.95);
    }

    .message.other .message-reply-sender {
        color: #1E64D4;
    }

    .message-reply-text {
        font-size: 13px;
        opacity: 0.85;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }

    .message.own .message-reply-text {
        color: rgba(255, 255, 255, 0.9);
    }

    .message.other .message-reply-text {
        color: #495057;
    }

    .message-text-content {
        word-wrap: break-word;
        line-height: 1.4;
    }

    .message-attachment {
        margin-top: 6px;
    }

    .message-attachment img.chat-attachment-image {
        max-width: 260px;
        border-radius: 8px;
        display: block;
        cursor: pointer;
    }

    /* Модальное окно просмотра изображения в чате */
    .chat-image-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.85);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        box-sizing: border-box;
    }
    .chat-image-modal {
        display: flex;
        flex-direction: column;
        max-width: 90vw;
        max-height: 90vh;
        background: #1c1c1e;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    }
    .chat-image-modal-header {
        flex-shrink: 0;
        padding: 12px 16px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        background: #2c2c2e;
        min-height: 48px;
    }
    .chat-image-modal-close {
        width: 40px;
        height: 40px;
        border: none;
        background: transparent;
        color: #fff;
        cursor: pointer;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        transition: background 0.2s;
    }
    .chat-image-modal-close:hover {
        background: rgba(255,255,255,0.15);
    }
    .chat-image-modal-body {
        flex: 1;
        overflow: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        min-height: 0;
    }
    .chat-image-modal-img {
        max-width: 100%;
        max-height: calc(90vh - 120px);
        width: auto;
        height: auto;
        object-fit: contain;
    }
    .chat-image-modal-footer {
        flex-shrink: 0;
        padding: 12px 16px;
        background: #2c2c2e;
        color: #8e8e93;
        font-size: 13px;
        text-align: center;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .chat-image-modal-caption {
        word-break: break-word;
    }

    .chat-attachment-file {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: inherit;
        text-decoration: underline;
        word-break: break-all;
    }

    .chat-attachment-file-icon {
        font-size: 14px;
    }

    .message.own .message-bubble {
        background: #1E64D4;
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message.other .message-bubble {
        background: white;
        color: #1c1c1e;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .message-info {
        display: flex;
        gap: 8px;
        margin-top: 4px;
        font-size: 11px;
        color: #6c757d;
    }

    .message-sender {
        font-weight: 500;
    }

    .message-time {
        color: #999;
    }

    .message-status {
        margin-left: 4px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }

    .message-status.read {
        color: #1E64D4;
    }

    .message-status.unread {
        color: #999;
    }

    .chat-input-area {
        padding: 16px;
        border-top: 1px solid #e5e5ea;
        background: white;
        position: sticky;
        bottom: 0;
        z-index: 10;
        flex-shrink: 0;
        padding-bottom:30px;
    }

    /* Блок ответа на сообщение */
    .reply-preview {
        display: none;
        background: #e8f0fe;
        border-left: 4px solid #1E64D4;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 10px;
        position: relative;
    }

    .reply-preview.show {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .reply-preview-icon {
        color: #1E64D4;
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .reply-preview-content {
        flex: 1;
        min-width: 0;
    }

    .reply-preview-sender {
        font-weight: 600;
        font-size: 13px;
        color: #1E64D4;
        margin-bottom: 4px;
    }

    .reply-preview-text {
        font-size: 13px;
        color: #495057;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .reply-preview-close {
        background: none;
        border: none;
        color: #6c757d;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: color 0.2s;
    }

    .reply-preview-close:hover {
        color: #1E64D4;
    }

    .chat-input-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .chat-attachment-button {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: none;
        background: #e5e5ea;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #555;
        flex-shrink: 0;
    }

    .chat-attachment-button i {
        font-size: 26px; /* увеличиваем иконку в 2 раза */
    }

    .chat-attachment-button:hover {
        background: #d4d4d8;
    }

    .chat-attachment-filename {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 4px;
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-input {
        flex: 1;
        padding: 10px 14px;
        border: 1px solid #e5e5ea;
        border-radius: 20px;
        font-size: 14px;
        resize: none;
        outline: none;
        max-height: 120px;
        font-family: inherit;
    }

    .chat-input:focus {
        border-color: #1E64D4;
    }

    .btn-send {
        padding: 10px 24px;
        background: #1E64D4;
        color: white;
        border: none;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-send:hover:not(:disabled) {
        background: #1557b0;
    }

    .btn-send:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-send-icon {
        display: none;
        font-size: 16px;
    }

    .btn-send-text {
        display: inline;
    }

    /* Модальное окно создания группы */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 8px;
        padding: 24px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }

    .modal-content .btn-close {
        position: absolute;
        top: 16px;
        right: 16px;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #aaa;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .modal-content .btn-close:hover {
        color: #333;
    }

    .modal-header {
        margin-bottom: 20px;
        border-radius:3px;
        padding:1em;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: white;
    }

    .modal-body {
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #1c1c1e;
    }

    .form-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e5e5ea;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        font-family: inherit;
    }

    .form-input:focus {
        border-color: #1E64D4;
    }

    .form-input[type="textarea"],
    textarea.form-input {
        min-height: 100px;
        resize: vertical;
    }

    .user-search-results {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #e5e5ea;
        border-radius: 8px;
        margin-top: 8px;
    }

    .user-search-item {
        padding: 10px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-search-item:hover {
        background: #f8f9fa;
    }

    .user-search-item.selected {
        background: #e8f0fe;
    }

    .selected-participants {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .selected-participant {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #e8f0fe;
        border-radius: 16px;
        font-size: 13px;
    }

    .selected-participant-remove {
        cursor: pointer;
        color: #1E64D4;
        font-weight: 600;
    }

    .modal-footer {
        display: block;
        background-color:white;
        justify-content: flex-end;
        gap: 10px;
        padding:0px;
        padding-top:10px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #1E64D4;
        color: white;
    }

    .btn-primary:hover {
        background: #1557b0;
    }

    .btn-secondary {
        background: #e5e5ea;
        color: #1c1c1e;
    }

    .header {
        height: 60px;
    }

    .btn-secondary:hover {
        background: #d0d0d0;
    }

    .loading {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        align-self: flex-start;
    }

    .user-search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e5ea;
        border-radius: 8px;
        margin-top: 8px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        z-index: 1000;
    }

    .chat-sidebar-header {
        position: relative;
    }

    /* Контекстное меню */
    .message-context-menu {
        display: none;
        position: fixed;
        background: white;
        border: 1px solid #e5e5ea;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        min-width: 180px;
        padding: 4px 0;
    }

    .message-context-menu.show {
        display: block;
    }

    .context-menu-item {
        padding: 10px 16px;
        cursor: pointer;
        font-size: 14px;
        color: #1c1c1e;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background 0.2s;
    }

    .context-menu-item:hover {
        background: #f8f9fa;
    }

    .context-menu-item.danger {
        color: #dc3545;
    }

    .context-menu-item.danger:hover {
        background: #fff5f5;
    }

    .message {
        position: relative;
    }

    /* Мобильная адаптация */
    @media (max-width: 768px) {
        /* Скрываем header на мобильных устройствах */
        .container_header,
        .header {
            display: none !important;
        }

        /* Контейнер занимает всю высоту экрана */
        .container_main {
            height: 100vh !important;
        }

        .chat-container {
            position: relative;
            overflow: hidden;
            height: 100vh;
        }

        .chat-sidebar {
            width: 100%;
            position: absolute;
            left: 0;
            top: 0;
            z-index: 10;
            transition: transform 0.3s ease;
        }

        .chat-sidebar.hidden {
            transform: translateX(-100%);
        }

        .chat-main {
            width: 100%;
            position: absolute;
            left: 0;
            top: 0;
            z-index: 5;
            transition: transform 0.3s ease;
            transform: translateX(100%);
        }

        .chat-main.hidden {
            transform: translateX(100%);
        }

        .chat-main:not(.hidden) {
            transform: translateX(0);
        }

        .chat-back-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-right: 12px;
            padding: 0;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .chat-back-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Фиксируем chat-header вверху экрана */
        .chat-header {
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }

        .chat-header-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        /* Добавляем отступ для chat-messages, чтобы контент не уходил под header */
        .chat-messages {
            padding-top: 70px; /* высота chat-header + небольшой отступ */
            padding-bottom: 100px; /* высота chat-input-area + небольшой отступ */
        }

        /* Фиксируем chat-input-area внизу экрана */
        .chat-input-area {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }

        /* Компактная кнопка отправки на мобильных */
        .btn-send {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            padding: 0;
            flex-shrink: 0;
        }

        .btn-send-text {
            display: none;
        }

        .btn-send-icon {
            display: inline-flex;
        }

        /* Модальное изображение чата на мобильных: крестик в правом верхнем углу */
        .chat-image-modal-overlay {
            padding: 0;
        }
        .chat-image-modal {
            max-width: 100vw;
            max-height: 100vh;
            width: 100%;
            height: 100%;
            border-radius: 0;
        }
        .chat-image-modal-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1;
            background: rgba(0,0,0,0.5);
            justify-content: flex-end;
        }
        .chat-image-modal-close {
            width: 48px;
            height: 48px;
            font-size: 1.5rem;
        }
        .chat-image-modal-body {
            padding: 60px 12px 56px;
        }
        .chat-image-modal-img {
            max-height: calc(100vh - 116px);
        }
        .chat-image-modal-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }
    }

    /* Десктоп - скрываем кнопку назад */
    @media (min-width: 769px) {
        .chat-back-button {
            display: none;
        }
    }

    /* Бейдж непрочитанных в правом верхнем углу (только на мобильных, когда header скрыт) */
    .chat-unread-badge-mobile {
        display: none;
    }
    @media (max-width: 768px) {
        .chat-unread-badge-mobile {
            position: fixed;
            top: max(12px, env(safe-area-inset-top));
            right: max(12px, env(safe-area-inset-right));
            min-width: 24px;
            height: 24px;
            padding: 0 6px;
            background: #dc3545;
            color: white;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            box-sizing: border-box;
        }
        .chat-unread-badge-mobile:empty,
        .chat-unread-badge-mobile[data-count="0"] {
            display: none !important;
        }
        .chat-unread-badge-mobile:not(:empty):not([data-count="0"]) {
            display: flex;
        }
    }
</style>

<!-- Счётчик непрочитанных в углу экрана (виден только на мобильных при скрытом header) -->
<span class="chat-unread-badge-mobile" id="chatUnreadBadgeMobile" data-count="0" style="display: none;" aria-label="Непрочитанных сообщений"></span>

<div class="chat-container">
    <!-- Левая колонка -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <input type="text" id="userSearch" class="chat-search" placeholder="Поиск пользователей...">
            <button type="button" class="btn-create-group" onclick="openCreateGroupModal()">+ Группа</button>
            <div id="userSearchResults" class="user-search-dropdown" style="display: none;"></div>
        </div>
        <ul class="chat-list" id="chatList">
            <li class="loading">Загрузка чатов...</li>
        </ul>
    </div>

    <!-- Правая колонка -->
    <div class="chat-main" id="chatMain">
        <div class="chat-main-empty">
            Выберите чат для начала переписки
        </div>
    </div>
</div>

<!-- Контекстное меню для сообщений -->
<div id="messageContextMenu" class="message-context-menu">
    <div class="context-menu-item" id="contextEdit" onclick="editMessage()" style="display: none;">
        <i class="fas fa-edit"></i>
        <span>Редактировать</span>
    </div>
    <div class="context-menu-item" id="contextReply" onclick="replyToMessage()">
        <i class="fas fa-reply"></i>
        <span>Ответить</span>
    </div>
    <div class="context-menu-item" id="contextCopy" onclick="copyMessageText()">
        <i class="fas fa-copy"></i>
        <span>Скопировать текст</span>
    </div>
    <div class="context-menu-item danger" id="contextDelete" onclick="deleteMessage()" style="display: none;">
        <i class="fas fa-trash"></i>
        <span>Удалить</span>
    </div>
</div>

<!-- Модальное окно редактирования сообщения -->
<div id="editMessageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Редактировать сообщение</h3>
            <button type="button" class="btn-close" onclick="closeEditMessageModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #aaa;">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Текст сообщения</label>
                <textarea id="editMessageText" class="form-input" rows="5" style="resize: vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
             <button type="button" class="btn btn-primary" onclick="saveEditedMessage()">Сохранить</button>
            <button type="button" class="btn btn-secondary" onclick="closeEditMessageModal()">Отмена</button>
            
        </div>
    </div>
</div>

<!-- Модальное окно создания группы -->
<div id="createGroupModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Создать группу</h3>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Название группы</label>
                <input type="text" id="groupName" class="form-input" placeholder="Введите название группы">
            </div>
            <div class="form-group">
                <label class="form-label">Поиск участников</label>
                <input type="text" id="groupUserSearch" class="form-input" placeholder="Начните вводить имя...">
                <div id="groupUserSearchResults" class="user-search-results" style="display: none;"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Выбранные участники</label>
                <div id="selectedParticipants" class="selected-participants"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button style="margin-left:0px "type="button" class="btn btn-primary" onclick="createGroup()">Создать</button>    
            <button type="button" class="btn btn-secondary" onclick="closeCreateGroupModal()">Отмена</button>
            
        </div>
    </div>
</div>

<!-- Модальное окно управления участниками группы -->
<div id="participantsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Участники группы</h3>
            <button type="button" class="btn-close" onclick="closeParticipantsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Поиск пользователей для добавления</label>
                <input type="text" id="participantsUserSearch" class="form-input" placeholder="Начните вводить имя...">
                <div id="participantsUserSearchResults" class="user-search-results" style="display: none;"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Участники группы</label>
                <div id="participantsList" class="user-search-results" style="max-height: 300px; overflow-y: auto;">
                    <div class="loading">Загрузка участников...</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeParticipantsModal()">Закрыть</button>
        </div>
    </div>
</div>

<!-- Модальное окно просмотра изображения в чате (без перехода на другую страницу) -->
<div id="chatImageModal" class="chat-image-modal-overlay" style="display: none;" aria-hidden="true">
    <div class="chat-image-modal">
        <header class="chat-image-modal-header">
            <button type="button" class="chat-image-modal-close" onclick="closeChatImageModal()" title="Закрыть" aria-label="Закрыть">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="chat-image-modal-body">
            <img id="chatImageModalImg" src="" alt="" class="chat-image-modal-img">
        </div>
        <footer class="chat-image-modal-footer">
            <span id="chatImageModalCaption" class="chat-image-modal-caption"></span>
        </footer>
    </div>
</div>

<script>
let currentChatId = null;
let pollingInterval = null;
let lastMessageId = 0;
let selectedGroupParticipants = [];
let userSearchTimeout = null;
let groupUserSearchTimeout = null;
let participantsUserSearchTimeout = null;
let contextMenuMessageId = null;
let contextMenuMessageText = null;
let contextMenuIsOwn = false;
let replyToMessageId = null;
let replyToMessageText = null;
let replyToUserName = null;
let currentParticipantsChatId = null;
let currentAttachmentName = '';


// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    loadChats();
    
    // Инициализация мобильного вида
    initMobileView();
    
    // Обработчик изменения размера окна
    window.addEventListener('resize', function() {
        handleResize();
    });
    
    // Обработчик поиска пользователей для создания приватного чата
    const userSearch = document.getElementById('userSearch');
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            clearTimeout(userSearchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                document.getElementById('userSearchResults').style.display = 'none';
                return;
            }
            
            userSearchTimeout = setTimeout(() => {
                searchUsers(query);
            }, 300);
        });
        
        // Скрываем результаты при клике вне
        document.addEventListener('click', function(e) {
            if (!userSearch.contains(e.target) && !document.getElementById('userSearchResults').contains(e.target)) {
                document.getElementById('userSearchResults').style.display = 'none';
            }
        });
    }

    // Обработчик поиска пользователей для группы
    const groupUserSearch = document.getElementById('groupUserSearch');
    if (groupUserSearch) {
        groupUserSearch.addEventListener('input', function() {
            clearTimeout(groupUserSearchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                document.getElementById('groupUserSearchResults').style.display = 'none';
                return;
            }
            
            groupUserSearchTimeout = setTimeout(() => {
                searchUsersForGroup(query);
            }, 300);
        });
    }

    // Обработчик поиска пользователей для добавления в группу
    const participantsUserSearch = document.getElementById('participantsUserSearch');
    if (participantsUserSearch) {
        participantsUserSearch.addEventListener('input', function() {
            clearTimeout(participantsUserSearchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                document.getElementById('participantsUserSearchResults').style.display = 'none';
                return;
            }
            
            participantsUserSearchTimeout = setTimeout(() => {
                searchUsersForParticipants(query);
            }, 300);
        });
    }

    // Поле ввода создаётся при открытии чата (в renderChatHeader),
    // обработчик Enter навешиваем там.
    
    // Закрытие контекстного меню при клике вне его
    document.addEventListener('click', function(e) {
        const contextMenu = document.getElementById('messageContextMenu');
        if (contextMenu && !contextMenu.contains(e.target)) {
            // Скрываем контекстное меню как по классу, так и по инлайн-стилям,
            // чтобы оно гарантированно пропадало даже если было показано через style.display
            contextMenu.classList.remove('show');
            contextMenu.style.display = 'none';
            contextMenu.style.visibility = 'hidden';
        }
    });

    // Закрытие модального окна редактирования при клике вне его
    document.addEventListener('click', function(e) {
        const editModal = document.getElementById('editMessageModal');
        if (editModal && e.target === editModal) {
            closeEditMessageModal();
        }
        
        // Закрытие модального окна участников при клике вне его
        const participantsModal = document.getElementById('participantsModal');
        if (participantsModal && e.target === participantsModal) {
            closeParticipantsModal();
        }

        // Закрытие модального просмотра изображения при клике по оверлею
        const chatImageModal = document.getElementById('chatImageModal');
        if (chatImageModal && e.target === chatImageModal) {
            closeChatImageModal();
        }
    });

    // Клик по изображению в чате — открыть в модальном окне (без перехода на другую страницу)
    document.addEventListener('click', function(e) {
        const img = e.target.closest('img.chat-attachment-image');
        if (img) {
            e.preventDefault();
            e.stopPropagation();
            const src = img.getAttribute('data-attachment-url') || img.src;
            const caption = img.getAttribute('data-attachment-name') || img.alt || '';
            openChatImageModal(src, caption);
        }
    });

    // Поддержка открытия по Enter для доступности
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.classList.contains('chat-attachment-image')) {
            e.preventDefault();
            const img = e.target;
            const src = img.getAttribute('data-attachment-url') || img.src;
            const caption = img.getAttribute('data-attachment-name') || img.alt || '';
            openChatImageModal(src, caption);
        }
    });

    // Закрытие модального окна редактирования по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const editModal = document.getElementById('editMessageModal');
            if (editModal && editModal.classList.contains('show')) {
                closeEditMessageModal();
            }
            const chatImageModal = document.getElementById('chatImageModal');
            if (chatImageModal && chatImageModal.style.display !== 'none') {
                closeChatImageModal();
            }
        }
    });

    // Обработчик правого клика на сообщения
    document.addEventListener('contextmenu', function(e) {
        const messageElement = e.target.closest('.message');
        if (messageElement) {
            e.preventDefault();
            const messageId = parseInt(messageElement.getAttribute('data-message-id'));
            const isOwn = messageElement.classList.contains('own');
            const messageText = messageElement.querySelector('.message-bubble').textContent;
            // Для собственных сообщений используем имя текущего пользователя, для чужих - имя отправителя
            let userName = messageElement.querySelector('.message-sender')?.textContent || '';
            if (isOwn && !userName) {
                userName = '{{ Auth::user()->name }}';
            }
            showMessageContextMenu(e, messageId, isOwn, messageText, userName);
        }
    });
});

// Загрузка списка чатов
async function loadChats() {
    try {
        const response = await fetch('{{ route("chat.chats") }}', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('Ошибка при загрузке чатов');
        }

        const data = await response.json();
        
        if (data.success) {
            renderChatList(data.chats);
            
            // Обновляем счетчик непрочитанных сообщений в шапке
            if (typeof updateChatUnreadBadge === 'function') {
                updateChatUnreadBadge();
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
        document.getElementById('chatList').innerHTML = '<li class="loading">Ошибка при загрузке чатов</li>';
    }
}

// Отображение списка чатов
function renderChatList(chats) {
    const chatList = document.getElementById('chatList');
    
    if (chats.length === 0) {
        chatList.innerHTML = '<li class="loading">Нет чатов</li>';
        return;
    }

    // Подсчитываем общее количество непрочитанных сообщений
    let totalUnreadCount = 0;

    chatList.innerHTML = chats.map(chat => {
        const lastMessage = chat.last_message;
        const unreadCount = chat.unread_count || 0;
        totalUnreadCount += unreadCount;
        
        const unreadBadge = unreadCount > 0 
            ? `<span class="chat-unread" title="${unreadCount} непрочитанных сообщений">${unreadCount > 99 ? '99+' : unreadCount}</span>` 
            : '';
        
        const avatarInitials = chat.name.substring(0, 2).toUpperCase();
        const time = lastMessage ? formatTime(lastMessage.created_at) : '';
        let lastMessageCoreText = '';
        if (lastMessage) {
            if (lastMessage.message) {
                lastMessageCoreText = lastMessage.message;
            } else if (lastMessage.attachment_name) {
                lastMessageCoreText = (lastMessage.is_image ? 'Изображение: ' : 'Файл: ') + lastMessage.attachment_name;
            } else {
                lastMessageCoreText = '';
            }
        }
        const lastMessageText = lastMessage
            ? (lastMessage.user_name === '{{ Auth::user()->name }}' ? 'Вы: ' : '') + lastMessageCoreText
            : 'Нет сообщений';

        return `
            <li class="chat-list-item ${chat.id === currentChatId ? 'active' : ''}" onclick="openChat(${chat.id}, event)">
                <div class="chat-avatar">${avatarInitials}</div>
                <div class="chat-info">
                    <div class="chat-name">${escapeHtml(chat.name)}</div>
                    <div class="chat-last-message">${escapeHtml(lastMessageText)}</div>
                </div>
                <div class="chat-meta">
                    ${unreadBadge}
                    <div class="chat-time">${time}</div>
                </div>
            </li>
        `;
    }).join('');
}

// Открыть чат
async function openChat(chatId, event) {
    currentChatId = chatId;
    lastMessageId = 0;
    
    // Очищаем данные ответа при открытии нового чата
    replyToMessageId = null;
    replyToMessageText = null;
    replyToUserName = null;
    
    // Обновляем активный элемент в списке
    document.querySelectorAll('.chat-list-item').forEach(item => {
        item.classList.remove('active');
    });
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    } else {
        // Если event не передан, находим элемент по chatId
        document.querySelectorAll('.chat-list-item').forEach(item => {
            if (item.getAttribute('onclick')?.includes(`openChat(${chatId}`)) {
                item.classList.add('active');
            }
        });
    }
    
    // Загружаем сообщения
    await loadMessages(chatId);
    
    // Отмечаем как прочитанное
    markAsRead(chatId);
    
    // Запускаем polling
    startPolling(chatId);
    
    // Обновляем список чатов для обновления счетчика непрочитанных
    loadChats();
    
    // На мобильных устройствах переключаемся на chat-main
    showChatMain();
    
    // Обновление счетчика уже происходит в markAsRead и loadChats
}

// Инициализация мобильного вида
function initMobileView() {
    if (window.innerWidth <= 768) {
        const chatSidebar = document.querySelector('.chat-sidebar');
        const chatMain = document.querySelector('.chat-main');
        
        if (chatSidebar && chatMain) {
            // На мобильных устройствах изначально показываем sidebar
            chatSidebar.classList.remove('hidden');
            chatMain.classList.add('hidden');
        }
    }
}

// Обработчик изменения размера окна
function handleResize() {
    const chatSidebar = document.querySelector('.chat-sidebar');
    const chatMain = document.querySelector('.chat-main');
    
    if (window.innerWidth > 768) {
        // На десктопе убираем все классы hidden
        if (chatSidebar) chatSidebar.classList.remove('hidden');
        if (chatMain) chatMain.classList.remove('hidden');
    } else {
        // На мобильных устройствах
        if (chatSidebar && chatMain) {
            // Если чат открыт, показываем chat-main, иначе sidebar
            if (currentChatId) {
                chatSidebar.classList.add('hidden');
                chatMain.classList.remove('hidden');
            } else {
                chatSidebar.classList.remove('hidden');
                chatMain.classList.add('hidden');
            }
        }
    }
}

// Показать chat-main (для мобильных устройств)
function showChatMain() {
    if (window.innerWidth <= 768) {
        const chatSidebar = document.querySelector('.chat-sidebar');
        const chatMain = document.querySelector('.chat-main');
        
        if (chatSidebar && chatMain) {
            chatSidebar.classList.add('hidden');
            chatMain.classList.remove('hidden');
        }
    }
}

// Показать chat-sidebar (для мобильных устройств)
function showChatSidebar() {
    if (window.innerWidth <= 768) {
        const chatSidebar = document.querySelector('.chat-sidebar');
        const chatMain = document.querySelector('.chat-main');
        
        if (chatSidebar && chatMain) {
            chatSidebar.classList.remove('hidden');
            chatMain.classList.add('hidden');
        }
        
        // Останавливаем polling при возврате к списку чатов
        stopPolling();
        currentChatId = null;
    }
}

// Загрузка сообщений
async function loadMessages(chatId) {
    try {
        const response = await fetch(`{{ route('chat.messages', ['chatId' => ':chatId']) }}`.replace(':chatId', chatId), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('Ошибка при загрузке сообщений');
        }

        const data = await response.json();
        
        if (data.success) {
            renderChatHeader(data.chat);
            renderMessages(data.messages);
            
            if (data.messages.length > 0) {
                lastMessageId = data.messages[data.messages.length - 1].id;
            }
            
            // Обновляем счетчик непрочитанных сообщений в шапке после загрузки сообщений
            if (typeof updateChatUnreadBadge === 'function') {
                updateChatUnreadBadge();
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
    }
}

// Отображение заголовка чата
function renderChatHeader(chat) {
    const chatMain = document.getElementById('chatMain');
    const participantsCount = chat.participants_count || 0;
    const participantsCountHtml = chat.type === 'group' && participantsCount > 0
        ? `<div class="chat-header-participants-count" onclick="openParticipantsModal(${chat.id})">${participantsCount} ${getParticipantsWord(participantsCount)}</div>`
        : '';
    
    // Определяем, является ли текущий пользователь создателем группы
    const currentUserId = {{ Auth::id() }};
    const isCreator = chat.created_by && chat.created_by === currentUserId;
    
    // Формируем меню в зависимости от роли
    let menuButtonHtml = '';
    if (chat.type === 'group') {
        if (isCreator) {
            // Для создателя группы - показываем "Удалить группу"
            menuButtonHtml = `<div class="chat-header-menu">
                <button type="button" class="chat-header-menu-btn" onclick="toggleChatMenu(event)" title="Меню">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="chat-header-menu-dropdown" id="chatMenuDropdown">
                    <div class="chat-header-menu-item danger" onclick="deleteGroup(${chat.id})">
                        <i class="fas fa-trash"></i>
                        <span>Удалить группу</span>
                    </div>
                </div>
            </div>`;
        } else {
            // Для обычных участников - показываем "Покинуть группу"
            menuButtonHtml = `<div class="chat-header-menu">
                <button type="button" class="chat-header-menu-btn" onclick="toggleChatMenu(event)" title="Меню">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="chat-header-menu-dropdown" id="chatMenuDropdown">
                    <div class="chat-header-menu-item danger" onclick="leaveGroup(${chat.id})">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Покинуть группу</span>
                    </div>
                </div>
            </div>`;
        }
    }
    
    chatMain.innerHTML = `
        <div class="chat-header">
            <button type="button" class="chat-back-button" onclick="showChatSidebar()" title="Назад к списку чатов">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="chat-header-info">
            <div class="chat-header-name">${escapeHtml(chat.name)}</div>
                ${participantsCountHtml}
            </div>
            ${menuButtonHtml}
        </div>
        <div class="chat-messages" id="chatMessages"></div>
        <div class="chat-input-area">
            <div class="reply-preview" id="replyPreview">
                <i class="fas fa-reply reply-preview-icon"></i>
                <div class="reply-preview-content">
                    <div class="reply-preview-sender" id="replyPreviewSender"></div>
                    <div class="reply-preview-text" id="replyPreviewText"></div>
                </div>
                <button type="button" class="reply-preview-close" onclick="cancelReply()" title="Отменить ответ">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="chat-input-form" onsubmit="sendMessage(); return false;">
                <input type="file" id="chatAttachment" style="display:none">
                <button type="button" class="chat-attachment-button" title="Прикрепить файл" onclick="document.getElementById('chatAttachment').click()">
                    <i class="fas fa-paperclip"></i>
                </button>
                <div style="flex:1; display:flex; flex-direction:column; gap:4px;">
                    <div id="chatAttachmentFilename" class="chat-attachment-filename" style="display:none;"></div>
                    <textarea id="chatInput" class="chat-input" placeholder="Введите сообщение..." rows="1"></textarea>
                </div>
                <button type="submit" class="btn-send" id="sendBtn">
                    <span class="btn-send-text">Отправить</span>
                    <i class="fas fa-arrow-right btn-send-icon"></i>
                </button>
            </form>
        </div>
    `;
    
    // Обновляем блок ответа, если есть активный ответ
    updateReplyPreview();
    
    // Обработчик Enter для поля ввода
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    // Обработчик выбора файла
    const attachmentInput = document.getElementById('chatAttachment');
    const attachmentFilename = document.getElementById('chatAttachmentFilename');
    if (attachmentInput && attachmentFilename) {
        attachmentInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                currentAttachmentName = this.files[0].name;
                attachmentFilename.textContent = currentAttachmentName;
                attachmentFilename.style.display = 'block';
            } else {
                currentAttachmentName = '';
                attachmentFilename.textContent = '';
                attachmentFilename.style.display = 'none';
            }
        });
    }

    // Прокрутка вниз
    setTimeout(() => {
        scrollToBottom();
    }, 100);
}

// Получить правильное склонение слова "участник"
function getParticipantsWord(count) {
    const lastDigit = count % 10;
    const lastTwoDigits = count % 100;
    
    if (lastTwoDigits >= 11 && lastTwoDigits <= 19) {
        return 'участников';
    }
    
    if (lastDigit === 1) {
        return 'участник';
    } else if (lastDigit >= 2 && lastDigit <= 4) {
        return 'участника';
    } else {
        return 'участников';
    }
}

// Отображение сообщений
function renderMessages(messages) {
    const chatMessages = document.getElementById('chatMessages');
    const currentUserId = {{ Auth::id() }};
    
    chatMessages.innerHTML = messages.map(message => {
        const isOwn = message.user_id === currentUserId;
        const time = formatTime(message.created_at);
        const readStatus = isOwn ? (message.is_read ? '<span class="message-status read">✓✓ Прочитано</span>' : '<span class="message-status unread">✓ Отправлено</span>') : '';
        
        // Блок ответа, если есть
        let replyBlock = '';
        if (message.reply_to) {
            const replyText = message.reply_to.message.length > 50 
                ? message.reply_to.message.substring(0, 50) + '...' 
                : message.reply_to.message;
            replyBlock = `
                <div class="message-reply-block">
                    <div class="message-reply-stripe"></div>
                    <div class="message-reply-content">
                        <div class="message-reply-sender">${escapeHtml(message.reply_to.user_name)}</div>
                        <div class="message-reply-text">${escapeHtml(replyText)}</div>
                    </div>
                </div>
            `;
        }

        // Вложение
        let attachmentBlock = '';
        if (message.attachment_url) {
            if (message.is_image) {
                attachmentBlock = `
                    <div class="message-attachment">
                        <img src="${message.attachment_url}" alt="${escapeHtml(message.attachment_name || 'Изображение')}" class="chat-attachment-image" data-attachment-url="${escapeHtml(message.attachment_url)}" data-attachment-name="${escapeHtml(message.attachment_name || '')}" role="button" tabindex="0">
                    </div>
                `;
            } else {
                const fileName = message.attachment_name || 'Файл';
                attachmentBlock = `
                    <div class="message-attachment">
                        <a href="${message.attachment_url}" target="_blank" rel="noopener noreferrer" class="chat-attachment-file">
                            <span class="chat-attachment-file-icon"><i class="fas fa-paperclip"></i></span>
                            <span>${escapeHtml(fileName)}</span>
                        </a>
                    </div>
                `;
            }
        }
        
        return `
            <div class="message ${isOwn ? 'own' : 'other'}" data-message-id="${message.id}">
                <div class="message-bubble">
                    ${replyBlock}
                    <div class="message-text-content">${escapeHtml(message.message || '')}</div>
                    ${attachmentBlock}
                </div>
                <div class="message-info">
                    ${!isOwn ? `<span class="message-sender">${escapeHtml(message.user_name)}</span>` : ''}
                    <span class="message-time">${time}</span>
                    ${readStatus}
                </div>
            </div>
        `;
    }).join('');
    
    scrollToBottom();
}

// Отправка сообщения
async function sendMessage() {
    const chatInput = document.getElementById('chatInput');
    const attachmentInput = document.getElementById('chatAttachment');
    const messageText = chatInput?.value.trim();
    const file = attachmentInput?.files && attachmentInput.files[0] ? attachmentInput.files[0] : null;

    if ((!messageText || messageText.length === 0) && !file) {
        return;
    }

    if (!currentChatId) {
        return;
    }
    
    // Проверяем наличие CSRF токена
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        const errorMsg = 'CSRF токен не найден. Пожалуйста, обновите страницу.';
        if (window.showNotification) {
            window.showNotification(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
        return;
    }
    
    let csrfToken = csrfTokenMeta.getAttribute('content');
    if (!csrfToken || csrfToken.trim() === '') {
        const errorMsg = 'CSRF токен пуст. Пожалуйста, обновите страницу.';
        if (window.showNotification) {
            window.showNotification(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
        return;
    }
    
    const sendBtn = document.getElementById('sendBtn');
    if (sendBtn) {
        sendBtn.disabled = true;
    }
    
    try {
        const formData = new FormData();
        formData.append('chat_id', currentChatId);
        formData.append('message', messageText || '');
        
        // Если есть активный ответ, отправляем ID сообщения, на которое отвечаем
        if (replyToMessageId) {
            formData.append('reply_to_message_id', replyToMessageId);
        }

        if (file) {
            formData.append('attachment', file);
        }
        
        const response = await fetch('{{ route("chat.message.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        // Обрабатываем ошибку CSRF token mismatch (419 статус)
        if (response.status === 419) {
            const errorMsg = 'Сессия истекла. Пожалуйста, обновите страницу (F5 или Ctrl+R) и попробуйте отправить сообщение снова.';
            if (window.showNotification) {
                window.showNotification(errorMsg, 'error');
            } else {
                alert(errorMsg);
            }
            if (sendBtn) {
                sendBtn.disabled = false;
            }
            return;
        }

        // Проверяем, что ответ является JSON
        const contentType = response.headers.get('content-type');
        let data;
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            // Если ответ не JSON (например, HTML страница с ошибкой), показываем общую ошибку
            const text = await response.text();
            console.error('Сервер вернул не-JSON ответ:', text.substring(0, 200));
            throw new Error('Ошибка при отправке сообщения. Сервер вернул неожиданный формат ответа.');
        }

        if (!response.ok || !data.success) {
            let errorMessage = data.message || 'Ошибка при отправке сообщения';
            if (data.errors) {
                const firstField = Object.keys(data.errors)[0];
                if (firstField && data.errors[firstField][0]) {
                    errorMessage = data.errors[firstField][0];
                }
            }
            throw new Error(errorMessage);
        }
        
        if (data.success) {
            chatInput.value = '';
            chatInput.style.height = 'auto';

            // Очищаем файл
            if (attachmentInput) {
                attachmentInput.value = '';
            }
            const attachmentFilename = document.getElementById('chatAttachmentFilename');
            if (attachmentFilename) {
                attachmentFilename.textContent = '';
                attachmentFilename.style.display = 'none';
            }
            
            // Очищаем данные ответа
            replyToMessageId = null;
            replyToMessageText = null;
            replyToUserName = null;
            updateReplyPreview();
            
            // Добавляем сообщение в UI
            addMessageToUI(data.message);
            lastMessageId = data.message.id;
            
            // Обновляем список чатов
            loadChats();
            
            // Обновляем счетчик непрочитанных сообщений в шапке
            if (typeof updateChatUnreadBadge === 'function') {
                updateChatUnreadBadge();
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
        if (window.showNotification) {
            window.showNotification(error.message || 'Ошибка при отправке сообщения', 'error');
        } else {
            alert(error.message || 'Ошибка при отправке сообщения');
        }
    } finally {
        if (sendBtn) {
            sendBtn.disabled = false;
        }
    }
}

// Добавить сообщение в UI
function addMessageToUI(message) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    // Проверяем, не было ли это сообщение уже добавлено (например, через polling)
    const existingMessage = chatMessages.querySelector(`[data-message-id="${message.id}"]`);
    if (existingMessage) {
        return;
    }
    
    const currentUserId = {{ Auth::id() }};
    const isOwn = message.user_id === currentUserId;
    const time = formatTime(message.created_at);
    const readStatus = isOwn ? (message.is_read ? '<span class="message-status read">✓✓ Прочитано</span>' : '<span class="message-status unread">✓ Отправлено</span>') : '';
    
    // Блок ответа, если есть
    let replyBlock = '';
    if (message.reply_to) {
        const replyText = message.reply_to.message.length > 50 
            ? message.reply_to.message.substring(0, 50) + '...' 
            : message.reply_to.message;
        replyBlock = `
            <div class="message-reply-block">
                <div class="message-reply-stripe"></div>
                <div class="message-reply-content">
                    <div class="message-reply-sender">${escapeHtml(message.reply_to.user_name)}</div>
                    <div class="message-reply-text">${escapeHtml(replyText)}</div>
                </div>
            </div>
        `;
    }

    // Вложение
    let attachmentBlock = '';
    if (message.attachment_url) {
        if (message.is_image) {
            attachmentBlock = `
                <div class="message-attachment">
                    <img src="${message.attachment_url}" alt="${escapeHtml(message.attachment_name || 'Изображение')}" class="chat-attachment-image" data-attachment-url="${escapeHtml(message.attachment_url)}" data-attachment-name="${escapeHtml(message.attachment_name || '')}" role="button" tabindex="0">
                </div>
            `;
        } else {
            const fileName = message.attachment_name || 'Файл';
            attachmentBlock = `
                <div class="message-attachment">
                    <a href="${message.attachment_url}" target="_blank" rel="noopener noreferrer" class="chat-attachment-file">
                        <span class="chat-attachment-file-icon"><i class="fas fa-paperclip"></i></span>
                        <span>${escapeHtml(fileName)}</span>
                    </a>
                </div>
            `;
        }
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isOwn ? 'own' : 'other'}`;
    messageDiv.setAttribute('data-message-id', message.id);
    messageDiv.addEventListener('contextmenu', function(e) {
        // Для собственных сообщений используем имя текущего пользователя
        const userName = isOwn ? '{{ Auth::user()->name }}' : message.user_name;
        showMessageContextMenu(e, message.id, isOwn, message.message, userName);
        e.preventDefault();
        return false;
    });
    messageDiv.innerHTML = `
        <div class="message-bubble">
            ${replyBlock}
            <div class="message-text-content">${escapeHtml(message.message || '')}</div>
            ${attachmentBlock}
        </div>
        <div class="message-info">
            ${!isOwn ? `<span class="message-sender">${escapeHtml(message.user_name)}</span>` : ''}
            <span class="message-time">${time}</span>
            ${readStatus}
        </div>
    `;
    
    chatMessages.appendChild(messageDiv);
    scrollToBottom();
}

// Polling для новых сообщений
function startPolling(chatId) {
    // Останавливаем предыдущий polling
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
    
    pollingInterval = setInterval(async () => {
        if (currentChatId === chatId) {
            await checkNewMessages(chatId);
        }
    }, 2500); // Каждые 2.5 секунды
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

// Проверка новых сообщений
async function checkNewMessages(chatId) {
    try {
        const url = `{{ route('chat.newMessages', ['chatId' => ':chatId']) }}?lastMessageId=${lastMessageId}`.replace(':chatId', chatId);
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        
        if (data.success && data.messages.length > 0) {
            data.messages.forEach(message => {
                addMessageToUI(message);
                lastMessageId = message.id;
            });
            
            // Обновляем список чатов
            loadChats();
            
            // Отмечаем как прочитанное
            markAsRead(chatId);
            
            // Обновление счетчика уже происходит в markAsRead
        }
        
        // Обновляем статусы прочтения для существующих сообщений
        await updateReadStatuses(chatId);
    } catch (error) {
        console.error('Ошибка при проверке новых сообщений:', error);
    }
}

// Обновить статусы прочтения для собственных сообщений
async function updateReadStatuses(chatId) {
    try {
        // Перезагружаем сообщения для получения актуальных статусов
        const response = await fetch(`{{ route('chat.messages', ['chatId' => ':chatId']) }}`.replace(':chatId', chatId), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        
        if (data.success && data.messages) {
            const currentUserId = {{ Auth::id() }};
            const chatMessages = document.getElementById('chatMessages');
            if (!chatMessages) return;
            
            // Обновляем статусы для собственных сообщений
            data.messages.forEach(message => {
                if (message.user_id === currentUserId) {
                    const messageElement = chatMessages.querySelector(`[data-message-id="${message.id}"]`);
                    if (messageElement) {
                        const statusElement = messageElement.querySelector('.message-status');
                        if (statusElement) {
                            if (message.is_read) {
                                statusElement.className = 'message-status read';
                                statusElement.textContent = '✓✓ Прочитано';
                            } else {
                                statusElement.className = 'message-status unread';
                                statusElement.textContent = '✓ Отправлено';
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Ошибка при обновлении статусов прочтения:', error);
    }
}

// Отметить как прочитанное
async function markAsRead(chatId) {
    try {
        await fetch(`{{ route('chat.read', ['chatId' => ':chatId']) }}`.replace(':chatId', chatId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        });
        
        // Обновляем счетчик непрочитанных сообщений в шапке
        if (typeof updateChatUnreadBadge === 'function') {
            updateChatUnreadBadge();
        }
    } catch (error) {
        console.error('Ошибка при отметке как прочитанное:', error);
    }
}

// Поиск пользователей для создания приватного чата
async function searchUsers(query) {
    try {
        const response = await fetch(`{{ route('chat.searchUsers') }}?q=${encodeURIComponent(query)}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        
        if (data.success) {
            renderUserSearchResultsForChat(data.users || []);
        }
    } catch (error) {
        console.error('Ошибка при поиске пользователей:', error);
    }
}

// Отображение результатов поиска пользователей для создания чата
function renderUserSearchResultsForChat(users) {
    const resultsDiv = document.getElementById('userSearchResults');
    if (!resultsDiv) return;
    
    if (users.length === 0) {
        resultsDiv.style.display = 'none';
        return;
    }
    
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = users.map(user => {
        const initials = (user.name || 'U').substring(0, 2).toUpperCase();
        return `
            <div class="user-search-item" onclick="createOrOpenPrivateChat(${user.id}, '${escapeHtml(user.name)}')">
                <div class="chat-avatar" style="width: 40px; height: 40px; font-size: 14px;">${initials}</div>
                <div>
                    <div style="font-weight: 500;">${escapeHtml(user.name)}</div>
                    <div style="font-size: 12px; color: #6c757d;">${escapeHtml(user.email || '')}</div>
                </div>
            </div>
        `;
    }).join('');
}

// Создать или открыть приватный чат
async function createOrOpenPrivateChat(userId, userName) {
    // Скрываем результаты поиска
    document.getElementById('userSearchResults').style.display = 'none';
    document.getElementById('userSearch').value = '';
    
    try {
        // Исправляем URL - убираем дублирование /chat/chat
        const url = `{{ route('chat.private', ['userId' => ':userId']) }}`.replace(':userId', userId).replace('/chat/chat/', '/chat/');
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('Ошибка при создании чата');
        }

        const data = await response.json();
        
        if (data.success) {
            // Открываем чат
            await openChat(data.chat.id);
            
            // Обновляем список чатов
            loadChats();
        }
    } catch (error) {
        console.error('Ошибка:', error);
        if (window.showNotification) {
            window.showNotification('Ошибка при создании чата', 'error');
        }
    }
}

// Поиск пользователей для группы
async function searchUsersForGroup(query) {
    try {
        const response = await fetch(`{{ route('chat.searchUsers') }}?q=${encodeURIComponent(query)}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        renderUserSearchResults(data.users || []);
    } catch (error) {
        console.error('Ошибка при поиске пользователей:', error);
    }
}

// Отображение результатов поиска пользователей
function renderUserSearchResults(users) {
    const resultsDiv = document.getElementById('groupUserSearchResults');
    if (!resultsDiv) return;
    
    if (users.length === 0) {
        resultsDiv.style.display = 'none';
        return;
    }
    
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = users
        .filter(user => !selectedGroupParticipants.find(p => p.id === user.id))
        .map(user => {
            const initials = (user.name || 'U').substring(0, 2).toUpperCase();
            return `
                <div class="user-search-item" onclick="selectGroupParticipant(${user.id}, '${escapeHtml(user.name)}')">
                    <div class="chat-avatar" style="width: 32px; height: 32px; font-size: 12px;">${initials}</div>
                    <div>${escapeHtml(user.name)}</div>
                </div>
            `;
        }).join('');
}

// Выбрать участника группы
function selectGroupParticipant(userId, userName) {
    if (!selectedGroupParticipants.find(p => p.id === userId)) {
        selectedGroupParticipants.push({ id: userId, name: userName });
        renderSelectedParticipants();
    }
    document.getElementById('groupUserSearch').value = '';
    document.getElementById('groupUserSearchResults').style.display = 'none';
}

// Отображение выбранных участников
function renderSelectedParticipants() {
    const container = document.getElementById('selectedParticipants');
    if (!container) return;
    
    container.innerHTML = selectedGroupParticipants.map(participant => `
        <div class="selected-participant">
            <span>${escapeHtml(participant.name)}</span>
            <span class="selected-participant-remove" onclick="removeGroupParticipant(${participant.id})">×</span>
        </div>
    `).join('');
}

// Удалить участника группы
function removeGroupParticipant(userId) {
    selectedGroupParticipants = selectedGroupParticipants.filter(p => p.id !== userId);
    renderSelectedParticipants();
}

// Открыть модальное окно создания группы
function openCreateGroupModal() {
    document.getElementById('createGroupModal').classList.add('show');
    selectedGroupParticipants = [];
    renderSelectedParticipants();
}

// Закрыть модальное окно создания группы
function closeCreateGroupModal() {
    document.getElementById('createGroupModal').classList.remove('show');
    document.getElementById('groupName').value = '';
    document.getElementById('groupUserSearch').value = '';
    document.getElementById('groupUserSearchResults').style.display = 'none';
    selectedGroupParticipants = [];
}

// Создать группу
async function createGroup() {
    const groupName = document.getElementById('groupName').value.trim();
    
    if (!groupName) {
        if (window.showNotification) {
            window.showNotification('Введите название группы', 'warning');
        }
        return;
    }
    
    if (selectedGroupParticipants.length === 0) {
        if (window.showNotification) {
            window.showNotification('Выберите хотя бы одного участника', 'warning');
        }
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('name', groupName);
        selectedGroupParticipants.forEach(p => {
            formData.append('participants[]', p.id);
        });
        
        const response = await fetch('{{ route("chat.group.create") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error('Ошибка при создании группы');
        }

        const data = await response.json();
        
        if (data.success) {
            closeCreateGroupModal();
            loadChats();
            if (window.showNotification) {
                window.showNotification('Группа создана успешно', 'success');
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
        if (window.showNotification) {
            window.showNotification('Ошибка при создании группы', 'error');
        }
    }
}

// Вспомогательные функции
function formatTime(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    
    // Форматируем дату и время: ДД.ММ.ГГГГ ЧЧ:ММ
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${day}.${month}.${year} ${hours}:${minutes}`;
}

function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Показать контекстное меню для сообщения
function showMessageContextMenu(event, messageId, isOwn, messageText, userName) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    contextMenuMessageId = messageId;
    contextMenuMessageText = messageText;
    contextMenuIsOwn = isOwn;
    
    const contextMenu = document.getElementById('messageContextMenu');
    const editItem = document.getElementById('contextEdit');
    const deleteItem = document.getElementById('contextDelete');
    
    // Показываем/скрываем пункты меню в зависимости от типа сообщения
    if (isOwn) {
        editItem.style.display = 'flex';
        deleteItem.style.display = 'flex';
    } else {
        editItem.style.display = 'none';
        deleteItem.style.display = 'none';
    }
    
    // Позиционируем меню с учетом границ экрана
    if (event) {
        // Сначала показываем меню, чтобы измерить его реальные размеры
        contextMenu.style.visibility = 'hidden';
        contextMenu.style.display = 'block';
        contextMenu.classList.add('show');
        
        const menuWidth = contextMenu.offsetWidth;
        const menuHeight = contextMenu.offsetHeight;
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const scrollX = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollY = window.pageYOffset || document.documentElement.scrollTop;
        
        let left = event.pageX;
        let top = event.pageY;
        
        // Проверяем правую границу
        if (left + menuWidth > viewportWidth + scrollX) {
            left = event.pageX - menuWidth;
        }
        
        // Проверяем левую границу
        if (left < scrollX + 10) {
            left = scrollX + 10;
        }
        
        // Проверяем нижнюю границу
        if (top + menuHeight > viewportHeight + scrollY) {
            top = event.pageY - menuHeight;
        }
        
        // Проверяем верхнюю границу
        if (top < scrollY + 10) {
            top = scrollY + 10;
        }
        
        contextMenu.style.left = left + 'px';
        contextMenu.style.top = top + 'px';
        contextMenu.style.visibility = 'visible';
    } else {
        contextMenu.classList.add('show');
    }
    
    // Сохраняем данные для ответа
    replyToMessageId = messageId;
    replyToMessageText = messageText;
    replyToUserName = userName;
}

// Открыть модальное окно редактирования сообщения
function editMessage() {
    if (!contextMenuMessageId) return;
    
    const editModal = document.getElementById('editMessageModal');
    const editTextarea = document.getElementById('editMessageText');
    
    if (editModal && editTextarea) {
        editTextarea.value = contextMenuMessageText;
        editModal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Закрываем контекстное меню
        document.getElementById('messageContextMenu').classList.remove('show');
        
        // Фокус на textarea
        setTimeout(() => {
            editTextarea.focus();
            editTextarea.setSelectionRange(editTextarea.value.length, editTextarea.value.length);
        }, 100);
    }
}

// Закрыть модальное окно редактирования
function closeEditMessageModal() {
    const editModal = document.getElementById('editMessageModal');
    if (editModal) {
        editModal.classList.remove('show');
        document.body.style.overflow = '';
        document.getElementById('editMessageText').value = '';
    }
}

// Открыть изображение чата в модальном окне поверх контента (без перехода)
function openChatImageModal(src, caption) {
    const modal = document.getElementById('chatImageModal');
    const imgEl = document.getElementById('chatImageModalImg');
    const captionEl = document.getElementById('chatImageModalCaption');
    if (!modal || !imgEl) return;
    imgEl.src = src;
    imgEl.alt = caption || 'Изображение';
    if (captionEl) captionEl.textContent = caption || '';
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

// Закрыть модальное окно просмотра изображения и вернуться к чату
function closeChatImageModal() {
    const modal = document.getElementById('chatImageModal');
    if (!modal) return;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    const imgEl = document.getElementById('chatImageModalImg');
    if (imgEl) imgEl.src = '';
}

// Сохранить отредактированное сообщение
async function saveEditedMessage() {
    if (!contextMenuMessageId) return;
    
    const editTextarea = document.getElementById('editMessageText');
    const newText = editTextarea?.value.trim();
    
    if (!newText) {
        if (window.showNotification) {
            window.showNotification('Сообщение не может быть пустым', 'warning');
        }
        return;
    }
    
    if (newText === contextMenuMessageText.trim()) {
        closeEditMessageModal();
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('message', newText);
        formData.append('_method', 'PUT');
        
        const url = `{{ route('chat.message.update', ['messageId' => ':messageId']) }}`.replace(':messageId', contextMenuMessageId);
        console.log('Отправка запроса на редактирование:', url, 'Message ID:', contextMenuMessageId);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        let responseData;
        try {
            responseData = await response.json();
        } catch (e) {
            const text = await response.text();
            console.error('Ошибка парсинга ответа:', text);
            throw new Error('Ошибка при обработке ответа сервера');
        }

        console.log('Ответ сервера:', responseData);

        if (!response.ok) {
            const errorMessage = responseData.message || responseData.error || `Ошибка ${response.status}: ${response.statusText}`;
            throw new Error(errorMessage);
        }

        if (responseData.success) {
            // Обновляем сообщение в UI
            const messageElement = document.querySelector(`[data-message-id="${contextMenuMessageId}"]`);
            if (messageElement) {
                const bubble = messageElement.querySelector('.message-bubble');
                if (bubble) {
                    bubble.textContent = responseData.message.message;
                }
            }
            
            closeEditMessageModal();
            
            if (window.showNotification) {
                window.showNotification('Сообщение отредактировано', 'success');
            }
        } else {
            throw new Error(responseData.message || 'Ошибка при редактировании сообщения');
        }
    } catch (error) {
        console.error('Ошибка при редактировании:', error);
        const errorMessage = error.message || 'Ошибка при редактировании сообщения';
        if (window.showNotification) {
            window.showNotification(errorMessage, 'error');
        } else {
            alert(errorMessage);
        }
    }
}

// Ответить на сообщение
function replyToMessage() {
    if (!replyToMessageId) return;
    
    // Показываем блок ответа
    updateReplyPreview();
    
    // Фокус на поле ввода
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.focus();
    }
    
    document.getElementById('messageContextMenu').classList.remove('show');
}

// Обновить блок предпросмотра ответа
function updateReplyPreview() {
    const replyPreview = document.getElementById('replyPreview');
    const replyPreviewSender = document.getElementById('replyPreviewSender');
    const replyPreviewText = document.getElementById('replyPreviewText');
    
    if (!replyPreview || !replyPreviewSender || !replyPreviewText) {
        return;
    }
    
    if (replyToMessageId && replyToMessageText) {
        // Если имя пользователя не указано, используем имя текущего пользователя (для собственных сообщений)
        const displayName = replyToUserName || '{{ Auth::user()->name }}';
        replyPreviewSender.textContent = displayName;
        const previewText = replyToMessageText.length > 60 
            ? replyToMessageText.substring(0, 60) + '...' 
            : replyToMessageText;
        replyPreviewText.textContent = previewText;
        replyPreview.classList.add('show');
    } else {
        replyPreview.classList.remove('show');
    }
}

// Отменить ответ
function cancelReply() {
    replyToMessageId = null;
    replyToMessageText = null;
    replyToUserName = null;
    updateReplyPreview();
}

// Скопировать текст сообщения
function copyMessageText() {
    if (!contextMenuMessageText) return;
    
    navigator.clipboard.writeText(contextMenuMessageText).then(function() {
        document.getElementById('messageContextMenu').classList.remove('show');
        if (window.showNotification) {
            window.showNotification('Текст скопирован', 'success');
        }
    }).catch(function(err) {
        console.error('Ошибка при копировании:', err);
        // Fallback для старых браузеров
        const textArea = document.createElement('textarea');
        textArea.value = contextMenuMessageText;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            document.getElementById('messageContextMenu').classList.remove('show');
            if (window.showNotification) {
                window.showNotification('Текст скопирован', 'success');
            }
        } catch (err) {
            console.error('Ошибка при копировании:', err);
            if (window.showNotification) {
                window.showNotification('Ошибка при копировании', 'error');
            }
        }
        document.body.removeChild(textArea);
    });
}

// Удалить сообщение
async function deleteMessage() {
    if (!contextMenuMessageId) return;
    
    if (!confirm('Вы уверены, что хотите удалить это сообщение?')) {
        document.getElementById('messageContextMenu').classList.remove('show');
        return;
    }
    
    try {
        const response = await fetch(`{{ route('chat.message.destroy', ['messageId' => ':messageId']) }}`.replace(':messageId', contextMenuMessageId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Ошибка при удалении сообщения');
        }

        const data = await response.json();
        
        if (data.success) {
            // Удаляем сообщение из UI
            const messageElement = document.querySelector(`[data-message-id="${contextMenuMessageId}"]`);
            if (messageElement) {
                messageElement.remove();
            }
            
            document.getElementById('messageContextMenu').classList.remove('show');
            
            // Обновляем список чатов
            loadChats();
            
            // Обновляем счетчик непрочитанных сообщений в шапке
            if (typeof updateChatUnreadBadge === 'function') {
                updateChatUnreadBadge();
            }
            
            if (window.showNotification) {
                window.showNotification('Сообщение удалено', 'success');
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
        if (window.showNotification) {
            window.showNotification('Ошибка при удалении сообщения', 'error');
        }
    }
}

// Открыть модальное окно участников группы
async function openParticipantsModal(chatId) {
    currentParticipantsChatId = chatId;
    document.getElementById('participantsModal').classList.add('show');
    document.getElementById('participantsUserSearch').value = '';
    document.getElementById('participantsUserSearchResults').style.display = 'none';
    await loadParticipants(chatId);
}

// Закрыть модальное окно участников группы
function closeParticipantsModal() {
    document.getElementById('participantsModal').classList.remove('show');
    document.getElementById('participantsUserSearch').value = '';
    document.getElementById('participantsUserSearchResults').style.display = 'none';
    currentParticipantsChatId = null;
}

// Загрузить участников группы
async function loadParticipants(chatId) {
    try {
        const response = await fetch(`{{ route('chat.group.getParticipants', ['chatId' => ':chatId']) }}`.replace(':chatId', chatId), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('Ошибка при загрузке участников');
        }

        const data = await response.json();
        
        if (data.success) {
            renderParticipants(data.participants, chatId);
        }
    } catch (error) {
        console.error('Ошибка:', error);
        document.getElementById('participantsList').innerHTML = '<div class="loading">Ошибка при загрузке участников</div>';
    }
}

// Отобразить участников группы
function renderParticipants(participants, chatId) {
    const participantsList = document.getElementById('participantsList');
    
    if (participants.length === 0) {
        participantsList.innerHTML = '<div class="loading">Нет участников</div>';
        return;
    }

    participantsList.innerHTML = participants.map(participant => {
        const initials = (participant.name || 'U').substring(0, 2).toUpperCase();
        const currentUserId = {{ Auth::id() }};
        const canRemove = participant.id !== currentUserId;
        const removeButton = canRemove
            ? `<button class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" onclick="removeParticipantFromGroup(${chatId}, ${participant.id})">Удалить</button>`
            : '<span style="font-size: 12px; color: #6c757d;">Вы</span>';
        
        return `
            <div class="user-search-item" style="justify-content: space-between;" data-participant-id="${participant.id}">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="chat-avatar" style="width: 40px; height: 40px; font-size: 14px;">${initials}</div>
                    <div>
                        <div style="font-weight: 500;">${escapeHtml(participant.name)}</div>
                        <div style="font-size: 12px; color: #6c757d;">${escapeHtml(participant.email || '')}</div>
                    </div>
                </div>
                ${removeButton}
            </div>
        `;
    }).join('');
}

// Удалить участника из группы
async function removeParticipantFromGroup(chatId, userId) {
    if (!confirm('Вы уверены, что хотите удалить этого участника из группы?')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route('chat.group.removeParticipant', ['chatId' => ':chatId']) }}?user_id=${userId}`.replace(':chatId', chatId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Ошибка при удалении участника');
        }

        const data = await response.json();
        
        if (data.success) {
            // Обновляем список участников
            await loadParticipants(chatId);
            
            // Обновляем заголовок чата для обновления количества участников
            if (currentChatId === chatId) {
                await loadMessages(chatId);
            }
            
            // Обновляем список чатов
            loadChats();
            
            if (window.showNotification) {
                window.showNotification('Участник удален', 'success');
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
        if (window.showNotification) {
            window.showNotification('Ошибка при удалении участника', 'error');
        }
    }
}

// Поиск пользователей для добавления в группу
async function searchUsersForParticipants(query) {
    try {
        const response = await fetch(`{{ route('chat.searchUsers') }}?q=${encodeURIComponent(query)}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        renderUserSearchResultsForParticipants(data.users || []);
    } catch (error) {
        console.error('Ошибка при поиске пользователей:', error);
    }
}

// Отображение результатов поиска пользователей для добавления в группу
async function renderUserSearchResultsForParticipants(users) {
    const resultsDiv = document.getElementById('participantsUserSearchResults');
    if (!resultsDiv) return;
    
    if (users.length === 0) {
        resultsDiv.style.display = 'none';
        return;
    }
    
    // Загружаем текущих участников, чтобы исключить их из списка
    if (!currentParticipantsChatId) {
        resultsDiv.style.display = 'none';
        return;
    }
    
    try {
        // Получаем список текущих участников через API
        const response = await fetch(`{{ route('chat.group.getParticipants', ['chatId' => ':chatId']) }}`.replace(':chatId', currentParticipantsChatId), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            resultsDiv.style.display = 'none';
            return;
        }

        const data = await response.json();
        
        if (data.success) {
            const currentParticipantIds = data.participants.map(p => p.id);
            
            // Фильтруем пользователей, исключая уже добавленных
            const availableUsers = users.filter(user => !currentParticipantIds.includes(user.id));
            
            if (availableUsers.length === 0) {
                resultsDiv.style.display = 'none';
                return;
            }
            
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = availableUsers.map(user => {
                const initials = (user.name || 'U').substring(0, 2).toUpperCase();
                return `
                    <div class="user-search-item" onclick="addParticipantToGroup(${currentParticipantsChatId}, ${user.id}, '${escapeHtml(user.name)}')">
                        <div class="chat-avatar" style="width: 40px; height: 40px; font-size: 14px;">${initials}</div>
                        <div>
                            <div style="font-weight: 500;">${escapeHtml(user.name)}</div>
                            <div style="font-size: 12px; color: #6c757d;">${escapeHtml(user.email || '')}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }
    } catch (error) {
        console.error('Ошибка при загрузке участников:', error);
        resultsDiv.style.display = 'none';
    }
}

// Добавить участника в группу
async function addParticipantToGroup(chatId, userId, userName) {
    try {
        const formData = new FormData();
        formData.append('participants[]', userId);
        
        const response = await fetch(`{{ route('chat.group.addParticipants', ['chatId' => ':chatId']) }}`.replace(':chatId', chatId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error('Ошибка при добавлении участника');
        }

        const data = await response.json();
        
        if (data.success) {
            // Очищаем поле поиска
            document.getElementById('participantsUserSearch').value = '';
            document.getElementById('participantsUserSearchResults').style.display = 'none';
            
            // Обновляем список участников
            await loadParticipants(chatId);
            
            // Обновляем заголовок чата для обновления количества участников
            if (currentChatId === chatId) {
                await loadMessages(chatId);
            }
            
            // Обновляем список чатов
            loadChats();
            
            if (window.showNotification) {
                window.showNotification('Участник добавлен', 'success');
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
        if (window.showNotification) {
            window.showNotification('Ошибка при добавлении участника', 'error');
        }
    }
}

// Функция для открытия/закрытия меню чата
function toggleChatMenu(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('chatMenuDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
        
        // Закрываем меню при клике вне его
        if (dropdown.classList.contains('show')) {
            setTimeout(() => {
                document.addEventListener('click', function closeMenu(e) {
                    if (!dropdown.contains(e.target) && !event.target.contains(e.target)) {
                        dropdown.classList.remove('show');
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }, 0);
        }
    }
}

// Функция для удаления группы
async function deleteGroup(chatId) {
    if (!confirm('Вы уверены, что хотите удалить эту группу? Это действие нельзя отменить.')) {
        return;
    }
    
    try {
        const response = await fetch(`/chat/group/${chatId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Закрываем меню
            const dropdown = document.getElementById('chatMenuDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
            
            // Очищаем область чата
            const chatMain = document.getElementById('chatMain');
            if (chatMain) {
                chatMain.innerHTML = '<div class="chat-main-empty">Выберите чат для начала переписки</div>';
            }
            
            // Обновляем список чатов
            loadChats();
            
            if (window.showNotification) {
                window.showNotification('Группа удалена', 'success');
            }
        } else {
            if (window.showNotification) {
                window.showNotification(data.message || 'Ошибка при удалении группы', 'error');
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
        if (window.showNotification) {
            window.showNotification('Ошибка при удалении группы', 'error');
        }
    }
}

// Функция для покидания группы
async function leaveGroup(chatId) {
    if (!confirm('Вы уверены, что хотите покинуть эту группу?')) {
        return;
    }
    
    try {
        const response = await fetch(`/chat/group/${chatId}/leave`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Закрываем меню
            const dropdown = document.getElementById('chatMenuDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
            
            // Очищаем область чата
            const chatMain = document.getElementById('chatMain');
            if (chatMain) {
                chatMain.innerHTML = '<div class="chat-main-empty">Выберите чат для начала переписки</div>';
            }
            
            // Обновляем список чатов
            loadChats();
            
            if (window.showNotification) {
                window.showNotification('Вы покинули группу', 'success');
            }
        } else {
            if (window.showNotification) {
                window.showNotification(data.message || 'Ошибка при покидании группы', 'error');
            }
        }
    } catch (error) {
        console.error('Ошибка:', error);
        if (window.showNotification) {
            window.showNotification('Ошибка при покидании группы', 'error');
        }
    }
}
</script>

@endsection

