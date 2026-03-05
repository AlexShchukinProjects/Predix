<?php

namespace App\Http\Requests\Crew;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlightDocRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
    /**
     * Get validation rules for ExpiryDate based on requirement validity period
     */
    protected function getExpiryDateRules(): string
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
        
        return $isUnlimited 
            ? "nullable|date|after_or_equal:Issued" 
            : "required|date|after_or_equal:Issued";
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            "requirement_id" => "required|integer|exists:requirements,id",
            "crew_id" => "required|integer|exists:crews,id",
            "TypeAC" => "nullable|string|max:255",
            "Issued" => "required|date",
            "ActiveMonth" => "nullable|integer|min:0",
            "ExpiryDate" => $this->getExpiryDateRules(),
            "Document" => "nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png", // 10MB max
            "SerialNumber" => "nullable|string|max:255",
            "Organisation" => "nullable|string|max:255",
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
        "Issued.required" => "Поле 'Действ. с' обязательно для заполнения",
        "Issued.date" => "Поле 'Действ. с' должно быть корректной датой",
        "ExpiryDate.required" => "Поле 'Действ. до' обязательно для заполнения",
        "ExpiryDate.date" => "Поле 'Действ. до' должно быть корректной датой",
        "ExpiryDate.after_or_equal" => "Поле 'Действ. до' должно быть не раньше даты начала действия",
        "Document.file" => "Документ должен быть файлом",
        "Document.max" => "Размер файла не должен превышать 10MB",
        "Document.mimes" => "Допустимые форматы файлов: PDF, DOC, DOCX, JPG, JPEG, PNG",
    ];

}


}
