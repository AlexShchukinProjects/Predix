<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\InspectionEefRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Link EEF registry rows to master-data NRC via NRC Number ↔ WORK ORDER + ITEM.
 */
final class EefNrcLinker
{
    /**
     * Spaces removed, lowercased — for comparing NRC Number strings.
     */
    public static function normalizeNrcString(string $s): string
    {
        return strtolower((string) preg_replace('/\s+/', '', trim($s)));
    }

    /**
     * Canonical key: "{WORK ORDER}-{ITEM 4-digit zero-padded}" e.g. 17766-0066.
     */
    public static function canonicalKeyFromWorkCard(?string $workOrder, ?string $item): ?string
    {
        $wo = trim((string) ($workOrder ?? ''));
        $it = trim((string) ($item ?? ''));
        if ($wo === '' || $it === '') {
            return null;
        }

        $itemDigits = preg_replace('/\D+/', '', $it) ?? '';
        if ($itemDigits === '') {
            return null;
        }

        $padded = str_pad($itemDigits, 4, '0', STR_PAD_LEFT);

        return self::normalizeNrcString($wo . '-' . $padded);
    }

    /**
     * Expand eef_registry.nrc_number into canonical keys.
     * Supports lists like "13116-0247,0236,0245" (short tokens inherit last WO).
     *
     * @return list<string>
     */
    public static function keysFromRegistryNrcNumber(?string $registryNrc): array
    {
        $raw = trim((string) ($registryNrc ?? ''));
        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/[,;]+/', $raw) ?: [];
        $keys = [];
        $lastWo = null;

        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }

            if (str_contains($part, '-')) {
                $segments = explode('-', $part, 2);
                $wo = trim((string) ($segments[0] ?? ''));
                $item = trim((string) ($segments[1] ?? ''));
                $key = self::canonicalKeyFromWorkCard($wo, $item);
                if ($key !== null) {
                    $keys[] = $key;
                    $lastWo = $wo;
                }
                continue;
            }

            if ($lastWo !== null) {
                $key = self::canonicalKeyFromWorkCard($lastWo, $part);
                if ($key !== null) {
                    $keys[] = $key;
                }
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * Count distinct EEF numbers linked to the given work-card NRC rows.
     *
     * @param iterable<int, object|array<string, mixed>> $nrcRows rows with work_order + item
     */
    public static function countDistinctEefForNrcRows(iterable $nrcRows): int
    {
        $nrcKeys = [];
        foreach ($nrcRows as $row) {
            $wo = is_array($row) ? ($row['work_order'] ?? '') : ($row->work_order ?? '');
            $item = is_array($row) ? ($row['item'] ?? '') : ($row->item ?? '');
            $key = self::canonicalKeyFromWorkCard((string) $wo, (string) $item);
            if ($key !== null) {
                $nrcKeys[$key] = true;
            }
        }

        if ($nrcKeys === []) {
            return 0;
        }

        if (!Schema::hasTable('eef_registry')) {
            return 0;
        }

        $matchedEef = [];
        InspectionEefRegistry::query()
            ->whereRaw("TRIM(COALESCE(nrc_number, '')) <> ''")
            ->select(['id', 'eef_number', 'nrc_number'])
            ->orderBy('id')
            ->chunkById(500, function ($chunk) use (&$matchedEef, $nrcKeys): void {
                foreach ($chunk as $eef) {
                    $eefNumber = trim((string) ($eef->eef_number ?? ''));
                    if ($eefNumber === '' || isset($matchedEef[$eefNumber])) {
                        continue;
                    }

                    foreach (self::keysFromRegistryNrcNumber((string) $eef->nrc_number) as $key) {
                        if (isset($nrcKeys[$key])) {
                            $matchedEef[$eefNumber] = true;
                            break;
                        }
                    }
                }
            });

        return count($matchedEef);
    }

    /**
     * Count distinct EEFs for STR NRCs whose SRC. CUST. CARD contains the given MPD.
     */
    public static function countDistinctEefForMpdStrNrcs(string $mpd, string $workCardsTable = 'work_cards_master'): int
    {
        $mpd = trim($mpd);
        if ($mpd === '' || !Schema::hasTable($workCardsTable)) {
            return 0;
        }

        $mpdLike = '%' . self::escapeLike($mpd) . '%';
        $rows = DB::table($workCardsTable)
            ->select(['work_order', 'item'])
            ->whereRaw('src_cust_card LIKE ?', [$mpdLike])
            ->whereRaw(GaesWorkCardSegregation::sqlIsNrc('order_type'))
            ->whereRaw("(
                UPPER(COALESCE(description, '')) LIKE '%STR%'
                OR UPPER(COALESCE(corrective_action, '')) LIKE '%STR%'
                OR UPPER(COALESCE(order_type, '')) LIKE '%STR%'
            )")
            ->get();

        return self::countDistinctEefForNrcRows($rows);
    }

    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
