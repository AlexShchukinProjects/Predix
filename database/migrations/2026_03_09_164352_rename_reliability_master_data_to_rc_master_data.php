<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('reliability_master_data', 'RC_master_data');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('RC_master_data', 'reliability_master_data');
    }
};
