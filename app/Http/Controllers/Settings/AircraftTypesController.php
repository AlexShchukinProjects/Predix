<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AircraftsType;
use Illuminate\Http\Request;

class AircraftTypesController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)($request->get('per_page', 15));
        $types = AircraftsType::orderBy('name_rus')
            ->paginate($perPage)
            ->appends($request->query());

        return view('Settings.Fleet.AircraftTypes.index', compact('types', 'perPage'));
    }

    public function create()
    {
        return view('Settings.Fleet.AircraftTypes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'iata' => 'nullable|string|max:10',
            'icao' => 'nullable|string|max:10',
            'rus' => 'nullable|string|max:50',
            'name_eng' => 'nullable|string|max:255',
            'name_rus' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:100',
            'crew1' => 'nullable|integer',
            'crew2' => 'nullable|integer',
            'country_manufacture' => 'nullable|string|max:100',
            'wingspan' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'helicopter' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['active'] = (bool)($validated['active'] ?? true);
        $validated['helicopter'] = (bool)($validated['helicopter'] ?? false);

        AircraftsType::create($validated);

        return redirect()->route('settings.fleet.aircraft-types.index')
            ->with('success', 'Тип ВС успешно создан');
    }

    public function edit(AircraftsType $aircraftType)
    {
        return view('Settings.Fleet.AircraftTypes.edit', compact('aircraftType'));
    }

    public function update(Request $request, AircraftsType $aircraftType)
    {
        $validated = $request->validate([
            'iata' => 'nullable|string|max:10',
            'icao' => 'nullable|string|max:10',
            'rus' => 'nullable|string|max:50',
            'name_eng' => 'nullable|string|max:255',
            'name_rus' => 'nullable|string|max:255',
            'group' => 'nullable|string|max:100',
            'crew1' => 'nullable|integer',
            'crew2' => 'nullable|integer',
            'country_manufacture' => 'nullable|string|max:100',
            'wingspan' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'helicopter' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['active'] = (bool)($validated['active'] ?? $aircraftType->active);
        $validated['helicopter'] = (bool)($validated['helicopter'] ?? $aircraftType->helicopter);

        $aircraftType->update($validated);

        return redirect()->route('settings.fleet.aircraft-types.index')
            ->with('success', 'Тип ВС обновлен');
    }

    public function destroy(AircraftsType $aircraftType)
    {
        $aircraftType->delete();
        return redirect()->route('settings.fleet.aircraft-types.index')
            ->with('success', 'Тип ВС удален');
    }
}


