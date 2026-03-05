<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $table = 'doc_categories';

    protected $fillable = [
        'section_id',
        'name',
        'order',
        'column',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Получить раздел, к которому принадлежит категория
     */
    public function section()
    {
        return $this->belongsTo(DocumentationSection::class, 'section_id');
    }

    /**
     * Получить документы категории
     */
    public function documents()
    {
        return $this->hasMany(DocumentationDocument::class, 'category_id');
    }

    /**
     * Получить подкатегории категории
     */
    public function subcategories()
    {
        return $this->hasMany(DocumentSubcategory::class, 'category_id')->orderBy('order');
    }
}
