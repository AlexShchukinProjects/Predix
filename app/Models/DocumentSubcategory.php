<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSubcategory extends Model
{
    use HasFactory;

    protected $table = 'doc_subcategories';

    protected $fillable = [
        'category_id',
        'name',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Получить категорию, к которой принадлежит подкатегория
     */
    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    /**
     * Получить документы подкатегории
     */
    public function documents()
    {
        return $this->hasMany(DocumentationDocument::class, 'subcategory_id');
    }
}
