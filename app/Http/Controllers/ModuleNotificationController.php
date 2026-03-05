<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleNotificationController extends Controller
{
    /**
     * Показать страницу управления e-mail уведомлениями для конкретного модуля
     */
    public function index(string $module): View
    {
        // Конфигурация уведомлений для каждого модуля
        $moduleConfigurations = [
            'risk-management' => [
                'title' => 'Управление рисками',
                'notifications' => [
                    'risk-creation' => [
                        'name' => 'УВЕДОМЛЕНИЕ О СОЗДАНИИ РИСКА',
                        'route' => '/notification/risk-management/risk-creation',
                        'status' => 'active'
                    ],
                    'assignment' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НАЗНАЧЕНИИ ОТВЕТСТВЕННЫМ ЗА ОЦЕНКУ РИСКА',
                        'route' => '/notification/risk-management/assignment',
                        'status' => 'active'
                    ],
                    'reassessment' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НЕОБХОДИМОСТИ ПЕРЕОЦЕНИТЬ РИСК',
                        'route' => '/notification/risk-management/reassessment',
                        'status' => 'active'
                    ],
                    'measure-assignment' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НАЗНАЧЕНИИ ОТВЕТСТВЕННЫМ ЗА ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ',
                        'route' => '/notification/risk-management/measure-assignment',
                        'status' => 'active'
                    ],
                    'measure-confirm-required' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НЕОБХОДИМОСТИ ПОДТВЕРДИТЬ ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ',
                        'route' => '/notification/risk-management/measure-confirm-required',
                        'status' => 'active'
                    ],
                    'measure-confirmed-or-revision' => [
                        'name' => 'ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ ПОДТВЕРЖДЕНО / ОТПРАВЛЕНО НА ДОРАБОТКУ',
                        'route' => '/notification/risk-management/measure-confirmed-or-revision',
                        'status' => 'active'
                    ]
                ]
            ],
            'planning' => [
                'title' => 'Планирование',
                'notifications' => [
                    'flight-assignment' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НАЗНАЧЕНИИ НА РЕЙС',
                        'route' => '/notification/planning/flight-assignment',
                        'status' => 'active'
                    ],
                    'schedule-change' => [
                        'name' => 'УВЕДОМЛЕНИЕ ОБ ИЗМЕНЕНИИ РАСПИСАНИЯ',
                        'route' => '/notification/planning/schedule-change',
                        'status' => 'active'
                    ]
                ]
            ],
            'safety-reporting' => [
                'title' => 'Безопасность полетов',
                'notifications' => [
                    'new-event' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НАЗНАЧЕНИИ ОТВЕТСТВЕННЫМ ЗА ОБРАБОТКУ СООБЩЕНИЯ ПО БЕЗОПАСНОСТИ ПОЛЕТОВ',
                        'route' => '/notification/safety-reporting/new-event',
                        'status' => 'active'
                    ]
                ]
            ],
            'safety-database' => [
                'title' => 'База данных безопасности',
                'notifications' => [
                    'new-event' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НАЗНАЧЕНИИ ОТВЕТСТВЕННЫМ ЗА ОБРАБОТКУ СООБЩЕНИЯ ПО БЕЗОПАСНОСТИ ПОЛЕТОВ',
                        'route' => '/notification/safety-database/new-event',
                        'status' => 'active'
                    ],
                    'investigation-deadline' => [
                        'name' => 'УВЕДОМЛЕНИЕ О СРОКАХ РАССЛЕДОВАНИЯ',
                        'route' => '/notification/safety-database/investigation-deadline',
                        'status' => 'active',
                        'source_module' => 'safety-reporting'
                    ],
                    'update' => [
                        'name' => 'УВЕДОМЛЕНИЕ ОБ ОБНОВЛЕНИИ БАЗЫ',
                        'route' => '/notification/safety-database/update',
                        'status' => 'active'
                    ],
                    'event-add-change' => [
                        'name' => 'СООБЩЕНИЕ О ДОБАВЛЕНИИ/ИЗМЕНЕНИИ СОБЫТИЯ ПО БЕЗОПАСНОСТИ ПОЛЕТОВ',
                        'route' => '/notification/safety-database/event-add-change',
                        'status' => 'active'
                    ],
                    'action-assignment' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НАЗНАЧЕНИИ ОТВЕТСТВЕННЫМ ЗА ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ',
                        'route' => '/notification/safety-database/action-assignment',
                        'status' => 'active'
                    ],
                    'action-confirm-required' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НЕОБХОДИМОСТИ ПОДТВЕРДИТЬ ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ',
                        'route' => '/notification/safety-database/action-confirm-required',
                        'status' => 'active'
                    ],
                    'action-confirmed-or-revision' => [
                        'name' => 'ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ ПОДТВЕРЖДЕНО / ОТПРАВЛЕНО НА ДОРАБОТКУ',
                        'route' => '/notification/safety-database/action-confirmed-or-revision',
                        'status' => 'active'
                    ]
                ]
            ],
            'training' => [
                'title' => 'Обучение',
                'notifications' => [
                    'certification-deadline' => [
                        'name' => 'УВЕДОМЛЕНИЕ О СРОКАХ СЕРТИФИКАЦИИ',
                        'route' => '/notification/training/certification-deadline',
                        'status' => 'active'
                    ],
                    'training-assignment' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НАЗНАЧЕНИИ НА ОБУЧЕНИЕ',
                        'route' => '/notification/training/training-assignment',
                        'status' => 'active'
                    ]
                ]
            ],
            'system' => [
                'title' => 'Системные уведомления',
                'notifications' => [
                    'update' => [
                        'name' => 'УВЕДОМЛЕНИЕ О СИСТЕМНОМ ОБНОВЛЕНИИ',
                        'route' => '/notification/system/update',
                        'status' => 'active'
                    ],
                    'maintenance' => [
                        'name' => 'УВЕДОМЛЕНИЕ О ПРОФИЛАКТИЧЕСКИХ РАБОТАХ',
                        'route' => '/notification/system/maintenance',
                        'status' => 'active'
                    ]
                ]
            ],
            'inspections' => [
                'title' => 'Аудиты / Инспекции',
                'notifications' => [
                    'audit-approval-required' => [
                        'name' => 'Уведомление о необходимости согласовать аудит',
                        'route' => '/notification/inspections/audit-approval-required',
                        'status' => 'active'
                    ],
                    'audit-signing-required' => [
                        'name' => 'Уведомление о необходимости утвердить аудит',
                        'route' => '/notification/inspections/audit-signing-required',
                        'status' => 'active'
                    ],
                    'remark-responsible-assignment' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НАЗНАЧЕНИИ ОТВЕТСТВЕННЫМ ЗА УСТРАНЕНИЕ ЗАМЕЧАНИЯ',
                        'route' => '/notification/inspections/remark-responsible-assignment',
                        'status' => 'active'
                    ],
                    'remark-confirm-required' => [
                        'name' => 'УВЕДОМЛЕНИЕ О НЕОБХОДИМОСТИ ПОДТВЕРДИТЬ ВЫПОЛНЕНИЕ КОРРЕКТИРУЮЩЕГО МЕРОПРИЯТИЯ',
                        'route' => '/notification/inspections/remark-confirm-required',
                        'status' => 'active'
                    ],
                    'remark-confirmed-or-revision' => [
                        'name' => 'ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ ПОДТВЕРЖДЕНО / ОТПРАВЛЕНО НА ДОРАБОТКУ',
                        'route' => '/notification/inspections/remark-confirmed-or-revision',
                        'status' => 'active'
                    ],
                ]
            ],
            'executive-discipline' => [
                'title' => 'Исполнительская дисциплина',
                'notifications' => [
                    'task-assignment' => [
                        'name' => 'Назначение ответственным за выполнение задачи',
                        'route' => '/notification/executive-discipline/task-assignment',
                        'status' => 'active'
                    ],
                    'task-confirm-required' => [
                        'name' => 'Подтвердить выполнение задачи',
                        'route' => '/notification/executive-discipline/task-confirm-required',
                        'status' => 'active'
                    ],
                    'task-confirmed' => [
                        'name' => 'Уведомление о подтверждении задачи',
                        'route' => '/notification/executive-discipline/task-confirmed',
                        'status' => 'active'
                    ],
                    'task-revision' => [
                        'name' => 'Уведомление о возврате задачи на доработку',
                        'route' => '/notification/executive-discipline/task-revision',
                        'status' => 'active'
                    ],
                ]
            ],
            'documentation' => [
                'title' => 'Документация',
                'notifications' => [
                    'document-sent-for-approval' => [
                        'name' => 'Документ направлен на согласование',
                        'route' => '/notification/documentation/document-sent-for-approval',
                        'status' => 'active'
                    ],
                    'document-sent-for-signing' => [
                        'name' => 'Документ направлен на утверждение',
                        'route' => '/notification/documentation/document-sent-for-signing',
                        'status' => 'active'
                    ],
                    'document-withdrawn' => [
                        'name' => 'Документ отозван',
                        'route' => '/notification/documentation/document-withdrawn',
                        'status' => 'active'
                    ],
                    'document-sent-for-revision' => [
                        'name' => 'Документ отправлен на доработку',
                        'route' => '/notification/documentation/document-sent-for-revision',
                        'status' => 'active'
                    ],
                    'document-signed' => [
                        'name' => 'Документ подписан',
                        'route' => '/notification/documentation/document-signed',
                        'status' => 'active'
                    ],
                    'document-approved' => [
                        'name' => 'Документ согласован',
                        'route' => '/notification/documentation/document-approved',
                        'status' => 'active'
                    ],
                    'document-familiarization-required' => [
                        'name' => 'Необходимость ознакомления с документом',
                        'route' => '/notification/documentation/document-familiarization-required',
                        'status' => 'active'
                    ]
                ]
            ]
        ];

        // Проверяем существование модуля
        if (!isset($moduleConfigurations[$module])) {
            abort(404, 'Модуль не найден');
        }

        $config = $moduleConfigurations[$module];

        // Переопределяем название и статус из сохранённых шаблонов,
        // чтобы названия на плашках совпадали с теми, что указаны
        // на странице редактирования конкретного шаблона.
        foreach ($config['notifications'] as $key => $notification) {
            $lookupModule = $notification['source_module'] ?? $module;
            $templateModel = NotificationTemplate::getByModuleAndKey($lookupModule, $key);

            if ($templateModel) {
                $config['notifications'][$key]['name'] = $templateModel->name;
                $config['notifications'][$key]['status'] = $templateModel->active ? 'active' : 'inactive';
            }
        }

        return view('notification.module-notifications', compact('module', 'config'));
    }
}
