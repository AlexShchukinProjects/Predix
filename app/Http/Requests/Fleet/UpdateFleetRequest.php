<?php

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFleetRequest extends FormRequest
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
            "RegN" => "required",
            "Owner" => "nullable|string|max:255",
            "FactoryNumber" => "nullable|string|max:255",
            "Type"=> "required|string|max:255",
            "type_code" => "nullable|string|max:50",
            "modification_code" => "nullable|string|max:50",
            "Class"=> "nullable|string|max:255",
            "Pax_number"=> "nullable|integer|min:0",
            "Airport_base"=> "nullable|string|max:255",
            "Date_manufacture"=> "nullable|date",
            "Repair" => "nullable|string",
            "Description" => "nullable|string",
            "Height" => "nullable|numeric|min:0",
            "Length" => "nullable|numeric|min:0",
            "Wing" => "nullable|numeric|min:0",
            "Cruise_speed" => "nullable|integer|min:0",
            "Range" => "nullable|integer|min:0",
            "MWM" => "nullable|numeric|min:0",
        ];
    }


    public function messages(): array
    {
        return [
            "RegN.required" => "Регистрационный номер обязателен",
            "Type.required" => "Тип обязателен",
            "Pax_number.integer" => "Количество пассажиров должно быть числом",
            "Pax_number.min" => "Количество пассажиров не может быть отрицательным",
            "Date_manufacture.date" => "Неверный формат даты",
            "Height.numeric" => "Высота должна быть числом",
            "Length.numeric" => "Длина должна быть числом",
            "Wing.numeric" => "Размах крыла должен быть числом",
            "Cruise_speed.integer" => "Крейсерская скорость должна быть числом",
            "Range.integer" => "Дальность должна быть числом",
            "MWM.numeric" => "MWM должно быть числом",
        ];
    }


}
