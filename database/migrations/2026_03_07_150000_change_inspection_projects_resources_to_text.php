<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE inspection_projects MODIFY COLUMN resources TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE inspection_projects MODIFY COLUMN resources VARCHAR(255) NULL');
    }
};
