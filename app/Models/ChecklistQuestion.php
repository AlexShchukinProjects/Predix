<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistQuestion extends Model
{
    use HasFactory;

    protected $table = 'insp_checklist_questions';

    protected $fillable = [
        'checklist_id',
        'title',
        'type',
        'required',
        'multiple',
        'section_id',
        'sort_order',
    ];

    protected $casts = [
        'required' => 'boolean',
        'multiple' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }
}
