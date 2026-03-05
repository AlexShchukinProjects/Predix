<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SpiFlightDataWeekly extends Model
{
    protected $table = 'spi_flight_data_weekly';

    protected $fillable = [
        'aircraft_type_id',
        'year',
        'week_number',
        'week_start_date',
        'week_end_date',
        'flights_count',
        'flight_hours',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
    ];

    public function aircraftType()
    {
        return $this->belongsTo(AircraftsType::class, 'aircraft_type_id');
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function getFormattedFlightHoursAttribute(): string
    {
        $raw = $this->getAttribute('flight_hours');
        if (!$raw) {
            return '00:00';
        }
        if (is_string($raw) && preg_match('/^\d+:\d{2}(?::\d{2})?$/', $raw)) {
            $parts = explode(':', $raw);
            $hours = (int) ($parts[0] ?? 0);
            $minutes = (int) ($parts[1] ?? 0);
            return sprintf('%d:%02d', $hours, $minutes);
        }
        if ($raw instanceof \DateTimeInterface) {
            return $raw->format('H:i');
        }
        return '00:00';
    }

    /**
     * Получить данные по неделям за год для всех типов ВС
     */
    public static function getWeeklyDataForYear(int $year): array
    {
        $aircraftTypes = AircraftsType::where('active', true)->orderBy('icao')->get();
        $weeks = self::getWeeksInYear($year);
        $data = [];

        foreach ($weeks as $week) {
            $weekData = [
                'week_number' => $week['week_number'],
                'start_date' => $week['start_date'],
                'end_date' => $week['end_date'],
                'label' => $week['label'],
                'aircraft_types' => [],
            ];

            foreach ($aircraftTypes as $aircraftType) {
                $flightData = self::where('aircraft_type_id', $aircraftType->id)
                    ->where('year', $year)
                    ->where('week_number', $week['week_number'])
                    ->first();

                $weekData['aircraft_types'][] = [
                    'aircraft_type' => $aircraftType,
                    'flights_count' => $flightData ? $flightData->flights_count : 0,
                    'flight_hours' => $flightData ? $flightData->formatted_flight_hours : '00:00',
                ];
            }

            $data[] = $weekData;
        }

        return $data;
    }

    /**
     * Получить все ISO-недели в году
     */
    public static function getWeeksInYear(int $year): array
    {
        $weeks = [];
        $weeksInYear = (int) Carbon::create($year, 12, 28)->isoWeeksInYear();

        for ($w = 1; $w <= $weeksInYear; $w++) {
            $weekStart = Carbon::create()->setISODate($year, $w)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::MONDAY);

            $weeks[] = [
                'week_number' => $w,
                'start_date' => $weekStart->format('d.m.Y'),
                'end_date' => $weekEnd->format('d.m.Y'),
                'label' => 'Неделя ' . $w . ' (' . $weekStart->format('d.m') . ' – ' . $weekEnd->format('d.m') . ')',
                'week_start_obj' => $weekStart,
                'week_end_obj' => $weekEnd,
            ];
        }

        return $weeks;
    }
}
