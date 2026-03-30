<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['RC_master_data', 'NRC_master_data'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                $drop = array_filter(
                    ['id_file', 'prim_skill', 'child_card_count', 'eef'],
                    fn (string $c) => Schema::hasColumn($table, $c)
                );
                if ($drop !== []) {
                    $t->dropColumn($drop);
                }
            });

            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'project')) {
                    $t->string('project', 512)->nullable();
                }
                if (!Schema::hasColumn($table, 'project_type')) {
                    $t->string('project_type', 255)->nullable();
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
                if (!Schema::hasColumn($table, 'corrective_action')) {
                    $t->text('corrective_action')->nullable();
                }
                if (!Schema::hasColumn($table, 'ata')) {
                    $t->string('ata', 255)->nullable();
                }
                if (!Schema::hasColumn($table, 'cust_card')) {
                    $t->string('cust_card', 512)->nullable();
                }
                if (!Schema::hasColumn($table, 'avg_time')) {
                    $t->string('avg_time', 255)->nullable();
                }
                if (!Schema::hasColumn($table, 'aircraft_location')) {
                    $t->string('aircraft_location', 512)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['RC_master_data', 'NRC_master_data'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                $drop = array_filter([
                    'project', 'project_type', 'tail_number', 'wo_station', 'work_order', 'item',
                    'src_order', 'src_item', 'corrective_action', 'ata', 'avg_time', 'aircraft_location',
                ], fn (string $c) => Schema::hasColumn($table, $c));
                if ($drop !== []) {
                    $t->dropColumn($drop);
                }
            });
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'id_file')) {
                    $t->string('id_file', 255)->nullable();
                }
                if (!Schema::hasColumn($table, 'prim_skill')) {
                    $t->string('prim_skill', 255)->nullable();
                }
                if (!Schema::hasColumn($table, 'child_card_count')) {
                    $t->string('child_card_count', 255)->nullable();
                }
                if (!Schema::hasColumn($table, 'eef')) {
                    $t->string('eef', 255)->nullable();
                }
            });
        }
    }
};
