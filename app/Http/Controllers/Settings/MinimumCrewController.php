<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AircraftsType;
use App\Models\Position;
use App\Models\MinimumCrew;
use App\Models\Aircraft;
use Illuminate\Http\Request;

class MinimumCrewController extends Controller
{
    public function index()
    {
        $aircraftTypes = AircraftsType::orderBy('icao')->get();
        $positions = Position::where('Active', true)
            ->orderBy('sort_order')
            ->orderBy('Name')
            ->get();
        
        // Получаем все записи minimum_crew и группируем по aircraft_type_id
        $minimumCrewData = MinimumCrew::with(['aircraftType', 'position'])->get();
        $crewMap = [];
        foreach ($minimumCrewData as $crew) {
            $crewMap[$crew->aircraft_type_id][$crew->position_id] = $crew->quantity;
        }
        
        return view('Settings.MinimumCrew.index', compact('aircraftTypes', 'positions', 'crewMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'aircraft_type_id' => 'required|exists:aircrafts_types,id',
            'position_id' => 'required|exists:positions,id',
            'quantity' => 'required|integer|min:0'
        ]);

        MinimumCrew::updateOrCreate(
            [
                'aircraft_type_id' => $request->aircraft_type_id,
                'position_id' => $request->position_id
            ],
            [
                'quantity' => $request->quantity
            ]
        );

        return response()->json(['success' => true, 'message' => 'Данные сохранены']);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.aircraft_type_id' => 'required|exists:aircrafts_types,id',
            'data.*.position_id' => 'required|exists:positions,id',
            'data.*.quantity' => 'required|integer|min:0'
        ]);

        foreach ($request->data as $item) {
            MinimumCrew::updateOrCreate(
                [
                    'aircraft_type_id' => $item['aircraft_type_id'],
                    'position_id' => $item['position_id']
                ],
                [
                    'quantity' => $item['quantity']
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Данные сохранены']);
    }

    public function getByAircraftType(Request $request)
    {
        $aircraftTypeId = $request->get('aircraft_type_id');
        
        if (!$aircraftTypeId) {
            return response()->json(['success' => false, 'message' => 'aircraft_type_id is required']);
        }

        $minimumCrew = MinimumCrew::where('aircraft_type_id', $aircraftTypeId)
            ->with(['position' => function($query) {
                $query->where('Active', true)
                    ->orderBy('sort_order')
                    ->orderBy('Name');
            }])
            ->get();

        // Группируем по типам персонала
        $grouped = [];
        foreach ($minimumCrew as $crew) {
            if ($crew->position && $crew->quantity > 0) {
                $crewType = $crew->position->crew_type ?? 'Другое';
                if (!isset($grouped[$crewType])) {
                    $grouped[$crewType] = [];
                }
                $item = [
                    'position_id' => $crew->position_id,
                    'position_name' => $crew->position->Name,
                    'position_short_name' => $crew->position->short_name,
                    'quantity' => (int)$crew->quantity // Явно преобразуем в int
                ];
                
                // Логируем для отладки
                \Log::info('MinimumCrew item:', [
                    'aircraft_type_id' => $aircraftTypeId,
                    'position_name' => $item['position_name'],
                    'position_short_name' => $item['position_short_name'],
                    'quantity' => $item['quantity'],
                    'quantity_raw' => $crew->quantity,
                    'quantity_type' => gettype($crew->quantity),
                    'crew_type' => $crewType
                ]);
                
                $grouped[$crewType][] = $item;
            }
        }
        
        // Логируем финальную структуру
        \Log::info('MinimumCrew grouped result:', [
            'aircraft_type_id' => $aircraftTypeId,
            'grouped' => $grouped
        ]);

        return response()->json([
            'success' => true,
            'data' => $grouped
        ]);
    }

    public function getAircraftTypeIdByRegN(Request $request)
    {
        $regn = $request->get('regn');
        
        if (!$regn) {
            return response()->json(['success' => false, 'message' => 'regn is required']);
        }

        $aircraft = Aircraft::where('RegN', $regn)->first();
        
        if (!$aircraft) {
            return response()->json(['success' => false, 'message' => 'Aircraft not found']);
        }

        $aircraftType = AircraftsType::where('icao', $aircraft->Type)->first();
        
        if (!$aircraftType) {
            return response()->json(['success' => false, 'message' => 'Aircraft type not found']);
        }

        return response()->json([
            'success' => true,
            'aircraft_type_id' => $aircraftType->id
        ]);
    }
}
