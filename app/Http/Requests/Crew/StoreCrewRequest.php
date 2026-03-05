<?php

namespace App\Http\Requests\Crew;

use Illuminate\Foundation\Http\FormRequest;

class StoreCrewRequest extends FormRequest
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
            "SurName" => "required|string|max:255",
            "FirstName" => "required|string|max:255",
            "MiddleName" => "nullable|string|max:255",
            "ShortName" => "required|string|max:255",
            "Position" => "required|string|exists:positions,short_name",
            "position_id" => "nullable|integer|exists:positions,id",
            "TabNumber" => "nullable|string|max:50",
            "DateOfBirth" => "required|date",
            "PlaceOfBirth" => "nullable|string|max:255",
            "aircraft_types" => "nullable|array",
            "aircraft_types.*" => "integer|exists:aircrafts_types,id",
            "Phone" => "nullable|string|max:20",
            "email" => "nullable|email|max:255",
            "Address" => "nullable|string|max:500",
            "Comment" => "nullable|string|max:1000",
        ];
    }

    public function messages(): array
    {
        return [
            "SurName.required" => "Поле 'Фамилия' обязательно для заполнения",
            "SurName.string" => "Поле 'Фамилия' должно быть текстом",
            "SurName.max" => "Поле 'Фамилия' не должно превышать 255 символов",
            
            "FirstName.required" => "Поле 'Имя' обязательно для заполнения",
            "FirstName.string" => "Поле 'Имя' должно быть текстом",
            "FirstName.max" => "Поле 'Имя' не должно превышать 255 символов",
            
            "MiddleName.string" => "Поле 'Отчество' должно быть текстом",
            "MiddleName.max" => "Поле 'Отчество' не должно превышать 255 символов",
            
            "ShortName.required" => "Поле 'Краткое имя' обязательно для заполнения",
            "ShortName.string" => "Поле 'Краткое имя' должно быть текстом",
            "ShortName.max" => "Поле 'Краткое имя' не должно превышать 255 символов",
            
            "Position.required" => "Поле 'Должность' обязательно для заполнения",
            "Position.exists" => "Выберите корректную должность",
            "TabNumber.string" => "Поле 'Таб номер' должно быть текстом",
            "TabNumber.max" => "Поле 'Таб номер' не должно превышать 50 символов",
            "PlaceOfBirth.string" => "Поле 'Место рождения' должно быть текстом",
            "PlaceOfBirth.max" => "Поле 'Место рождения' не должно превышать 255 символов",
            "aircraft_types.array" => "Типы ВС должны быть массивом",
            "aircraft_types.*.integer" => "ID типа ВС должен быть числом",
            "aircraft_types.*.exists" => "Выбранный тип ВС не найден",
            
            "DateOfBirth.required" => "Поле 'Дата рождения' обязательно для заполнения",
            "DateOfBirth.date" => "Поле 'Дата рождения' должно быть корректной датой",
            
            "Phone.string" => "Поле 'Телефон' должно быть текстом",
            "Phone.max" => "Поле 'Телефон' не должно превышать 20 символов",
            
            "email.email" => "Поле 'E-mail' должно быть корректным email адресом",
            "email.max" => "Поле 'E-mail' не должно превышать 255 символов",
            
            "Address.string" => "Поле 'Адрес' должно быть текстом",
            "Address.max" => "Поле 'Адрес' не должно превышать 500 символов",
            
            "Comment.string" => "Поле 'Комментарий' должно быть текстом",
            "Comment.max" => "Поле 'Комментарий' не должно превышать 1000 символов",
        ];
    }


}
