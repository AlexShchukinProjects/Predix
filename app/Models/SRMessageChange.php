<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SRMessageChange extends Model
{
    protected $table = 'sr_message_changes';

    protected $fillable = [
        'sr_message_id',
        'section',
        'section_label',
        'description',
        'user_id',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(SRMessage::class, 'sr_message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Записать запись в историю изменений по сообщению.
     *
     * @param string|null $description Краткое описание: что изменили (например "Изменены: ответственный, название").
     */
    public static function log(
        int $srMessageId,
        string $section,
        ?int $userId = null,
        ?string $sectionLabel = null,
        ?string $description = null
    ): self {
        $labels = [
            'message' => 'Сообщение',
            'event_description' => 'Описание события',
            'risk_assessment' => 'Оценка рисков',
            'analysis' => 'Анализ',
            'feedback' => 'Обратная связь',
            'form_data' => 'Данные формы',
            'actions_state' => 'Мероприятия (состояние)',
            'action' => 'Мероприятие',
        ];
        return self::create([
            'sr_message_id' => $srMessageId,
            'section' => $section,
            'section_label' => $sectionLabel ?? ($labels[$section] ?? $section),
            'description' => $description,
            'user_id' => $userId,
            'changed_at' => now(),
        ]);
    }

    /**
     * Форматирование значения для отображения в истории (было/стало).
     */
    public static function formatChangeValue(mixed $v): string
    {
        if ($v === null || $v === '') {
            return '—';
        }
        if (is_bool($v)) {
            return $v ? 'Да' : 'Нет';
        }
        if (is_array($v)) {
            return implode(', ', array_map('strval', $v)) ?: '—';
        }
        if ($v instanceof \Carbon\CarbonInterface || $v instanceof \DateTimeInterface) {
            return $v->format('d.m.Y');
        }
        return (string) $v;
    }

    /**
     * Одна строка изменения: "Поле: было «X» → стало «Y»" или null если не изменилось.
     */
    public static function changeLine(string $label, mixed $old, mixed $new): ?string
    {
        $o = self::formatChangeValue($old);
        $n = self::formatChangeValue($new);
        if ($o === $n) {
            return null;
        }
        return $label . ': было «' . $o . '» → стало «' . $n . '»';
    }
}
