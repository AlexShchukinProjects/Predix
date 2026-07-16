<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reliability_card_format_rules')) {
            return;
        }

        Schema::create('reliability_card_format_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('document_type', 32)->default('any');
            $table->string('oem', 32)->nullable();
            $table->string('mask', 128);
            $table->json('digit_blocks');
            $table->boolean('is_builtin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(100);
            $table->string('example_raw', 255)->nullable();
            $table->string('example_normalized', 255)->nullable();
            $table->json('mapping')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'priority']);
            $table->index(['document_type', 'oem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reliability_card_format_rules');
    }
};
