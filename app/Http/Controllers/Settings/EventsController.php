<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Flight;
use App\Models\EventsCrew;
use App\Models\Crew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventsController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return view('Settings.Events.index', compact('events'));
    }

    public function create()
    {
        return view('Settings.Events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:10',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'work_type' => 'boolean',
            'active' => 'boolean',
        ]);
        Event::create($request->only('name', 'abbreviation', 'color', 'work_type', 'active'));
        return redirect()->route('events.index')->with('success', 'Мероприятие добавлено');
    }

    public function edit(Event $event)
    {
        return view('Settings.Events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:10',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'work_type' => 'boolean',
            'active' => 'boolean',
        ]);
        $event->update($request->only('name', 'abbreviation', 'color', 'work_type', 'active'));
        return redirect()->route('events.index')->with('success', 'Мероприятие обновлено');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Мероприятие удалено');
    }

    public function getActiveEvents()
    {
        $events = Event::where('active', true)->get(['id', 'name', 'abbreviation', 'color']);
        return response()->json(['success' => true, 'events' => $events]);
    }

    // Создание записи мероприятия как записи в таблице flights (activity_type = crew_events)
    public function storeCrewEvent(Request $request)
    {
        $validated = $request->validate([
            'crew_id' => 'required|integer',
            'event_id' => 'required|integer',
            'event_name' => 'required|string|max:255',
            'date_start' => 'required|date',
            'time_start' => 'required|date_format:H:i',
            'date_end' => 'required|date|after_or_equal:date_start',
            'time_end' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:2000',
            'force_save' => 'sometimes|boolean',
        ]);

        // Проверяем конфликты, если не установлен force_save
        if (!($validated['force_save'] ?? false)) {
            $conflicts = $this->checkConflicts(
                [$validated['crew_id']],
                $validated['date_start'],
                $validated['time_start'],
                $validated['date_end'],
                $validated['time_end']
            );

            if (!empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'has_conflicts' => true,
                    'conflicts' => $conflicts,
                    'message' => 'Обнаружены пересечения с существующими мероприятиями или рейсами'
                ]);
            }
        }

        // Получаем информацию о мероприятии для цвета
        $event = Event::find($validated['event_id']);
        $eventColor = $event ? $event->color : '#007bff';
        
        // Создаем запись в таблице flights
        $flight = Flight::create([
            'activity_type'   => 'crew_events',
            'flight_type'     => $validated['event_name'],
            'date_departure'  => $validated['date_start'],
            'time_departure'  => $validated['time_start'],
            'date_arrival'    => $validated['date_end'],
            'time_arrival'    => $validated['time_end'],
            'notes'           => $validated['notes'] ?? null,
        ]);

        // Создаем запись в таблице events_crew для связи мероприятия с сотрудником
        EventsCrew::create([
            'flight_id' => $flight->id,
            'crew_id'   => $validated['crew_id'],
            'event_id'  => $validated['event_id'],
        ]);

        return response()->json([
            'success' => true,
            'flight_id' => $flight->id,
        ]);
    }

    // Обновление записи мероприятия
    public function updateCrewEvent(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:flights,id',
            'crew_id' => 'nullable|integer',
            'event_id' => 'nullable|integer',
            'event_name' => 'nullable|string|max:255',
            'date_start' => 'required|date',
            'time_start' => 'required|date_format:H:i',
            'date_end' => 'required|date|after_or_equal:date_start',
            'time_end' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:2000',
            'force_save' => 'sometimes|boolean',
        ]);

        // Находим запись в таблице flights
        $flight = Flight::findOrFail($validated['id']);
        
        // Проверяем, что это действительно мероприятие
        if ($flight->activity_type !== 'crew_events') {
            return response()->json([
                'success' => false,
                'message' => 'Запись не является мероприятием',
            ], 400);
        }

        // Получаем crew_id из связанной записи events_crew
        $eventsCrew = EventsCrew::where('flight_id', $flight->id)->first();
        $crewId = $validated['crew_id'] ?? ($eventsCrew ? $eventsCrew->crew_id : null);

        // Проверяем конфликты, если не установлен force_save
        if (!($validated['force_save'] ?? false) && $crewId) {
            $conflicts = $this->checkConflicts(
                [$crewId],
                $validated['date_start'],
                $validated['time_start'],
                $validated['date_end'],
                $validated['time_end'],
                $validated['id'] // Исключаем текущее мероприятие из проверки
            );

            if (!empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'has_conflicts' => true,
                    'conflicts' => $conflicts,
                    'message' => 'Обнаружены пересечения с существующими мероприятиями или рейсами'
                ]);
            }
        }
        
        // Обновляем запись
        $flight->update([
            'flight_type'     => $validated['event_name'] ?? $flight->flight_type,
            'date_departure'  => $validated['date_start'],
            'time_departure'  => $validated['time_start'],
            'date_arrival'    => $validated['date_end'],
            'time_arrival'    => $validated['time_end'],
            'notes'           => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'flight_id' => $flight->id,
        ]);
    }

    // Удаление записи мероприятия
    public function deleteCrewEvent(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:flights,id',
        ]);

        // Находим запись в таблице flights
        $flight = Flight::findOrFail($validated['id']);
        
        // Проверяем, что это действительно мероприятие
        if ($flight->activity_type !== 'crew_events') {
            return response()->json([
                'success' => false,
                'message' => 'Запись не является мероприятием',
            ], 400);
        }
        
        // Удаляем связанные записи в events_crew
        EventsCrew::where('flight_id', $flight->id)->delete();
        
        // Удаляем запись
        $flight->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Проверка конфликтов мероприятий и рейсов для указанных членов экипажа
     * 
     * @param array $crewMemberIds ID членов экипажа
     * @param string $dateStart Дата начала
     * @param string $timeStart Время начала
     * @param string $dateEnd Дата окончания
     * @param string $timeEnd Время окончания
     * @param int|null $excludeEventId ID мероприятия, которое нужно исключить из проверки (для редактирования)
     * @return array Массив конфликтов
     */
    private function checkConflicts($crewMemberIds, $dateStart, $timeStart, $dateEnd, $timeEnd, $excludeEventId = null)
    {
        $conflicts = [];
        
        // Формируем даты и времена для проверки пересечений
        $newStart = $dateStart . ' ' . $timeStart;
        $newEnd = $dateEnd . ' ' . $timeEnd;

        foreach ($crewMemberIds as $crewId) {
            $crew = Crew::find($crewId);
            if (!$crew) continue;

            // Проверяем пересечения с другими мероприятиями (crew_events)
            $existingEvents = Flight::where('activity_type', 'crew_events')
                ->whereHas('eventsCrew', function($q) use ($crewId) {
                    $q->where('crew_id', $crewId);
                })
                ->where(function($query) use ($newStart, $newEnd) {
                    $query->where(function($q) use ($newStart, $newEnd) {
                        // Существующее мероприятие начинается в период нового
                        $q->whereRaw("CONCAT(date_departure, ' ', time_departure) >= ?", [$newStart])
                          ->whereRaw("CONCAT(date_departure, ' ', time_departure) < ?", [$newEnd]);
                    })->orWhere(function($q) use ($newStart, $newEnd) {
                        // Существующее мероприятие заканчивается в период нового
                        $q->whereRaw("CONCAT(date_arrival, ' ', time_arrival) > ?", [$newStart])
                          ->whereRaw("CONCAT(date_arrival, ' ', time_arrival) <= ?", [$newEnd]);
                    })->orWhere(function($q) use ($newStart, $newEnd) {
                        // Существующее мероприятие полностью покрывает новое
                        $q->whereRaw("CONCAT(date_departure, ' ', time_departure) <= ?", [$newStart])
                          ->whereRaw("CONCAT(date_arrival, ' ', time_arrival) >= ?", [$newEnd]);
                    });
                })
                ->when($excludeEventId, function($query) use ($excludeEventId) {
                    return $query->where('id', '!=', $excludeEventId);
                })
                ->with(['eventsCrew.event'])
                ->get();

            foreach ($existingEvents as $event) {
                $eventType = $event->eventsCrew->first()?->event;
                $conflicts[] = [
                    'crew_id' => $crewId,
                    'crew_name' => $crew->ShortName,
                    'type' => 'Мероприятие',
                    'event_type' => $eventType ? $eventType->name : $event->flight_type,
                    'start_date' => $event->date_departure,
                    'start_time' => $event->time_departure,
                    'end_date' => $event->date_arrival,
                    'end_time' => $event->time_arrival,
                ];
            }

            // Проверяем пересечения с рейсами
            $existingFlights = Flight::where('activity_type', '!=', 'crew_events')
                ->whereHas('crews', function($q) use ($crewId) {
                    $q->where('crews.id', $crewId);
                })
                ->where(function($query) use ($newStart, $newEnd) {
                    $query->where(function($q) use ($newStart, $newEnd) {
                        // Рейс начинается в период нового мероприятия
                        $q->whereRaw("CONCAT(date_departure, ' ', time_departure) >= ?", [$newStart])
                          ->whereRaw("CONCAT(date_departure, ' ', time_departure) < ?", [$newEnd]);
                    })->orWhere(function($q) use ($newStart, $newEnd) {
                        // Рейс заканчивается в период нового мероприятия
                        $q->whereRaw("CONCAT(date_arrival, ' ', time_arrival) > ?", [$newStart])
                          ->whereRaw("CONCAT(date_arrival, ' ', time_arrival) <= ?", [$newEnd]);
                    })->orWhere(function($q) use ($newStart, $newEnd) {
                        // Рейс полностью покрывает новое мероприятие
                        $q->whereRaw("CONCAT(date_departure, ' ', time_departure) <= ?", [$newStart])
                          ->whereRaw("CONCAT(date_arrival, ' ', time_arrival) >= ?", [$newEnd]);
                    });
                })
                ->get();

            foreach ($existingFlights as $flight) {
                $conflicts[] = [
                    'crew_id' => $crewId,
                    'crew_name' => $crew->ShortName,
                    'type' => 'Рейс',
                    'event_type' => $flight->flight_number ?: $flight->flight_type,
                    'start_date' => $flight->date_departure,
                    'start_time' => $flight->time_departure,
                    'end_date' => $flight->date_arrival,
                    'end_time' => $flight->time_arrival,
                ];
            }
        }

        return $conflicts;
    }
} 