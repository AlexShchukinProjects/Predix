<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\StoreFleetRequest;
use App\Http\Requests\Fleet\UpdateFleetRequest;
use App\Http\Requests\Fleet\FilterFleetRequest;
use App\Models\Aircraft;
use App\Models\AircraftsType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;


class FleetController extends Controller
{

    public function index(FilterFleetRequest $request){

        $data=$request->validated();

       // dd($data);

        $flightQuery=Aircraft::query();

        if(isset($data["aircraft_number"])) {
            $flightQuery->where('aircraft_number', "like",'%'.$data["aircraft_number"].'%');
        }

        if(isset($data["Type"])) {
            $flightQuery->where('Type', "like",'%'.$data["Type"].'%');
        }

        if(isset($data["Airport_base"])) {
            $flightQuery->where('Airport_base', "like",'%'.$data["Airport_base"].'%');
        }

        $perPage = (int) $request->get('per_page', 10);
        $aircrafts = $flightQuery->paginate($perPage)->appends($request->query());

        return view('Settings.Fleet.fleet', compact('aircrafts'));


        /*


        $aircrafts=fleet::all();

    return view('Settings.Fleet.fleet', compact('aircrafts'));


    */








    }




    public function create()
    {
        $aircraftTypes = AircraftsType::where('active', true)->orderBy('name_rus')->get();
        return view('Settings.Fleet.Fleet.CreateFleet', compact('aircraftTypes'));
    }


    public function store(StoreFleetRequest $request)
    {
        $validated = $request->validated();

        Aircraft::create($validated);

        return redirect()->route('fleet.index')->with('success', 'Воздушное судно успешно добавлено');
    }


    public function edit(Aircraft $aircraft){
        $aircraftTypes = AircraftsType::where('active', true)->orderBy('name_rus')->get();
        return view('Settings.Fleet.Fleet.EditFleet', compact('aircraft', 'aircraftTypes'));
    }


    public function update(UpdateFleetRequest $request, Aircraft $aircraft)
    {
        $data = $request->validated();

        $aircraft->update($data);

        return redirect()->route('fleet.index')->with('success', 'Воздушное судно успешно обновлено');
    }


    public function destroy(Aircraft $aircraft){

       // dd($aircraft);

        Aircraft::destroy($aircraft->id);

        return redirect()->route('fleet.index')->with('success', 'Воздушное судно успешно удалено');


    }

    /**
     * Загрузка кодов Тип ВС (код) и Модификация (код) из Excel для всего парка.
     * В файле должны быть колонки: Бортовой № ВС (или Рег. №), Тип ВС (код), Модификация (код).
     */
    public function uploadTypeCodes(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx|max:20480',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        Artisan::call('fleet:import-type-codes-from-excel', ['path' => $path]);

        $output = trim(Artisan::output());
        $success = Artisan::exitCode() === 0;

        if ($success) {
            return redirect()->back()->with('success', 'Коды загружены. ' . $output);
        }

        return redirect()->back()->with('error', 'Ошибка загрузки: ' . $output);
    }


}
