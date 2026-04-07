<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aircrafts')) {
            return;
        }

        Schema::table('aircrafts', function (Blueprint $table): void {
            if (!Schema::hasColumn('aircrafts', 'status')) {
                $table->string('status', 100)->nullable()->after('tail_number');
            }
            if (!Schema::hasColumn('aircrafts', 'first_flight')) {
                $table->date('first_flight')->nullable()->after('status');
            }
            if (!Schema::hasColumn('aircrafts', 'type_ac')) {
                $table->string('type_ac', 100)->nullable()->after('aircraft_type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('aircrafts')) {
            return;
        }

        Schema::table('aircrafts', function (Blueprint $table): void {
            $drop = array_filter(
                ['status', 'first_flight', 'type_ac'],
                fn (string $col): bool => Schema::hasColumn('aircrafts', $col)
            );
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};

