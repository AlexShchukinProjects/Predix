<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $positions = Position::orderBy('sort_order')->orderBy('Name')->get();
        return view('Settings.Position.index', compact('positions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Settings.Position.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'crew_type' => 'nullable|string|in:Летный экипаж,Кабинный экипаж,ИТП',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ]);

        $position = Position::create([
            'Name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'crew_type' => $validated['crew_type'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'Active' => $request->boolean('active')
        ]);

        return redirect()->route('Position.index')
            ->with('success', 'Должность успешно создана');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $position = Position::findOrFail($id);
        return view('Settings.Position.show', compact('position'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $position = Position::findOrFail($id);
        return view('Settings.Position.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
            'crew_type' => 'nullable|string|in:Летный экипаж,Кабинный экипаж,ИТП',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ]);

        $position = Position::findOrFail($id);
        $position->update([
            'Name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'crew_type' => $validated['crew_type'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'Active' => $request->boolean('active')
        ]);

        return redirect()->route('Position.index')
            ->with('success', 'Должность успешно обновлена');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $position = Position::findOrFail($id);
        $position->delete();

        return redirect()->route('Position.index')
            ->with('success', 'Должность успешно удалена');
    }

    public function getActivePositions()
    {
        $positions = Position::where('Active', true)
            ->orderBy('sort_order')
            ->orderBy('Name')
            ->get(['id', 'Name', 'short_name', 'crew_type', 'sort_order']);
        
        return response()->json([
            'success' => true,
            'data' => $positions
        ]);
    }
}
