<?php

namespace App\Http\Requests\Flights;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlightsRequest extends FormRequest
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
            "departure_airport" => "required",
            "date_departure" => "required",
            "time_departure" => "required",
            "arrival_airport" => "required",
            "date_arrival" => "required",
            "time_arrival" => "required",

            "Captain" => "nullable|string",
            "FO" => "nullable|string",
            "AddCrew1" => "nullable|string",
            "AddCrew2" => "nullable|string",
            "SeniorFlightAttendant" => "nullable|string",
            "FlightAttendant1" => "nullable|string",
            "FlightAttendant2" => "nullable|string",
            "FlightAttendant3" => "nullable|string",


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
