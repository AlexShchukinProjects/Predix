<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\StubRelModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelFailureAttachment extends Model
{
    use StubRelModel;

    protected $table = 'rel_stub';

    protected $fillable = [
        'failure_id',
        'path',
        'original_name',
        'size',
        'mime_type',
        'sort_order',
    ];

    public function failure(): BelongsTo
    {
        return $this->belongsTo(ReliabilityFailure::class, 'failure_id');
    }
}
