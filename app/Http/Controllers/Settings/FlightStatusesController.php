<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\FlightStatus;
use Illuminate\Http\Request;

class FlightStatusesController extends Controller
{
    public function index()
    {
        $statuses = FlightStatus::whereNotIn('name', ['Взлетел', 'Тех обслуживание', 'Фактическое время'])->orderBy('name')->paginate(20);
        $takenOffStatus = FlightStatus::where('name', 'Взлетел')->first();
        $takenOffColor = $takenOffStatus ? ($takenOffStatus->color ?? '#4CAF50') : '#4CAF50';
        $maintenanceStatus = FlightStatus::where('name', 'Тех обслуживание')->first();
        $maintenanceColor = $maintenanceStatus ? ($maintenanceStatus->color ?? '#FF9800') : '#FF9800';
        $actualTimeStatus = FlightStatus::where('name', 'Фактическое время')->first();
        $actualTimeColor = $actualTimeStatus ? ($actualTimeStatus->color ?? '#2196F3') : '#2196F3';
        return view('Settings.FlightStatuses.index', compact('statuses', 'takenOffColor', 'maintenanceColor', 'actualTimeColor'));
    }

    public function create()
    {
        return view('Settings.FlightStatuses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'color' => ['nullable','string','regex:/^#[0-9A-Fa-f]{6}$/'],
            'active' => ['nullable','boolean'],
        ]);

        // Запрещаем создавать статус с именем "Взлетел", "Тех обслуживание" или "Фактическое время" через обычную форму
        if (in_array($validated['name'], ['Взлетел', 'Тех обслуживание', 'Фактическое время'])) {
            return redirect()->route('settings.flight-statuses.index')->with('error', 'Статус "' . $validated['name'] . '" используется для специальной настройки цвета');
        }

        $validated['active'] = (bool)($validated['active'] ?? false);
        FlightStatus::create($validated);
        return redirect()->route('settings.flight-statuses.index')->with('success', 'Статус создан');
    }

    public function edit(FlightStatus $flightStatus)
    {
        return view('Settings.FlightStatuses.edit', compact('flightStatus'));
    }

    public function update(Request $request, FlightStatus $flightStatus)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'color' => ['nullable','string','regex:/^#[0-9A-Fa-f]{6}$/'],
            'active' => ['nullable','boolean'],
        ]);

        // Запрещаем переименовывать статусы в "Взлетел", "Тех обслуживание" или "Фактическое время"
        if (in_array($validated['name'], ['Взлетел', 'Тех обслуживание', 'Фактическое время'])) {
            return redirect()->route('settings.flight-statuses.index')->with('error', 'Статус "' . $validated['name'] . '" используется для специальной настройки цвета');
        }

        $validated['active'] = (bool)($validated['active'] ?? false);
        $flightStatus->update($validated);
        return redirect()->route('settings.flight-statuses.index')->with('success', 'Статус обновлен');
    }

    public function destroy(FlightStatus $flightStatus)
    {
        $flightStatus->delete();
        return redirect()->route('settings.flight-statuses.index')->with('success', 'Статус успешно удален');
    }

    public function updateSpecialColor(Request $request)
    {
        $validated = $request->validate([
            'status_name' => ['required','string','in:Взлетел,Тех обслуживание,Фактическое время'],
            'color' => ['required','string','regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        // Находим или создаем запись специального статуса
        $status = FlightStatus::firstOrNew(['name' => $validated['status_name']]);
        $status->color = $validated['color'];
        $status->active = false; // Не показываем в списке обычных статусов
        $status->save();

        return redirect()->route('settings.flight-statuses.index')->with('success', 'Цвет "' . $validated['status_name'] . '" обновлен');
    }
}


