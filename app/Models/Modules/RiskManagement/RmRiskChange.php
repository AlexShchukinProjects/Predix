<?php

declare(strict_types=1);

namespace App\Models\Modules\RiskManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RmRiskChange extends Model
{
    protected $table = 'rm_risk_changes';

    protected $fillable = [
        'rm_risk_id',
        'section',
        'section_label',
        'description',
        'user_id',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function risk(): BelongsTo
    {
        return $this->belongsTo(RmRisk::class, 'rm_risk_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function log(
        int $rmRiskId,
        string $section,
        ?int $userId = null,
        ?string $sectionLabel = null,
        ?string $description = null
    ): self {
        $labels = [
            'identification' => 'Идентификация риска',
            'assessment' => 'Оценка риска',
            'measures' => 'Мероприятия',
            'residual_assessment' => 'Оценка остаточного риска',
            'documents' => 'Документы',
            'risk' => 'Риск',
        ];
        return self::create([
            'rm_risk_id' => $rmRiskId,
            'section' => $section,
            'section_label' => $sectionLabel ?? ($labels[$section] ?? $section),
            'description' => $description,
            'user_id' => $userId,
            'changed_at' => now(),
        ]);
    }

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
