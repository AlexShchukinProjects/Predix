<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SRMessageAnalysis extends Model
{
    use HasFactory;

    protected $table = 'sr_message_analysis';

    protected $fillable = [
        'sr_message_id',
        'root_causes_old',
        'contributing_factors_old', 
        'findings_old',
        'meta',
        // Новые поля анализа
        'analysis_required',
        'analysis_active',
        'analysis_comment',
        'investigation_status',
        'commission_order_number',
        'circumstances',
        'main_data',
        'works_performed',
        'outcome',
        'conclusion',
        'recommendations',
        'commission_order_files',
        'investigation_report_files',
        'investigation_materials_files',
        'media_files',
        'report_comment',
        'coapprover_user_ids',
        'approver_user_id',
        'hazard_factor',
        'responsible_department',
    ];

    protected $casts = [
        'meta' => 'array',
        'analysis_required' => 'boolean',
        'analysis_active' => 'boolean',
        'commission_order_files' => 'array',
        'investigation_report_files' => 'array',
        'investigation_materials_files' => 'array',
        'media_files' => 'array',
        'coapprover_user_ids' => 'array',
    ];

    /**
     * Фильтрует файлы, проверяя их существование на диске
     */
    private function filterExistingFiles(array $files): array
    {
        if (empty($files)) {
            return [];
        }

        return array_values(array_filter($files, function ($file) {
            if (!is_array($file) || !isset($file['path'])) {
                return false;
            }
            // Проверяем существование файла на диске
            return Storage::disk('public')->exists($file['path']);
        }));
    }

    /**
     * Нормализует значение JSON-поля с файлами.
     *
     * Важно: классические accessors `getXAttribute($value)` получают сырое значение из БД (строку JSON),
     * и при наличии такого accessor Laravel НЕ применяет `$casts` автоматически.
     */
    private function normalizeFilesValue(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Получить файлы приказа комиссии (только существующие)
     */
    public function getCommissionOrderFilesAttribute($value)
    {
        return $this->filterExistingFiles($this->normalizeFilesValue($value));
    }

    /**
     * Получить файлы отчета расследования (только существующие)
     */
    public function getInvestigationReportFilesAttribute($value)
    {
        return $this->filterExistingFiles($this->normalizeFilesValue($value));
    }

    /**
     * Получить файлы материалов расследования (только существующие)
     */
    public function getInvestigationMaterialsFilesAttribute($value)
    {
        return $this->filterExistingFiles($this->normalizeFilesValue($value));
    }

    /**
     * Получить медиа файлы (только существующие)
     */
    public function getMediaFilesAttribute($value)
    {
        return $this->filterExistingFiles($this->normalizeFilesValue($value));
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(SRMessage::class, 'sr_message_id');
    }

    /** Фактор из вкладки «Анализ» (справочник sr_factors) */
    public function factor(): BelongsTo
    {
        return $this->belongsTo(SrFactor::class, 'hazard_factor', 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}


