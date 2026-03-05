<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationTemplateController extends Controller
{
    /**
     * Подсказки по переменным для подстановки в шаблонах уведомлений.
     * Ключ: 'module.template_key', значение: массив [['var' => '{{placeholder}}', 'label' => 'Описание'], ...]
     */
    public static function getVariableHintsForTemplate(string $module, string $template): array
    {
        $key = $module . '.' . $template;
        $hints = [
            'risk-management.risk-creation' => [
                ['var' => '{{risk.risk_number}}', 'label' => 'Номер риска'],
                ['var' => '{{risk.title}}', 'label' => 'Название'],
                ['var' => '{{risk.description}}', 'label' => 'Описание'],
                ['var' => '{{risk.area}}', 'label' => 'Область'],
                ['var' => '{{risk.responsible_person}}', 'label' => 'Ответственный за оценку'],
                ['var' => '{{risk.created_at}}', 'label' => 'Дата создания'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'risk-management.assignment' => [
                ['var' => '{{risk.risk_number}}', 'label' => 'Номер риска'],
                ['var' => '{{risk.title}}', 'label' => 'Название'],
                ['var' => '{{risk.description}}', 'label' => 'Описание'],
                ['var' => '{{risk.area}}', 'label' => 'Область'],
                ['var' => '{{risk.responsible_person}}', 'label' => 'Ответственный'],
                ['var' => '{{user.name}}', 'label' => 'Имя пользователя'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'risk-management.reassessment' => [
                ['var' => '{{risk.risk_card_number}}', 'label' => 'Номер КР'],
                ['var' => '{{risk.description}}', 'label' => 'Описание'],
                ['var' => '{{risk.last_assessment_date}}', 'label' => 'Дата последней оценки'],
                ['var' => '{{risk.next_assessment_date}}', 'label' => 'Следующая оценка'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'safety-reporting.new-event' => [
                ['var' => '{{message.message_id}}', 'label' => 'Номер сообщения'],
                ['var' => '{{message.type_name}}', 'label' => 'Тип сообщения'],
                ['var' => '{{message.title}}', 'label' => 'Заголовок'],
                ['var' => '{{message.submitted_at}}', 'label' => 'Дата подачи'],
                ['var' => '{{message.created_by}}', 'label' => 'Подано пользователем'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'safety-database.new-event' => [
                ['var' => '{{message.message_id}}', 'label' => 'Номер сообщения'],
                ['var' => '{{message.type_name}}', 'label' => 'Тип сообщения'],
                ['var' => '{{message.title}}', 'label' => 'Заголовок'],
                ['var' => '{{message.submitted_at}}', 'label' => 'Дата подачи'],
                ['var' => '{{message.created_by}}', 'label' => 'Подано пользователем'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'safety-database.update' => [
                ['var' => '{{database.version}}', 'label' => 'Версия базы'],
                ['var' => '{{database.update_date}}', 'label' => 'Дата обновления'],
                ['var' => '{{database.records_count}}', 'label' => 'Количество записей'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'safety-database.investigation-deadline' => [
                ['var' => '{{event.event_number}}', 'label' => 'Номер события'],
                ['var' => '{{event.event_type}}', 'label' => 'Тип события'],
                ['var' => '{{event.event_date}}', 'label' => 'Дата события'],
                ['var' => '{{event.investigation_deadline}}', 'label' => 'Срок расследования'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'safety-database.event-add-change' => [
                ['var' => '{{event.subject}}', 'label' => 'Тема'],
                ['var' => '{{event.body}}', 'label' => 'Текст'],
                ['var' => '{{event.status_label}}', 'label' => 'Статус'],
                ['var' => '{{event.message_id}}', 'label' => 'Номер сообщения'],
                ['var' => '{{event.event_date}}', 'label' => 'Дата события'],
                ['var' => '{{event.event_type}}', 'label' => 'Тип события'],
                ['var' => '{{event.description}}', 'label' => 'Описание'],
                ['var' => '{{event.added_by}}', 'label' => 'Кем добавлено'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'safety-database.action-assignment' => [
                ['var' => '{{action.subject}}', 'label' => 'Тема'],
                ['var' => '{{action.message_id}}', 'label' => 'Номер сообщения'],
                ['var' => '{{action.description}}', 'label' => 'Описание мероприятия'],
                ['var' => '{{action.due_date}}', 'label' => 'Срок выполнения'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'safety-database.action-confirm-required' => [
                ['var' => '{{action.subject}}', 'label' => 'Тема'],
                ['var' => '{{action.message_id}}', 'label' => 'Номер сообщения'],
                ['var' => '{{action.description}}', 'label' => 'Описание'],
                ['var' => '{{action.due_date}}', 'label' => 'Срок выполнения'],
                ['var' => '{{action.responsible_name}}', 'label' => 'Ответственный исполнитель'],
                ['var' => '{{action.actual_work_volume}}', 'label' => 'Фактический объём работ'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'safety-database.action-confirmed-or-revision' => [
                ['var' => '{{action.subject}}', 'label' => 'Тема'],
                ['var' => '{{action.outcome_text}}', 'label' => 'Исход (подтверждено/на доработку)'],
                ['var' => '{{action.status_label}}', 'label' => 'Статус'],
                ['var' => '{{action.message_id}}', 'label' => 'Номер сообщения'],
                ['var' => '{{action.description}}', 'label' => 'Описание'],
                ['var' => '{{action.due_date}}', 'label' => 'Срок выполнения'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'inspections.audit-approval-required' => [
                ['var' => '{{inspection.doc_number}}', 'label' => 'Номер документа инспекции'],
                ['var' => '{{inspection.conducted_date}}', 'label' => 'Дата проведения'],
                ['var' => '{{inspection.initiator}}', 'label' => 'Инициатор'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'inspections.audit-signing-required' => [
                ['var' => '{{inspection.doc_number}}', 'label' => 'Номер документа инспекции'],
                ['var' => '{{inspection.conducted_date}}', 'label' => 'Дата проведения'],
                ['var' => '{{inspection.initiator}}', 'label' => 'Инициатор'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'inspections.remark-responsible-assignment' => [
                ['var' => '{{remark.inspection_doc_number}}', 'label' => 'Номер документа инспекции'],
                ['var' => '{{remark.ncr_number}}', 'label' => 'Номер ЗНО'],
                ['var' => '{{remark.question_text}}', 'label' => 'Текст вопроса'],
                ['var' => '{{remark.comment}}', 'label' => 'Комментарий'],
                ['var' => '{{remark.deadline}}', 'label' => 'Срок'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'inspections.remark-confirm-required' => [
                ['var' => '{{remark.inspection_doc_number}}', 'label' => 'Номер документа инспекции'],
                ['var' => '{{remark.ncr_number}}', 'label' => 'Номер ЗНО'],
                ['var' => '{{remark.comment}}', 'label' => 'Комментарий'],
                ['var' => '{{remark.responsible_name}}', 'label' => 'Ответственный'],
                ['var' => '{{remark.deadline}}', 'label' => 'Срок'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'inspections.remark-confirmed-or-revision' => [
                ['var' => '{{remark.subject}}', 'label' => 'Тема'],
                ['var' => '{{remark.outcome_text}}', 'label' => 'Исход'],
                ['var' => '{{remark.status_label}}', 'label' => 'Статус'],
                ['var' => '{{remark.inspection_doc_number}}', 'label' => 'Номер документа инспекции'],
                ['var' => '{{remark.ncr_number}}', 'label' => 'Номер ЗНО'],
                ['var' => '{{remark.comment}}', 'label' => 'Комментарий'],
                ['var' => '{{remark.deadline}}', 'label' => 'Срок'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'executive-discipline.task-assignment' => [
                ['var' => '{{task.subject}}', 'label' => 'Тема задачи'],
                ['var' => '{{task.description}}', 'label' => 'Описание задачи'],
                ['var' => '{{task.due_date}}', 'label' => 'Срок выполнения'],
                ['var' => '{{task.created_by}}', 'label' => 'Кем создана задача'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка на задачу'],
            ],
            'executive-discipline.task-confirm-required' => [
                ['var' => '{{task.subject}}', 'label' => 'Тема задачи'],
                ['var' => '{{task.description}}', 'label' => 'Описание задачи'],
                ['var' => '{{task.due_date}}', 'label' => 'Срок выполнения'],
                ['var' => '{{task.responsible_name}}', 'label' => 'Ответственный исполнитель'],
                ['var' => '{{task.actual_work_volume}}', 'label' => 'Фактический объём работ / отчёт'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка на задачу'],
            ],
            'executive-discipline.task-confirmed' => [
                ['var' => '{{task.subject}}', 'label' => 'Тема задачи'],
                ['var' => '{{task.description}}', 'label' => 'Описание задачи'],
                ['var' => '{{task.confirmer_name}}', 'label' => 'Подтвердивший исполнение'],
                ['var' => '{{task.confirmed_at}}', 'label' => 'Дата подтверждения'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка на задачу'],
            ],
            'executive-discipline.task-revision' => [
                ['var' => '{{task.subject}}', 'label' => 'Тема задачи'],
                ['var' => '{{task.description}}', 'label' => 'Описание задачи'],
                ['var' => '{{task.revision_comment}}', 'label' => 'Комментарий / причина возврата на доработку'],
                ['var' => '{{task.confirmer_name}}', 'label' => 'Вернувший на доработку'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка на задачу'],
            ],
            'documentation.document-sent-for-approval' => [
                ['var' => '{{document.name}}', 'label' => 'Название документа'],
                ['var' => '{{document.number}}', 'label' => 'Номер документа'],
                ['var' => '{{document.revision}}', 'label' => 'Ревизия'],
                ['var' => '{{document.initiator}}', 'label' => 'Инициатор'],
                ['var' => '{{document.approval_type}}', 'label' => 'Вид согласования'],
                ['var' => '{{document.version}}', 'label' => 'Версия'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'documentation.document-sent-for-signing' => [
                ['var' => '{{document.name}}', 'label' => 'Название документа'],
                ['var' => '{{document.number}}', 'label' => 'Номер документа'],
                ['var' => '{{document.revision}}', 'label' => 'Ревизия'],
                ['var' => '{{document.signer}}', 'label' => 'Утверждающий'],
                ['var' => '{{document.version}}', 'label' => 'Версия'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'documentation.document-withdrawn' => [
                ['var' => '{{document.name}}', 'label' => 'Название документа'],
                ['var' => '{{document.number}}', 'label' => 'Номер документа'],
                ['var' => '{{document.revision}}', 'label' => 'Ревизия'],
                ['var' => '{{document.initiator}}', 'label' => 'Инициатор'],
                ['var' => '{{document.version}}', 'label' => 'Версия'],
                ['var' => '{{document.withdrawn_date}}', 'label' => 'Дата отзыва'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'documentation.document-sent-for-revision' => [
                ['var' => '{{document.name}}', 'label' => 'Название документа'],
                ['var' => '{{document.number}}', 'label' => 'Номер документа'],
                ['var' => '{{document.revision}}', 'label' => 'Ревизия'],
                ['var' => '{{document.revision_reason}}', 'label' => 'Причина доработки'],
                ['var' => '{{document.version}}', 'label' => 'Версия'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'documentation.document-signed' => [
                ['var' => '{{document.name}}', 'label' => 'Название документа'],
                ['var' => '{{document.number}}', 'label' => 'Номер документа'],
                ['var' => '{{document.revision}}', 'label' => 'Ревизия'],
                ['var' => '{{document.signer}}', 'label' => 'Подписант'],
                ['var' => '{{document.signed_date}}', 'label' => 'Дата подписания'],
                ['var' => '{{document.version}}', 'label' => 'Версия'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'documentation.document-approved' => [
                ['var' => '{{document.name}}', 'label' => 'Название документа'],
                ['var' => '{{document.number}}', 'label' => 'Номер документа'],
                ['var' => '{{document.revision}}', 'label' => 'Ревизия'],
                ['var' => '{{document.approver}}', 'label' => 'Согласующий'],
                ['var' => '{{document.approved_date}}', 'label' => 'Дата согласования'],
                ['var' => '{{document.version}}', 'label' => 'Версия'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'documentation.document-familiarization-required' => [
                ['var' => '{{document.name}}', 'label' => 'Название документа'],
                ['var' => '{{document.familiarization_deadline}}', 'label' => 'Срок ознакомления'],
                ['var' => '{{document.number}}', 'label' => 'Номер документа'],
                ['var' => '{{document.revision}}', 'label' => 'Ревизия'],
                ['var' => '{{document.category}}', 'label' => 'Категория'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка для перехода к ознакомлению'],
            ],
            'planning.flight-assignment' => [
                ['var' => '{{flight.flight_number}}', 'label' => 'Номер рейса'],
                ['var' => '{{flight.route}}', 'label' => 'Маршрут'],
                ['var' => '{{flight.departure_date}}', 'label' => 'Дата вылета'],
                ['var' => '{{flight.departure_time}}', 'label' => 'Время вылета'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'planning.schedule-change' => [
                ['var' => '{{flight.flight_number}}', 'label' => 'Номер рейса'],
                ['var' => '{{flight.old_time}}', 'label' => 'Старое время'],
                ['var' => '{{flight.new_time}}', 'label' => 'Новое время'],
                ['var' => '{{flight.change_reason}}', 'label' => 'Причина изменения'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'training.certification-deadline' => [
                ['var' => '{{certification.type}}', 'label' => 'Тип сертификации'],
                ['var' => '{{certification.expiry_date}}', 'label' => 'Дата окончания'],
                ['var' => '{{certification.responsible}}', 'label' => 'Ответственный'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'training.training-assignment' => [
                ['var' => '{{training.type}}', 'label' => 'Тип обучения'],
                ['var' => '{{training.start_date}}', 'label' => 'Дата начала'],
                ['var' => '{{training.duration}}', 'label' => 'Продолжительность'],
                ['var' => '{{training.location}}', 'label' => 'Место проведения'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'system.update' => [
                ['var' => '{{system.version}}', 'label' => 'Версия системы'],
                ['var' => '{{system.update_date}}', 'label' => 'Дата обновления'],
                ['var' => '{{system.downtime}}', 'label' => 'Время простоя'],
                ['var' => '{{system.new_features}}', 'label' => 'Новые функции'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
            'system.maintenance' => [
                ['var' => '{{maintenance.type}}', 'label' => 'Тип работ'],
                ['var' => '{{maintenance.date}}', 'label' => 'Дата проведения'],
                ['var' => '{{maintenance.start_time}}', 'label' => 'Время начала'],
                ['var' => '{{maintenance.duration}}', 'label' => 'Продолжительность'],
                ['var' => '{{SMS_LINK}}', 'label' => 'Ссылка'],
            ],
        ];

        return $hints[$key] ?? [];
    }

    /**
     * Показать форму редактирования шаблона e-mail уведомления
     */
    public function edit(string $module, string $template): View
    {
        // Получаем шаблон из базы данных
        $templateModel = NotificationTemplate::getByModuleAndKey($module, $template);
        
        // Если шаблон не найден, создаем его с значениями по умолчанию
        if (!$templateModel) {
            // Получаем название из конфигурации модуля
            $moduleConfigurations = [
                'safety-database' => [
                    'new-event' => 'Уведомление о назначении ответственным за обработку сообщения по безопасности полетов',
                    'investigation-deadline' => 'Уведомление о сроках расследования',
                ],
                'inspections' => [
                    'audit-approval-required' => 'Уведомление о необходимости согласовать аудит',
                    'audit-signing-required' => 'Уведомление о необходимости утвердить аудит',
                ],
                'documentation' => [
                    'document-sent-for-approval' => 'Документ направлен на согласование',
                    'document-sent-for-signing' => 'Документ направлен на утверждение',
                    'document-withdrawn' => 'Документ отозван',
                    'document-sent-for-revision' => 'Документ отправлен на доработку',
                    'document-signed' => 'Документ подписан',
                    'document-approved' => 'Документ согласован',
                    'document-familiarization-required' => 'Необходимость ознакомления с документом'
                ],
                'risk-management' => [
                    'measure-assignment' => 'УВЕДОМЛЕНИЕ О НАЗНАЧЕНИИ ОТВЕТСТВЕННЫМ ЗА ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ',
                    'measure-confirm-required' => 'УВЕДОМЛЕНИЕ О НЕОБХОДИМОСТИ ПОДТВЕРДИТЬ ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ',
                    'measure-confirmed-or-revision' => 'ВЫПОЛНЕНИЕ МЕРОПРИЯТИЯ ПОДТВЕРЖДЕНО / ОТПРАВЛЕНО НА ДОРАБОТКУ'
                ],
                'executive-discipline' => [
                    'task-assignment' => 'Назначение ответственным за выполнение задачи',
                    'task-confirm-required' => 'Подтвердить выполнение задачи',
                    'task-confirmed' => 'Уведомление о подтверждении задачи',
                    'task-revision' => 'Уведомление о возврате задачи на доработку',
                ],
            ];
            
            $defaultName = $moduleConfigurations[$module][$template] ?? 'Новое уведомление';

            $defaultContent = "Здравствуйте!\n\n{{SMS_LINK}}\n\nС уважением,\nкоманда АСППАП\ne-mail: asppap@volga-dnepr.com";
            $defaultSubject = $defaultName;

            if ($module === 'executive-discipline') {
                $subjectByTemplate = [
                    'task-assignment' => 'Вам назначена задача: {{task.subject}}',
                    'task-confirm-required' => 'Требуется подтвердить выполнение задачи: {{task.subject}}',
                    'task-confirmed' => 'Выполнение задачи подтверждено: {{task.subject}}',
                    'task-revision' => 'Задача возвращена на доработку: {{task.subject}}',
                ];
                $defaultSubject = $subjectByTemplate[$template] ?? $defaultName;
                $defaults = [
                    'task-assignment' => "Здравствуйте!\n\nВам назначена задача по исполнительской дисциплине.\n\nДетали задачи:\n- Тема: {{task.subject}}\n- Описание: {{task.description}}\n- Срок выполнения: {{task.due_date}}\n- Кем создана: {{task.created_by}}\n\nДля просмотра и выполнения задачи пройдите по ссылке: {{SMS_LINK}}\n\nС уважением,\nкоманда АСППАП\ne-mail: asppap@volga-dnepr.com",
                    'task-confirm-required' => "Здравствуйте!\n\nТребуется подтвердить выполнение задачи по исполнительской дисциплине.\n\nДетали задачи:\n- Тема: {{task.subject}}\n- Описание: {{task.description}}\n- Срок выполнения: {{task.due_date}}\n- Ответственный исполнитель: {{task.responsible_name}}\n- Фактически выполненный объём работ: {{task.actual_work_volume}}\n\nДля подтверждения или возврата на доработку пройдите по ссылке: {{SMS_LINK}}\n\nС уважением,\nкоманда АСППАП\ne-mail: asppap@volga-dnepr.com",
                    'task-confirmed' => "Здравствуйте!\n\nВыполнение вашей задачи подтверждено.\n\nДетали задачи:\n- Тема: {{task.subject}}\n- Описание: {{task.description}}\n- Подтвердил: {{task.confirmer_name}}\n- Дата подтверждения: {{task.confirmed_at}}\n\nДля просмотра задачи пройдите по ссылке: {{SMS_LINK}}\n\nС уважением,\nкоманда АСППАП\ne-mail: asppap@volga-dnepr.com",
                    'task-revision' => "Здравствуйте!\n\nЗадача возвращена на доработку.\n\nДетали задачи:\n- Тема: {{task.subject}}\n- Описание: {{task.description}}\n- Комментарий / причина возврата: {{task.revision_comment}}\n- Вернул на доработку: {{task.confirmer_name}}\n\nДля просмотра задачи и внесения изменений пройдите по ссылке: {{SMS_LINK}}\n\nС уважением,\nкоманда АСППАП\ne-mail: asppap@volga-dnepr.com",
                ];
                $defaultContent = $defaults[$template] ?? $defaultContent;
            }
            if ($module === 'documentation' && $template === 'document-familiarization-required') {
                $defaultSubject = 'Ознакомление с документом: {{document.name}} — срок {{document.familiarization_deadline}}';
                $defaultContent = "Здравствуйте!\n\nТребуется ознакомление с документом.\n\nДетали документа:\n- Название документа: {{document.name}}\n- Номер документа: {{document.number}}\n- Ревизия: {{document.revision}}\n- Срок ознакомления: {{document.familiarization_deadline}}\n- Категория: {{document.category}}\n\nДля ознакомления с документом пройдите по ссылке: {{SMS_LINK}}\n\nС уважением,\nкоманда АСППАП\ne-mail: asppap@volga-dnepr.com";
            }
            if ($module === 'safety-database' && $template === 'new-event') {
                $defaultContent = "Здравствуйте!\n\nВы назначены ответственным за обработку сообщения по безопасности полетов.\n\nДетали сообщения:\n- Номер сообщения: {{message.message_id}}\n- Тип сообщения: {{message.type_name}}\n- Заголовок: {{message.title}}\n- Дата подачи: {{message.submitted_at}}\n- Подано пользователем: {{message.created_by}}\n\nДля просмотра и обработки сообщения пройдите по ссылке: {{SMS_LINK}}\n\nС уважением,\nкоманда АСППАП\ne-mail: asppap@volga-dnepr.com";
            }
            if ($module === 'safety-database' && $template === 'investigation-deadline') {
                $defaultContent = "Здравствуйте!\n\nПриближается срок завершения расследования события.\n\nДетали события:\n- Номер события: {{event.event_number}}\n- Тип события: {{event.event_type}}\n- Дата события: {{event.event_date}}\n- Срок расследования: {{event.investigation_deadline}}\n\nДля завершения расследования пройдите по ссылке: {{SMS_LINK}}\n\nС уважением,\nкоманда АСППАП\ne-mail: asppap@volga-dnepr.com";
            }

            // Создаем шаблон с значениями по умолчанию
            $templateModel = NotificationTemplate::createOrUpdate($module, $template, [
                'name' => $defaultName,
                'subject' => $defaultSubject ?? $defaultName,
                'content' => $defaultContent,
                'active' => true,
                'notify_days' => $module === 'safety-database' && $template === 'investigation-deadline' ? 3 : 0,
                'notify_frequency' => 'days'
            ]);
        }

        // Преобразуем модель в массив для совместимости с представлением
        $config = [
            'name' => $templateModel->name,
            'title' => $templateModel->name,
            'subject' => $templateModel->subject,
            'active' => $templateModel->active,
            'notify_days' => $templateModel->notify_days,
            'content' => $templateModel->content,
            'notify_frequency' => $templateModel->notify_frequency
        ];

        $variableHints = self::getVariableHintsForTemplate($module, $template);

        return view('notification.template', compact('module', 'template', 'config', 'variableHints'));
    }

    /**
     * Обновить шаблон e-mail уведомления
     */
    public function update(Request $request, string $module, string $template): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'active' => 'boolean',
            'notify_days' => 'required|integer|min:0',
            'content' => 'required|string',
            'notify_frequency' => 'required|in:days,hours,weeks'
        ]);

        try {
            // Обновляем или создаем шаблон
            NotificationTemplate::createOrUpdate($module, $template, [
                'name' => $request->input('name'),
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'active' => $request->has('active'),
                'notify_days' => $request->input('notify_days'),
                'notify_frequency' => $request->input('notify_frequency')
            ]);

            return redirect()->back()->with('success', 'Шаблон успешно обновлен!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ошибка при сохранении шаблона: ' . $e->getMessage());
        }
    }

    /**
     * Предпросмотр шаблона e-mail уведомления
     */
    public function preview(Request $request, string $module, string $template): View
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        $previewData = [
            'name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'content' => $request->input('content'),
            'module' => $module,
            'template' => $template
        ];

        return view('notification.template-preview', compact('previewData'));
    }
}
