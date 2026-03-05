<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TRLessonProgress extends Model
{
    use HasFactory;

    protected $table = 'tr_lesson_progress';

    protected $fillable = [
        'user_id',
        'course_lesson_id',
        'status',
        'viewed_slides',
        'test_score',
        'attempts',
        'viewed_at',
        'completed_at',
    ];

    protected $casts = [
        'viewed_slides' => 'array',
        'test_score' => 'integer',
        'attempts' => 'integer',
        'viewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }
}
