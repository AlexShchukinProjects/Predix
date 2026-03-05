<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFamiliarization extends Model
{
    use HasFactory;

    protected $table = 'doc_document_familiarizations';

    protected $fillable = [
        'document_id',
        'user_id',
        'familiarize_by',
        'status',
        'read_at',
        'sent',
        'sent_at',
    ];

    protected $casts = [
        'familiarize_by' => 'date',
        'read_at' => 'datetime',
        'sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentationDocument::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
