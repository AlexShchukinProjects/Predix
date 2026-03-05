<?php

declare(strict_types=1);

/**
 * Все страницы системы для управления ролями, сгруппированные по модулям.
 * Ключ массива — название группы (модуля), значение — [ 'route_name' => 'Название страницы' ].
 */
return [
    'Общие' => [
        'dashboard' => 'Главная (дашборд)',
        'chat.index' => 'Чат',
        'admin.users.index' => 'Пользователи',
        'admin.users.create' => 'Добавление пользователя',
        'admin.roles.index' => 'Управление ролями',
    ],
    'Настройки (общие)' => [
        'settings.index' => 'Настройки',
        'settings.modules' => 'Модули дашборда',
        'directory.index' => 'Справочники',
        'crew-requirements.index' => 'Требования к экипажу',
        'ReadinessType.index' => 'Типы готовности',
        'Position.index' => 'Должности',
        'events.index' => 'Мероприятия (справочник)',
        'settings.flight-statuses.index' => 'Статусы рейсов',
        'settings.fleet.aircraft-types.index' => 'Типы ВС',
    ],
    'Надежность' => [
        'modules.reliability.index' => 'Отказы агрегатов',
        'modules.reliability.settings.index' => 'Настройки',
    ],
];
