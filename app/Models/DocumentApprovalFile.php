<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApprovalFile extends Model
{
    use HasFactory;

    protected $table = 'doc_document_approval_files';

    protected $fillable = [
        'approval_id',
        'file_name',
        'file_path',
        'file_size',
        'uploaded_by_name',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function approval()
    {
        return $this->belongsTo(DocumentApproval::class, 'approval_id');
    }
}
