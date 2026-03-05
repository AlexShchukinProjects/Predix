<?php

namespace App\Http\Requests\Flights;

use Illuminate\Foundation\Http\FormRequest;

class FilterFlightsRequest extends FormRequest
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
            "aircraft" => "nullable|array",
            "aircraft.*" => "nullable|string",
            "flight_number" => "nullable|string",
            "departure_airport" => "nullable|string",
            "arrival_airport" => "nullable|string",
            "passenger_name" => "nullable|string",
            "departure_date_from" => "nullable|date",
            "departure_date_to" => "nullable|date",
            "sort" => "nullable|string|in:date_departure",
            "direction" => "nullable|string|in:asc,desc",
            "per_page" => "nullable|string|in:10,25,50,100",
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $departureDateFrom = $this->input('departure_date_from');
            $departureDateTo = $this->input('departure_date_to');
            
            // Проверяем, что если обе даты установлены, то дата "до" должна быть после или равна дате "с"
            if ($departureDateFrom && $departureDateTo && $departureDateTo < $departureDateFrom) {
                $validator->errors()->add('departure_date_to', 'Дата "до" должна быть после или равна дате "с"');
            }
        });
    }
}
