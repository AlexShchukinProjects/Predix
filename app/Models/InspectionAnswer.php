<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionAnswer extends Model
{
    use HasFactory;

    protected $table = 'insp_inspection_answers';

    protected $fillable = [
        'inspection_id',
        'question_id',
        'question_index',
        'section_id',
        'answer_value',
        'answer_data',
        'notes',
        'media_files',
        'nonconformity_type_id',
    ];

    protected $casts = [
        'answer_data' => 'array',
        'media_files' => 'array',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ChecklistQuestion::class, 'question_id');
    }

    public function nonconformityType(): BelongsTo
    {
        return $this->belongsTo(NonconformityType::class, 'nonconformity_type_id');
    }
}
