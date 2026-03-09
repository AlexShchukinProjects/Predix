<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reliability_master_data', function (Blueprint $table) {
            $table->id();
            $table->string('aircraft_type', 255)->nullable();
            $table->string('src_cust_card', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('prim_skill', 255)->nullable();
            $table->string('order_type', 255)->nullable();
            $table->string('act_time', 255)->nullable();
            $table->string('child_card_count', 255)->nullable();
            $table->string('eef', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reliability_master_data');
    }
};
