<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceType;
use Illuminate\Http\Request;

class MaintenanceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $maintenanceTypes = MaintenanceType::orderBy('name')->paginate(20);
        return view('Settings.maintenance-types.index', compact('maintenanceTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Settings.maintenance-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_types,name',
            'active' => 'boolean'
        ]);

        MaintenanceType::create($validated);

        return redirect()->route('maintenance-types.index')
            ->with('success', 'Тип технического обслуживания успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(MaintenanceType $maintenanceType)
    {
        return view('Settings.maintenance-types.show', compact('maintenanceType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MaintenanceType $maintenanceType)
    {
        return view('Settings.maintenance-types.edit', compact('maintenanceType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaintenanceType $maintenanceType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_types,name,' . $maintenanceType->id,
            'active' => 'boolean'
        ]);

        $maintenanceType->update($validated);

        return redirect()->route('maintenance-types.index')
            ->with('success', 'Тип технического обслуживания успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceType $maintenanceType)
    {
        $maintenanceType->delete();

        return redirect()->route('maintenance-types.index')
            ->with('success', 'Тип технического обслуживания успешно удален');
    }
}
