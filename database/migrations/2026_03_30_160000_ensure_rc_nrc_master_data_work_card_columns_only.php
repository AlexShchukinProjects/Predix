<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RC_master_data и NRC_master_data: только поля Work Card (как при импорте) + id и timestamps.
 * Удаляет любые лишние колонки, добавляет отсутствующие — обе таблицы в итоге совпадают по схеме.
 */
return new class extends Migration
{
    private const ALLOWED = [
        'id',
        'project',
        'project_type',
        'aircraft_type',
        'tail_number',
        'wo_station',
        'work_order',
        'item',
        'src_order',
        'src_item',
        'src_cust_card',
        'description',
        'corrective_action',
        'ata',
        'cust_card',
        'order_type',
        'avg_time',
        'act_time',
        'aircraft_location',
        'created_at',
        'updated_at',
    ];

    public function up(): void
    {
        foreach (['RC_master_data', 'NRC_master_data'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $existing = Schema::getColumnListing($table);
            $toDrop = array_values(array_diff($existing, self::ALLOWED));
            if ($toDrop !== []) {
                Schema::table($table, function (Blueprint $t) use ($toDrop) {
                    $t->dropColumn($toDrop);
                });
            }
            $this->ensureWorkCardColumns($table);
        }
    }

    private function ensureWorkCardColumns(string $table): void
    {
        Schema::table($table, function (Blueprint $t) use ($table) {
            if (!Schema::hasColumn($table, 'project')) {
                $t->string('project', 512)->nullable();
            }
            if (!Schema::hasColumn($table, 'project_type')) {
                $t->string('project_type', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'aircraft_type')) {
                $t->string('aircraft_type', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'tail_number')) {
                $t->string('tail_number', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'wo_station')) {
                $t->string('wo_station', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'work_order')) {
                $t->string('work_order', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'item')) {
                $t->string('item', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'src_order')) {
                $t->string('src_order', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'src_item')) {
                $t->string('src_item', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'src_cust_card')) {
                $t->string('src_cust_card', 512)->nullable();
            }
            if (!Schema::hasColumn($table, 'description')) {
                $t->text('description')->nullable();
            }
            if (!Schema::hasColumn($table, 'corrective_action')) {
                $t->text('corrective_action')->nullable();
            }
            if (!Schema::hasColumn($table, 'ata')) {
                $t->string('ata', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'cust_card')) {
                $t->string('cust_card', 512)->nullable();
            }
            if (!Schema::hasColumn($table, 'order_type')) {
                $t->string('order_type', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'avg_time')) {
                $t->string('avg_time', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'act_time')) {
                $t->string('act_time', 255)->nullable();
            }
            if (!Schema::hasColumn($table, 'aircraft_location')) {
                $t->string('aircraft_location', 512)->nullable();
            }
            $hasCreated = Schema::hasColumn($table, 'created_at');
            $hasUpdated = Schema::hasColumn($table, 'updated_at');
            if (!$hasCreated && !$hasUpdated) {
                $t->timestamps();
            } else {
                if (!$hasCreated) {
                    $t->timestamp('created_at')->nullable();
                }
                if (!$hasUpdated) {
                    $t->timestamp('updated_at')->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        // Не восстанавливаем удалённые колонки — откат только при откате всего набора миграций вручную.
    }
};
