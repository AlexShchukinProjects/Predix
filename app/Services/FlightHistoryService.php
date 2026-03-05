<?php

namespace App\Services;

use App\Models\PlFlightChangesHistory;
use App\Models\Flight;
use Illuminate\Support\Facades\Auth;

class FlightHistoryService
{
    /**
     * Логирование создания рейса
     */
    public function logCreation(Flight $flight, array $data = []): void
    {
        $this->logChange($flight, 'created', null, $data, 'Рейс создан');
    }

    /**
     * Логирование обновления рейса
     */
    public function logUpdate(Flight $flight, array $oldData, array $newData): void
    {
        $changes = $this->getChanges($oldData, $newData);
        
        if (!empty($changes)) {
            $description = $this->formatChangesDescription($changes);
            $this->logChange($flight, 'updated', $oldData, $newData, $description);
        }
    }

    /**
     * Логирование удаления рейса
     */
    public function logDeletion(Flight $flight): void
    {
        $this->logChange($flight, 'deleted', $flight->toArray(), null, 'Рейс удален');
    }

    /**
     * Основной метод логирования
     */
    private function logChange(Flight $flight, string $action, ?array $oldData, ?array $newData, string $description): void
    {
        $changes = null;
        
        if ($action === 'updated' && $oldData && $newData) {
            $changes = $this->getChanges($oldData, $newData);
        } elseif ($action === 'created' && $newData) {
            $changes = $newData;
        } elseif ($action === 'deleted' && $oldData) {
            $changes = $oldData;
        }

        PlFlightChangesHistory::create([
            'flight_id' => $flight->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $changes,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Получить список изменений между старыми и новыми данными
     */
    private function getChanges(array $oldData, array $newData): array
    {
        $changes = [];
        
        // Исключаем поля, которые не нужно логировать
        $excludedFields = ['updated_at', 'created_at'];
        
        foreach ($newData as $key => $newValue) {
            if (in_array($key, $excludedFields)) {
                continue;
            }
            
            $oldValue = $oldData[$key] ?? null;
            
            // Сравниваем значения
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }
        
        return $changes;
    }

    /**
     * Форматировать описание изменений
     */
    private function formatChangesDescription(array $changes): string
    {
        $descriptions = [];
        
        $fieldNames = [
            'flight_number' => 'Номер рейса',
            'status' => 'Статус',
            'flight_type' => 'Тип рейса',
            'service_code' => 'Сервисный код',
            'customer' => 'Оператор',
            'departure_airport_id' => 'Аэропорт вылета',
            'arrival_airport_id' => 'Аэропорт прилета',
            'date_departure' => 'Дата вылета',
            'time_departure' => 'Время вылета',
            'date_arrival' => 'Дата прилета',
            'time_arrival' => 'Время прилета',
            'block_time' => 'Время полета',
            'terminaldep' => 'Терминал вылета',
            'parkingdep' => 'Стоянка вылета',
            'terminalarr' => 'Терминал прилета',
            'parkingarr' => 'Стоянка прилета',
            'passengers_count' => 'Количество пассажиров',
            'total_fuel' => 'ГСМ',
            'notes' => 'Примечания',
        ];
        
        foreach ($changes as $field => $change) {
            $fieldName = $fieldNames[$field] ?? $field;
            $oldValue = $change['old'] ?? 'не указано';
            $newValue = $change['new'] ?? 'не указано';
            
            $descriptions[] = "{$fieldName}: {$oldValue} → {$newValue}";
        }
        
        return implode(', ', $descriptions);
    }

    /**
     * Получить историю изменений для рейса
     */
    public function getHistory(int $flightId)
    {
        return PlFlightChangesHistory::where('flight_id', $flightId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

