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
     *   oem: string|null
     * }
     */
    public static function infer(string $rawExample, string $expectedOutput, ?string $oem = null, ?string $documentType = null): array
    {
        $preparedRaw = CardFormatValue::prepare($rawExample);
        $expected = strtoupper(trim($expectedOutput));

        if ($preparedRaw === '' || $expected === '') {
            throw new \InvalidArgumentException('Both raw example and expected output are required.');
        }

        $digitBlocks = self::inferDigitBlocks($preparedRaw, $expected);
        if ($digitBlocks === []) {
            throw new \InvalidArgumentException('Could not infer digit groups from the examples. Check that the expected output contains the same digits as the raw value.');
        }

        $mask = CardFormatMask::maskFromDigitBlocks($digitBlocks);
        $preview = CardFormatMask::apply($rawExample, $digitBlocks);
        $mapping = self::buildMapping($preparedRaw, $expected, $digitBlocks);
        $resolvedDocumentType = self::resolveDocumentType($documentType, $rawExample, $oem);
        $resolvedOem = self::resolveOem($oem);

        return [
            'mask' => $mask,
            'digit_blocks' => $digitBlocks,
            'mapping' => $mapping,
            'preview_normalized' => $preview,
            'matches_expected' => self::valuesEquivalent($preview, $expected),
            'document_type' => $resolvedDocumentType,
            'oem' => $resolvedOem,
        ];
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

        $rawDigits = preg_replace('/\D/', '', $preparedRaw) ?? '';
        $expectedDigits = preg_replace('/\D/', '', $expectedCore) ?? '';

        if ($rawDigits !== '' && $expectedDigits !== '' && str_contains($rawDigits, $expectedDigits)) {
            return self::splitExpectedDigitGroups($expectedCore);
        }

        if ($rawDigits !== '' && $expectedDigits !== '' && str_contains($expectedDigits, $rawDigits)) {
            return self::splitExpectedDigitGroups($expectedCore);
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
        $offset = 0;
        $mapping = [];
        $blockIndex = 0;

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            if (preg_match('/^\d+$/', $segment) === 1) {
                $length = strlen($segment);
                $rawPart = substr($rawDigits, $offset, $length) ?: $segment;
                $offset += $length;

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
