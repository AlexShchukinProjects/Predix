<?php

declare(strict_types=1);

namespace App\Support;

final class CardFormatRuleInference
{
    /**
     * @return array{
     *   mask: string,
     *   digit_blocks: list<int>,
     *   mapping: list<array<string, mixed>>,
     *   preview_normalized: string|null,
     *   matches_expected: bool,
     *   document_type: string,
     *   oem: string|null,
     *   keep_suffix: bool,
     *   output_mask: string
     * }
     */
    public static function infer(string $rawExample, string $expectedOutput, ?string $oem = null, ?string $documentType = null): array
    {
        $preparedRaw = CardFormatValue::prepare($rawExample);
        $expected = strtoupper(trim($expectedOutput));

        if ($preparedRaw === '' || $expected === '') {
            throw new \InvalidArgumentException('Both raw example and expected output are required.');
        }

        $extraction = self::inferExtractionPattern($preparedRaw, $expected);
        if ($extraction !== null) {
            $preview = CardFormatMask::applySourceMask($rawExample, $extraction['mask']);
            $resolvedDocumentType = self::resolveDocumentType($documentType, $rawExample, $oem);
            $resolvedOem = self::resolveOem($oem);

            return [
                'mask' => $extraction['mask'],
                'digit_blocks' => $extraction['digit_blocks'],
                'mapping' => $extraction['mapping'],
                'preview_normalized' => $preview,
                'matches_expected' => self::valuesEquivalent($preview, $expected),
                'document_type' => $resolvedDocumentType,
                'oem' => $resolvedOem,
                'keep_suffix' => false,
                'output_mask' => $extraction['output_mask'],
            ];
        }

        $digitBlocks = self::inferDigitBlocks($preparedRaw, $expected);
        if ($digitBlocks === []) {
            throw new \InvalidArgumentException('Could not infer digit groups from the examples. Check that the expected output contains the same digits as the raw value.');
        }

        $outputMask = CardFormatMask::maskFromDigitBlocks($digitBlocks);
        $previewWithoutSuffix = CardFormatMask::apply($rawExample, $digitBlocks, $outputMask, false);
        $keepSuffix = !self::valuesEquivalent($previewWithoutSuffix, $expected);
        $preview = CardFormatMask::apply($rawExample, $digitBlocks, $outputMask, $keepSuffix);
        $mapping = self::buildMapping($preparedRaw, $expected, $digitBlocks);
        $resolvedDocumentType = self::resolveDocumentType($documentType, $rawExample, $oem);
        $resolvedOem = self::resolveOem($oem);

        return [
            'mask' => $outputMask,
            'digit_blocks' => $digitBlocks,
            'mapping' => $mapping,
            'preview_normalized' => $preview,
            'matches_expected' => self::valuesEquivalent($preview, $expected),
            'document_type' => $resolvedDocumentType,
            'oem' => $resolvedOem,
            'keep_suffix' => $keepSuffix,
            'output_mask' => $outputMask,
        ];
    }

    /**
     * When expected is a contiguous slice of prepared raw (e.g. 4N-21-061-01-C → 21-061-01),
     * build source mask 4N-dd-ddd-dd-C that extracts only the middle digit groups.
     *
     * @return array{mask: string, output_mask: string, digit_blocks: list<int>, mapping: list<array<string, mixed>>}|null
     */
    private static function inferExtractionPattern(string $preparedRaw, string $expected): ?array
    {
        $pos = strpos($preparedRaw, $expected);
        if ($pos === false) {
            // Also try matching expected digits as a contiguous digit subsequence
            return self::inferExtractionByDigits($preparedRaw, $expected);
        }

        $prefix = substr($preparedRaw, 0, $pos);
        $suffix = substr($preparedRaw, $pos + strlen($expected));
        $outputMask = CardFormatMask::maskFromValue($expected);
        $digitBlocks = CardFormatMask::digitBlocksFromMask($outputMask) ?? [];
        if ($digitBlocks === []) {
            return null;
        }

        $sourceMask = $prefix . $outputMask . $suffix;
        $mapping = [];

        if ($prefix !== '') {
            $mapping[] = [
                'type' => 'discard_prefix',
                'raw_part' => $prefix,
                'formatted_part' => '—',
                'length' => strlen($prefix),
                'mask_token' => $prefix,
            ];
        }

        $mapping = array_merge($mapping, self::buildAlignedDigitMapping($expected, $expected));

        if ($suffix !== '') {
            $mapping[] = [
                'type' => 'discard_suffix',
                'raw_part' => $suffix,
                'formatted_part' => '—',
                'length' => strlen($suffix),
                'mask_token' => $suffix,
            ];
        }

        return [
            'mask' => $sourceMask,
            'output_mask' => $outputMask,
            'digit_blocks' => $digitBlocks,
            'mapping' => $mapping,
        ];
    }

