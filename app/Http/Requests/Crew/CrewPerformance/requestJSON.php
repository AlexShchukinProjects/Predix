<?php

namespace App\Http\Requests\Crew\CrewPerformance;

use Illuminate\Foundation\Http\FormRequest;

class requestJSON extends FormRequest
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

            "Name" => "nullable",





        ];
    }


public function messages(): array

{

    return [
        "Name" => "Обязательное поле",
    ];

}


}
