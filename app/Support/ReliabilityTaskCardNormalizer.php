<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Единая точка нормализации номеров task card / cust. card (как CUST. CARD NORM в master data).
 * Логика совпадает с {@see MpdCardNormalizer}; при доработках править преимущественно там
 * или переносить общую реализацию сюда.
 */
final class ReliabilityTaskCardNormalizer
{
    public static function normalize(?string $raw, ?string $oem = null, ?string $documentType = null): ?string
    {
        $normalizedOem = strtolower(trim((string) ($oem ?? '')));
        $normalizedDocumentType = strtolower(trim((string) ($documentType ?? '')));

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

        $autoDetectedDocumentType = self::detectBulletinType($raw);
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

    private static function detectBulletinType(?string $raw): ?string
    {
        $value = self::prepareValue($raw);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, 'EASA')) {
            return 'easa';
        }

        if (str_contains($value, 'FAA')) {
            return 'faa';
        }

        if (preg_match('/(?<!\d)\d{4}\D+\d{2}\D+\d{2}(?!\d)/', $value) === 1) {
            return 'faa';
        }

        if (preg_match('/(?<!\d)\d{4}\D+\d{4}(?!\d)/', $value) === 1) {
            return 'easa';
        }

        if (preg_match('/(?<!\d)(\d{8})(?!\d)/', $value, $match) === 1) {
            $digits = $match[1];
            $month = (int) substr($digits, 4, 2);
            $day = (int) substr($digits, 6, 2);

            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                return 'faa';
            }

            return 'easa';
        }

        return null;
    }

    private static function normalizeBoeing(?string $raw): ?string
    {
        return self::normalizeWithMask($raw, [2, 3, 2, 2]);
    }

    private static function normalizeAirbus(?string $raw): ?string
    {
        return self::normalizeWithMask($raw, [6, 2, 1]);
    }

    private static function normalizeEasa(?string $raw): ?string
    {
        return self::normalizeWithMask($raw, [4, 4]);
    }

    private static function normalizeFaa(?string $raw): ?string
    {
        return self::normalizeWithMask($raw, [4, 2, 2]);
    }

    /**
     * @param list<int> $blocks
     */
    private static function normalizeWithMask(?string $raw, array $blocks): ?string
    {
        $value = self::prepareValue($raw);
        if ($value === '') {
            return null;
        }

        $digitsLength = array_sum($blocks);
        if (preg_match('/(?<!\d)(\d{' . $digitsLength . '})(?!\d)/', $value, $m, PREG_OFFSET_CAPTURE) === 1) {
            return self::formatMaskedDigits($m[1][0], $blocks, substr($value, $m[1][1] + strlen($m[1][0])));
        }

        $patternParts = [];
        foreach ($blocks as $block) {
            $patternParts[] = '(\d{' . $block . '})';
        }
        $pattern = '/' . implode('\D*', $patternParts) . '/';

        if (preg_match($pattern, $value, $m, PREG_OFFSET_CAPTURE) === 1) {
            $parts = [];
            $lastIndex = 0;
            foreach (array_keys($blocks) as $i) {
                $parts[] = $m[$i + 1][0];
                $lastIndex = $m[$i + 1][1] + strlen($m[$i + 1][0]);
            }

            return self::appendSuffix(implode('-', $parts), substr($value, $lastIndex));
        }

        return null;
    }

    private static function prepareValue(?string $raw): string
    {
        $value = strtoupper(trim((string) ($raw ?? '')));
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/^(TASK|MPD|CARD|TC|JOB)\s*[:#-]?\s*/', '', $value) ?? $value;
        $value = str_replace(['(', ')', '[', ']', '{', '}', '/', '\\', '_', '.', ','], '-', $value);
        $value = preg_replace('/\s+/', '-', $value) ?? $value;
        $value = preg_replace('/[^A-Z0-9-]/', '', $value) ?? $value;
        $value = preg_replace('/-+/', '-', $value) ?? $value;

        return trim($value, '-');
    }

    /**
     * @param list<int> $blocks
     */
    private static function formatMaskedDigits(string $digits, array $blocks, string $tail): string
    {
        $parts = [];
        $offset = 0;
        foreach ($blocks as $block) {
            $parts[] = substr($digits, $offset, $block);
            $offset += $block;
        }

        return self::appendSuffix(implode('-', $parts), $tail);
    }

    private static function appendSuffix(string $core, string $tail): string
    {
        $tail = trim((string) preg_replace('/^-+/', '', $tail));

        if ($tail !== '' && preg_match('/^[A-Z]\d{0,3}$/', $tail) === 1) {
            return $core . '-' . $tail;
        }

        if ($tail !== '' && preg_match('/^([A-Z]\d{0,3})-.*$/', $tail, $suffix) === 1) {
            return $core . '-' . $suffix[1];
        }

        return $core;
    }
}
