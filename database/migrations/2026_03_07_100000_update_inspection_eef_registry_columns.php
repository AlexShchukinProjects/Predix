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
            $table->dropForeign(['work_card_id']);
        });

        Schema::table('inspection_eef_registry', function (Blueprint $table) {
            $table->dropColumn([
                'tc_number',
                'work_card_id',
                'eef_ref',
                'eef_subject',
                'eef_remarks',
                'source_card_ref',
            ]);
        });

        Schema::table('inspection_eef_registry', function (Blueprint $table) {
            $table->string('eef_number', 100)->nullable()->after('id');
            $table->string('nrc_number', 100)->nullable();
            $table->string('ac_type', 100)->nullable();
            $table->string('ata', 50)->nullable();
            $table->string('project_no', 100)->nullable();
            $table->string('subject', 500)->nullable();
            $table->text('remarks')->nullable();
            $table->string('location', 255)->nullable();
            $table->string('eef_status', 100)->nullable();
            $table->string('link', 500)->nullable();
            $table->string('link_path', 500)->nullable();
            $table->decimal('man_hours', 12, 2)->nullable();
            $table->string('chargeable_to_customer', 50)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('inspection_source_task', 500)->nullable();
            $table->string('rc_number', 100)->nullable();
            $table->date('open_date')->nullable();
            $table->string('assigned_engineering_engineer', 255)->nullable();
            $table->string('open_continuation_raised_by_production_dates', 255)->nullable();
            $table->string('answer_provided_by_engineering_dates', 255)->nullable();
            $table->string('oem_communication_reference', 500)->nullable();
            $table->string('gaes_eo', 255)->nullable();
            $table->string('manual_limits_out_within', 255)->nullable();
            $table->string('backup_engineer', 255)->nullable();
            $table->string('project_status', 100)->nullable();
            $table->string('eef_priority', 100)->nullable();
            $table->string('latest_processing', 255)->nullable();
            $table->string('project_status2', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_eef_registry', function (Blueprint $table) {
            $table->dropColumn([
                'eef_number', 'nrc_number', 'ac_type', 'ata', 'project_no', 'subject',
                'remarks', 'location', 'eef_status', 'link', 'link_path', 'man_hours',
                'chargeable_to_customer', 'customer_name', 'inspection_source_task', 'rc_number',
                'open_date', 'assigned_engineering_engineer', 'open_continuation_raised_by_production_dates',
                'answer_provided_by_engineering_dates', 'oem_communication_reference', 'gaes_eo',
                'manual_limits_out_within', 'backup_engineer', 'project_status', 'eef_priority',
                'latest_processing', 'project_status2',
            ]);
        });

        Schema::table('inspection_eef_registry', function (Blueprint $table) {
            $table->string('tc_number', 100)->nullable()->after('id');
            $table->foreignId('work_card_id')->nullable()->constrained('inspection_work_cards')->nullOnDelete();
            $table->string('eef_ref', 100)->nullable();
            $table->string('eef_subject', 500)->nullable();
            $table->text('eef_remarks')->nullable();
            $table->string('source_card_ref', 255)->nullable();
        });
    }
};
