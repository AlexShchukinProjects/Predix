<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApprovalSheet extends Model
{
    use HasFactory;

    protected $table = 'doc_document_approval_sheet';

    protected $fillable = [
        'approval_id',
        'approver_id',
        'status',
        'started_at',
        'responded_at',
        'comment',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function approval()
    {
        return $this->belongsTo(DocumentApproval::class, 'approval_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
