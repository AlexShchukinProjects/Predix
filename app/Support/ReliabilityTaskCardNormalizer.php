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
    public static function normalize(?string $raw): ?string
    {
        return MpdCardNormalizer::normalize((string) ($raw ?? ''));
    }
}