    /**
     * Fallback when expected is not a literal substring but its digits are inside raw digits.
     *
     * @return array{mask: string, output_mask: string, digit_blocks: list<int>, mapping: list<array<string, mixed>>}|null
     */
    private static function inferExtractionByDigits(string $preparedRaw, string $expected): ?array
    {
        $rawDigits = preg_replace('/\D/', '', $preparedRaw) ?? '';
        $expectedDigits = preg_replace('/\D/', '', $expected) ?? '';
        if ($rawDigits === '' || $expectedDigits === '' || !str_contains($rawDigits, $expectedDigits)) {
            return null;
        }

        // Only use this path when expected is a proper subset (prefix/suffix digits exist outside it)
        if ($rawDigits === $expectedDigits) {
            return null;
        }

        $digitPos = strpos($rawDigits, $expectedDigits);
        if ($digitPos === false) {
            return null;
        }

        // Walk prepared raw and rebuild source mask around the extracted digit span
        $outputMask = CardFormatMask::maskFromValue($expected);
        $digitBlocks = CardFormatMask::digitBlocksFromMask($outputMask) ?? [];
        if ($digitBlocks === []) {
            return null;
        }

        $sourceMask = '';
        $mapping = [];
        $digitIndex = 0;
        $extractStart = $digitPos;
        $extractEnd = $digitPos + strlen($expectedDigits);
        $inExtract = false;
        $prefixBuf = '';
        $suffixBuf = '';
        $len = strlen($preparedRaw);

        for ($i = 0; $i < $len; $i++) {
            $char = $preparedRaw[$i];
            if (ctype_digit($char)) {
                $beforeExtract = $digitIndex < $extractStart;
                $insideExtract = $digitIndex >= $extractStart && $digitIndex < $extractEnd;
                $digitIndex++;

                if ($insideExtract) {
                    if ($prefixBuf !== '') {
                        $mapping[] = [
                            'type' => 'discard_prefix',
                            'raw_part' => $prefixBuf,
                            'formatted_part' => '—',
                            'length' => strlen($prefixBuf),
                            'mask_token' => $prefixBuf,
                        ];
                        $sourceMask .= $prefixBuf;
                        $prefixBuf = '';
                    }
                    if (!$inExtract) {
                        $sourceMask .= $outputMask;
                        $inExtract = true;
                        $mapping = array_merge($mapping, self::buildAlignedDigitMapping($expected, $expected));
                    }
                    // skip raw digits inside extract — already represented by outputMask once
                    continue;
                }

                if ($beforeExtract) {
                    $prefixBuf .= $char;
                } else {
                    $suffixBuf .= $char;
                }
                continue;
            }

            if ($digitIndex <= $extractStart && !$inExtract) {
                $prefixBuf .= $char;
            } elseif ($inExtract && $digitIndex >= $extractEnd) {
                $suffixBuf .= $char;
            } elseif (!$inExtract) {
                $prefixBuf .= $char;
            } else {
                // separators inside extract region are part of outputMask already
            }
        }

        if ($suffixBuf !== '') {
            $sourceMask .= $suffixBuf;
            $mapping[] = [
                'type' => 'discard_suffix',
                'raw_part' => $suffixBuf,
                'formatted_part' => '—',
                'length' => strlen($suffixBuf),
                'mask_token' => $suffixBuf,
            ];
        }

        if ($sourceMask === '' || !CardFormatMask::hasFixedLiterals($sourceMask)) {
            return null;
        }

        // Verify it works
        $preview = CardFormatMask::applySourceMask($preparedRaw, $sourceMask);
        if (!self::valuesEquivalent($preview, $expected)) {
            return null;
        }

        return [
            'mask' => $sourceMask,
            'output_mask' => $outputMask,
            'digit_blocks' => $digitBlocks,
            'mapping' => $mapping,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function buildAlignedDigitMapping(string $rawSlice, string $expected): array
    {
        $segments = preg_split('/([^A-Z0-9]+)/', $expected, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
        $rawSegments = preg_split('/([^A-Z0-9]+)/', $rawSlice, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
        $mapping = [];
        $blockIndex = 0;

        $count = max(count($segments), count($rawSegments));
        for ($i = 0; $i < $count; $i++) {
            $formatted = $segments[$i] ?? '';
            $rawPart = $rawSegments[$i] ?? $formatted;
            if ($formatted === '' && $rawPart === '') {
                continue;
            }

            if (preg_match('/^\d+$/', $formatted) === 1) {
                $mapping[] = [
                    'type' => 'digits',
                    'raw_part' => $rawPart,
                    'formatted_part' => $formatted,
                    'length' => strlen($formatted),
                    'mask_token' => str_repeat('d', strlen($formatted)),
                    'block_index' => $blockIndex,
                ];
                $blockIndex++;
                continue;
            }

            if (preg_match('/^[A-Z]+$/', $formatted) === 1) {
                $mapping[] = [
                    'type' => 'letters',
                    'raw_part' => $rawPart,
                    'formatted_part' => $formatted,
                    'length' => strlen($formatted),
                    'mask_token' => str_repeat('A', strlen($formatted)),
                ];
                continue;
            }

            $mapping[] = [
                'type' => 'literal',
                'raw_part' => $rawPart !== '' ? $rawPart : $formatted,
                'formatted_part' => $formatted !== '' ? $formatted : $rawPart,
                'length' => strlen($formatted !== '' ? $formatted : $rawPart),
                'mask_token' => $formatted !== '' ? $formatted : $rawPart,
            ];
        }

        return $mapping;
    }

    /**
     * @return list<int>
     */
    private static function inferDigitBlocks(string $preparedRaw, string $expected): array
    {
        $expectedCore = self::stripSuffix($expected);
        $segments = preg_split('/[^A-Z0-9]+/', $expectedCore, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $blocks = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\d+$/', $segment) === 1) {
                $blocks[] = strlen($segment);
            }
        }

        if ($blocks !== []) {
            $rawDigits = preg_replace('/\D/', '', $preparedRaw) ?? '';
            if ($rawDigits !== '' && array_sum($blocks) === strlen($rawDigits)) {
                return $blocks;
            }
        }

        return self::splitExpectedDigitGroups($expectedCore);
    }

    /**
     * @return list<int>
     */
    private static function splitExpectedDigitGroups(string $expectedCore): array
    {
        $segments = preg_split('/[^0-9]+/', $expectedCore, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $blocks = [];

        foreach ($segments as $segment) {
            $blocks[] = strlen($segment);
        }

        return $blocks;
    }

    /**
     * @param list<int> $digitBlocks
     * @return list<array<string, mixed>>
     */
    private static function buildMapping(string $preparedRaw, string $expected, array $digitBlocks): array
    {
        $expectedCore = self::stripSuffix($expected);
        $segments = preg_split('/([^A-Z0-9]+)/', $expectedCore, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
        $rawDigits = preg_replace('/\D/', '', $preparedRaw) ?? '';
        $expectedDigits = preg_replace('/\D/', '', $expectedCore) ?? '';
        $offset = 0;
        if ($expectedDigits !== '' && str_contains($rawDigits, $expectedDigits)) {
            $offset = (int) strpos($rawDigits, $expectedDigits);
        }

        $mapping = [];
        $blockIndex = 0;
        $digitCursor = $offset;

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            if (preg_match('/^\d+$/', $segment) === 1) {
                $length = strlen($segment);
                $rawPart = substr($rawDigits, $digitCursor, $length) ?: $segment;
                $digitCursor += $length;

                $mapping[] = [
                    'type' => 'digits',
                    'raw_part' => $rawPart,
                    'formatted_part' => $segment,
                    'length' => $length,
                    'mask_token' => str_repeat('d', $length),
                    'block_index' => $blockIndex,
                ];
                $blockIndex++;
                continue;
            }

            if (preg_match('/^[A-Z]+$/', $segment) === 1) {
                $mapping[] = [
                    'type' => 'letters',
                    'raw_part' => $segment,
                    'formatted_part' => $segment,
                    'length' => strlen($segment),
                    'mask_token' => str_repeat('A', strlen($segment)),
                ];
                continue;
            }

            $mapping[] = [
                'type' => 'literal',
                'raw_part' => $segment,
                'formatted_part' => $segment,
                'length' => strlen($segment),
                'mask_token' => $segment,
            ];
        }

        $suffix = self::extractSuffix($expected);
        if ($suffix !== null) {
            $mapping[] = [
                'type' => 'suffix',
                'raw_part' => $suffix,
                'formatted_part' => $suffix,
                'length' => strlen($suffix),
                'mask_token' => 'A' . (strlen($suffix) > 1 ? str_repeat('d', strlen($suffix) - 1) : ''),
            ];
        }

        return $mapping;
    }

    private static function stripSuffix(string $expected): string
    {
        if (preg_match('/^(.+)-([A-Z]\d{0,3})$/', $expected, $match) === 1) {
            return $match[1];
        }

        return $expected;
    }

    private static function extractSuffix(string $expected): ?string
    {
        if (preg_match('/^(.+)-([A-Z]\d{0,3})$/', $expected, $match) === 1) {
            return $match[2];
        }

        return null;
    }

    private static function resolveDocumentType(?string $documentType, string $rawExample, ?string $oem): string
    {
        $normalized = strtolower(trim((string) ($documentType ?? '')));
        if (in_array($normalized, ['task_card', 'easa', 'faa', 'mpd', 'any'], true)) {
            return $normalized;
        }

        $detected = CardFormatValue::detectBulletinType($rawExample);
        if ($detected !== null) {
            return $detected;
        }

        if (self::resolveOem($oem) !== null) {
            return 'task_card';
        }

        return 'mpd';
    }

    private static function resolveOem(?string $oem): ?string
    {
        $value = strtolower(trim((string) ($oem ?? '')));

        return in_array($value, ['airbus', 'boeing'], true) ? $value : null;
    }

    private static function valuesEquivalent(?string $left, string $right): bool
    {
        if ($left === null) {
            return false;
        }

        return strtoupper(trim($left)) === strtoupper(trim($right));
    }
}
