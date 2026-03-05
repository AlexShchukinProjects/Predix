<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApprover extends Model
{
    use HasFactory;

    protected $table = 'doc_document_approvers';

    protected $fillable = [
        'approval_id',
        'user_id',
        'order',
    ];

    public function approval()
    {
        return $this->belongsTo(DocumentApproval::class, 'approval_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
