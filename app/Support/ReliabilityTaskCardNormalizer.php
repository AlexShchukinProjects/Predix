<?php

declare(strict_types=1);

namespace App\Support;

final class ReliabilityTaskCardNormalizer
{
    public static function normalize(?string $raw, ?string $oem = null, ?string $documentType = null): ?string
    {
        $normalizedOem = strtolower(trim((string) ($oem ?? '')));
        $normalizedDocumentType = strtolower(trim((string) ($documentType ?? '')));

        $resolvedDocumentType = $normalizedDocumentType !== ''
            ? $normalizedDocumentType
            : CardFormatValue::detectBulletinType($raw);

        if ($resolvedDocumentType === null && in_array($normalizedOem, ['airbus', 'boeing'], true)) {
            $resolvedDocumentType = 'task_card';
        }

        // Try all applicable masks: longer first, then more specific (e.g. ddd-Addd-dd before ddd-dddd-dd).
        $rulesResult = self::applyOrderedRules(
            $raw,
            $normalizedOem !== '' ? $normalizedOem : null,
            $resolvedDocumentType
        );
        if ($rulesResult !== null) {
            return $rulesResult;
        }

        // Last resort: legacy MPD cascade (classic → alt → min) if no rule mask matched.
        return MpdCardNormalizer::normalize((string) ($raw ?? ''));
    }

    private static function applyOrderedRules(?string $raw, ?string $oem, ?string $documentType): ?string
    {
        // Include MPD / any-type rules even when OEM resolved to task_card,
        // so short Boeing-only masks cannot block longer/shorter MPD fallbacks incorrectly.
        $rules = CardFormatRuleRepository::activeRulesForNormalization($oem, $documentType);

        foreach ($rules as $rule) {
            $mask = (string) ($rule['mask'] ?? '');
            $blocks = $rule['digit_blocks'] ?? CardFormatMask::digitBlocksFromMask($mask);
            if (!is_array($blocks) || $blocks === []) {
                continue;
            }

            $result = CardFormatMask::apply($raw, array_map('intval', $blocks), $mask !== '' ? $mask : null, false);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }
}
