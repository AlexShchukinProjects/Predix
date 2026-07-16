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
}
