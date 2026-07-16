<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ReliabilityCardFormatRule;
use Illuminate\Support\Collection;

final class CardFormatRuleRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function builtinDefinitions(): array
    {
        return [
            [
                'key' => 'builtin_boeing_task_card',
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
                'key' => 'builtin_airbus_task_card',
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
                'key' => 'builtin_easa',
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
                'key' => 'builtin_faa',
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
                'key' => 'builtin_mpd_classic',
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
                'key' => 'builtin_mpd_alt',
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
                'key' => 'builtin_mpd_min',
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
        $custom = ReliabilityCardFormatRule::query()
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->map(static function (ReliabilityCardFormatRule $rule): array {
                return [
                    'id' => $rule->id,
                    'key' => 'custom_' . $rule->id,
                    'name' => $rule->name ?: 'Custom rule #' . $rule->id,
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
            });

        $builtins = collect(self::builtinDefinitions())->map(static function (array $rule): array {
            return array_merge($rule, [
                'id' => null,
                'is_builtin' => true,
                'is_active' => true,
                'mapping' => null,
            ]);
        });

        return $builtins->concat($custom)->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function activeCustomRules(?string $oem = null, ?string $documentType = null): array
    {
        if (!self::tableExists()) {
            return [];
        }

        $normalizedOem = self::normalizeOem($oem);
        $normalizedDocumentType = self::normalizeDocumentType($documentType);
        $rules = [];

        $customRules = ReliabilityCardFormatRule::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        foreach ($customRules as $rule) {
            $payload = [
                'key' => 'custom_' . $rule->id,
                'name' => $rule->name,
                'document_type' => $rule->document_type,
                'oem' => $rule->oem,
                'mask' => $rule->mask,
                'digit_blocks' => $rule->digit_blocks,
                'priority' => $rule->priority,
            ];

            if (!self::ruleMatchesContext($payload, $normalizedOem, $normalizedDocumentType)) {
                continue;
            }

            $rules[] = $payload;
        }

        usort($rules, static function (array $a, array $b): int {
            return ($a['priority'] ?? 100) <=> ($b['priority'] ?? 100);
        });

        return $rules;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function activeRules(?string $oem = null, ?string $documentType = null): array
    {
        $normalizedOem = self::normalizeOem($oem);
        $normalizedDocumentType = self::normalizeDocumentType($documentType);

        $rules = [];

        foreach (self::builtinDefinitions() as $definition) {
            if (!self::ruleMatchesContext($definition, $normalizedOem, $normalizedDocumentType)) {
                continue;
            }

            $rules[] = $definition;
        }

        if (self::tableExists()) {
            $customRules = ReliabilityCardFormatRule::query()
                ->where('is_active', true)
                ->orderBy('priority')
                ->orderBy('id')
                ->get();

            foreach ($customRules as $rule) {
                $payload = [
                    'key' => 'custom_' . $rule->id,
                    'name' => $rule->name,
                    'document_type' => $rule->document_type,
                    'oem' => $rule->oem,
                    'mask' => $rule->mask,
                    'digit_blocks' => $rule->digit_blocks,
                    'priority' => $rule->priority,
                ];

                if (!self::ruleMatchesContext($payload, $normalizedOem, $normalizedDocumentType)) {
                    continue;
                }

                $rules[] = $payload;
            }
        }

        usort($rules, static function (array $a, array $b): int {
            return ($a['priority'] ?? 100) <=> ($b['priority'] ?? 100);
        });

        return $rules;
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
