<?php

namespace App\Http\Requests\Crew;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCrewRequest extends FormRequest
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
            'SurName' => 'required|string',
            'FirstName' => 'required|string',
            'Position' => 'nullable|string|exists:positions,short_name',
            'position_id' => 'nullable|integer|exists:positions,id',
            'aircraft_types' => 'nullable|array',
            'aircraft_types.*' => 'integer|exists:aircrafts_types,id',
            'DateOfBirth' => 'required|string',
            'Address' => 'nullable|string',
            'Phone' => 'nullable|string',
            'email' => 'nullable|email',
            'MiddleName' => 'nullable|string',
            'ShortName' => 'nullable|string',
            'NameENG' => 'nullable|string',
            'TabNumber' => 'nullable|string|max:50',
            'PlaceOfBirth' => 'nullable|string|max:255',
            'Comment' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'SurName.required' => 'Обязательное поле',
            'FirstName.required' => 'Обязательное поле',
            'Position.exists' => 'Выберите корректную должность',
            'aircraft_types.array' => 'Типы ВС должны быть массивом',
            'aircraft_types.*.integer' => 'Тип ВС должен быть числом',
            'aircraft_types.*.exists' => 'Выбранный тип ВС не найден',
            'DateOfBirth.required' => 'Обязательное поле',
            'TabNumber.string' => 'Поле "Таб номер" должно быть текстом',
            'TabNumber.max' => 'Поле "Таб номер" не должно превышать 50 символов',
            'PlaceOfBirth.string' => 'Поле "Место рождения" должно быть текстом',
            'PlaceOfBirth.max' => 'Поле "Место рождения" не должно превышать 255 символов',
            'email.email' => 'Некорректный E-mail',
        ];
    }
}
