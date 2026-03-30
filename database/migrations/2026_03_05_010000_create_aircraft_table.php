<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Парк ВС (Fleet): модель {@see \App\Models\Aircraft}, таблица aircraft (единственное число).
     */
    public function up(): void
    {
        if (Schema::hasTable('aircraft')) {
            return;
        }

        Schema::create('aircraft', function (Blueprint $table): void {
            $table->id();
            $table->string('RegN');
            $table->string('aircraft_number')->nullable()->comment('Legacy / фильтр в FleetController');
            $table->string('Owner')->nullable();
            $table->string('FactoryNumber')->nullable();
            $table->string('Type');
            $table->string('type_code', 50)->nullable();
            $table->string('modification_code', 50)->nullable();
            $table->string('Class')->nullable();
            $table->unsignedInteger('Pax_number')->nullable();
            $table->string('Airport_base')->nullable();
            $table->date('Date_manufacture')->nullable();
            $table->text('Repair')->nullable();
            $table->text('Description')->nullable();
            $table->decimal('Height', 10, 2)->nullable();
            $table->decimal('Length', 10, 2)->nullable();
            $table->decimal('Wing', 10, 2)->nullable();
            $table->unsignedInteger('Cruise_speed')->nullable();
            $table->unsignedInteger('Range')->nullable();
            $table->decimal('MWM', 12, 2)->nullable();
            $table->unsignedBigInteger('aircraft_type_id')->nullable()->index();
            $table->timestamps();

            $table->unique('RegN');
        });

        if (Schema::hasTable('aircrafts_types')) {
            Schema::table('aircraft', function (Blueprint $table): void {
                $table->foreign('aircraft_type_id')
                    ->references('id')
                    ->on('aircrafts_types')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aircraft');
    }
};
