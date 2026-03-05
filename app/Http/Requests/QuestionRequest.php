<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $questionId = $this->route('question')?->id ?? null;
        $type = $this->input('type');

        $rules = [
            'question_group_id' => ['nullable', 'exists:tr_question_groups,id'],
            'question_text' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // Максимум 5MB
            'remove_image' => ['nullable', 'boolean'],
            'type' => ['required', 'in:single,multiple,text'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1'],
        ];

        // Для текстовых вопросов варианты ответов не требуются
        if ($type !== 'text') {
            $rules['answers'] = ['required', 'array', 'min:2'];
            $rules['answers.*.answer_text'] = ['required', 'string'];
            $rules['answers.*.is_correct'] = ['sometimes', 'in:0,1,true,false'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $answers = $this->input('answers', []);

            if ($type !== 'text' && is_array($answers)) {
                $correctCount = 0;
                foreach ($answers as $answer) {
                    if (isset($answer['is_correct']) && ($answer['is_correct'] === '1' || $answer['is_correct'] === true || $answer['is_correct'] === 1)) {
                        $correctCount++;
                    }
                }

                if ($type === 'single' && $correctCount !== 1) {
                    $validator->errors()->add('answers', 'Для вопроса с одним правильным ответом должен быть выбран ровно один правильный ответ');
                } elseif ($type === 'multiple' && $correctCount < 1) {
                    $validator->errors()->add('answers', 'Для вопроса с несколькими правильными ответами должен быть выбран хотя бы один правильный ответ');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'question_text.required' => 'Текст вопроса обязателен для заполнения',
            'type.required' => 'Тип вопроса обязателен для выбора',
            'points.required' => 'Баллы обязательны для заполнения',
            'points.min' => 'Баллы должны быть не менее 1',
            'answers.required' => 'Необходимо добавить минимум 2 варианта ответа',
            'answers.min' => 'Необходимо добавить минимум 2 варианта ответа',
            'answers.*.answer_text.required' => 'Текст ответа обязателен для заполнения',
        ];
    }
}
