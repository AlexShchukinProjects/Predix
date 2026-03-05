<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TRCourseAssignment extends Model
{
    use HasFactory;

    protected $table = 'tr_course_assignments';

    protected $fillable = [
        'course_id',
        'user_id',
        'planned_start_at',
        'planned_finish_at',
        'actual_finish_at',
        'progress_percent',
        'status',
        'test_score',
        'attempts',
    ];

    protected $casts = [
        'planned_start_at' => 'date',
        'planned_finish_at' => 'date',
        'actual_finish_at' => 'date',
        'progress_percent' => 'integer',
        'test_score' => 'integer',
        'attempts' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


