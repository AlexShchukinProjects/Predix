<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PR_0112 – Project Inquiry (WINGS)
        Schema::create('inspection_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project', 100)->nullable()->comment('Project code/name');
            $table->string('scope', 500)->nullable();
            $table->string('customer', 255)->nullable();
            $table->decimal('flight_cycles', 15, 2)->nullable();
            $table->decimal('flight_hours', 15, 2)->nullable();
            $table->timestamps();
        });

        // PR_0030 + Airfleets – Aircrafts
        Schema::create('inspection_aircrafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('inspection_projects')->nullOnDelete();
            $table->string('msn', 100)->nullable()->comment('Manufacturer Serial Number');
            $table->date('first_flight')->nullable();
            $table->string('current_status', 255)->nullable();
            $table->timestamps();
        });

        // Справочник источников карт (MPD/AD/SB/EO/Customer)
        Schema::create('inspection_source_card_refs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->nullable();
            $table->string('name', 255)->nullable();
            $table->timestamps();
        });

        // PR_0059 – Work Card Inquiry (WINGS) + External
        Schema::create('inspection_work_cards', function (Blueprint $table) {
            $table->id();
            $table->string('tc_number', 100)->nullable()->comment('GAES WO Ref / TC#');
            $table->foreignId('project_id')->nullable()->constrained('inspection_projects')->nullOnDelete();
            $table->string('ac_registration', 50)->nullable();
            $table->string('source_card_ref', 255)->nullable();
            $table->foreignId('source_card_ref_id')->nullable()->constrained('inspection_source_card_refs')->nullOnDelete();
            $table->text('rc_nrc_description')->nullable();
            $table->text('rectification_action_ref')->nullable();
            $table->string('skill_code', 50)->nullable();
            $table->decimal('mhrs_spent', 12, 2)->nullable();
            $table->unsignedInteger('no_of_child_cards')->nullable()->comment('Number of NRC');
            $table->string('source', 20)->nullable()->comment('wings|customer');
            $table->decimal('flight_cycles', 15, 2)->nullable();
            $table->decimal('flight_hours', 15, 2)->nullable();
            $table->timestamps();
        });

        // EEF Registry (Excel)
        Schema::create('inspection_eef_registry', function (Blueprint $table) {
            $table->id();
            $table->string('tc_number', 100)->nullable()->comment('GAES WO Ref');
            $table->foreignId('work_card_id')->nullable()->constrained('inspection_work_cards')->nullOnDelete();
            $table->string('eef_ref', 100)->nullable();
            $table->string('eef_subject', 500)->nullable();
            $table->text('eef_remarks')->nullable();
            $table->string('source_card_ref', 255)->nullable();
            $table->timestamps();
        });

        // IC_0097 Material Data (WING) – колонки как в CSV
        Schema::create('inspection_work_card_materials', function (Blueprint $table) {
            $table->id();
            $table->string('project_number', 100)->nullable();
            $table->string('work_order_number', 100)->nullable();
            $table->string('zone_number', 50)->nullable();
            $table->string('item_number', 50)->nullable();
            $table->string('wip_status', 50)->nullable();
            $table->text('card_description')->nullable();
            $table->string('customer_work_card', 255)->nullable();
            $table->string('source_card_number', 255)->nullable();
            $table->string('source_customer_card', 255)->nullable();
            $table->string('tail_number', 50)->nullable();
            $table->decimal('est_time', 12, 2)->nullable();
            $table->string('tag_number', 100)->nullable();
            $table->string('part_number', 100)->nullable();
            $table->string('description', 500)->nullable();
            $table->string('oem_spec_number', 100)->nullable();
            $table->string('group_code', 50)->nullable();
            $table->dateTime('expire_dt')->nullable();
            $table->string('csp', 20)->nullable();
            $table->string('order_number', 100)->nullable();
            $table->dateTime('req_dt')->nullable();
            $table->dateTime('req_due_dt')->nullable();
            $table->decimal('req_qty', 15, 4)->nullable();
            $table->text('req_line_internal_comment')->nullable();
            $table->string('location', 255)->nullable();
            $table->dateTime('order_dt')->nullable();
            $table->dateTime('order_due_dt')->nullable();
            $table->decimal('order_qty', 15, 4)->nullable();
            $table->dateTime('receipt_dt')->nullable();
            $table->string('waybill', 100)->nullable();
            $table->dateTime('eta_dt')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('reason', 255)->nullable();
            $table->decimal('alloc_qty', 15, 4)->nullable();
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('item_list_price', 15, 4)->nullable();
            $table->decimal('order_unit_cost', 15, 4)->nullable();
            $table->string('currency', 10)->nullable();
            $table->timestamps();
        });

        // Previous Case Analyses (PDF + критичность)
        Schema::create('inspection_case_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_card_id')->nullable()->constrained('inspection_work_cards')->nullOnDelete();
            $table->string('tc_number', 100)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->boolean('is_critical')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_case_analyses');
        Schema::dropIfExists('inspection_work_card_materials');
        Schema::dropIfExists('inspection_eef_registry');
        Schema::dropIfExists('inspection_work_cards');
        Schema::dropIfExists('inspection_source_card_refs');
        Schema::dropIfExists('inspection_aircrafts');
        Schema::dropIfExists('inspection_projects');
    }
};
