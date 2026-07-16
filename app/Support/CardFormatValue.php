<?php

declare(strict_types=1);

namespace App\Support;

final class CardFormatValue
{
    public static function prepare(?string $raw): string
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

    public static function detectBulletinType(?string $raw): ?string
    {
        $value = self::prepare($raw);
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

    /**
     * Structural signature for grouping similar card numbers:
     * digits → d, letters → A, separators kept. Example: 12-AB-004 → dd-AA-ddd
     */
    public static function structureSignature(?string $raw): string
    {
        $value = self::prepare($raw);
        if ($value === '') {
            return '';
        }

        $signature = '';
        $len = strlen($value);
        for ($i = 0; $i < $len; $i++) {
            $char = $value[$i];
            if (ctype_digit($char)) {
                $signature .= 'd';
            } elseif (ctype_alpha($char)) {
                $signature .= 'A';
            } else {
                $signature .= $char;
            }
        }

        return $signature;
    }

    /**
     * Build a consensus format mask from many examples of the same structure.
     * Tokens that never change stay as literals; varying digit runs become d's,
     * varying letter runs become A's.
     * With a single example, digit runs are masked as d's and letters stay literal
     * (e.g. 4N-21-061-01-C → 4N-dd-ddd-dd-C).
     *
     * @param list<string> $rawValues
     */
    public static function consensusFormatMask(array $rawValues): string
    {
        $prepared = [];
        foreach ($rawValues as $raw) {
            $value = self::prepare((string) $raw);
            if ($value !== '') {
                $prepared[] = $value;
            }
        }

        $prepared = array_values(array_unique($prepared));
        if ($prepared === []) {
            return '—';
        }

        $tokenRows = [];
        foreach ($prepared as $value) {
            $tokenRows[] = self::tokenizeRuns($value);
        }

        $tokenCount = count($tokenRows[0]);
        foreach ($tokenRows as $tokens) {
            if (count($tokens) !== $tokenCount) {
                // Fallback: structural signature of first value
                return self::structureSignature($prepared[0]) ?: '—';
            }
        }

        $singleExample = count($prepared) === 1;
        $mask = '';

        for ($i = 0; $i < $tokenCount; $i++) {
            $first = $tokenRows[0][$i];
            $type = $first['type'];
            $allSame = true;
            foreach ($tokenRows as $tokens) {
                if ($tokens[$i]['type'] !== $type || $tokens[$i]['value'] !== $first['value']) {
                    $allSame = false;
                    break;
                }
            }

            if ($type === 'sep') {
                $mask .= $first['value'];
                continue;
            }

            $adjacentToLetters =
                ($i > 0 && $tokenRows[0][$i - 1]['type'] === 'letters')
                || ($i + 1 < $tokenCount && $tokenRows[0][$i + 1]['type'] === 'letters');

            if ($type === 'digits') {
                // Single example: keep digits glued to letters (e.g. 4N), mask standalone number groups.
                // Multiple examples: keep only tokens that never change across the set.
                if ($singleExample) {
                    $mask .= $adjacentToLetters
                        ? $first['value']
                        : str_repeat('d', strlen($first['value']));
                } elseif ($allSame) {
                    $mask .= $first['value'];
                } else {
                    $mask .= str_repeat('d', strlen($first['value']));
                }
                continue;
            }

            // letters
            if ($singleExample || $allSame) {
                $mask .= $first['value'];
            } else {
                $mask .= str_repeat('A', strlen($first['value']));
            }
        }

        return $mask !== '' ? $mask : '—';
    }

    /**
     * Split value into runs: digits / letters / separators.
     * Example: 4N-21-061-01-C → 4 | N | - | 21 | - | 061 | - | 01 | - | C
     *
     * @return list<array{type: string, value: string}>
     */
    public static function tokenizeRuns(string $value): array
    {
        $tokens = [];
        $len = strlen($value);
        $i = 0;

        while ($i < $len) {
            $char = $value[$i];
            if (ctype_digit($char)) {
                $start = $i;
                while ($i < $len && ctype_digit($value[$i])) {
                    $i++;
                }
                $tokens[] = ['type' => 'digits', 'value' => substr($value, $start, $i - $start)];
                continue;
            }

            if (ctype_alpha($char)) {
                $start = $i;
                while ($i < $len && ctype_alpha($value[$i])) {
                    $i++;
                }
                $tokens[] = ['type' => 'letters', 'value' => substr($value, $start, $i - $start)];
                continue;
            }

            $start = $i;
            while ($i < $len && !ctype_digit($value[$i]) && !ctype_alpha($value[$i])) {
                $i++;
            }
            $tokens[] = ['type' => 'sep', 'value' => substr($value, $start, $i - $start)];
        }

        return $tokens;
    }
}
