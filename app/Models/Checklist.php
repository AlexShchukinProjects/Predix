<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    use HasFactory;

    protected $table = 'insp_checklists';

    protected $fillable = [
        'title',
        'description',
        'image',
        'structure',
        'is_template',
        'published_date',
        'access',
        'status',
    ];

    protected $casts = [
        'structure' => 'array',
        'is_template' => 'boolean',
        'published_date' => 'date',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(ChecklistQuestion::class, 'checklist_id');
    }
}
