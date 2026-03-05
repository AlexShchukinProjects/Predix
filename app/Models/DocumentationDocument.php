<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentationDocument extends Model
{
    use HasFactory;

    protected $table = 'doc_documents';

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'name',
        'file_path',
        'file_original_name',
        'description',
        'expiry_date',
        'status',
        // Поля из вкладки "Карточка"
        'document_number',
        'revision',
        'document_type',
        'document_kind',
        'owner',
        'owner_id',
        'document_status',
        'valid_from',
        'next_revision',
        'uploaded_by_id',
        'upload_date',
        // Поля согласования
        'approval_required',
        'familiarization_duration_minutes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'valid_from' => 'date',
        'next_revision' => 'date',
        'upload_date' => 'datetime',
    ];

    /**
     * Получить категорию, к которой принадлежит документ
     */
    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    /**
     * Получить подкатегорию, к которой принадлежит документ
     */
    public function subcategory()
    {
        return $this->belongsTo(DocumentSubcategory::class, 'subcategory_id');
    }

    /**
     * Получить пользователя, который загрузил документ
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    /**
     * Получить владельца документа
     */
    public function ownerUser()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Получить все версии согласования
     */
    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class, 'document_id');
    }

    /**
     * Получить все записи ознакомления
     */
    public function familiarizations()
    {
        return $this->hasMany(DocumentFamiliarization::class, 'document_id');
    }
}
