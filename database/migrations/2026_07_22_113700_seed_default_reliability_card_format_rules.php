<?php

declare(strict_types=1);

use App\Support\CardFormatRuleRepository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reliability_card_format_rules')) {
            return;
        }

        // Former code-level rules become normal editable DB rows.
        DB::table('reliability_card_format_rules')
            ->where('is_builtin', true)
            ->update(['is_builtin' => false, 'updated_at' => now()]);

        $now = now();

        foreach (CardFormatRuleRepository::defaultSeedDefinitions() as $definition) {
            $exists = DB::table('reliability_card_format_rules')
                ->where('mask', $definition['mask'])
                ->where('name', $definition['name'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('reliability_card_format_rules')->insert([
                'name' => $definition['name'],
                'document_type' => $definition['document_type'],
                'oem' => $definition['oem'],
                'mask' => $definition['mask'],
                'digit_blocks' => json_encode($definition['digit_blocks'], JSON_THROW_ON_ERROR),
                'is_builtin' => false,
                'is_active' => true,
                'priority' => $definition['priority'],
                'example_raw' => $definition['example_raw'],
                'example_normalized' => $definition['example_normalized'],
                'mapping' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('reliability_card_format_rules')) {
            return;
        }

        foreach (CardFormatRuleRepository::defaultSeedDefinitions() as $definition) {
            DB::table('reliability_card_format_rules')
                ->where('mask', $definition['mask'])
                ->where('name', $definition['name'])
                ->where('is_builtin', false)
                ->delete();
        }
    }
};
