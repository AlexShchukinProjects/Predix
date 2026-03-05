<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ReadinessType;
use Illuminate\Http\Request;

class ReadinessTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $readinessTypes = ReadinessType::all();
        return view('Settings.ReadinessType.index', compact('readinessTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Settings.ReadinessType.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean'
        ]);

        $readinessType = ReadinessType::create([
            'name' => $validated['name'],
            'active' => $request->boolean('active')
        ]);

        return redirect()->route('ReadinessType.index')
            ->with('success', 'Тип готовности успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $readinessType = ReadinessType::findOrFail($id);
        return view('Settings.ReadinessType.show', compact('readinessType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $readinessType = ReadinessType::findOrFail($id);
        return view('Settings.ReadinessType.edit', compact('readinessType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean'
        ]);

        $readinessType = ReadinessType::findOrFail($id);
        $readinessType->update([
            'name' => $validated['name'],
            'active' => $request->boolean('active')
        ]);

        return redirect()->route('ReadinessType.index')
            ->with('success', 'Тип готовности успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $readinessType = ReadinessType::findOrFail($id);
        $readinessType->delete();

        return redirect()->route('ReadinessType.index')
            ->with('success', 'Тип готовности успешно удален');
    }

    public function getActiveTypes()
    {
        $readinessTypes = ReadinessType::where('active', true)->get();
        
        return response()->json([
            'success' => true,
            'data' => $readinessTypes
        ]);
    }
}
