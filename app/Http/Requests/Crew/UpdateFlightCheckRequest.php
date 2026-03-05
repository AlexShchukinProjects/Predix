<?php

namespace App\Http\Requests\Crew;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlightCheckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Проверяем, является ли срок действия бессрочным
        $requirementId = $this->input('requirement_id');
        $isUnlimited = false;
        
        if ($requirementId) {
            $requirement = \App\Models\Requirement::find($requirementId);
            if ($requirement && $requirement->validity_period_months === 0) {
                $isUnlimited = true;
            }
        }
        
        $expiryDateRules = $isUnlimited 
            ? "nullable|date|after_or_equal:DateOfCheck" 
            : "required|date|after_or_equal:DateOfCheck";
        
        return [
            "requirement_id" => "required|integer|exists:requirements,id",
            "crew_id" => "required|integer|exists:crews,id",
            "TypeAC" => "nullable|string|max:255",
            "DateOfCheck" => "required|date",
            "ExpiryDate" => $expiryDateRules,
            "Organisation" => "nullable|string|max:255",
            "Document" => "nullable|string|max:255",
            "SerialNumber" => "nullable|string|max:255",
            "Task" => "nullable|string|max:255",
            "Paragraph" => "nullable|string|max:255",
            "Checker" => "nullable|string|max:255",
            "Grade" => "nullable|string|max:255",
            "Note" => "nullable|string",
        ];
    }

    public function messages(): array
    {
        return [
            "requirement_id.required" => "Поле 'Требование' обязательно для заполнения",
            "requirement_id.exists" => "Выбранное требование не существует",
            "crew_id.required" => "Поле 'Сотрудник' обязательно для заполнения",
            "crew_id.exists" => "Выбранный сотрудник не существует",
            "DateOfCheck.required" => "Поле 'Дата проверки' обязательно для заполнения",
            "DateOfCheck.date" => "Поле 'Дата проверки' должно быть корректной датой",
            "ExpiryDate.required" => "Поле 'Дата окончания' обязательно для заполнения",
            "ExpiryDate.date" => "Поле 'Дата окончания' должно быть корректной датой",
            "ExpiryDate.after_or_equal" => "Поле 'Дата окончания' должно быть не раньше даты проверки",
        ];
    }
}