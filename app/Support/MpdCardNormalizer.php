<?php

declare(strict_types=1);

namespace App\Support;

final class MpdCardNormalizer
{
    /**
     * Normalize CUST. CARD value to MPD-like format:
     * - XX-XX-XX-XXX-XXX[-SUFFIX] (classic MPD)
     * - XX-XXX-XX-XX[-SUFFIX]      (alt format with 3-digit second block)
     * - minimum accepted core: first 3 blocks (XX-XX-XX or XX-XXX-XX)
     */
    public static function normalize(string $raw): ?string
    {
        $value = strtoupper(trim($raw));
        if ($value === '') {
            return null;
        }

        // Remove common leading words that are not part of the MPD number.
        $value = preg_replace('/^(TASK|MPD|CARD|TC|JOB)\s*[:#-]?\s*/', '', $value) ?? $value;

        // Normalize delimiters and strip obvious noise while keeping digits/letters.
        $value = str_replace(['(', ')', '[', ']', '{', '}', '/', '\\', '_', '.', ','], '-', $value);
        $value = preg_replace('/\s+/', '-', $value) ?? $value;
        $value = preg_replace('/[^A-Z0-9-]/', '', $value) ?? $value;
        $value = preg_replace('/-+/', '-', $value) ?? $value;
        $value = trim($value, '-');

        if ($value === '') {
            return null;
        }

        // 1) Compact 12-digit form: 291105210804 -> 29-11-05-210-804
        if (preg_match('/(?<!\d)(\d{12})(?!\d)/', $value, $m) === 1) {
            $digits = $m[1];
            return sprintf(
                '%s-%s-%s-%s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 2),
                substr($digits, 4, 2),
                substr($digits, 6, 3),
                substr($digits, 9, 3)
            );
        }

        // 1b) Compact 9-digit alt form: 121080001 -> 12-108-00-01
        if (preg_match('/(?<!\d)(\d{9})(?!\d)/', $value, $m) === 1) {
            $digits = $m[1];
            return sprintf(
                '%s-%s-%s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 3),
                substr($digits, 5, 2),
                substr($digits, 7, 2)
            );
        }

        // 2) Delimited or mixed noisy classic MPD form.
        if (preg_match('/(\d{2})\D*(\d{2})\D*(\d{2})\D*(\d{3})\D*(\d{3})/', $value, $m, PREG_OFFSET_CAPTURE) === 1) {
            $core = sprintf('%s-%s-%s-%s-%s', $m[1][0], $m[2][0], $m[3][0], $m[4][0], $m[5][0]);
            $endOffset = $m[5][1] + strlen($m[5][0]);
            $tail = substr($value, $endOffset);
            return self::appendSuffix($core, $tail);
        }

        // 3) Delimited alt form where second block may have 2 or 3 digits: 12-108-00-01
        if (preg_match('/(\d{2})\D*(\d{2,3})\D*(\d{2})\D*(\d{2})/', $value, $m, PREG_OFFSET_CAPTURE) === 1) {
            $core = sprintf('%s-%s-%s-%s', $m[1][0], $m[2][0], $m[3][0], $m[4][0]);
            $endOffset = $m[4][1] + strlen($m[4][0]);
            $tail = substr($value, $endOffset);
            return self::appendSuffix($core, $tail);
        }

        // 4) Minimum acceptable core: first 3 blocks only (e.g. 24-041-03).
        if (preg_match('/(\d{2})\D*(\d{2,3})\D*(\d{2})/', $value, $m, PREG_OFFSET_CAPTURE) === 1) {
            $core = sprintf('%s-%s-%s', $m[1][0], $m[2][0], $m[3][0]);
            $endOffset = $m[3][1] + strlen($m[3][0]);
            $tail = substr($value, $endOffset);
            return self::appendSuffix($core, $tail);
        }

        return null;
    }

    private static function appendSuffix(string $core, string $tail): string
    {
        $tail = trim((string) preg_replace('/^-+/', '', $tail));

        // Keep common revision/suffix markers (A, A1, F00, R2, etc).
        if ($tail !== '' && preg_match('/^[A-Z]\d{0,3}$/', $tail) === 1) {
            return $core . '-' . $tail;
        }

        if ($tail !== '' && preg_match('/^([A-Z]\d{0,3})-.*$/', $tail, $suffix) === 1) {
            return $core . '-' . $suffix[1];
        }

        return $core;
    }
}

