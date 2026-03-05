<?php

declare(strict_types=1);

namespace App\Http\Controllers\Modules\Reliability;

use App\Http\Controllers\Controller;
use App\Models\RelBufSetting;
use App\Models\RelFailureFormSetting;
use App\Models\RelEngineNumber;
use App\Models\RelEngineType;
use App\Models\RelFailureConsequence;
use App\Models\RelFailureDetectionStage;
use App\Models\RelFailureSystem;
use App\Models\RelFailureAggregate;
use App\Models\RelTakenMeasure;
use App\Models\AircraftsType;
use App\Models\RelWoStatus;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class ReliabilitySettingsController extends Controller
{
    public function index()
    {
        return view('Modules.Reliability.settings.index');
    }

    /**
     * Страница настроек структуры отчёта BUF.
     */
    public function reportStructureBuf()
    {
        $setting = RelBufSetting::first();
        if (!$setting) {
            $setting = RelBufSetting::create([
                'start_number_prefix' => 2388,
            ]);
        }

        return view('Modules.Reliability.settings.report_structure_buf', [
            'bufSetting' => $setting,
        ]);
    }

    /**
     * Обновление префикса номера записи BUF.
     */
    public function reportStructureBufUpdate(Request $request)
    {
        $data = $request->validate([
            'start_number_prefix' => 'required|integer|min:1',
        ]);

        $setting = RelBufSetting::first();
        if (!$setting) {
            $setting = RelBufSetting::create($data);
        } else {
            $setting->update($data);
        }

        return redirect()
            ->route('modules.reliability.settings.report-structure-buf.index')
            ->with('success', 'Префикс номера записи BUF обновлён');
    }

    // Этапы обнаружения отказа
    public function detectionStagesIndex()
    {
        $items = RelFailureDetectionStage::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('Modules.Reliability.settings.detection_stages.index', compact('items'));
    }

    public function detectionStagesCreate()
    {
        $allStages = RelFailureDetectionStage::orderBy('sort_order')->orderBy('name')->get();

        return view('Modules.Reliability.settings.detection_stages.edit', [
            'allStages' => $allStages,
        ]);
    }

    public function detectionStagesStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'parent_id' => 'nullable|integer',
        ]);
        $data['active'] = $request->boolean('active', true);
        $data['sort_order'] = (int) (RelFailureDetectionStage::max('sort_order') ?? 0) + 1;

        RelFailureDetectionStage::create($data);

        return redirect()->route('modules.reliability.settings.detection-stages.index')
            ->with('success', 'Этап обнаружения отказа добавлен');
    }

    public function detectionStagesEdit(RelFailureDetectionStage $stage)
    {
        $allStages = RelFailureDetectionStage::where('id', '!=', $stage->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('Modules.Reliability.settings.detection_stages.edit', [
            'item' => $stage,
            'allStages' => $allStages,
        ]);
    }

    public function detectionStagesUpdate(Request $request, RelFailureDetectionStage $stage)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'parent_id' => 'nullable|integer',
        ]);
        $data['active'] = $request->boolean('active', true);

        $stage->update($data);

        return redirect()->route('modules.reliability.settings.detection-stages.index')
            ->with('success', 'Этап обнаружения отказа обновлён');
    }

    public function detectionStagesDestroy(RelFailureDetectionStage $stage)
    {
        $stage->delete();

        return redirect()->route('modules.reliability.settings.detection-stages.index')
            ->with('success', 'Этап обнаружения отказа удалён');
    }

    // Последствия
    public function consequencesIndex()
    {
        $items = RelFailureConsequence::orderBy('sort_order')->orderBy('name')->paginate(20);
        return view('Modules.Reliability.settings.consequences.index', compact('items'));
    }

    public function consequencesCreate()
    {
        return view('Modules.Reliability.settings.consequences.edit');
    }

    public function consequencesStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);
        $data['sort_order'] = (int) (RelFailureConsequence::max('sort_order') ?? 0) + 1;

        RelFailureConsequence::create($data);

        return redirect()->route('modules.reliability.settings.consequences.index')
            ->with('success', 'Последствие добавлено');
    }

    public function consequencesEdit(RelFailureConsequence $consequence)
    {
        return view('Modules.Reliability.settings.consequences.edit', ['item' => $consequence]);
    }

    public function consequencesUpdate(Request $request, RelFailureConsequence $consequence)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);

        $consequence->update($data);

        return redirect()->route('modules.reliability.settings.consequences.index')
            ->with('success', 'Последствие обновлено');
    }

    public function consequencesDestroy(RelFailureConsequence $consequence)
    {
        $consequence->delete();

        return redirect()->route('modules.reliability.settings.consequences.index')
            ->with('success', 'Последствие удалено');
    }

    // Статусы WO
    public function woStatusesIndex()
    {
        $items = RelWoStatus::orderBy('sort_order')->orderBy('name')->paginate(20);
        return view('Modules.Reliability.settings.wo_statuses.index', compact('items'));
    }

    public function woStatusesCreate()
    {
        return view('Modules.Reliability.settings.wo_statuses.edit');
    }

    public function woStatusesStore(Request $request)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);
        $data['sort_order'] = (int) (RelWoStatus::max('sort_order') ?? 0) + 1;

        RelWoStatus::create($data);

        return redirect()->route('modules.reliability.settings.wo-statuses.index')
            ->with('success', 'Статус WO добавлен');
    }

    public function woStatusesEdit(RelWoStatus $status)
    {
        return view('Modules.Reliability.settings.wo_statuses.edit', ['item' => $status]);
    }

    public function woStatusesUpdate(Request $request, RelWoStatus $status)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);

        $status->update($data);

        return redirect()->route('modules.reliability.settings.wo-statuses.index')
            ->with('success', 'Статус WO обновлён');
    }

    public function woStatusesDestroy(RelWoStatus $status)
    {
        $status->delete();

        return redirect()->route('modules.reliability.settings.wo-statuses.index')
            ->with('success', 'Статус WO удалён');
    }

    // Системы / подсистемы
    public function systemsIndex(Request $request)
    {
        $aircraftTypes = AircraftsType::orderBy('name_rus')->get();
        $selectedAircraftTypeId = $request->filled('aircraft_type_id')
            ? (int) $request->input('aircraft_type_id')
            : null;

        $systemsQuery = RelFailureSystem::query();
        if ($selectedAircraftTypeId !== null) {
            $systemsQuery->where('aircraft_type_id', $selectedAircraftTypeId);
        }
        // Список уникальных систем (берём минимальный id для каждой системы)
        $systems = $systemsQuery->selectRaw('MIN(id) as id, system_name')
            ->groupBy('system_name')
            ->orderBy('system_name')
            ->get();

        $selectedSystem = $request->get('system');
        if (!$selectedSystem && $systems->count() > 0) {
            $selectedSystem = $systems->first()->system_name;
        }

        $subsystems = collect();
        $aggregates = collect();

        // Агрегаты, не входящие ни в одну систему (фильтр по типу ВС)
        $freeAggregatesQuery = RelFailureAggregate::whereNull('failure_system_id');
        if ($selectedAircraftTypeId !== null) {
            $freeAggregatesQuery->where('aircraft_type_id', $selectedAircraftTypeId);
        }
        $freeAggregates = (clone $freeAggregatesQuery)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $freeAggregateNames = $freeAggregatesQuery->pluck('name')->all();
        $selectedSubsystemId = null;
        if ($selectedSystem) {
            $subsystemsQuery = RelFailureSystem::where('system_name', $selectedSystem);
            if ($selectedAircraftTypeId !== null) {
                $subsystemsQuery->where('aircraft_type_id', $selectedAircraftTypeId);
            }
            $subsystems = $subsystemsQuery
                ->orderBy('sort_order')
                ->orderBy('subsystem_name')
                ->paginate(50);

            // выбранная подсистема для отображения агрегатов
            $selectedSubsystemId = $request->integer('subsystem_id') ?: null;
            if ($selectedSubsystemId) {
                $aggregatesQuery = RelFailureAggregate::where('failure_system_id', $selectedSubsystemId);

                // Исключаем из списка агрегаты, которые помечены как "не в составе систем"
                if (!empty($freeAggregateNames)) {
                    $aggregatesQuery->whereNotIn('name', $freeAggregateNames);
                }

                $aggregates = $aggregatesQuery
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('Modules.Reliability.settings.systems.index', [
            'aircraftTypes' => $aircraftTypes,
            'selectedAircraftTypeId' => $selectedAircraftTypeId,
            'systems' => $systems,
            'selectedSystem' => $selectedSystem,
            'subsystems' => $subsystems,
            'aggregates' => $aggregates,
            'selectedSubsystemId' => $selectedSubsystemId,
            'freeAggregates' => $freeAggregates,
        ]);
    }

    public function systemsCreate()
    {
        return view('Modules.Reliability.settings.systems.edit');
    }

    public function systemsStore(Request $request)
    {
        $data = $request->validate([
            'system_name' => 'required|string|max:255',
            'subsystem_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'aircraft_type_id' => 'nullable|exists:aircrafts_types,id',
        ]);
        $data['active'] = $request->boolean('active', true);
        $data['aircraft_type_id'] = !empty($data['aircraft_type_id']) ? (int) $data['aircraft_type_id'] : null;
        $sortOrderQuery = RelFailureSystem::query();
        if ($data['aircraft_type_id'] !== null) {
            $sortOrderQuery->where('aircraft_type_id', $data['aircraft_type_id']);
        }
        $data['sort_order'] = (int) ($sortOrderQuery->max('sort_order') ?? 0) + 1;

        RelFailureSystem::create($data);

        $params = ['system' => $data['system_name']];
        if ($request->filled('aircraft_type_id')) {
            $params['aircraft_type_id'] = $request->input('aircraft_type_id');
        }

        return redirect()->route('modules.reliability.settings.systems.index', $params)
            ->with('success', 'Система/подсистема добавлена');
    }

    public function systemsEdit(RelFailureSystem $system)
    {
        return view('Modules.Reliability.settings.systems.edit', ['item' => $system]);
    }

    public function systemsUpdate(Request $request, RelFailureSystem $system)
    {
        $data = $request->validate([
            'system_name' => 'required|string|max:255',
            'subsystem_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);

        $system->update($data);

        return redirect()->route('modules.reliability.settings.systems.index')
            ->with('success', 'Система/подсистема обновлена');
    }

    public function systemsDestroy(RelFailureSystem $system)
    {
        $system->delete();

        return redirect()->route('modules.reliability.settings.systems.index')
            ->with('success', 'Система/подсистема удалена');
    }

    /**
     * Переименование системы (код + наименование) для всех связанных подсистем
     */
    public function systemsRename(Request $request, RelFailureSystem $system)
    {
        $data = $request->validate([
            'system_code' => 'nullable|string|max:50',
            'system_name_display' => 'required|string|max:255',
        ]);

        $code = trim((string) ($data['system_code'] ?? ''));
        $name = trim($data['system_name_display']);
        $newFullName = $code !== '' ? ($code . ' - ' . $name) : $name;

        $oldName = $system->system_name;

        RelFailureSystem::where('system_name', $oldName)->update([
            'system_name' => $newFullName,
        ]);

        $params = ['system' => $newFullName];
        if ($request->filled('aircraft_type_id')) {
            $params['aircraft_type_id'] = $request->input('aircraft_type_id');
        }

        return redirect()->route('modules.reliability.settings.systems.index', $params)
            ->with('success', 'Система переименована');
    }

    /**
     * Сохранение агрегата для выбранной подсистемы
     */
    public function aggregatesStore(Request $request)
    {
        $data = $request->validate([
            'failure_system_id' => 'nullable|integer',
            'aggregate_code' => 'nullable|string|max:50',
            'aggregate_name_display' => 'required|string|max:255',
            'aircraft_type_id' => 'nullable|exists:aircrafts_types,id',
        ]);

        $code = trim((string) ($data['aggregate_code'] ?? ''));
        $name = trim($data['aggregate_name_display']);
        $fullName = $code !== '' ? ($code . ' - ' . $name) : $name;

        $failureSystemId = $data['failure_system_id'] ?? null;

        $sortOrderQuery = RelFailureAggregate::query();
        if ($failureSystemId) {
            $sortOrderQuery->where('failure_system_id', $failureSystemId);
        } else {
            $sortOrderQuery->whereNull('failure_system_id');
        }

        $sortOrder = (int) ($sortOrderQuery->max('sort_order') ?? 0) + 1;

        $system = $failureSystemId ? RelFailureSystem::findOrFail($failureSystemId) : null;
        $aircraftTypeId = !empty($data['aircraft_type_id']) ? (int) $data['aircraft_type_id'] : null;
        $aggregateData = [
            'failure_system_id' => $failureSystemId,
            'name' => $fullName,
            'description' => null,
            'active' => true,
            'sort_order' => $sortOrder,
            'aircraft_type_id' => $system ? ($system->aircraft_type_id ?? $aircraftTypeId) : $aircraftTypeId,
        ];
        RelFailureAggregate::create($aggregateData);

        $redirectParams = [];
        if ($system) {
            $redirectParams['system'] = $system->system_name;
            $redirectParams['subsystem_id'] = $system->id;
        }
        if ($request->filled('aircraft_type_id')) {
            $redirectParams['aircraft_type_id'] = $request->input('aircraft_type_id');
        }

        return redirect()->route('modules.reliability.settings.systems.index', $redirectParams)
            ->with('success', 'Агрегат добавлен');
    }

    // Типы двигателей
    public function engineTypesIndex()
    {
        $items = RelEngineType::orderBy('sort_order')->orderBy('name')->paginate(20);
        return view('Modules.Reliability.settings.engine_types.index', compact('items'));
    }

    public function engineTypesCreate()
    {
        return view('Modules.Reliability.settings.engine_types.edit');
    }

    public function engineTypesStore(Request $request)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);
        $data['sort_order'] = (int) (RelEngineType::max('sort_order') ?? 0) + 1;

        RelEngineType::create($data);

        return redirect()->route('modules.reliability.settings.engine-types.index')
            ->with('success', 'Тип двигателя добавлен');
    }

    public function engineTypesEdit(RelEngineType $engineType)
    {
        return view('Modules.Reliability.settings.engine_types.edit', ['item' => $engineType]);
    }

    public function engineTypesUpdate(Request $request, RelEngineType $engineType)
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);

        $engineType->update($data);

        return redirect()->route('modules.reliability.settings.engine-types.index')
            ->with('success', 'Тип двигателя обновлён');
    }

    public function engineTypesDestroy(RelEngineType $engineType)
    {
        $engineType->delete();

        return redirect()->route('modules.reliability.settings.engine-types.index')
            ->with('success', 'Тип двигателя удалён');
    }

    // Номера двигателей
    public function engineNumbersIndex()
    {
        $items = RelEngineNumber::with('engineType')->orderBy('sort_order')->orderBy('number')->paginate(20);
        $engineTypes = RelEngineType::orderBy('name')->get();
        return view('Modules.Reliability.settings.engine_numbers.index', compact('items', 'engineTypes'));
    }

    public function engineNumbersCreate()
    {
        $engineTypes = RelEngineType::orderBy('name')->get();
        return view('Modules.Reliability.settings.engine_numbers.edit', compact('engineTypes'));
    }

    public function engineNumbersStore(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|max:255',
            'engine_type_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);
        $data['sort_order'] = (int) (RelEngineNumber::max('sort_order') ?? 0) + 1;

        RelEngineNumber::create($data);

        return redirect()->route('modules.reliability.settings.engine-numbers.index')
            ->with('success', 'Номер двигателя добавлен');
    }

    public function engineNumbersEdit(RelEngineNumber $engineNumber)
    {
        $engineTypes = RelEngineType::orderBy('name')->get();
        return view('Modules.Reliability.settings.engine_numbers.edit', [
            'item' => $engineNumber,
            'engineTypes' => $engineTypes,
        ]);
    }

    public function engineNumbersUpdate(Request $request, RelEngineNumber $engineNumber)
    {
        $data = $request->validate([
            'number' => 'required|string|max:255',
            'engine_type_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);

        $engineNumber->update($data);

        return redirect()->route('modules.reliability.settings.engine-numbers.index')
            ->with('success', 'Номер двигателя обновлён');
    }

    public function engineNumbersDestroy(RelEngineNumber $engineNumber)
    {
        $engineNumber->delete();

        return redirect()->route('modules.reliability.settings.engine-numbers.index')
            ->with('success', 'Номер двигателя удалён');
    }

    // Принятые меры
    public function takenMeasuresIndex()
    {
        $items = RelTakenMeasure::orderBy('sort_order')->orderBy('name')->paginate(20);
        return view('Modules.Reliability.settings.taken_measures.index', compact('items'));
    }

    public function takenMeasuresCreate()
    {
        return view('Modules.Reliability.settings.taken_measures.edit');
    }

    public function takenMeasuresStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);
        $data['sort_order'] = (int) (RelTakenMeasure::max('sort_order') ?? 0) + 1;

        RelTakenMeasure::create($data);

        return redirect()->route('modules.reliability.settings.taken-measures.index')
            ->with('success', 'Принятая мера добавлена');
    }

    public function takenMeasuresEdit(RelTakenMeasure $takenMeasure)
    {
        return view('Modules.Reliability.settings.taken_measures.edit', ['item' => $takenMeasure]);
    }

    public function takenMeasuresUpdate(Request $request, RelTakenMeasure $takenMeasure)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $data['active'] = $request->boolean('active', true);

        $takenMeasure->update($data);

        return redirect()->route('modules.reliability.settings.taken-measures.index')
            ->with('success', 'Принятая мера обновлена');
    }

    public function takenMeasuresDestroy(RelTakenMeasure $takenMeasure)
    {
        $takenMeasure->delete();

        return redirect()->route('modules.reliability.settings.taken-measures.index')
            ->with('success', 'Принятая мера удалена');
    }

    /**
     * Справочник «Коды типа ВС» — редактирование кода для существующих типов ВС.
     */
    public function aircraftTypeCodesIndex(Request $request)
    {
        $perPage = (int) $request->get('per_page', 50);
        if (!in_array($perPage, [25, 50, 100], true)) {
            $perPage = 50;
        }

        $types = AircraftsType::orderBy('name_rus')
            ->orderBy('icao')
            ->paginate($perPage)
            ->appends($request->query());

        return view('Modules.Reliability.settings.aircraft_type_codes.index', [
            'types' => $types,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Сохранение кодов типа ВС для справочника типов.
     */
    public function aircraftTypeCodesUpdate(Request $request)
    {
        $data = $request->validate([
            'codes' => ['array'],
            'codes.*' => ['nullable', 'string', 'max:50'],
        ]);

        $codes = $data['codes'] ?? [];
        if (!empty($codes)) {
            $ids = array_keys($codes);
            $types = AircraftsType::whereIn('id', $ids)->get();

            foreach ($types as $type) {
                $code = $codes[$type->id] ?? null;
                $type->rus = $code !== '' ? $code : null;
                $type->save();
            }
        }

        return redirect()
            ->route('modules.reliability.settings.aircraft-type-codes.index')
            ->with('success', 'Коды типа ВС сохранены');
    }

    /**
     * Настройки формы отказа: видимость полей в формах добавления/редактирования.
     */
    public function failureFormIndex()
    {
        $visibility = RelFailureFormSetting::getFieldVisibility();
        $fields = RelFailureFormSetting::formFieldsList();

        return view('Modules.Reliability.settings.failure_form.index', [
            'fields' => $fields,
            'visibility' => $visibility,
        ]);
    }

    /**
     * Сохранение видимости полей формы отказа.
     */
    public function failureFormUpdate(Request $request)
    {
        $fields = RelFailureFormSetting::formFieldsList();
        $visibility = [];
        foreach (array_keys($fields) as $key) {
            $visibility[$key] = $request->boolean('visible.' . $key);
        }
        RelFailureFormSetting::setFieldVisibility($visibility);

        return redirect()
            ->route('modules.reliability.settings.failure-form.index')
            ->with('success', 'Настройки формы отказа сохранены');
    }

    /**
     * Настройки вкладок модуля Надёжность.
     */
    public function tabsIndex()
    {
        $visibility = RelFailureFormSetting::getTabsVisibility();
        $tabs = RelFailureFormSetting::tabsList();

        return view('Modules.Reliability.settings.tabs.index', [
            'tabs' => $tabs,
            'visibility' => $visibility,
        ]);
    }

    /**
     * Сохранение видимости вкладок.
     */
    public function tabsUpdate(Request $request)
    {
        $tabs = RelFailureFormSetting::tabsList();
        $visibility = [];

        foreach (array_keys($tabs) as $key) {
            $visibility[$key] = $request->boolean('visible.' . $key);
        }

        RelFailureFormSetting::setTabsVisibility($visibility);

        return redirect()
            ->route('modules.reliability.settings.tabs.index')
            ->with('success', 'Настройки вкладок сохранены');
    }

    public function orgCodeIndex()
    {
        $orgCode = SystemSetting::get('reliability_org_code', '');

        return view('Modules.Reliability.settings.org_code.index', compact('orgCode'));
    }

    public function orgCodeUpdate(Request $request)
    {
        $data = $request->validate([
            'org_code' => ['nullable', 'string', 'max:100'],
        ]);

        SystemSetting::set('reliability_org_code', $data['org_code'] ?? '');

        return redirect()
            ->route('modules.reliability.settings.org-code.index')
            ->with('success', 'Код организации сохранён');
    }
}


