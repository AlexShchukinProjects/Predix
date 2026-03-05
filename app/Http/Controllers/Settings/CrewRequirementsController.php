<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Crew;
use App\Models\RequirementType;
use App\Models\Requirement;
use App\Models\Position;
use Illuminate\Support\Facades\DB;

class CrewRequirementsController extends Controller
{
    public function index()
    {
        // Получаем активные должности из справочника positions
        $positions = Position::where('Active', true)
            ->orderBy('sort_order')
            ->orderBy('Name')
            ->pluck('short_name', 'Name')
            ->toArray();

        // Получаем типы требований и сами требования из базы данных
        $requirementTypes = RequirementType::active()
            ->with(['requirements' => function($query) {
                $query->active()->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        // Преобразуем в старый формат для совместимости с view
        $requirements = [];
        foreach ($requirementTypes as $type) {
            $requirements[$type->name] = $type->requirements->pluck('name')->toArray();
        }

        // Получаем сохраненные требования из базы данных
        $savedRequirements = DB::table('crew_requirements')
            ->select('position', 'requirement', 'required')
            ->get()
            ->keyBy(function ($item) {
                return $item->position . '_' . $item->requirement;
            });

        return view('Settings.CrewRequirements', compact('positions', 'requirements', 'savedRequirements', 'requirementTypes'));
    }

    public function update(Request $request)
    {
        try {
            $data = $request->all();
            
            // Детальное логирование входящих данных
            \Log::info('=== CREW REQUIREMENTS UPDATE START ===');
            \Log::info('All request data:', $data);
            \Log::info('Request keys count: ' . count($data));
            
            // Получаем список должностей для проверки
            $positions = Position::where('Active', true)
                ->pluck('short_name', 'short_name')
                ->toArray();
            
            \Log::info('Available positions:', $positions);
            
            // Удаляем старые записи
            DB::table('crew_requirements')->truncate();
            
            $inserted = 0;
            $skipped = 0;
            $reqKeys = [];
            
            // Добавляем новые записи
            // Формат ключа: req_{requirement_id}_{position}
            foreach ($data as $key => $value) {
                if (strpos($key, 'req_') === 0) {
                    $reqKeys[] = $key;
                    // Убираем префикс 'req_'
                    $keyWithoutPrefix = substr($key, 4);
                    
                    // Разбиваем на части: requirement_id_position
                    $parts = explode('_', $keyWithoutPrefix, 2);
                    
                    if (count($parts) === 2 && is_numeric($parts[0])) {
                        $requirementId = (int)$parts[0];
                        $position = $parts[1];
                        
                        \Log::info('Processing checkbox:', [
                            'key' => $key,
                            'value' => $value,
                            'requirement_id' => $requirementId,
                            'position' => $position
                        ]);
                        
                        // Проверяем, что должность существует
                        if (!isset($positions[$position])) {
                            \Log::warning('Unknown position:', [
                                'position' => $position,
                                'key' => $key,
                                'available_positions' => array_keys($positions)
                            ]);
                            $skipped++;
                            continue;
                        }
                        
                        // Находим требование по ID
                        $requirementRecord = DB::table('requirements')->find($requirementId);
                        
                        if ($requirementRecord) {
                            \Log::info('Inserting requirement:', [
                                'position' => $position,
                                'requirement' => $requirementRecord->name,
                                'requirement_id' => $requirementRecord->id,
                                'required' => $value == 'on' ? 1 : 0
                            ]);
                            
                            DB::table('crew_requirements')->insert([
                                'position' => $position,
                                'requirement' => $requirementRecord->name,
                                'requirement_id' => $requirementRecord->id,
                                'required' => $value == 'on' ? 1 : 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $inserted++;
                        } else {
                            \Log::warning('Requirement not found by ID:', [
                                'requirement_id' => $requirementId,
                                'position' => $position,
                                'key' => $key
                            ]);
                            $skipped++;
                        }
                    } else {
                        \Log::warning('Invalid key format:', [
                            'key' => $key,
                            'keyWithoutPrefix' => $keyWithoutPrefix,
                            'parts' => $parts
                        ]);
                        $skipped++;
                    }
                }
            }
            
            \Log::info('=== CREW REQUIREMENTS UPDATE END ===');
            \Log::info('Summary:', [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'total_req_keys' => count($reqKeys),
                'req_keys' => $reqKeys
            ]);
            
            return redirect()->back()->with('success', 'Требования успешно обновлены');
        } catch (\Exception $e) {
            \Log::error('Error saving crew requirements:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Ошибка при сохранении требований: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created requirement type
     */
    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $requirementType = RequirementType::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'description' => $request->description,
            'sort_order' => RequirementType::max('sort_order') + 1
        ]);

        return redirect()->back()->with('success', 'Тип требования успешно добавлен');
    }

    /**
     * Store a newly created requirement
     */
    public function storeRequirement(Request $request)
    {
        $request->validate([
            'requirement_type_id' => 'required|exists:requirement_types,id',
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'validity_period_months' => 'nullable|integer|in:0,1,2,3,4,5,6,7,8,9,10,11,12,24,36,48,60',
            'control_level_days' => 'nullable|integer|min:0',
            'warning_level_days' => 'nullable|integer|min:0',
            'for_all_aircraft_types' => 'nullable|boolean',
        ]);

        // Отладка: проверяем что приходит
        \Log::info('Creating requirement with validity_period_months', [
            'raw_value' => $request->validity_period_months,
            'is_empty_string' => $request->validity_period_months === '',
            'is_null' => $request->validity_period_months === null,
            'type' => gettype($request->validity_period_months)
        ]);

        $requirement = Requirement::create([
            'requirement_type_id' => $request->requirement_type_id,
            'name' => $request->name,
            'short_name' => $request->short_name,
            'description' => $request->description,
            'validity_period_months' => $request->validity_period_months !== '' && $request->validity_period_months !== null ? (int)$request->validity_period_months : null,
            'control_level_days' => $request->control_level_days,
            'warning_level_days' => $request->warning_level_days,
            'for_all_aircraft_types' => (bool) $request->boolean('for_all_aircraft_types', false),
            'sort_order' => (int) (Requirement::where('requirement_type_id', $request->requirement_type_id)->max('sort_order') ?? 0) + 1
        ]);

        return redirect()->back()->with('success', 'Требование успешно добавлено');
    }

    /**
     * Update a requirement type
     */
    public function updateType(Request $request, RequirementType $requirementType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $requirementType->update($request->only(['name', 'name_en', 'description']));

        return redirect()->back()->with('success', 'Тип требования успешно обновлен');
    }

    /**
     * Update a requirement
     */
    public function updateRequirement(Request $request, Requirement $requirement)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirement_type_id' => 'required|exists:requirement_types,id',
            'validity_period_months' => 'nullable|integer|in:0,1,2,3,4,5,6,7,8,9,10,11,12,24,36,48,60',
            'control_level_days' => 'nullable|integer|min:0',
            'warning_level_days' => 'nullable|integer|min:0',
            'for_all_aircraft_types' => 'nullable|boolean',
        ]);

        // Если изменился тип, переместим запись в конец нового типа
        if ((int)$requirement->requirement_type_id !== (int)$request->requirement_type_id) {
            $newSort = (int) (Requirement::where('requirement_type_id', $request->requirement_type_id)->max('sort_order') ?? 0) + 1;
            $requirement->requirement_type_id = (int) $request->requirement_type_id;
            $requirement->sort_order = $newSort;
        }

        // Отладка: проверяем что приходит
        \Log::info('Updating requirement with validity_period_months', [
            'requirement_id' => $requirement->id,
            'raw_value' => $request->validity_period_months,
            'is_empty_string' => $request->validity_period_months === '',
            'is_null' => $request->validity_period_months === null,
            'type' => gettype($request->validity_period_months)
        ]);
        
        $requirement->name = $request->name;
        $requirement->short_name = $request->short_name;
        $requirement->description = $request->description;
        $requirement->validity_period_months = $request->validity_period_months !== '' && $request->validity_period_months !== null ? (int)$request->validity_period_months : null;
        $requirement->control_level_days = $request->control_level_days;
        $requirement->warning_level_days = $request->warning_level_days;
        $requirement->for_all_aircraft_types = (bool) $request->boolean('for_all_aircraft_types', false);
        $requirement->save();

        return redirect()->back()->with('success', 'Требование успешно обновлено');
    }

    /**
     * Delete a requirement type
     */
    public function destroyType(RequirementType $requirementType)
    {
        $requirementType->delete();
        return redirect()->back()->with('success', 'Тип требования успешно удален');
    }

    /**
     * Delete a requirement
     */
    public function destroyRequirement(Requirement $requirement)
    {
        $requirement->delete();
        return redirect()->back()->with('success', 'Требование успешно удалено');
    }

    /**
     * Return impacted employees count for a requirement
     */
    public function impact(Requirement $requirement)
    {
        // Считаем фактически заведенные данные по требованию через связанные таблицы с нормализацией, как во вью EditCrew
        $normalize = function ($s) {
            $s = (string) $s;
            $s = preg_replace('/\s+/u', ' ', $s ?? '');
            $s = trim($s);
            return mb_strtolower($s, 'UTF-8');
        };

        $needle = $normalize($requirement->name);

        $collectCrewIds = function (string $table) use ($normalize, $needle) {
            return DB::table($table)
                ->select('crew_id', 'Description')
                ->whereNotNull('Description')
                ->get()
                ->filter(function ($row) use ($normalize, $needle) {
                    return $normalize($row->Description) === $needle;
                })
                ->pluck('crew_id');
        };

        // Сначала считаем по requirement_id, затем добавляем старые записи по Description
        $byId = function (string $table) use ($requirement) {
            return DB::table($table)->where('requirement_id', $requirement->id)->pluck('crew_id');
        };

        $ids = collect()
            ->merge($byId('permissions'))
            ->merge($byId('flightdocs'))
            ->merge($byId('flightchecks'))
            ->merge($byId('trainings'))
            ->merge($collectCrewIds('permissions'))
            ->merge($collectCrewIds('flightdocs'))
            ->merge($collectCrewIds('flightchecks'))
            ->merge($collectCrewIds('trainings'))
            ->unique()
            ->filter();

        return response()->json(['count' => $ids->count()]);
    }

    /**
     * Reorder requirements within a requirement type
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'requirement_type_id' => 'required|exists:requirement_types,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:requirements,id',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $index => $requirementId) {
                Requirement::where('id', $requirementId)
                    ->where('requirement_type_id', $request->requirement_type_id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json(['status' => 'ok']);
    }
}
