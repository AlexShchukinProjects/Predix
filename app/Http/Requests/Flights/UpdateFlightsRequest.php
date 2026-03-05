<?php

namespace App\Http\Requests\Flights;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlightsRequest extends FormRequest
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
            "aircraft" => "required",
            "flight_number" => "required",
            // UI still sends ICAO, but controller maps to *_airport_id
            "departure_airport" => "required|string",
            "arrival_airport" => "required|string",
            "date_departure" => "required",
            "date_arrival" => "required",
        ];
    }
    public function messages(): array

    {

        return [
            "aircraft.required" => "Обязательное поле",
            "flight_number.required" => "Обязательное поле",
            "departure_airport.required" => "Обязательное поле",
            "date_departure.required" => "Обязательное поле",
            "arrival_airport.required" => "Обязательное поле",
            "date_arrival.required" => "Обязательное поле",
        ];

    }


}
