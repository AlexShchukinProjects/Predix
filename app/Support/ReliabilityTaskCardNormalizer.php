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

        $customResult = self::applyCustomRules($raw, $normalizedOem !== '' ? $normalizedOem : null, $resolvedDocumentType);
        if ($customResult !== null) {
            return $customResult;
        }

        if ($normalizedDocumentType === 'easa') {
            return self::normalizeEasa($raw);
        }

        if ($normalizedDocumentType === 'faa') {
            return self::normalizeFaa($raw);
        }

        if ($normalizedDocumentType === 'task_card') {
            return match ($normalizedOem) {
                'boeing' => self::normalizeBoeing($raw),
                'airbus' => self::normalizeAirbus($raw),
                default => null,
            };
        }

        $autoDetectedDocumentType = CardFormatValue::detectBulletinType($raw);
        if ($autoDetectedDocumentType === 'easa') {
            return self::normalizeEasa($raw);
        }

        if ($autoDetectedDocumentType === 'faa') {
            return self::normalizeFaa($raw);
        }

        if ($normalizedOem === 'boeing') {
            return self::normalizeBoeing($raw);
        }

        if ($normalizedOem === 'airbus') {
            return self::normalizeAirbus($raw);
        }

        return MpdCardNormalizer::normalize((string) ($raw ?? ''));
    }

    private static function applyCustomRules(?string $raw, ?string $oem, ?string $documentType): ?string
    {
        $rules = CardFormatRuleRepository::activeCustomRules($oem, $documentType);

        foreach ($rules as $rule) {
            $blocks = $rule['digit_blocks'] ?? CardFormatMask::digitBlocksFromMask((string) ($rule['mask'] ?? ''));
            if (!is_array($blocks) || $blocks === []) {
                continue;
            }

            $result = CardFormatMask::apply($raw, array_map('intval', $blocks));
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    private static function normalizeBoeing(?string $raw): ?string
    {
        return CardFormatMask::apply($raw, [2, 3, 2, 2]);
    }

    private static function normalizeAirbus(?string $raw): ?string
    {
        return CardFormatMask::apply($raw, [6, 2, 1]);
    }

    private static function normalizeEasa(?string $raw): ?string
    {
        return CardFormatMask::apply($raw, [4, 4]);
    }

    private static function normalizeFaa(?string $raw): ?string
    {
        return CardFormatMask::apply($raw, [4, 2, 2]);
    }
}
