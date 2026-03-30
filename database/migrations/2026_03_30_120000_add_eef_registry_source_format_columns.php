<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_eef_registry', function (Blueprint $table) {
            $table->string('eef_with', 100)->nullable()->after('project_status2');
            $table->text('standard_remarks_on_current_progress')->nullable()->after('eef_with');
            $table->text('latest_comments_short_answer')->nullable()->after('standard_remarks_on_current_progress');
            $table->string('project_status3', 100)->nullable()->after('latest_comments_short_answer');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_eef_registry', function (Blueprint $table) {
            $table->dropColumn([
                'eef_with',
                'standard_remarks_on_current_progress',
                'latest_comments_short_answer',
                'project_status3',
            ]);
        });
    }
};
