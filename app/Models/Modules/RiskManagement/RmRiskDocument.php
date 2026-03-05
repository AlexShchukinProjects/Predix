<?php

namespace App\Models\Modules\RiskManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class RmRiskDocument extends Model
{
    use HasFactory;

    protected $table = 'rm_risk_documents';

    protected $fillable = [
        'risk_id',
        'corrective_measure_id',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'document_type',
        'description',
        'uploaded_by_id',
    ];

    // Отношения
    public function risk(): BelongsTo
    {
        return $this->belongsTo(RmRisk::class, 'risk_id');
    }

    public function correctiveMeasure(): BelongsTo
    {
        return $this->belongsTo(RmCorrectiveMeasure::class, 'corrective_measure_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    // Скоупы
    public function scopeIdentification($query)
    {
        return $query->where('document_type', 'identification');
    }

    public function scopeAssessment($query)
    {
        return $query->where('document_type', 'assessment');
    }

    public function scopeCorrectiveMeasure($query)
    {
        return $query->where('document_type', 'corrective_measure');
    }

    public function scopeResidualAssessment($query)
    {
        return $query->whereIn('document_type', ['residual_assessment', 'other']);
    }

    // Методы
    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getDocumentTypeText(): string
    {
        return match($this->document_type) {
            'identification' => 'Идентификация',
            'assessment' => 'Оценка',
            'residual_assessment' => 'Оценка остаточного риска',
            'corrective_measure' => 'Корректирующее мероприятие',
            'other' => 'Прочее',
            default => 'Неизвестно'
        };
    }
}
