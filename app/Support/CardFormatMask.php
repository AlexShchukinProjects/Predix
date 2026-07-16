<?php

declare(strict_types=1);

namespace App\Support;

final class CardFormatMask
{
    /**
     * @return list<int>|null
     */
    public static function digitBlocksFromMask(string $mask): ?array
    {
        $mask = trim($mask);
        if ($mask === '') {
            return null;
        }

        $blocks = [];
        $current = 0;

        for ($i = 0, $len = strlen($mask); $i < $len; $i++) {
            $char = $mask[$i];
            if ($char === 'd' || $char === 'x' || $char === 'X') {
                $current++;
                continue;
            }

            if ($current > 0) {
                $blocks[] = $current;
                $current = 0;
            }
        }

        if ($current > 0) {
            $blocks[] = $current;
        }

        return $blocks !== [] ? $blocks : null;
    }

    public static function maskFromDigitBlocks(array $blocks, string $separator = '-'): string
    {
        $parts = [];
        foreach ($blocks as $block) {
            $parts[] = str_repeat('d', max(1, (int) $block));
        }

        return implode($separator, $parts);
    }

    /**
     * True when mask has fixed literals beyond digit placeholders and separators
     * (e.g. 4N-dd-ddd-dd-C).
     */
    public static function hasFixedLiterals(string $mask): bool
    {
        $mask = trim($mask);
        if ($mask === '') {
            return false;
        }

        return preg_match('/[^dxX\-\s]/i', $mask) === 1;
    }

    /**
     * Apply mask to raw value.
     *
     * @param list<int> $blocks
     */
    public static function apply(?string $raw, array $blocks, ?string $mask = null, bool $keepSuffix = true): ?string
    {
        if ($mask !== null && self::hasFixedLiterals($mask)) {
            return self::applySourceMask($raw, $mask);
        }

        $value = CardFormatValue::prepare($raw);
        if ($value === '') {
            return null;
        }

        $digitsLength = array_sum($blocks);
        if (preg_match('/(?<!\d)(\d{' . $digitsLength . '})(?!\d)/', $value, $m, PREG_OFFSET_CAPTURE) === 1) {
            return self::formatDigits($m[1][0], $blocks, substr($value, $m[1][1] + strlen($m[1][0])), $keepSuffix);
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

            $core = implode('-', $parts);
            if (!$keepSuffix) {
                return $core;
            }

            return self::appendSuffix($core, substr($value, $lastIndex));
        }

        return null;
    }

    /**
     * Match a source pattern like 4N-dd-ddd-dd-C and return only digit groups: 21-061-01.
     */
    public static function applySourceMask(?string $raw, string $sourceMask): ?string
    {
        $value = CardFormatValue::prepare($raw);
        $sourceMask = trim($sourceMask);
        if ($value === '' || $sourceMask === '') {
            return null;
        }

        $regex = self::sourceMaskToRegex($sourceMask);
        if ($regex === null) {
            return null;
        }

        if (preg_match($regex, $value, $m) !== 1) {
            // Fallback: allow flexible separators between tokens
            $regexFlex = self::sourceMaskToRegex($sourceMask, true);
            if ($regexFlex === null || preg_match($regexFlex, $value, $m) !== 1) {
                return null;
            }
        }

        $parts = [];
        for ($i = 1, $count = count($m); $i < $count; $i++) {
            if (array_key_exists($i, $m) && $m[$i] !== '') {
                $parts[] = $m[$i];
            }
        }

        return $parts !== [] ? implode('-', $parts) : null;
    }

    /**
     * Convert source mask 4N-dd-ddd-dd-C to regex with digit capture groups.
     */
    public static function sourceMaskToRegex(string $sourceMask, bool $flexibleSeparators = false): ?string
    {
        $sourceMask = trim($sourceMask);
        if ($sourceMask === '') {
            return null;
        }

        $pattern = '';
        $digitRun = 0;
        $hasCapture = false;
        $len = strlen($sourceMask);

        for ($i = 0; $i < $len; $i++) {
            $char = $sourceMask[$i];

            if ($char === 'd' || $char === 'x' || $char === 'X') {
                $digitRun++;
                continue;
            }

            if ($digitRun > 0) {
                $pattern .= '(\d{' . $digitRun . '})';
                $hasCapture = true;
                $digitRun = 0;
            }

            if ($char === 'A') {
                $pattern .= '[A-Z]';
                continue;
            }

            if ($flexibleSeparators && preg_match('/[^A-Za-z0-9]/', $char) === 1) {
                $pattern .= '\D*';
                continue;
            }

            $pattern .= preg_quote($char, '/');
        }

        if ($digitRun > 0) {
            $pattern .= '(\d{' . $digitRun . '})';
            $hasCapture = true;
        }

        if (!$hasCapture) {
            return null;
        }

        return '/^' . $pattern . '$/';
    }

    /**
     * Build output-only mask from expected value: 21-061-01 → dd-ddd-dd
     */
    public static function maskFromValue(string $value): string
    {
        $value = strtoupper(trim($value));
        $mask = '';
        $len = strlen($value);

        for ($i = 0; $i < $len; $i++) {
            $char = $value[$i];
            if (ctype_digit($char)) {
                $mask .= 'd';
            } elseif (ctype_alpha($char)) {
                $mask .= 'A';
            } else {
                $mask .= $char;
            }
        }

        return $mask;
    }

    /**
     * @param list<int> $blocks
     */
    private static function formatDigits(string $digits, array $blocks, string $tail, bool $keepSuffix): string
    {
        $parts = [];
        $offset = 0;
        foreach ($blocks as $block) {
            $parts[] = substr($digits, $offset, $block);
            $offset += $block;
        }

        $core = implode('-', $parts);
        if (!$keepSuffix) {
            return $core;
        }

        return self::appendSuffix($core, $tail);
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
