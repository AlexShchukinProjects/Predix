<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutiveDisciplineMeasureHistory extends Model
{
    public $timestamps = false;

    protected $table = 'executive_discipline_measure_history';

    protected $fillable = [
        'measure_id',
        'user_id',
        'event',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function measure(): BelongsTo
    {
        return $this->belongsTo(ExecutiveDisciplineMeasure::class, 'measure_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
