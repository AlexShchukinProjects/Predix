<?php

declare(strict_types=1);

namespace App\Support;

/**
 * GAES Work Card segregation (RC / NRC) per business instruction:
 * - RC: ORDER TYPE ≠ NON-ROUTINE
 * - NRC: ORDER TYPE = NON-ROUTINE
 * - GAES ID: WORK ORDER & "-" & ITEM (columns G-I), e.g. 21661-0388
 * - Source RC ID (for NRC): SRC. ORDER & "-" & SRC. ITEM (columns O-Q)
 */
final class GaesWorkCardSegregation
{
    /**
     * Normalize order type for comparison: "NON-ROUTINE", "NONROUTINE", "Non Routine" → nonroutine
     */
    public static function normalizeOrderType(?string $orderType): string
    {
        $value = strtolower(trim((string) ($orderType ?? '')));
        $value = str_replace(['-', '_'], '', $value);
        $value = (string) preg_replace('/\s+/', '', $value);

        return $value;
    }

    public static function isNonRoutine(?string $orderType): bool
    {
        return self::normalizeOrderType($orderType) === 'nonroutine';
    }

    public static function isNrc(?string $orderType): bool
    {
        return self::isNonRoutine($orderType);
    }

    public static function isRc(?string $orderType): bool
    {
        return !self::isNonRoutine($orderType);
    }

    /**
     * SQL expression that normalizes order_type to compare with 'nonroutine'.
     */
    public static function sqlNormalizedOrderType(string $column = 'order_type'): string
    {
        return "REPLACE(REPLACE(LOWER(TRIM(COALESCE({$column}, ''))), '-', ''), ' ', '')";
    }

    /**
     * SQL WHERE fragment: NRC rows (ORDER TYPE = NON-ROUTINE).
     */
    public static function sqlIsNrc(string $column = 'order_type'): string
    {
        return '(' . self::sqlNormalizedOrderType($column) . " = 'nonroutine')";
    }

    /**
     * SQL WHERE fragment: RC rows (ORDER TYPE ≠ NON-ROUTINE).
     */
    public static function sqlIsRc(string $column = 'order_type'): string
    {
        return '(' . self::sqlNormalizedOrderType($column) . " <> 'nonroutine')";
    }

    /**
     * GAES RC/NRC ID = Column G & "-" & Column I.
     */
    public static function gaesId(?string $workOrder, ?string $item): ?string
    {
        $wo = trim((string) ($workOrder ?? ''));
        $it = trim((string) ($item ?? ''));
        if ($wo === '' || $it === '') {
            return null;
        }

        return $wo . '-' . $it;
    }

    /**
     * GAES Source RC ID = Column O & "-" & Column Q.
     */
    public static function sourceRcId(?string $srcOrder, ?string $srcItem): ?string
    {
        $order = trim((string) ($srcOrder ?? ''));
        $item = trim((string) ($srcItem ?? ''));
        if ($order === '' || $item === '') {
            return null;
        }

        return $order . '-' . $item;
    }
}
