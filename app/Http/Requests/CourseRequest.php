<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->route('course')?->id ?? null;

        return [
            'code' => ['nullable', 'string', 'max:64', 'unique:courses,code,' . ($courseId ?? 'NULL') . ',id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_hours' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}


