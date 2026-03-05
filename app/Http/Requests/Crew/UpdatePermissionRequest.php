<?php

namespace App\Http\Requests\Crew;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
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
        
        $validUntilRules = $isUnlimited 
            ? "nullable|date|after_or_equal:Issued" 
            : "required|date|after_or_equal:Issued";
        
        return [
            "requirement_id" => "required|integer|exists:requirements,id",
            "crew_id" => "required|integer|exists:crews,id",
            "TypeAC" => "nullable|string|max:255",
            "Issued" => "required|date",
            "ValidUntil" => $validUntilRules,
            "Organisation" => "nullable|string|max:255",
            "Document" => "nullable|string|max:255",
            "SerialNumber" => "nullable|string|max:255",
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
            "Issued.required" => "Поле 'Дата выдачи' обязательно для заполнения",
            "Issued.date" => "Поле 'Дата выдачи' должно быть корректной датой",
            "ValidUntil.required" => "Поле 'Действует до' обязательно для заполнения",
            "ValidUntil.date" => "Поле 'Действует до' должно быть корректной датой",
            "ValidUntil.after_or_equal" => "Поле 'Действует до' должно быть не раньше даты выдачи",
        ];
    }


}
