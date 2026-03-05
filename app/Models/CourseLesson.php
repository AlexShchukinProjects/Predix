<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLesson extends Model
{
    use HasFactory;

    protected $table = 'tr_course_lessons';

    protected $fillable = [
        'course_module_id',
        'type',
        'title',
        'description',
        'sort_order',
        'test_data',
    ];

    protected $casts = [
        'test_data' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'normal' => 'Презентация',
            'assignment' => 'Задание',
            'test' => 'Тест',
            'webinar' => 'Видео',
            'document' => 'Документ',
            default => $this->type,
        };
    }

    public function files(): HasMany
    {
        return $this->hasMany(TRLessonFile::class, 'course_lesson_id')->orderBy('sort_order');
    }
}

