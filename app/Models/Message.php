<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_type',
        'title',
        'message_date',
        'service',
        'responsible_person',
        'status',
        'accepted_for_work',
        'risk_level',
        'analysis',
        'actions',
        'feedback_connection'
    ];

    protected $casts = [
        'message_date' => 'datetime',
        'accepted_for_work' => 'datetime'
    ];

    /**
     * Get sample data for demonstration
     */
    public static function getSampleData()
    {
        return [
            [
                'id' => 1624,
                'message_type' => 'Добровольное сообщение',
                'title' => 'Неисправность ВС. Вагин Виктор Анатольевич 03.09.2025 08:12:01',
                'message_date' => '03.09.2025 08:12',
                'service' => 'Инженерно-авиационная служба',
                'responsible_person' => 'Суслов Глеб Владимирович',
                'status' => 'Закрыто',
                'accepted_for_work' => 'Закрыто',
                'risk_level' => '0.12',
                'analysis' => 'Не требуется',
                'actions' => 'Не требуется',
                'feedback_connection' => 'Представлена'
            ],
            [
                'id' => 1623,
                'message_type' => 'Обязательный отчет',
                'title' => 'КМ-114 Корылов Александр Сергеевич 02.09.2025 18:47:32',
                'message_date' => '02.09.2025 18:47',
                'service' => 'Инженерно-авиационная служба',
                'responsible_person' => 'Вдовин Сергей Васильевич',
                'status' => 'Закрыто',
                'accepted_for_work' => 'Закрыто',
                'risk_level' => '0.08',
                'analysis' => 'Не требуется',
                'actions' => 'Не требуется',
                'feedback_connection' => 'Представлена'
            ],
            [
                'id' => 1622,
                'message_type' => 'Анонимное сообщение',
                'title' => 'сообщаю о нарушении трудовых прав и создании психологически некомфортной обстановки в коллективе ИАС.',
                'message_date' => '25.08.2025 19:06',
                'service' => '',
                'responsible_person' => 'Васильев Сергей Игоревич',
                'status' => 'Просрочено',
                'accepted_for_work' => 'В работе',
                'risk_level' => '0.20',
                'analysis' => 'В работе',
                'actions' => 'Не начато',
                'feedback_connection' => 'Не требуется'
            ],
            [
                'id' => 1621,
                'message_type' => 'Добровольное сообщение',
                'title' => 'Неисправность Вагин Виктор Анатольевич 20.08.2025 13:43:38',
                'message_date' => '20.08.2025 13:43',
                'service' => 'Инженерно-авиационная служба',
                'responsible_person' => 'Суслов Глеб Владимирович',
                'status' => 'Закрыто',
                'accepted_for_work' => 'Закрыто',
                'risk_level' => '0.12',
                'analysis' => 'Не требуется',
                'actions' => 'Не требуется',
                'feedback_connection' => 'Представлена'
            ],
            [
                'id' => 1620,
                'message_type' => 'Добровольное сообщение',
                'title' => 'Задержка рейса VDA 716 Мохов Константин Леонидович 19.08.2025 08:38:39',
                'message_date' => '19.08.2025 08:38',
                'service' => 'Инженерно-авиационная служба',
                'responsible_person' => 'Суслов Глеб Владимирович',
                'status' => 'В работе',
                'accepted_for_work' => 'В работе',
                'risk_level' => 'N/A',
                'analysis' => 'Анализ завершен',
                'actions' => '2 / 1',
                'feedback_connection' => 'Не начато'
            ],
            [
                'id' => 1619,
                'message_type' => 'Обязательный отчет',
                'title' => 'Табло ГК «кабина разгерм» Мещерский Леонид Владимирович 17.08.2025 14:55:39',
                'message_date' => '17.08.2025 14:55',
                'service' => 'Инженерно-авиационная служба',
                'responsible_person' => 'Суслов Глеб Владимирович',
                'status' => 'Закрыто',
                'accepted_for_work' => 'Закрыто',
                'risk_level' => '0.12',
                'analysis' => 'Не требуется',
                'actions' => 'Не требуется',
                'feedback_connection' => 'Не требуется'
            ]
        ];
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'Закрыто' => 'flight-status-completed',
            'В работе' => 'flight-status-in_progress',
            'Просрочено' => 'flight-status-delayed',
            default => 'flight-status-new'
        };
    }

    /**
     * Get accepted for work badge class
     */
    public function getAcceptedBadgeClass(): string
    {
        return match($this->accepted_for_work) {
            'Закрыто' => 'flight-status-completed',
            'В работе' => 'flight-status-in_progress',
            'Просрочено' => 'flight-status-delayed',
            default => 'flight-status-new'
        };
    }

    /**
     * Get risk level badge class
     */
    public function getRiskBadgeClass(): string
    {
        if ($this->risk_level === 'N/A') {
            return 'flight-status-new';
        }
        
        $risk = (float) $this->risk_level;
        if ($risk >= 0.2) {
            return 'flight-status-delayed'; // Red
        } elseif ($risk >= 0.1) {
            return 'flight-status-daily_plan'; // Yellow
        } else {
            return 'flight-status-completed'; // Green
        }
    }
}
