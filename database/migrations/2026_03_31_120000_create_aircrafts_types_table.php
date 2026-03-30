<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Справочник типов ВС: {@see \App\Models\AircraftsType} (Fleet, форма отказа, minimum crew, BUF-код rus).
     */
    public function up(): void
    {
        if (Schema::hasTable('aircrafts_types')) {
            return;
        }

        Schema::create('aircrafts_types', function (Blueprint $table): void {
            $table->id();
            $table->string('iata', 10)->nullable();
            $table->string('icao', 10)->nullable()->index();
            $table->string('rus', 50)->nullable()->comment('Код типа ВС для BUF / настройки');
            $table->string('name_eng')->nullable();
            $table->string('name_rus')->nullable()->index();
            $table->string('group', 100)->nullable();
            $table->unsignedInteger('crew1')->nullable();
            $table->unsignedInteger('crew2')->nullable();
            $table->string('country_manufacture', 100)->nullable();
            $table->decimal('wingspan', 10, 2)->nullable();
            $table->decimal('long', 10, 2)->nullable();
            $table->boolean('helicopter')->default(false);
            $table->boolean('active')->default(true)->index();
            $table->string('color', 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aircrafts_types');
    }
};
