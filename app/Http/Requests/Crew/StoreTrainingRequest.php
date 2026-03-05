<?php

namespace App\Http\Requests\Crew;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingRequest extends FormRequest
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
            "TypeAC" => "nullable",
            "Issued" => "nullable",
            "ActiveMonth" => "nullable",
            "ExpiryDate" => "nullable",
            "SerialNumber" => "nullable",
            "Document" => "nullable",
            "Organisation" => "nullable",
            "Note" => "nullable",
            "Grade" => "nullable",
            "Checker" => "nullable",





        ];
    }


public function messages(): array

{

    return [
        "requirement_id.required" => "Требование обязательно для выбора",
        "requirement_id.exists" => "Выбранное требование не существует",
    ];

}


}
