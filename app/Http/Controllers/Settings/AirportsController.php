<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Airports;
use Illuminate\Http\Request;

class AirportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Airports::query();
        
        // Поиск по полям
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('NameRus', 'like', "%{$search}%")
                  ->orWhere('NameEng', 'like', "%{$search}%")
                  ->orWhere('iata', 'like', "%{$search}%")
                  ->orWhere('icao', 'like', "%{$search}%");
            });
        }
        
        // Количество строк на странице
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;

        // Сортировка
        $allowedSorts = ['NameRus', 'NameEng', 'iata', 'icao'];
        $sort = $request->get('sort');
        $direction = strtolower((string) $request->get('direction')) === 'desc' ? 'desc' : 'asc';
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'NameRus';
        }

        if ($sort === 'NameRus') {
            $airports = $query
                ->orderByRaw("CASE WHEN NameRus IS NULL OR NameRus = '' THEN 1 ELSE 0 END ASC")
                ->orderBy('NameRus', $direction)
                ->paginate($perPage);
        } else {
            $airports = $query->orderBy($sort, $direction)->paginate($perPage);
        }
        $airports->appends($request->query());
        
        return view('Settings.Airports.index', compact('airports'));
    }

    public function search(Request $request)
    {
        $query = Airports::query();
        
        // Поиск по полям
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('NameRus', 'like', "%{$search}%")
                  ->orWhere('NameEng', 'like', "%{$search}%")
                  ->orWhere('iata', 'like', "%{$search}%")
                  ->orWhere('icao', 'like', "%{$search}%");
            });
        }
        
        // Количество строк на странице
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;

        // Сортировка
        $allowedSorts = ['NameRus', 'NameEng', 'iata', 'icao'];
        $sort = $request->get('sort');
        $direction = strtolower((string) $request->get('direction')) === 'desc' ? 'desc' : 'asc';
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'NameRus';
        }

        if ($sort === 'NameRus') {
            $airports = $query
                ->orderByRaw("CASE WHEN NameRus IS NULL OR NameRus = '' THEN 1 ELSE 0 END ASC")
                ->orderBy('NameRus', $direction)
                ->paginate($perPage);
        } else {
            $airports = $query->orderBy($sort, $direction)->paginate($perPage);
        }
        $airports->appends($request->query());
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('Settings.Airports.partials.table', compact('airports'))->render(),
                'pagination' => view('Settings.Airports.partials.pagination', compact('airports'))->render()
            ]);
        }
        
        return view('Settings.Airports.index', compact('airports'));
    }

    /**
     * JSON для Select2 (поиск аэропортов, минимум 2 символа).
     * Используется в модуле Safety Reporting — поле «Место события».
     */
    public function searchOptions(Request $request)
    {
        $q = (string) $request->get('q', '');
        $q = trim($q);

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $query = Airports::query()
            ->select('City', 'icao', 'iata', 'NameRus', 'NameEng')
            ->where(function ($qb) use ($q) {
                $like = '%' . $q . '%';
                $qb->where('City', 'like', $like)
                    ->orWhere('NameRus', 'like', $like)
                    ->orWhere('NameEng', 'like', $like)
                    ->orWhere('iata', 'like', $like)
                    ->orWhere('icao', 'like', $like);
            })
            ->orderByRaw("CASE WHEN City IS NULL OR City = '' THEN 1 ELSE 0 END ASC")
            ->orderBy('City')
            ->limit(50);

        $airports = $query->get();

        $results = $airports->map(function ($ap) {
            $code = $ap->icao ?: $ap->iata;
            $name = trim($ap->NameRus ?? $ap->NameEng ?? '');
            $parts = array_filter([$ap->icao ?? '', $ap->iata ?? '', $ap->City ?? '', $name], fn ($v) => $v !== null && $v !== '');
            $text = implode('-', $parts) ?: $code;
            return ['id' => $code, 'text' => $text];
        })->values()->all();

        return response()->json(['results' => $results]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Settings.Airports.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            [
            'NameRus' => 'required|string|max:255',
            'NameEng' => 'nullable|string|max:255',
            'iata' => 'required|string|max:3|unique:airports,iata',
            'icao' => 'nullable|string|max:4',
            'City' => 'nullable|string|max:255',
            'Country' => 'nullable|string|max:255',
                // Формат смещения: -5, 4, 4:30, -5:30 и т.п.
                'SummurUTC' => ['nullable', 'string', 'max:10', 'regex:/^-?\d{1,2}(?::30)?$/'],
                'WinterUTC' => ['nullable', 'string', 'max:10', 'regex:/^-?\d{1,2}(?::30)?$/'],
            'Reglament' => 'nullable|string',
            'PDSP_ADP' => 'nullable|string',
            'vip' => 'nullable|string',
            'Comments' => 'nullable|string',
            'Checked' => 'nullable|boolean',
            ],
            [
                'SummurUTC.regex' => 'Формат поля недопустим. Формат ЧЧ:ММ',
                'WinterUTC.regex' => 'Формат поля недопустим. Формат ЧЧ:ММ',
            ]
        );

        Airports::create($data);

        return redirect()->route('airports.index')->with('success', 'Аэропорт успешно создан!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Airports $airport)
    {
        return view('Settings.Airports.show', compact('airport'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Airports $airport)
    {
        return view('Settings.Airports.edit', compact('airport'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Airports $airport)
    {
        $data = $request->validate(
            [
            'NameRus' => 'required|string|max:255',
            'NameEng' => 'nullable|string|max:255',
            'iata' => 'required|string|max:3|unique:airports,iata,' . $airport->id,
            'icao' => 'nullable|string|max:4',
            'City' => 'nullable|string|max:255',
            'Country' => 'nullable|string|max:255',
                // Формат смещения: -5, 4, 4:30, -5:30 и т.п.
                'SummurUTC' => ['nullable', 'string', 'max:10', 'regex:/^-?\d{1,2}(?::30)?$/'],
                'WinterUTC' => ['nullable', 'string', 'max:10', 'regex:/^-?\d{1,2}(?::30)?$/'],
            'Reglament' => 'nullable|string',
            'PDSP_ADP' => 'nullable|string',
            'vip' => 'nullable|string',
            'Comments' => 'nullable|string',
            'Checked' => 'nullable|date',
            ],
            [
                'SummurUTC.regex' => 'Формат поля недопустим. Формат ЧЧ:ММ',
                'WinterUTC.regex' => 'Формат поля недопустим. Формат ЧЧ:ММ',
            ]
        );

        $airport->update($data);

        return redirect()->route('airports.index')->with('success', 'Аэропорт успешно обновлен!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Airports $airport)
    {
        $airport->delete();

        return redirect()->route('airports.index')->with('success', 'Аэропорт успешно удален!');
    }
}
