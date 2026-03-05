<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Таблицы удалённых модулей (инспекции, риски, SR, документация, планирование, обучение, SPI, исп.дисциплина). */
    private const TABLES_TO_DROP = [
        // Инспекции
        'insp_inspection_answers',
        'insp_checklist_questions',
        'insp_remarks',
        'insp_report_approvals',
        'insp_checklists',
        'insp_audit_subtypes',
        'insp_audit_types',
        'insp_inspections',
        'insp_departments',
        'insp_nonconformity_types',
        // Управление рисками
        'rm_corrective_measures',
        'rm_risk_documents',
        'rm_risk_residual_assessment_history',
        'rm_risk_assessment_history',
        'rm_risk_changes',
        'rm_risk_notifications',
        'rm_risks',
        'rm_identification_settings',
        'rm_assessment_settings',
        'rm_danger_characteristics',
        'rm_categories',
        'rm_programs',
        'rm_areas',
        'rm_department_codes',
        'rm_risk_areas',
        // Safety Reporting
        'sr_message_actions',
        'sr_event_description_message_notifications',
        'sr_message_changes',
        'sr_message_analysis',
        'sr_message_feedback',
        'sr_message_risk_assessments',
        'sr_message_data',
        'sr_message_type_sections',
        'sr_message_type_fields',
        'sr_message_event_descriptions',
        'sr_messages',
        'sr_message_types',
        'sr_operation_stages',
        'sr_factors',
        'sr_aircraft_event_types',
        'sr_time_of_day',
        'sr_sources',
        'sr_hazardous_weather',
        'sr_hazard_factor_details',
        'sr_hazard_factors',
        'sr_asobp_codes',
        'sr_customers',
        'sr_activity_areas',
        'sr_message_field_definitions',
        // Документация
        'doc_document_approval_files',
        'doc_document_familiarizations',
        'doc_document_approval_sheet',
        'doc_document_approvers',
        'doc_document_approvals',
        'doc_documents',
        'doc_subcategories',
        'doc_categories',
        'doc_sections',
        'doc_module_settings',
        // Планирование / экипажи
        'pl_flight_changes_history',
        'pl_flight_pax',
        'pl_mnt_resources',
        'pl_crew_performance_settings',
        'crewperformances',
        'crewperformancelistpersonells',
        'flight_pax',
        'flight_servises',
        'services',
        // Обучение
        'tr_lesson_progress',
        'tr_lesson_files',
        'tr_question_answers',
        'tr_questions',
        'tr_question_groups',
        'tr_course_lessons',
        'tr_course_assignments',
        // SPI
        'spi_indicator_levels',
        'spi_settings',
        // Исполнительская дисциплина
        'executive_discipline_measure_history',
        'executive_discipline_measures',
        // Прочие
        'special_situation_types',
        'maintenance_types',
        'risk_registry',
    ];

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        foreach (self::TABLES_TO_DROP as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        // Восстановление таблиц не предусмотрено — нужен полный бэкап перед миграцией.
    }
};
