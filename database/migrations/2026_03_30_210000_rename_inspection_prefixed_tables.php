<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Убирает префикс inspection_ у таблиц GAES / Reliability (для БД, созданных старыми миграциями).
     * При migrate:fresh с обновлёнными миграциями таблицы уже создаются без префикса — шаг пропускается.
     */
    public function up(): void
    {
        if (! Schema::hasTable('inspection_projects')) {
            return;
        }

        $this->dropForeignKeysBeforeRename();

        $renames = [
            'inspection_work_card_materials' => 'work_card_materials',
            'inspection_eef_registry'        => 'eef_registry',
            'inspection_case_analyses'     => 'case_analyses',
            'inspection_work_cards'        => 'work_cards',
            'inspection_aircrafts'         => 'aircrafts',
            'inspection_source_card_refs'  => 'source_card_refs',
            'inspection_projects'          => 'projects',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }

        $this->ensureForeignKeysAfterRename();
    }

    public function down(): void
    {
        if (! Schema::hasTable('projects') || Schema::hasTable('inspection_projects')) {
            return;
        }

        $this->dropForeignKeysNewNames();

        $renames = [
            'work_card_materials'   => 'inspection_work_card_materials',
            'eef_registry'          => 'inspection_eef_registry',
            'case_analyses'         => 'inspection_case_analyses',
            'work_cards'            => 'inspection_work_cards',
            'aircrafts'             => 'inspection_aircrafts',
            'source_card_refs'      => 'inspection_source_card_refs',
            'projects'              => 'inspection_projects',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }

        $this->ensureForeignKeysOldNames();
    }

    private function dropForeignKeysBeforeRename(): void
    {
        $specs = [
            ['inspection_eef_registry', 'work_card_id'],
            ['inspection_case_analyses', 'work_card_id'],
            ['inspection_work_cards', 'project_id'],
            ['inspection_work_cards', 'source_card_ref_id'],
            ['inspection_aircrafts', 'project_id'],
        ];

        foreach ($specs as [$table, $column]) {
            $this->tryDropForeign($table, $column);
        }
    }

    private function dropForeignKeysNewNames(): void
    {
        $specs = [
            ['eef_registry', 'work_card_id'],
            ['case_analyses', 'work_card_id'],
            ['work_cards', 'project_id'],
            ['work_cards', 'source_card_ref_id'],
            ['aircrafts', 'project_id'],
        ];

        foreach ($specs as [$table, $column]) {
            $this->tryDropForeign($table, $column);
        }
    }

    private function tryDropForeign(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($column): void {
                $t->dropForeign([$column]);
            });
        } catch (\Throwable) {
            // дубликат или драйвер без имени как в Laravel
        }
    }

    private function ensureForeignKeysAfterRename(): void
    {
        if (Schema::hasTable('aircrafts') && Schema::hasColumn('aircrafts', 'project_id')) {
            $this->tryDropForeign('aircrafts', 'project_id');
            Schema::table('aircrafts', function (Blueprint $table): void {
                $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            });
        }

        if (Schema::hasTable('work_cards')) {
            if (Schema::hasColumn('work_cards', 'project_id')) {
                $this->tryDropForeign('work_cards', 'project_id');
                Schema::table('work_cards', function (Blueprint $table): void {
                    $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
                });
            }
            if (Schema::hasColumn('work_cards', 'source_card_ref_id')) {
                $this->tryDropForeign('work_cards', 'source_card_ref_id');
                Schema::table('work_cards', function (Blueprint $table): void {
                    $table->foreign('source_card_ref_id')->references('id')->on('source_card_refs')->nullOnDelete();
                });
            }
        }

        if (Schema::hasTable('eef_registry') && Schema::hasColumn('eef_registry', 'work_card_id')) {
            $this->tryDropForeign('eef_registry', 'work_card_id');
            Schema::table('eef_registry', function (Blueprint $table): void {
                $table->foreign('work_card_id')->references('id')->on('work_cards')->nullOnDelete();
            });
        }

        if (Schema::hasTable('case_analyses') && Schema::hasColumn('case_analyses', 'work_card_id')) {
            $this->tryDropForeign('case_analyses', 'work_card_id');
            Schema::table('case_analyses', function (Blueprint $table): void {
                $table->foreign('work_card_id')->references('id')->on('work_cards')->nullOnDelete();
            });
        }
    }

    private function ensureForeignKeysOldNames(): void
    {
        if (Schema::hasTable('inspection_aircrafts') && Schema::hasColumn('inspection_aircrafts', 'project_id')) {
            $this->tryDropForeign('inspection_aircrafts', 'project_id');
            Schema::table('inspection_aircrafts', function (Blueprint $table): void {
                $table->foreign('project_id')->references('id')->on('inspection_projects')->nullOnDelete();
            });
        }

        if (Schema::hasTable('inspection_work_cards')) {
            if (Schema::hasColumn('inspection_work_cards', 'project_id')) {
                $this->tryDropForeign('inspection_work_cards', 'project_id');
                Schema::table('inspection_work_cards', function (Blueprint $table): void {
                    $table->foreign('project_id')->references('id')->on('inspection_projects')->nullOnDelete();
                });
            }
            if (Schema::hasColumn('inspection_work_cards', 'source_card_ref_id')) {
                $this->tryDropForeign('inspection_work_cards', 'source_card_ref_id');
                Schema::table('inspection_work_cards', function (Blueprint $table): void {
                    $table->foreign('source_card_ref_id')->references('id')->on('inspection_source_card_refs')->nullOnDelete();
                });
            }
        }

        if (Schema::hasTable('inspection_eef_registry') && Schema::hasColumn('inspection_eef_registry', 'work_card_id')) {
            $this->tryDropForeign('inspection_eef_registry', 'work_card_id');
            Schema::table('inspection_eef_registry', function (Blueprint $table): void {
                $table->foreign('work_card_id')->references('id')->on('inspection_work_cards')->nullOnDelete();
            });
        }

        if (Schema::hasTable('inspection_case_analyses') && Schema::hasColumn('inspection_case_analyses', 'work_card_id')) {
            $this->tryDropForeign('inspection_case_analyses', 'work_card_id');
            Schema::table('inspection_case_analyses', function (Blueprint $table): void {
                $table->foreign('work_card_id')->references('id')->on('inspection_work_cards')->nullOnDelete();
            });
        }
    }
};
