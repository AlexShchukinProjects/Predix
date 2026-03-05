<?php

namespace App\Http\Requests\Crew;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlightDocRequest extends FormRequest
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
        return [

            "requirement_id" => "required|integer|exists:requirements,id",
            "crew_id" => "required|integer|exists:crews,id",
            "TypeAC" => "nullable|string",
            "Issued" => "nullable",
            "ActiveMonth" => "nullable",
            "ExpiryDate" => "nullable",
            "Document" => "nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png", // 10MB max
            "SerialNumber" => "nullable",
            "Organisation" => "nullable",
            "Note" => "nullable",


        ];
    }


public function messages(): array

{

    return [
        "requirement_id.required" => "Требование обязательно для выбора",
        "requirement_id.exists" => "Выбранное требование не существует",
        "Document.file" => "Документ должен быть файлом",
        "Document.max" => "Размер файла не должен превышать 10MB",
        "Document.mimes" => "Допустимые форматы файлов: PDF, DOC, DOCX, JPG, JPEG, PNG",
    ];

}


}
