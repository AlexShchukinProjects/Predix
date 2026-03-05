<?php

namespace App\Services;

use App\Models\Inspection;
use App\Models\InspectionRemark;
use App\Models\NotificationTemplate;
use App\Models\Modules\RiskManagement\RmRisk;
use App\Models\SRMessage;
use App\Models\SRMessageAction;
use App\Models\User;
use App\Models\EmailLog;
use App\Models\DocumentationDocument;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Отправить уведомление о создании риска
     */
    public function sendRiskCreationNotification(RmRisk $risk, string $email = 'aleksandr.mai@mail.ru')
    {
        try {
            // Получаем шаблон уведомления
            $template = NotificationTemplate::getByModuleAndKey('risk-management', 'risk-creation');
            
            if (!$template || !$template->active) {
                Log::info('Шаблон уведомления о создании риска не найден или неактивен');
                return false;
            }

            // Подготавливаем данные для шаблона
            $data = [
                'risk' => [
                    'risk_number' => $risk->risk_number,
                    'title' => $risk->title,
                    'description' => $risk->description,
                    'area' => $risk->area ? $risk->area->name : 'Не указано',
                    'responsible_person' => $risk->responsiblePerson ? $risk->responsiblePerson->name : 'Не указано',
                    'created_at' => $risk->created_at ? $risk->created_at->format('d.m.Y H:i') : '',
                ],
                'SMS_LINK' => route('modules.risk-management.risk.edit', $risk->id)
            ];

            // Заменяем переменные в шаблоне
            $subject = $this->replaceVariables($template->subject, $data);
            $content = $this->replaceVariables($template->content, $data);

            // Отправляем email
            Mail::raw($content, function ($message) use ($email, $subject) {
                $message->to($email)
                        ->subject($subject);
            });

            // Логируем успешную отправку
            EmailLog::create([
                'module' => 'risk-management',
                'template_key' => 'risk-creation',
                'subject' => $subject,
                'recipient_email' => $email,
                'success' => true,
                'error_message' => null,
            ]);

            Log::info("Уведомление о создании риска {$risk->risk_number} отправлено на {$email}");
            return true;

        } catch (\Exception $e) {
            // Логируем неуспешную попытку
            EmailLog::create([
                'module' => 'risk-management',
                'template_key' => 'risk-creation',
                'subject' => $subject ?? '',
                'recipient_email' => $email,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Ошибка отправки уведомления о создании риска: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Отправить уведомление о назначении ответственным за оценку риска
     * (шаблон risk-management / assignment).
     */
    public function sendRiskAssessmentAssignmentNotification(RmRisk $risk, int $userId): void
    {
        $template = NotificationTemplate::getByModuleAndKey('risk-management', 'assignment');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления risk-management/assignment не найден или неактивен');
            return;
        }

        $user = User::find($userId);
        if (!$user || !$user->email) {
            return;
        }

        $risk->loadMissing(['area', 'responsiblePerson']);

        $data = [
            'risk' => [
                'risk_number' => (string) $risk->risk_number,
                'title' => (string) ($risk->title ?? ''),
                'description' => (string) ($risk->description ?? ''),
                'area' => $risk->area ? (string) $risk->area->name : 'Не указано',
                'responsible_person' => $risk->responsiblePerson ? (string) $risk->responsiblePerson->name : '',
            ],
            'user' => [
                'name' => (string) $user->name,
            ],
            'SMS_LINK' => route('modules.risk-management.risk.edit', $risk->id),
        ];

        $subject = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);

        try {
            Mail::raw($content, function ($message) use ($user, $subject) {
                $message->to($user->email)->subject($subject);
            });

            EmailLog::create([
                'module' => 'risk-management',
                'template_key' => 'assignment',
                'subject' => $subject,
                'recipient_email' => $user->email,
                'success' => true,
                'error_message' => null,
            ]);

            Log::info("Уведомление risk-management/assignment для риска {$risk->risk_number} отправлено на {$user->email}");
        } catch (\Exception $e) {
            EmailLog::create([
                'module' => 'risk-management',
                'template_key' => 'assignment',
                'subject' => $subject ?? '',
                'recipient_email' => $user->email,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Ошибка отправки уведомления risk-management/assignment: " . $e->getMessage());
        }
    }

    /**
     * Отправить уведомление о добавлении/изменении события по безопасности полетов
     * (шаблон safety-database / event-add-change). Отправляется указанным в разделе
     * «Уведомления» сотрудникам: однократно при «Первоначальное» и однократно при
     * «Завершение» когда статус расследования становится «Завершено».
     *
     * @param  'initial'|'final'  $type
     */
    public function sendSafetyEventAddChangeNotification(SRMessage $message, string $type): void
    {
        if (!in_array($type, ['initial', 'final'], true)) {
            return;
        }

        $template = NotificationTemplate::getByModuleAndKey('safety-database', 'event-add-change');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления safety-database/event-add-change не найден или неактивен');
            return;
        }

        // Проверка «уже отправлено» — храним на сообщении, т.к. при сохранении раздела «Уведомления»
        // записи удаляются и создаются заново, и initial_sent_at/final_sent_at в таблице уведомлений теряются
        $messageSentAtColumn = $type === 'initial' ? 'initial_safety_notification_sent_at' : 'final_safety_notification_sent_at';
        if ($message->{$messageSentAtColumn} !== null) {
            return;
        }

        $flagColumn = $type === 'initial' ? 'initial' : 'final';
        $recipients = $message->notifications()
            ->where($flagColumn, true)
            ->with('user')
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $message->load(['eventDescription.aircraftEventType', 'analysis', 'messageType', 'creator']);
        $eventDesc = $message->eventDescription;
        $analysis = $message->analysis;

        $eventDate = '';
        if ($eventDesc && $eventDesc->event_date) {
            try {
                $eventDate = \Carbon\Carbon::parse($eventDesc->event_date)->format('d.m.Y');
            } catch (\Throwable $e) {
                $eventDate = (string) $eventDesc->event_date;
            }
        }
        // Описание события — из раздела «Описание события» (поле description), не из сообщения
        $description = $eventDesc && $eventDesc->description !== null && $eventDesc->description !== ''
            ? (string) $eventDesc->description
            : (string) ($message->description ?? '');
        // Тип события — из справочника «Тип ВС события» (Катастрофа, Инцидент и т.д.), не тип формы сообщения
        $eventType = $eventDesc && $eventDesc->aircraftEventType
            ? (string) $eventDesc->aircraftEventType->name
            : '';
        // Тип ВС и рег. номер ВС — из раздела «Описание события»
        $aircraftType = $eventDesc ? (string) ($eventDesc->aircraft_type_icao ?? '') : '';
        $aircraftRegn = $eventDesc ? (string) ($eventDesc->aircraft_regn ?? '') : '';
        $addedBy = $message->creator ? (string) $message->creator->name : '';

        if ($type === 'initial') {
            $subject = 'Добавление/изменение события по безопасности полетов';
            $body = 'Зарегистрировано добавление или изменение события по безопасности полетов.';
            $statusLabel = 'Добавлено событие по безопасности полетов';
        } else {
            $subject = 'Завершение расследования события по безопасности полетов';
            $body = 'Расследование по событию по безопасности полетов завершено.';
            $statusLabel = 'Завершено расследование события по безопасности полетов';
        }

        $link = route('modules.safety-reporting.messages.show', $message->id);

        $data = [
            'event' => [
                'subject' => $subject,
                'body' => $body,
                'status_label' => $statusLabel,
                'message_id' => (string) $message->id,
                'event_date' => $eventDate,
                'event_type' => $eventType,
                'aircraft_type' => $aircraftType,
                'aircraft_regn' => $aircraftRegn,
                'description' => mb_substr($description, 0, 500),
                'added_by' => $addedBy,
            ],
            'SMS_LINK' => $link,
        ];

        $subjectText = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);

        foreach ($recipients as $notification) {
            $user = $notification->user;
            if (!$user || !$user->email) {
                continue;
            }
            try {
                Mail::raw($content, function ($mail) use ($user, $subjectText) {
                    $mail->to($user->email)->subject($subjectText);
                });

                EmailLog::create([
                    'module' => 'safety-database',
                    'template_key' => 'event-add-change',
                    'subject' => $subjectText,
                    'recipient_email' => $user->email,
                    'success' => true,
                    'error_message' => null,
                ]);

                Log::info("Уведомление safety-database/event-add-change ({$type}) отправлено для сообщения {$message->id} на {$user->email}");
            } catch (\Exception $e) {
                EmailLog::create([
                    'module' => 'safety-database',
                    'template_key' => 'event-add-change',
                    'subject' => $subjectText ?? '',
                    'recipient_email' => $user->email,
                    'success' => false,
                    'error_message' => $e->getMessage(),
                ]);

                Log::error("Ошибка отправки уведомления safety-database/event-add-change: " . $e->getMessage());
            }
        }

        $message->update([$messageSentAtColumn => now()]);
    }

    /**
     * Отправить уведомление о назначении ответственным за обработку сообщения по безопасности полетов
     * (шаблон safety-reporting / new-event).
     * Используется:
     * - при подаче сообщения (ответственный берется из типа сообщения);
     * - при изменении ответственного в разделе «Сообщение».
     */
    public function sendSafetyReportingResponsibleAssignmentNotification(SRMessage $message, ?int $userId = null): void
    {
        $template = NotificationTemplate::getByModuleAndKey('safety-database', 'new-event')
            ?? NotificationTemplate::getByModuleAndKey('safety-reporting', 'new-event');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления new-event (safety-database / safety-reporting) не найден или неактивен');
            return;
        }

        $userId = $userId
            ?? $message->responsible_user_id
            ?? optional($message->messageType)->responsible_user_id;

        if (!$userId) {
            return;
        }

        $user = User::find($userId);
        if (!$user || !$user->email) {
            return;
        }

        $message->loadMissing(['messageType', 'creator']);

        $submittedAt = $message->created_at
            ? $message->created_at->format('d.m.Y H:i')
            : '';

        $data = [
            'message' => [
                'message_id' => (string) $message->id,
                'type_name' => optional($message->messageType)->name ?? '',
                'title' => (string) ($message->title ?? ''),
                'submitted_at' => $submittedAt,
                'created_by' => optional($message->creator)->name ?? '',
            ],
            'SMS_LINK' => route('modules.safety-reporting.messages.show', $message->id),
        ];

        $subjectText = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);

        try {
            Mail::raw($content, function ($mail) use ($user, $subjectText) {
                $mail->to($user->email)->subject($subjectText);
            });

            EmailLog::create([
                'module' => $template->module,
                'template_key' => 'new-event',
                'subject' => $subjectText,
                'recipient_email' => $user->email,
                'success' => true,
                'error_message' => null,
            ]);

            Log::info("Уведомление new-event (назначение ответственного) отправлено для сообщения {$message->id} на {$user->email}");
        } catch (\Exception $e) {
            EmailLog::create([
                'module' => $template->module,
                'template_key' => 'new-event',
                'subject' => $subjectText ?? '',
                'recipient_email' => $user->email,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Ошибка отправки уведомления new-event (назначение ответственного): " . $e->getMessage());
        }
    }

    /**
     * Отправить уведомление о назначении ответственным за выполнение мероприятия
     * (шаблон safety-database / action-assignment). Отправляется сотруднику,
     * указанному ответственным при добавлении/изменении мероприятия на вкладке «Мероприятия».
     */
    public function sendSafetyActionAssignmentNotification(SRMessageAction $action): void
    {
        if (!$action->responsible_user_id) {
            return;
        }

        $template = NotificationTemplate::getByModuleAndKey('safety-database', 'action-assignment');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления safety-database/action-assignment не найден или неактивен');
            return;
        }

        $action->load(['message', 'responsible']);
        $user = $action->responsible;
        if (!$user || !$user->email) {
            return;
        }

        $dueDate = $action->due_date
            ? $action->due_date->format('d.m.Y')
            : '';

        $link = route('modules.safety-reporting.messages.show', $action->sr_message_id);

        $data = [
            'action' => [
                'subject' => 'Назначение ответственным за выполнение мероприятия',
                'message_id' => (string) $action->sr_message_id,
                'description' => mb_substr((string) ($action->description ?? ''), 0, 500),
                'due_date' => $dueDate,
            ],
            'SMS_LINK' => $link,
        ];

        $subjectText = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);

        try {
            Mail::raw($content, function ($mail) use ($user, $subjectText) {
                $mail->to($user->email)->subject($subjectText);
            });

            EmailLog::create([
                'module' => 'safety-database',
                'template_key' => 'action-assignment',
                'subject' => $subjectText,
                'recipient_email' => $user->email,
                'success' => true,
                'error_message' => null,
            ]);

            Log::info("Уведомление safety-database/action-assignment отправлено для мероприятия {$action->id} на {$user->email}");
        } catch (\Exception $e) {
            EmailLog::create([
                'module' => 'safety-database',
                'template_key' => 'action-assignment',
                'subject' => $subjectText ?? '',
                'recipient_email' => $user->email,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Ошибка отправки уведомления safety-database/action-assignment: " . $e->getMessage());
        }
    }

    /**
     * Отправить уведомление о необходимости подтвердить выполнение мероприятия
     * (шаблон safety-database / action-confirm-required). Отправляется подтверждающему исполнение,
     * когда исполнитель нажимает «Сохранить и завершить» и статус становится «На подтверждении».
     */
    public function sendSafetyActionConfirmRequiredNotification(SRMessageAction $action): void
    {
        if (!$action->confirming_user_id) {
            return;
        }

        $template = NotificationTemplate::getByModuleAndKey('safety-database', 'action-confirm-required');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления safety-database/action-confirm-required не найден или неактивен');
            return;
        }

        $action->load(['message', 'responsible', 'confirmingUser']);
        $user = $action->confirmingUser;
        if (!$user || !$user->email) {
            return;
        }

        $dueDate = $action->due_date
            ? $action->due_date->format('d.m.Y')
            : '';
        $responsibleName = $action->responsible ? $action->responsible->name : 'Не назначен';

        $link = route('modules.safety-reporting.messages.show', $action->sr_message_id);

        $data = [
            'action' => [
                'subject' => 'Необходимо подтвердить выполнение мероприятия',
                'message_id' => (string) $action->sr_message_id,
                'description' => mb_substr((string) ($action->description ?? ''), 0, 500),
                'due_date' => $dueDate,
                'responsible_name' => $responsibleName,
                'actual_work_volume' => mb_substr((string) ($action->actual_work_volume ?? ''), 0, 1000),
            ],
            'SMS_LINK' => $link,
        ];

        $subjectText = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);

        try {
            Mail::raw($content, function ($mail) use ($user, $subjectText) {
                $mail->to($user->email)->subject($subjectText);
            });

            EmailLog::create([
                'module' => 'safety-database',
                'template_key' => 'action-confirm-required',
                'subject' => $subjectText,
                'recipient_email' => $user->email,
                'success' => true,
                'error_message' => null,
            ]);

            Log::info("Уведомление safety-database/action-confirm-required отправлено для мероприятия {$action->id} на {$user->email}");
        } catch (\Exception $e) {
            EmailLog::create([
                'module' => 'safety-database',
                'template_key' => 'action-confirm-required',
                'subject' => $subjectText ?? '',
                'recipient_email' => $user->email,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Ошибка отправки уведомления safety-database/action-confirm-required: " . $e->getMessage());
        }
    }

    /**
     * Отправить уведомление «Выполнение мероприятия подтверждено» или «Отправлено на доработку»
     * (шаблон safety-database / action-confirmed-or-revision). Отправляется ответственному исполнителю,
     * когда подтверждающий нажимает «Подтвердить» (outcome=confirmed) или «Отправить на доработку» (outcome=revision).
     *
     * @param  'confirmed'|'revision'  $outcome
     */
    public function sendSafetyActionConfirmedOrRevisionNotification(SRMessageAction $action, string $outcome): void
    {
        if (!in_array($outcome, ['confirmed', 'revision'], true)) {
            return;
        }
        if (!$action->responsible_user_id) {
            return;
        }

        $template = NotificationTemplate::getByModuleAndKey('safety-database', 'action-confirmed-or-revision');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления safety-database/action-confirmed-or-revision не найден или неактивен');
            return;
        }

        $action->load(['message', 'responsible']);
        $user = $action->responsible;
        if (!$user || !$user->email) {
            return;
        }

        $dueDate = $action->due_date
            ? $action->due_date->format('d.m.Y')
            : '';

        $outcomeText = $outcome === 'confirmed'
            ? 'Выполнение мероприятия подтверждено.'
            : 'Мероприятие отправлено на доработку.';

        $statusLabel = $outcome === 'confirmed'
            ? 'подтверждено'
            : 'отправлено на доработку';

        $subjectText = $outcome === 'confirmed'
            ? 'Выполнение мероприятия подтверждено'
            : 'Мероприятие отправлено на доработку';

        $link = route('modules.safety-reporting.messages.show', $action->sr_message_id);

        $data = [
            'action' => [
                'subject' => $subjectText,
                'message_id' => (string) $action->sr_message_id,
                'description' => mb_substr((string) ($action->description ?? ''), 0, 500),
                'due_date' => $dueDate,
                'outcome_text' => $outcomeText,
                'status_label' => $statusLabel,
            ],
            'SMS_LINK' => $link,
        ];

        $subjectResult = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);

        try {
            Mail::raw($content, function ($mail) use ($user, $subjectResult) {
                $mail->to($user->email)->subject($subjectResult);
            });

            EmailLog::create([
                'module' => 'safety-database',
                'template_key' => 'action-confirmed-or-revision',
                'subject' => $subjectResult,
                'recipient_email' => $user->email,
                'success' => true,
                'error_message' => null,
            ]);

            Log::info("Уведомление safety-database/action-confirmed-or-revision ({$outcome}) отправлено для мероприятия {$action->id} на {$user->email}");
        } catch (\Exception $e) {
            EmailLog::create([
                'module' => 'safety-database',
                'template_key' => 'action-confirmed-or-revision',
                'subject' => $subjectResult ?? '',
                'recipient_email' => $user->email,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Ошибка отправки уведомления safety-database/action-confirmed-or-revision: " . $e->getMessage());
        }
    }

    /**
     * Уведомление о необходимости согласовать аудит/инспекцию (шаблон inspections/audit-approval-required).
     */
    public function sendInspectionApprovalRequired(Inspection $inspection, User $recipient): bool
    {
        $template = NotificationTemplate::getByModuleAndKey('inspections', 'audit-approval-required');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления inspections/audit-approval-required не найден или неактивен');
            return false;
        }
        $inspection->load('user');
        $data = [
            'inspection' => [
                'doc_number' => $inspection->doc_number ?? (string) $inspection->id,
                'conducted_date' => $inspection->conducted_date ? $inspection->conducted_date->format('d.m.Y') : '',
                'initiator' => $inspection->user ? $inspection->user->name : '',
            ],
            'SMS_LINK' => route('modules.inspections.show', $inspection),
        ];
        $subject = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);
        return $this->sendInspectionMail($recipient->email, $subject, $content, 'inspections', 'audit-approval-required');
    }

    /**
     * Уведомление о необходимости утвердить аудит/инспекцию (шаблон inspections/audit-signing-required).
     */
    public function sendInspectionSigningRequired(Inspection $inspection, User $recipient): bool
    {
        $template = NotificationTemplate::getByModuleAndKey('inspections', 'audit-signing-required');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления inspections/audit-signing-required не найден или неактивен');
            return false;
        }
        $inspection->load('user');
        $data = [
            'inspection' => [
                'doc_number' => $inspection->doc_number ?? (string) $inspection->id,
                'conducted_date' => $inspection->conducted_date ? $inspection->conducted_date->format('d.m.Y') : '',
                'initiator' => $inspection->user ? $inspection->user->name : '',
            ],
            'SMS_LINK' => route('modules.inspections.show', $inspection),
        ];
        $subject = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);
        return $this->sendInspectionMail($recipient->email, $subject, $content, 'inspections', 'audit-signing-required');
    }

    /**
     * Уведомление о назначении ответственным за устранение замечания (шаблон inspections/remark-responsible-assignment).
     */
    public function sendInspectionRemarkResponsibleAssignment(InspectionRemark $remark): bool
    {
        if (!$remark->responsible_id) {
            return false;
        }
        $template = NotificationTemplate::getByModuleAndKey('inspections', 'remark-responsible-assignment');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления inspections/remark-responsible-assignment не найден или неактивен');
            return false;
        }
        $remark->load(['inspection', 'responsible']);
        $user = $remark->responsible;
        if (!$user || !$user->email) {
            return false;
        }
        $inspection = $remark->inspection;
        $link = route('modules.inspections.show', $inspection);
        $deadlineStr = $remark->deadline ? $remark->deadline->format('d.m.Y') : '';
        $data = [
            'remark' => [
                'inspection_doc_number' => $inspection->doc_number ?? (string) $inspection->id,
                'ncr_number' => $remark->ncr_number ?? '',
                'question_text' => mb_substr((string) ($remark->question_text ?? ''), 0, 500),
                'comment' => mb_substr((string) ($remark->comment ?? ''), 0, 500),
                'deadline' => $deadlineStr,
            ],
            'SMS_LINK' => $link,
        ];
        $subject = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);
        return $this->sendInspectionMail($user->email, $subject, $content, 'inspections', 'remark-responsible-assignment');
    }

    /**
     * Уведомление о необходимости подтвердить выполнение КМ (шаблон inspections/remark-confirm-required).
     */
    public function sendInspectionRemarkConfirmRequired(InspectionRemark $remark): bool
    {
        if (!$remark->confirming_user_id) {
            return false;
        }
        $template = NotificationTemplate::getByModuleAndKey('inspections', 'remark-confirm-required');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления inspections/remark-confirm-required не найден или неактивен');
            return false;
        }
        $remark->load(['inspection', 'responsible', 'confirmingUser']);
        $user = $remark->confirmingUser;
        if (!$user || !$user->email) {
            return false;
        }
        $inspection = $remark->inspection;
        $link = route('modules.inspections.show', $inspection);
        $deadlineStr = $remark->deadline ? $remark->deadline->format('d.m.Y') : '';
        $data = [
            'remark' => [
                'inspection_doc_number' => $inspection->doc_number ?? (string) $inspection->id,
                'ncr_number' => $remark->ncr_number ?? '',
                'comment' => mb_substr((string) ($remark->comment ?? ''), 0, 500),
                'responsible_name' => $remark->responsible ? ($remark->responsible->name ?? '') : '—',
                'deadline' => $deadlineStr,
            ],
            'SMS_LINK' => $link,
        ];
        $subject = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);
        return $this->sendInspectionMail($user->email, $subject, $content, 'inspections', 'remark-confirm-required');
    }

    /**
     * Уведомление «Выполнение подтверждено» / «Отправлено на доработку» (шаблон inspections/remark-confirmed-or-revision).
     * @param 'Закрыто'|'На доработке' $newStatus
     */
    public function sendInspectionRemarkConfirmedOrRevision(InspectionRemark $remark, string $newStatus): bool
    {
        if (!in_array($newStatus, ['Закрыто', 'На доработке'], true) || !$remark->responsible_id) {
            return false;
        }
        $template = NotificationTemplate::getByModuleAndKey('inspections', 'remark-confirmed-or-revision');
        if (!$template || !$template->active) {
            Log::info('Шаблон уведомления inspections/remark-confirmed-or-revision не найден или неактивен');
            return false;
        }
        $remark->load(['inspection', 'responsible']);
        $user = $remark->responsible;
        if (!$user || !$user->email) {
            return false;
        }
        $inspection = $remark->inspection;
        $link = route('modules.inspections.show', $inspection);
        $deadlineStr = $remark->deadline ? $remark->deadline->format('d.m.Y') : '';
        $outcomeText = $newStatus === 'Закрыто'
            ? 'Выполнение корректирующего мероприятия подтверждено.'
            : 'Корректирующее мероприятие отправлено на доработку.';
        $statusLabel = $newStatus === 'Закрыто' ? 'подтверждено' : 'отправлено на доработку';
        $subjectText = $newStatus === 'Закрыто'
            ? 'Выполнение мероприятия подтверждено'
            : 'Мероприятие отправлено на доработку';
        $data = [
            'remark' => [
                'subject' => $subjectText,
                'outcome_text' => $outcomeText,
                'status_label' => $statusLabel,
                'inspection_doc_number' => $inspection->doc_number ?? (string) $inspection->id,
                'ncr_number' => $remark->ncr_number ?? '',
                'comment' => mb_substr((string) ($remark->comment ?? ''), 0, 500),
                'deadline' => $deadlineStr,
            ],
            'SMS_LINK' => $link,
        ];
        $subject = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);
        return $this->sendInspectionMail($user->email, $subject, $content, 'inspections', 'remark-confirmed-or-revision');
    }

    private function sendInspectionMail(string $email, string $subject, string $content, string $module, string $templateKey): bool
    {
        if (!$email) {
            return false;
        }
        try {
            Mail::raw($content, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
            EmailLog::create([
                'module' => $module,
                'template_key' => $templateKey,
                'subject' => $subject,
                'recipient_email' => $email,
                'success' => true,
                'error_message' => null,
            ]);
            return true;
        } catch (\Exception $e) {
            EmailLog::create([
                'module' => $module,
                'template_key' => $templateKey,
                'subject' => $subject ?? '',
                'recipient_email' => $email,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);
            Log::error("Ошибка отправки уведомления {$module}/{$templateKey}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Отправить уведомление о необходимости ознакомления с документом (шаблон documentation/document-familiarization-required).
     */
    public function sendDocumentFamiliarizationRequired(DocumentationDocument $document, User $user, ?string $familiarizeByFormatted = null): bool
    {
        $template = NotificationTemplate::getByModuleAndKey('documentation', 'document-familiarization-required');
        if (! $template || ! $template->active) {
            Log::info('Шаблон уведомления documentation/document-familiarization-required не найден или неактивен');
            return false;
        }

        $document->loadMissing(['category.section']);
        $categoryName = $document->category ? $document->category->name : '';
        // Ссылка ведёт на «Мои задачи» → «Ознакомление», а не на документ или категорию
        $link = route('modules.documentation.my-tasks');

        $data = [
            'document' => [
                'name' => $document->name ?? '',
                'number' => $document->document_number ?? '',
                'revision' => $document->revision ?? '',
                'familiarization_deadline' => $familiarizeByFormatted ?? '',
                'category' => $categoryName,
            ],
            'SMS_LINK' => $link,
        ];

        $subject = $this->replaceVariables($template->subject, $data);
        $content = $this->replaceVariables($template->content, $data);

        if (! $user->email) {
            Log::info("Пропуск отправки ознакомления с документом: у пользователя {$user->id} нет email");
            return false;
        }

        try {
            Mail::raw($content, function ($message) use ($user, $subject) {
                $message->to($user->email)->subject($subject);
            });
            EmailLog::create([
                'module' => 'documentation',
                'template_key' => 'document-familiarization-required',
                'subject' => $subject,
                'recipient_email' => $user->email,
                'success' => true,
                'error_message' => null,
            ]);
            Log::info("Уведомление об ознакомлении с документом {$document->id} отправлено на {$user->email}");
            return true;
        } catch (\Exception $e) {
            EmailLog::create([
                'module' => 'documentation',
                'template_key' => 'document-familiarization-required',
                'subject' => $subject ?? '',
                'recipient_email' => $user->email,
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);
            Log::error('Ошибка отправки уведомления об ознакомлении с документом: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Заменить переменные в тексте шаблона
     */
    private function replaceVariables(string $text, array $data): string
    {
        $result = $text;

        // Заменяем переменные вида {{variable.subvariable}}
        preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);
        
        foreach ($matches[1] as $match) {
            $value = $this->getNestedValue($data, trim($match));
            $result = str_replace('{{' . $match . '}}', $value, $result);
        }

        return $result;
    }

    /**
     * Получить значение по вложенному ключу (например, risk.title)
     */
    private function getNestedValue(array $data, string $key): string
    {
        $keys = explode('.', $key);
        $value = $data;

        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return '';
            }
        }

        return is_string($value) ? $value : '';
    }
}