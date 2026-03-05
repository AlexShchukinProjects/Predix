<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TRLessonFile extends Model
{
    use HasFactory;

    protected $table = 'tr_lesson_files';

    protected $fillable = [
        'course_lesson_id',
        'type',
        'path',
        'original_name',
        'mime',
        'size',
        'sort_order',
    ];

    protected $casts = [
        'course_lesson_id' => 'integer',
        'size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }
}

