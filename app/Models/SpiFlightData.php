<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SpiFlightData extends Model
{
    protected $table = 'spi_flight_data';
    
    protected $fillable = [
        'aircraft_type_id',
        'year',
        'month_number',
        'month_start_date',
        'month_end_date',
        'flights_count',
        'flight_hours'
    ];

    protected $casts = [
        'month_start_date' => 'date',
        'month_end_date' => 'date'
    ];

    /**
     * Связь с типом ВС
     */
    public function aircraftType()
    {
        return $this->belongsTo(AircraftsType::class, 'aircraft_type_id');
    }

    /**
     * Получить данные за определенный год
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Получить данные за определенный месяц
     */
    public function scopeForMonth($query, $monthNumber)
    {
        return $query->where('month_number', $monthNumber);
    }

    /**
     * Получить данные для определенного типа ВС
     */
    public function scopeForAircraftType($query, $aircraftTypeId)
    {
        return $query->where('aircraft_type_id', $aircraftTypeId);
    }

    /**
     * Получить форматированное время налета (HH:MM), поддерживает часы > 24
     */
    public function getFormattedFlightHoursAttribute()
    {
        $raw = $this->getAttribute('flight_hours');
        if (!$raw) {
            return '00:00';
        }
        if (preg_match('/^\d+:\d{2}(?::\d{2})?$/', $raw)) {
            $parts = explode(':', $raw);
            $hours = (int)($parts[0] ?? 0);
            $minutes = (int)($parts[1] ?? 0);
            return sprintf('%d:%02d', $hours, $minutes);
        }
        return substr($raw, 0, 5);
    }

    /**
     * Получить данные за месяцы для всех типов ВС
     */
    public static function getMonthlyDataForYear($year)
    {
        $aircraftTypes = AircraftsType::where('active', true)->orderBy('icao')->get();
        $months = self::getMonthsInYear($year);

        $data = [];

        foreach ($months as $month) {
            $monthData = [
                'month_number' => $month['month_number'],
                'start_date' => $month['start_date'],
                'end_date' => $month['end_date'],
                'label' => $month['label'],
                'aircraft_types' => []
            ];

            foreach ($aircraftTypes as $aircraftType) {
                $flightData = self::where('aircraft_type_id', $aircraftType->id)
                    ->where('year', $year)
                    ->where('month_number', $month['month_number'])
                    ->first();

                $monthData['aircraft_types'][] = [
                    'aircraft_type' => $aircraftType,
                    'flights_count' => $flightData ? $flightData->flights_count : 0,
                    'flight_hours' => $flightData ? $flightData->formatted_flight_hours : '00:00'
                ];
            }

            $data[] = $monthData;
        }

        return $data;
    }

    /**
     * Получить все месяцы в году
     */
    public static function getMonthsInYear($year)
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1)->startOfMonth();
            $end = Carbon::create($year, $m, 1)->endOfMonth();
            $months[] = [
                'month_number' => $m,
                'start_date' => $start->format('d.m.Y'),
                'end_date' => $end->format('d.m.Y'),
                'label' => $start->locale('ru')->isoFormat('MMMM'),
                'start_date_obj' => $start,
                'end_date_obj' => $end
            ];
        }
        return $months;
    }
}