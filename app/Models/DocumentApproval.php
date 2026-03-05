<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApproval extends Model
{
    use HasFactory;

    protected $table = 'doc_document_approvals';

    protected $fillable = [
        'document_id',
        'version_number',
        'status',
        'approval_type',
        'initiator_id',
        'executor_id',
        'signer_id',
        'sent',
        'sent_at',
        'withdrawn_by_id',
        'withdrawn_at',
        'linked_documents',
    ];

    protected $casts = [
        'sent' => 'boolean',
        'sent_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'linked_documents' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(DocumentationDocument::class, 'document_id');
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signer_id');
    }

    public function withdrawnBy()
    {
        return $this->belongsTo(User::class, 'withdrawn_by_id');
    }

    public function approvers()
    {
        return $this->hasMany(DocumentApprover::class, 'approval_id')->orderBy('order');
    }

    public function approvalSheet()
    {
        return $this->hasMany(DocumentApprovalSheet::class, 'approval_id');
    }

    public function files()
    {
        return $this->hasMany(DocumentApprovalFile::class, 'approval_id');
    }
}
