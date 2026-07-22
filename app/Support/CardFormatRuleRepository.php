<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ReliabilityCardFormatRule;
use Illuminate\Support\Collection;

final class CardFormatRuleRepository
{
    /**
     * Default rules seeded into the database (formerly hard-coded built-ins).
     *
     * @return list<array<string, mixed>>
     */
    public static function defaultSeedDefinitions(): array
    {
        return [
            [
                'name' => 'Task card — Boeing',
                'document_type' => 'task_card',
                'oem' => 'boeing',
                'mask' => 'dd-ddd-dd-dd',
                'digit_blocks' => [2, 3, 2, 2],
                'priority' => 10,
                'example_raw' => '12-108-00-01',
                'example_normalized' => '12-108-00-01',
            ],
            [
                'name' => 'Task card — Airbus',
                'document_type' => 'task_card',
                'oem' => 'airbus',
                'mask' => 'dddddd-dd-d',
                'digit_blocks' => [6, 2, 1],
                'priority' => 20,
                'example_raw' => '291105210804',
                'example_normalized' => '291105-21-0',
            ],
            [
                'name' => 'EASA bulletin',
                'document_type' => 'easa',
                'oem' => null,
                'mask' => 'dddd-dddd',
                'digit_blocks' => [4, 4],
                'priority' => 30,
                'example_raw' => '2024-1234',
                'example_normalized' => '2024-1234',
            ],
            [
                'name' => 'FAA bulletin',
                'document_type' => 'faa',
                'oem' => null,
                'mask' => 'dddd-dd-dd',
                'digit_blocks' => [4, 2, 2],
                'priority' => 40,
                'example_raw' => '2024-12-31',
                'example_normalized' => '2024-12-31',
            ],
            [
                'name' => 'MPD classic',
                'document_type' => 'mpd',
                'oem' => null,
                'mask' => 'dd-dd-dd-ddd-ddd',
                'digit_blocks' => [2, 2, 2, 3, 3],
                'priority' => 50,
                'example_raw' => '291105210804',
                'example_normalized' => '29-11-05-210-804',
            ],
            [
                'name' => 'MPD alternate',
                'document_type' => 'mpd',
                'oem' => null,
                'mask' => 'dd-ddd-dd-dd',
                'digit_blocks' => [2, 3, 2, 2],
                'priority' => 60,
                'example_raw' => '121080001',
                'example_normalized' => '12-108-00-01',
            ],
            [
                'name' => 'MPD minimum core',
                'document_type' => 'mpd',
                'oem' => null,
                'mask' => 'dd-ddd-dd',
                'digit_blocks' => [2, 3, 2],
                'priority' => 70,
                'example_raw' => '24-041-03',
                'example_normalized' => '24-041-03',
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function allForDisplay(): Collection
    {
        if (!self::tableExists()) {
            return collect();
        }

        return ReliabilityCardFormatRule::query()
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->map(static function (ReliabilityCardFormatRule $rule): array {
                return self::toPayload($rule);
            })
            ->sort(static fn (array $a, array $b): int => CardFormatMask::compareRulesByMask($a, $b))
            ->values()
            ->map(static function (array $rule, int $index): array {
                $rule['match_priority'] = $index + 1;

                return $rule;
            });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function activeCustomRules(?string $oem = null, ?string $documentType = null): array
    {
        return self::activeRules($oem, $documentType);
    }

    /**
     * Rules used by the normalizer, sorted by mask length then specificity.
     * MPD / any document types stay available even when OEM implies task_card.
     *
     * @return list<array<string, mixed>>
     */
    public static function activeRulesForNormalization(?string $oem = null, ?string $documentType = null): array
    {
        if (!self::tableExists()) {
            return [];
        }

        $normalizedOem = self::normalizeOem($oem);
        $normalizedDocumentType = self::normalizeDocumentType($documentType);
        $rules = [];

        $rows = ReliabilityCardFormatRule::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        foreach ($rows as $rule) {
            $payload = self::toPayload($rule);
            if (!self::ruleMatchesNormalizationContext($payload, $normalizedOem, $normalizedDocumentType)) {
                continue;
            }
            $rules[] = $payload;
        }

        usort($rules, [CardFormatMask::class, 'compareRulesByMask']);

        return $rules;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function activeRules(?string $oem = null, ?string $documentType = null): array
    {
        if (!self::tableExists()) {
            return [];
        }

        $normalizedOem = self::normalizeOem($oem);
        $normalizedDocumentType = self::normalizeDocumentType($documentType);
        $rules = [];

        $rows = ReliabilityCardFormatRule::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        foreach ($rows as $rule) {
            $payload = self::toPayload($rule);
            if (!self::ruleMatchesContext($payload, $normalizedOem, $normalizedDocumentType)) {
                continue;
            }
            $rules[] = $payload;
        }

        usort($rules, [CardFormatMask::class, 'compareRulesByMask']);

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    private static function toPayload(ReliabilityCardFormatRule $rule): array
    {
        return [
            'id' => $rule->id,
            'key' => 'rule_' . $rule->id,
            'name' => $rule->name ?: 'Rule #' . $rule->id,
            'document_type' => $rule->document_type,
            'oem' => $rule->oem,
            'mask' => $rule->mask,
            'digit_blocks' => $rule->digit_blocks,
            'is_builtin' => false,
            'is_active' => $rule->is_active,
            'priority' => $rule->priority,
            'example_raw' => $rule->example_raw,
            'example_normalized' => $rule->example_normalized,
            'mapping' => $rule->mapping,
        ];
    }

    /**
     * @param array<string, mixed> $rule
     */
    private static function ruleMatchesContext(array $rule, ?string $oem, ?string $documentType): bool
    {
        $ruleOem = self::normalizeOem($rule['oem'] ?? null);
        $ruleDocumentType = self::normalizeDocumentType($rule['document_type'] ?? 'any');

        if ($oem !== null && $ruleOem !== null && $ruleOem !== $oem) {
            return false;
        }

        if ($documentType !== null && $ruleDocumentType !== 'any' && $ruleDocumentType !== $documentType) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $rule
     */
    private static function ruleMatchesNormalizationContext(array $rule, ?string $oem, ?string $documentType): bool
    {
        $ruleOem = self::normalizeOem($rule['oem'] ?? null);
        $ruleDocumentType = self::normalizeDocumentType($rule['document_type'] ?? 'any') ?? 'any';

        if ($oem !== null && $ruleOem !== null && $ruleOem !== $oem) {
            return false;
        }

        if ($ruleDocumentType === 'any' || $ruleDocumentType === 'mpd') {
            return true;
        }

        if ($documentType === null) {
            return true;
        }

        return $ruleDocumentType === $documentType;
    }

    private static function normalizeOem(?string $oem): ?string
    {
        $value = strtolower(trim((string) ($oem ?? '')));

        return in_array($value, ['airbus', 'boeing'], true) ? $value : null;
    }

    private static function normalizeDocumentType(?string $documentType): ?string
    {
        $value = strtolower(trim((string) ($documentType ?? '')));

        return in_array($value, ['task_card', 'easa', 'faa', 'mpd', 'any'], true) ? $value : null;
    }

    private static function tableExists(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('reliability_card_format_rules');
        } catch (\Throwable) {
            return false;
        }
    }
}
