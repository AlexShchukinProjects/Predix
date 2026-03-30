<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Одна таблица master data: бывш. RC_master_data → work_cards_master; NRC_master_data удалена.
 * Вкладки RC/NRC в UI — фильтры по ORDER TYPE и SRC. CUST. CARD, не отдельные таблицы.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('NRC_master_data');
        if (Schema::hasTable('RC_master_data')) {
            Schema::rename('RC_master_data', 'work_cards_master');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('work_cards_master')) {
            Schema::rename('work_cards_master', 'RC_master_data');
        }
        // NRC_master_data не восстанавливаем — при откате нужна старая миграция create.
    }
};
