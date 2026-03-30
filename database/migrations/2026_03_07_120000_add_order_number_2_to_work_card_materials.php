<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_card_materials', function (Blueprint $table) {
            $table->string('order_number_2', 100)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('work_card_materials', function (Blueprint $table) {
            $table->dropColumn('order_number_2');
        });
    }
};
