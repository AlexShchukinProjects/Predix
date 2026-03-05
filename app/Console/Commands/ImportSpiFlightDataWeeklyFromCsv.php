<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AircraftsType;
use App\Models\SpiFlightDataWeekly;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportSpiFlightDataWeeklyFromCsv extends Command
{
    protected $signature = 'spi:import-flight-data-weekly
                            {--dry-run : Тестовый запуск без сохранения в БД}
                            {--flights= : Путь к CSV полетов (по умолчанию storage/excel/indicators_flights.csv)}
                            {--types= : Путь к CSV типов ВС (по умолчанию storage/excel/aircraft_types.csv)}';

    protected $description = 'Импорт еженедельных данных по полетам и налету из CSV (indicators_flights + aircraft_types) в spi_flight_data_weekly';

    /**
     * Фиксированный маппинг: старый id типа ВС (из старой БД) => текущий id (aircrafts_types).
     */
    private const OLD_TO_CURRENT_AIRCRAFT_TYPE_IDS = [
        1 => 2957,
        2 => 3044,
        3 => 3080,
        342 => 2953,
        362 => 3089,
        402 => 2929,
    ];

    private array $oldIdToOurId = [];

    public function handle(): int
    {
        $flightsPath = $this->option('flights') ?: storage_path('excel/indicators_flights.csv');
        $typesPath = $this->option('types') ?: storage_path('excel/aircraft_types.csv');
        $dryRun = $this->option('dry-run');

        if (!is_file($flightsPath)) {
            $this->error("Файл не найден: {$flightsPath}");
            return 1;
        }
        if (!is_file($typesPath)) {
            $this->error("Файл не найден: {$typesPath}");
            return 1;
        }

        $this->info('Построение маппинга старый aircraft_type_id → текущий id...');
        $this->buildAircraftTypeMapping($typesPath);
        if (empty($this->oldIdToOurId)) {
            $this->warn('Не найдено ни одного сопоставления типов ВС.');
        } else {
            $this->info('Сопоставлено типов ВС: ' . count($this->oldIdToOurId));
        }

        $rows = $this->readCsv($flightsPath);
        $this->info('Загружено строк из indicators_flights: ' . count($rows));

        // Агрегация: (year, week_number, aircraft_type_id) => [flights => sum, minutes => sum]
        $aggregated = [];
        $skippedUnknownType = 0;

        foreach ($rows as $row) {
            $dt = isset($row['dt']) ? trim((string) $row['dt'], " \t\"'") : null;
            $oldAircraftTypeId = isset($row['aircraft_type_id']) ? (int) trim((string) $row['aircraft_type_id'], " \t\"'") : null;
            $qty = isset($row['qty']) ? (int) $row['qty'] : 0;
            $qty2Raw = isset($row['qty2']) ? trim((string) $row['qty2'], " \t\"'") : '0';
            $qty2 = (int) $qty2Raw;

            if (!$dt || $oldAircraftTypeId === null) {
                continue;
            }

            $ourAircraftTypeId = $this->oldIdToOurId[$oldAircraftTypeId] ?? null;
            if ($ourAircraftTypeId === null) {
                $skippedUnknownType++;
                continue;
            }

            try {
                $date = Carbon::parse($dt);
            } catch (\Throwable) {
                continue;
            }

            $year = (int) $date->isoFormat('GGGG');
            $weekNumber = (int) $date->isoWeek();

            $key = "{$year}|{$weekNumber}|{$ourAircraftTypeId}";
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'year' => $year,
                    'week_number' => $weekNumber,
                    'aircraft_type_id' => $ourAircraftTypeId,
                    'flights_count' => 0,
                    'minutes' => 0,
                ];
            }
            $aggregated[$key]['flights_count'] += $qty;
            $aggregated[$key]['minutes'] += max(0, $qty2);
        }

        if ($skippedUnknownType > 0) {
            $this->warn("Пропущено записей из-за неизвестного типа ВС: {$skippedUnknownType}");
        }

        $this->info('Уникальных комбинаций (год, неделя, тип ВС) после агрегации: ' . count($aggregated));

        if ($dryRun) {
            $this->warn('Режим dry-run: данные не сохраняются.');
            $sample = array_slice($aggregated, 0, 5, true);
            foreach ($sample as $k => $v) {
                $hh = (int) floor($v['minutes'] / 60);
                $mm = $v['minutes'] % 60;
                $this->line("  {$k} => полеты {$v['flights_count']}, налет " . sprintf('%d:%02d', $hh, $mm));
            }
            return 0;
        }

        $saved = 0;
        foreach ($aggregated as $record) {
            $year = $record['year'];
            $weekNumber = $record['week_number'];
            $aircraftTypeId = $record['aircraft_type_id'];
            $flightsCount = $record['flights_count'];
            $minutes = $record['minutes'];

            $hours = (int) floor($minutes / 60);
            $mins = $minutes % 60;
            $flightHours = sprintf('%d:%02d:00', $hours, $mins);

            $weekStart = Carbon::create()->setISODate($year, $weekNumber)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::MONDAY);

            SpiFlightDataWeekly::updateOrCreate(
                [
                    'aircraft_type_id' => $aircraftTypeId,
                    'year' => $year,
                    'week_number' => $weekNumber,
                ],
                [
                    'week_start_date' => $weekStart->format('Y-m-d'),
                    'week_end_date' => $weekEnd->format('Y-m-d'),
                    'flights_count' => $flightsCount,
                    'flight_hours' => $flightHours,
                ]
            );
            $saved++;
        }

        $this->info("Сохранено записей в spi_flight_data_weekly: {$saved}");
        return 0;
    }

    private function buildAircraftTypeMapping(string $typesPath): void
    {
        $this->oldIdToOurId = self::OLD_TO_CURRENT_AIRCRAFT_TYPE_IDS;

        $rows = $this->readCsv($typesPath);
        $oldIdToTitle = [];
        foreach ($rows as $row) {
            $id = isset($row['aircraft_type_id']) ? (int) trim($row['aircraft_type_id'], '" ') : null;
            $title = isset($row['aircraft_type_title']) ? trim($row['aircraft_type_title'], '" ') : '';
            if ($id !== null && $title !== '' && !isset($this->oldIdToOurId[$id])) {
                $oldIdToTitle[$id] = $title;
            }
        }

        if (empty($oldIdToTitle)) {
            return;
        }

        $ourTypes = AircraftsType::all(['id', 'name_rus', 'icao']);
        foreach ($oldIdToTitle as $oldId => $title) {
            $our = $ourTypes->first(function ($t) use ($oldId, $title) {
                if ((int) $t->id === $oldId) {
                    return true;
                }
                $nameRus = $t->name_rus ?? '';
                $icao = $t->icao ?? '';
                return $nameRus === $title || $icao === $title;
            });
            if ($our) {
                $this->oldIdToOurId[$oldId] = (int) $our->id;
            }
        }
    }

    /**
     * @return list<array<string, string>>
     */
    private function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return [];
        }
        $header = fgetcsv($handle, 0, ',');
        if ($header === false) {
            fclose($handle);
            return [];
        }
        $header = array_map(function ($c) {
            return strtolower(trim(str_replace('"', '', $c)));
        }, $header);
        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $line[$i] ?? '';
            }
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }
}
