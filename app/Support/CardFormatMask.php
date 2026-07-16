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
     * @param list<int> $blocks
     */
    public static function apply(?string $raw, array $blocks): ?string
    {
        $value = CardFormatValue::prepare($raw);
        if ($value === '') {
            return null;
        }

        $digitsLength = array_sum($blocks);
        if (preg_match('/(?<!\d)(\d{' . $digitsLength . '})(?!\d)/', $value, $m, PREG_OFFSET_CAPTURE) === 1) {
            return self::formatDigits($m[1][0], $blocks, substr($value, $m[1][1] + strlen($m[1][0])));
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

    /**
     * @param list<int> $blocks
     */
    private static function formatDigits(string $digits, array $blocks, string $tail): string
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
