<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentationSection extends Model
{
    use HasFactory;

    protected $table = 'doc_sections';

    protected $fillable = [
        'name',
        'code',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Получить категории документов для раздела
     */
    public function categories()
    {
        return $this->hasMany(DocumentCategory::class, 'section_id')->orderBy('order');
    }
}
