<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('rel_stub');
    }

    public function down(): void
    {
        Schema::create('rel_stub', function ($table) {
            $table->id();
        });
    }
};
