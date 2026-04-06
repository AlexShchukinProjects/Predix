<?php

declare(strict_types=1);

namespace App\Http\Controllers\Modules\Reliability;

use App\Http\Controllers\Controller;
use App\Models\RelBufSetting;
use Illuminate\Http\Request;
use App\Models\RelFailureDetectionStage;
use App\Models\RelFailureConsequence;
use App\Models\RelWoStatus;
use App\Models\RelFailureSystem;
use App\Models\RelEngineType;
use App\Models\RelEngineNumber;
use App\Models\RelTakenMeasure;
use App\Models\RelFailureFormSetting;
use App\Models\ReliabilityFailure;
use App\Models\RelFailureAttachment;
use App\Models\SystemSetting;
use App\Models\InspectionWorkCard;
use App\Models\InspectionProject;
use App\Models\InspectionEefRegistry;
use App\Models\ReliabilityMasterData;
use Illuminate\Support\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ReliabilityController extends Controller
{
    /**
     * Главная страница модуля Надежность
     */
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'failures'); // failures, defects, monitoring, aging_aircraft, aging_components, systems

        // Список ВС для модального окна "Добавить отказ"
        $aircraftList = \App\Models\Aircraft::query()
            ->orderBy('RegN')
            ->get(['id', 'RegN', 'Type', 'Date_manufacture', 'FactoryNumber', 'type_code', 'modification_code']);

        // Справочники для модального окна "Добавить отказ"
        $detectionStages = RelFailureDetectionStage::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $failureConsequences = RelFailureConsequence::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $woStatuses = RelWoStatus::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $failureSystems = RelFailureSystem::select('system_name')
            ->distinct()
            ->orderBy('system_name')
            ->get();

        $failureSubsystems = RelFailureSystem::whereNotNull('subsystem_name')
            ->orderBy('system_name')
            ->orderBy('subsystem_name')
            ->get();

        $engineTypes = RelEngineType::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $engineNumbers = RelEngineNumber::where('active', true)
            ->with('engineType')
            ->orderBy('number')
            ->get();

        $takenMeasures = RelTakenMeasure::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Варианты для фильтра «Типы ВС» — из фактических значений в отказах (чтобы выбор совпадал с БД)
        $aircraftTypesForFilter = ReliabilityFailure::whereNotNull('aircraft_type')
            ->where('aircraft_type', '!=', '')
            ->distinct()
            ->orderBy('aircraft_type')
            ->pluck('aircraft_type');

        // Типы агрегатов из справочника, с учётом выбранной системы/подсистемы (берём первые выбранные для списка)
        $aggregateTypes = collect();
        $filterSystem = $request->input('system');
        $filterSubsystem = $request->input('subsystem');
        if (is_array($filterSystem)) {
            $filterSystem = $filterSystem[0] ?? null;
        }
        if (is_array($filterSubsystem)) {
            $filterSubsystem = $filterSubsystem[0] ?? null;
        }

        if ($filterSystem && $filterSubsystem) {
            // Находим записи конкретной системы и выбранной подсистемы
            $subsystemsForFilter = RelFailureSystem::where('system_name', $filterSystem)
                ->where('subsystem_name', $filterSubsystem)
                ->pluck('id');

            if ($subsystemsForFilter->isNotEmpty()) {
                $aggregateTypes = \App\Models\RelFailureAggregate::whereIn('failure_system_id', $subsystemsForFilter)
                    ->where('active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
            }
        } else {
            // Если подсистема не выбрана, не показываем список агрегатов (кроме опции "Все")
            $aggregateTypes = collect();
        }

        // Реальные отказы для таблицы + фильтры
        // work_cards_master: NRC = ADDNRC|NONROUTINE + непустой src_cust_card; RC = дополнение (чтобы RC+NRC = все строки)
        // # of RC / Max Hours on RC — по подмножеству RC; NRC-метрики — по подмножеству NRC + STR
        // EEF Count: зарезервировано (0)
        $wcm = 'work_cards_master';
        $nrcTab = "(LOWER(TRIM(COALESCE({$wcm}.order_type, ''))) IN ('addnrc', 'nonroutine') AND TRIM(COALESCE({$wcm}.src_cust_card, '')) <> '')";
        $rcTab = "((LOWER(TRIM(COALESCE({$wcm}.order_type, ''))) NOT IN ('addnrc', 'nonroutine')) OR (TRIM(COALESCE({$wcm}.src_cust_card, '')) = ''))";
        $nrcStrMatch = "(
                UPPER(COALESCE({$wcm}.description, '')) LIKE '%STR%'
                OR UPPER(COALESCE({$wcm}.corrective_action, '')) LIKE '%STR%'
                OR UPPER(COALESCE({$wcm}.order_type, '')) LIKE '%STR%'
            )";
        $failuresQuery = ReliabilityFailure::query()
            ->selectRaw("rel_stub.*,
                (SELECT COUNT(*) FROM {$wcm} WHERE COALESCE(TRIM(rel_stub.mpd), '') != '' AND {$wcm}.cust_card LIKE CONCAT('%', rel_stub.mpd, '%') AND {$rcTab}) as num_rc,
                (SELECT MAX(CAST({$wcm}.act_time AS DECIMAL(15,2))) FROM {$wcm} WHERE COALESCE(TRIM(rel_stub.work_order_number), '') != '' AND {$wcm}.cust_card LIKE CONCAT('%', rel_stub.work_order_number, '%') AND {$rcTab}) as max_hours_on_rc,
                (SELECT COUNT(*) FROM {$wcm} WHERE COALESCE(TRIM(rel_stub.mpd), '') != '' AND {$wcm}.src_cust_card LIKE CONCAT('%', rel_stub.mpd, '%') AND {$nrcTab} AND {$nrcStrMatch}) as num_str_nrcs,
                (SELECT MAX(CAST({$wcm}.act_time AS DECIMAL(15,2))) FROM {$wcm} WHERE COALESCE(TRIM(rel_stub.mpd), '') != '' AND {$wcm}.src_cust_card LIKE CONCAT('%', rel_stub.mpd, '%') AND {$nrcTab} AND {$nrcStrMatch}) as max_mhs_str_nrc,
                (SELECT AVG(CAST({$wcm}.act_time AS DECIMAL(15,2))) FROM {$wcm} WHERE COALESCE(TRIM(rel_stub.mpd), '') != '' AND {$wcm}.src_cust_card LIKE CONCAT('%', rel_stub.mpd, '%') AND {$nrcTab} AND {$nrcStrMatch}) as avg_str_mhs_raw,
                (SELECT 0) as eef_count")
            ->with(['detectionStage', 'consequence', 'takenMeasure'])
            ->orderByDesc('failure_date')
            ->orderByDesc('id');

        // Фильтр по дате создания (failure_date)
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        if ($dateFrom) {
            $failuresQuery->whereDate('failure_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $failuresQuery->whereDate('failure_date', '<=', $dateTo);
        }

        // Фильтр по ID
        if ($request->filled('id')) {
            $failuresQuery->where('id', $request->input('id'));
        }

        // TASK CARD DESCRIPTION (описание задачи / проявление неисправности)
        if ($request->filled('task_card_description')) {
            $desc = $request->input('task_card_description');
            $failuresQuery->where(function ($q) use ($desc) {
                $q->where('aircraft_malfunction', 'like', '%' . $desc . '%')
                  ->orWhere('component_cause', 'like', '%' . $desc . '%')
                  ->orWhere('component_malfunction', 'like', '%' . $desc . '%');
            });
        }

        // TASK CARD (номер WO / work order)
        if ($request->filled('task_card')) {
            $taskCard = $request->input('task_card');
            $failuresQuery->where(function ($q) use ($taskCard) {
                $q->where('wo_number', 'like', '%' . $taskCard . '%')
                  ->orWhere('work_order_number', 'like', '%' . $taskCard . '%');
            });
        }

        // Max Hours on RC (calculated: filter by minimum value)
        if ($request->filled('max_hours_rc') && is_numeric($request->input('max_hours_rc'))) {
            $failuresQuery->havingRaw('max_hours_on_rc >= ?', [(float) $request->input('max_hours_rc')]);
        }

        // # of STR NRCs (calculated: filter by minimum value)
        if ($request->filled('num_str_nrcs') && is_numeric($request->input('num_str_nrcs'))) {
            $failuresQuery->havingRaw('num_str_nrcs >= ?', [(int) $request->input('num_str_nrcs')]);
        }

        // Пагинация
        $perPage = (int) $request->input('per_page', 100);
        if (!in_array($perPage, [10, 50, 100, 200], true)) {
            $perPage = 100;
        }
        $failures = $failuresQuery->paginate($perPage)->withQueryString();

        // Видимость полей формы отказа
        $failureFormVisibility = RelFailureFormSetting::getFieldVisibility();
        $hiddenFormFields = array_keys(array_filter($failureFormVisibility, fn (bool $v): bool => !$v));

        // Только вкладка Failures
        $tabsVisibility = ['failures' => true];
        $visibleTabs = ['failures'];
        $activeTab = 'failures';

        return view('Modules.Reliability.index', [
            'activeTab' => $activeTab,
            'aircraftList' => $aircraftList,
            'aircraftTypesForFilter' => $aircraftTypesForFilter,
            'detectionStages' => $detectionStages,
            'failureConsequences' => $failureConsequences,
            'woStatuses' => $woStatuses,
            'failureSystems' => $failureSystems,
            'failureSubsystems' => $failureSubsystems,
            'engineTypes' => $engineTypes,
            'engineNumbers' => $engineNumbers,
            'takenMeasures' => $takenMeasures,
            'aggregateTypes' => $aggregateTypes,
            'failures' => $failures,
            'failureFormVisibility' => $failureFormVisibility,
            'hiddenFormFields' => $hiddenFormFields,
            'tabsVisibility' => $tabsVisibility,
        ]);
    }

    /**
     * Применить фильтр по одному или нескольким значениям (массив из request).
     */
    private function applyMultiFilter($query, Request $request, string $paramName, string $column, string $cast = null): void
    {
        $v = $request->input($paramName);
        if ($v === null || $v === '') {
            return;
        }
        $arr = is_array($v) ? array_values(array_filter($v)) : [$v];
        if (empty($arr)) {
            return;
        }
        if ($cast === 'int') {
            $arr = array_map('intval', array_filter($arr));
            if (empty($arr)) {
                return;
            }
        } else {
            $arr = array_map('trim', $arr);
            $arr = array_values(array_filter($arr, fn($x) => $x !== ''));
            if (empty($arr)) {
                return;
            }
        }
        $query->whereIn($column, $arr);
    }

    /**
     * Справочники и настройки для формы создания/редактирования отказа
     */
    private function getFailureFormReferenceData(): array
    {
        $aircraftList = \App\Models\Aircraft::query()
            ->orderBy('RegN')
            ->get(['id', 'RegN', 'Type', 'Date_manufacture', 'FactoryNumber', 'type_code', 'modification_code']);

        $detectionStages = RelFailureDetectionStage::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $failureConsequences = RelFailureConsequence::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $woStatuses = RelWoStatus::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $failureSystems = RelFailureSystem::select('system_name')
            ->distinct()
            ->orderBy('system_name')
            ->get();

        $failureSubsystems = RelFailureSystem::whereNotNull('subsystem_name')
            ->orderBy('system_name')
            ->orderBy('subsystem_name')
            ->get();

        $engineTypes = RelEngineType::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $engineNumbers = RelEngineNumber::where('active', true)
            ->with('engineType')
            ->orderBy('number')
            ->get();

        $takenMeasures = RelTakenMeasure::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $aircraftTypes = \App\Models\AircraftsType::orderBy('name_rus')->get(['id', 'name_rus', 'icao']);

        $failureFormVisibility = RelFailureFormSetting::getFieldVisibility();
        $hiddenFormFields = array_keys(array_filter($failureFormVisibility, fn (bool $v): bool => !$v));

        return [
            'aircraftList' => $aircraftList,
            'detectionStages' => $detectionStages,
            'failureConsequences' => $failureConsequences,
            'woStatuses' => $woStatuses,
            'failureSystems' => $failureSystems,
            'failureSubsystems' => $failureSubsystems,
            'engineTypes' => $engineTypes,
            'engineNumbers' => $engineNumbers,
            'takenMeasures' => $takenMeasures,
            'aircraftTypes' => $aircraftTypes,
            'hiddenFormFields' => $hiddenFormFields,
        ];
    }

    /**
     * Dashboards: таблица по заказчикам, бар-чарт Task/Manhours по типу карты, круговые по ATA (Routine / Nonroutine).
     * Агрегации в SQL + chunk для pie — без загрузки всех строк (память при больших импортах).
     */
    public function dashboards(Request $request)
    {
        $projectFilter = $request->input('project', $request->input('customer', 'all'));
        $customerFilter = $request->input('customer_name', 'all');
        $aircraftTypeFilter = $request->input('aircraft_type', 'all');
        $tailFilter = $request->input('tail_number', 'all');
        $msnFilter = $request->input('msn', 'all');

        $useMaster = ! $this->dashboardWorkCardsHasRows($projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter);
        $table = $useMaster ? 'work_cards_master' : 'work_cards';

        $aggRows = $this->dashboardCustomerAggregateQuery($table, $useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter)->get();

        $projects = collect($aggRows)->map(function ($row) {
            $name = $row->cn;
            $trimName = trim((string) $name);
            $eefCount = InspectionEefRegistry::query()
                ->where(function ($q) use ($name, $trimName) {
                    $q->where('customer_name', $name)
                        ->orWhereRaw('TRIM(COALESCE(project_no, \'\')) = ?', [$trimName]);
                })
                ->count();

            return [
                'project' => $name,
                'project_count' => (int) $row->project_count,
                'task' => (int) $row->task,
                'mhrs' => round((float) $row->mhrs, 0),
                'eef' => $eefCount ?: null,
            ];
        })->values();

        $totalTask = $projects->sum('task');
        $totalMhrs = $projects->sum('mhrs');
        $totalEef = $projects->sum(fn ($c) => $c['eef'] ?? 0);

        $barChart = $this->dashboardBarChartAggregates($table, $useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter);

        $ataLabel = function ($ata, $prim) {
            $ata = trim((string) $ata);
            $prim = trim((string) $prim);
            if ($ata !== '' && $prim !== '') {
                $ch = preg_replace('/[^0-9]/', '', substr($ata, 0, 2));
                if ($ch !== '') {
                    return $ch . ' + ' . (str_contains(strtoupper($prim), 'STR') ? 'STR' : (str_contains(strtoupper($prim), 'INT') ? 'INT' : (str_contains(strtoupper($prim), 'PROP') ? 'PROP' : 'OTHER')));
                }
            }

            return $ata !== '' ? $ata : 'SYSTEMS';
        };

        $routineByTrade = $this->dashboardTradeLabelCounts($useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter, true, $ataLabel);
        $nonroutineByTrade = $this->dashboardTradeLabelCounts($useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter, false, $ataLabel);

        $projectList = $projects->pluck('project')->unique()->filter()->values()->all();
        $customerList = $this->dashboardDistinctCustomerList($table, $useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter);
        $aircraftTypes = $this->dashboardDistinctColumn($table, $useMaster, 'aircraft_type', $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter);
        $tailNumbers = $this->dashboardDistinctColumn($table, $useMaster, 'tail_number', $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter);
        $msnList = $this->dashboardDistinctMsnList($table, $useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter);

        return view('Modules.Reliability.dashboards', [
            'projects' => $projects,
            'totalTask' => $totalTask,
            'totalMhrs' => $totalMhrs,
            'totalEef' => $totalEef,
            'totalProjectCount' => $projects->sum('project_count'),
            'barChart' => $barChart,
            'routineByTrade' => $routineByTrade,
            'nonroutineByTrade' => $nonroutineByTrade,
            'projectList' => $projectList,
            'customerList' => $customerList,
            'aircraftTypes' => $aircraftTypes,
            'tailNumbers' => $tailNumbers,
            'msnList' => $msnList,
            'selectedProject' => $projectFilter,
            'selectedCustomer' => $customerFilter,
            'selectedAircraftType' => $aircraftTypeFilter,
            'selectedTailNumber' => $tailFilter,
            'selectedMsn' => $msnFilter,
        ]);
    }

    private function dashboardWorkCardsHasRows(string $projectFilter, string $customerFilter, string $aircraftTypeFilter, string $tailFilter, string $msnFilter): bool
    {
        $q = InspectionWorkCard::query();
        $this->dashboardApplyFiltersEloquent($q, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter, false);
        // Если в work_cards нет PROJECT, дашборды должны брать unified master-data.
        $q->whereRaw("TRIM(COALESCE(project, '')) <> ''");

        return $q->exists();
    }

    private function dashboardBaseTableQuery(
        string $table,
        bool $useMaster,
        string $projectFilter,
        string $customerFilter,
        string $aircraftTypeFilter,
        string $tailFilter,
        string $msnFilter,
    ): QueryBuilder {
        $q = DB::table($table);
        $this->dashboardApplyFiltersQuery($q, $table, $useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter);

        return $q;
    }

    /**
     * Агрегация по «заказчику» с ONLY_FULL_GROUP_BY: внутренний подзапрос выносит cn / project_key / act_val,
     * внешний — COUNT(*) и COUNT(DISTINCT project_key) только по алиасам.
     */
    private function dashboardCustomerAggregateQuery(
        string $table,
        bool $useMaster,
        string $projectFilter,
        string $customerFilter,
        string $aircraftTypeFilter,
        string $tailFilter,
        string $msnFilter,
    ): QueryBuilder {
        $projectExpr = "COALESCE(NULLIF(TRIM(project), ''), '—')";

        $sub = $this->dashboardBaseTableQuery($table, $useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter)
            ->selectRaw("{$projectExpr} AS cn, NULLIF(TRIM(project), '') AS project_key, COALESCE(act_time, 0) AS act_val");

        return DB::query()
            ->fromSub($sub, 'd')
            ->selectRaw('d.cn, COUNT(*) AS task, COUNT(DISTINCT d.project_key) AS project_count, COALESCE(SUM(d.act_val), 0) AS mhrs')
            ->groupBy('d.cn');
    }

    private function dashboardApplyFiltersQuery(
        QueryBuilder $q,
        string $table,
        bool $useMaster,
        string $projectFilter,
        string $customerFilter,
        string $aircraftTypeFilter,
        string $tailFilter,
        string $msnFilter,
    ): void {
        if ($projectFilter !== 'all') {
            $q->whereRaw("COALESCE(NULLIF(TRIM(project), ''), '—') = ?", [$projectFilter]);
        }
        if ($customerFilter !== 'all') {
            $q->whereExists(function ($sub) use ($table, $customerFilter) {
                $sub->selectRaw('1')
                    ->from('projects')
                    ->whereRaw("UPPER(TRIM(COALESCE(project_number, ''))) = UPPER(TRIM(COALESCE({$table}.project, '')))")
                    ->whereRaw("COALESCE(NULLIF(TRIM(customer_name), ''), '—') = ?", [$customerFilter]);
            });
        }
        if ($aircraftTypeFilter !== 'all') {
            $q->where('aircraft_type', $aircraftTypeFilter);
        }
        if ($tailFilter !== 'all') {
            $q->where('tail_number', $tailFilter);
        }
        if ($msnFilter !== 'all') {
            $q->whereExists(function ($sub) use ($table, $msnFilter) {
                $sub->selectRaw('1')
                    ->from('aircrafts')
                    ->whereRaw("UPPER(TRIM(COALESCE(aircrafts.tail_number, ''))) = UPPER(TRIM(COALESCE({$table}.tail_number, '')))")
                    ->whereRaw("COALESCE(NULLIF(TRIM(aircrafts.serial_number), ''), '—') = ?", [$msnFilter]);
            });
        }
    }

    /** @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $q */
    private function dashboardApplyFiltersEloquent($q, string $projectFilter, string $customerFilter, string $aircraftTypeFilter, string $tailFilter, string $msnFilter, bool $useMaster): void
    {
        $q->when($projectFilter !== 'all', fn ($qq) => $qq->whereRaw("COALESCE(NULLIF(TRIM(project), ''), '—') = ?", [$projectFilter]));
        if ($customerFilter !== 'all') {
            $table = $q->getModel()->getTable();
            $q->whereExists(function ($sub) use ($table, $customerFilter) {
                $sub->selectRaw('1')
                    ->from('projects')
                    ->whereRaw("UPPER(TRIM(COALESCE(project_number, ''))) = UPPER(TRIM(COALESCE({$table}.project, '')))")
                    ->whereRaw("COALESCE(NULLIF(TRIM(customer_name), ''), '—') = ?", [$customerFilter]);
            });
        }
        if ($aircraftTypeFilter !== 'all') {
            $q->where('aircraft_type', $aircraftTypeFilter);
        }
        if ($tailFilter !== 'all') {
            $q->where('tail_number', $tailFilter);
        }
        if ($msnFilter !== 'all') {
            $table = $q->getModel()->getTable();
            $q->whereExists(function ($sub) use ($table, $msnFilter) {
                $sub->selectRaw('1')
                    ->from('aircrafts')
                    ->whereRaw("UPPER(TRIM(COALESCE(aircrafts.tail_number, ''))) = UPPER(TRIM(COALESCE({$table}.tail_number, '')))")
                    ->whereRaw("COALESCE(NULLIF(TRIM(aircrafts.serial_number), ''), '—') = ?", [$msnFilter]);
            });
        }
    }

    /**
     * @return array{labels: list<string>, manhours: list<float>, task: list<int>}
     */
    private function dashboardBarChartAggregates(
        string $table,
        bool $useMaster,
        string $projectFilter,
        string $customerFilter,
        string $aircraftTypeFilter,
        string $tailFilter,
        string $msnFilter,
    ): array {
        $base = $this->dashboardBaseTableQuery($table, $useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter);
        $routineSql = "NOT (UPPER(COALESCE(order_type, '')) LIKE '%NON%' OR UPPER(COALESCE(order_type, '')) LIKE '%NRC%')";
        $nonroutineSql = "(UPPER(COALESCE(order_type, '')) LIKE '%NON%' OR UPPER(COALESCE(order_type, '')) LIKE '%NRC%')";

        $rTask = (int) $base->clone()->whereRaw($routineSql)->count();
        $nTask = (int) $base->clone()->whereRaw($nonroutineSql)->count();
        $rMhrs = (float) ($base->clone()->whereRaw($routineSql)->sum('act_time') ?? 0);
        $nMhrs = (float) ($base->clone()->whereRaw($nonroutineSql)->sum('act_time') ?? 0);

        if ($rTask === 0 && $nTask === 0 && ! $useMaster) {
            $tot = (int) $base->clone()->count();
            if ($tot > 0) {
                $split = $this->dashboardBarSplitByPrimSkill($projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter);
                $rTask = $split['r_task'];
                $nTask = $split['n_task'];
                $rMhrs = $split['r_mhrs'];
                $nMhrs = $split['n_mhrs'];
            }
        }

        return [
            'labels' => ['Routine', 'Nonroutine'],
            'manhours' => [$rMhrs, $nMhrs],
            'task' => [$rTask, $nTask],
        ];
    }

    /**
     * Резерв, если order_type не даёт ни routine, ни nonroutine (редко).
     *
     * @return array{r_task: int, n_task: int, r_mhrs: float, n_mhrs: float}
     */
    private function dashboardBarSplitByPrimSkill(string $projectFilter, string $customerFilter, string $aircraftTypeFilter, string $tailFilter, string $msnFilter): array
    {
        $rTask = 0;
        $nTask = 0;
        $rMhrs = 0.0;
        $nMhrs = 0.0;
        $q = InspectionWorkCard::query();
        $this->dashboardApplyFiltersEloquent($q, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter, false);
        $q->select(['id', 'act_time', 'prim_skill'])->chunkById(4000, function ($rows) use (&$rTask, &$nTask, &$rMhrs, &$nMhrs) {
            foreach ($rows as $r) {
                $mh = $this->dashboardParseActTime($r->act_time ?? null);
                if (stripos((string) $r->prim_skill, 'STR') !== false) {
                    $nTask++;
                    $nMhrs += $mh;
                } else {
                    $rTask++;
                    $rMhrs += $mh;
                }
            }
        });

        return ['r_task' => $rTask, 'n_task' => $nTask, 'r_mhrs' => $rMhrs, 'n_mhrs' => $nMhrs];
    }

    private function dashboardParseActTime(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        $s = trim((string) $value);
        if ($s === '') {
            return 0.0;
        }
        $s = str_replace(',', '.', preg_replace('/[^\d,.-]/', '', $s));
        if ($s === '' || ! is_numeric($s)) {
            return 0.0;
        }

        return (float) $s;
    }

    private function dashboardTradeLabelCounts(
        bool $useMaster,
        string $projectFilter,
        string $customerFilter,
        string $aircraftTypeFilter,
        string $tailFilter,
        string $msnFilter,
        bool $routineBucket,
        callable $ataLabel,
    ): Collection {
        $model = $useMaster ? ReliabilityMasterData::class : InspectionWorkCard::class;
        $q = $model::query();
        $this->dashboardApplyFiltersEloquent($q, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter, $useMaster);
        $routineSql = "NOT (UPPER(COALESCE(order_type, '')) LIKE '%NON%' OR UPPER(COALESCE(order_type, '')) LIKE '%NRC%')";
        $nonroutineSql = "(UPPER(COALESCE(order_type, '')) LIKE '%NON%' OR UPPER(COALESCE(order_type, '')) LIKE '%NRC%')";
        $q->whereRaw($routineBucket ? $routineSql : $nonroutineSql);
        if ($useMaster) {
            $q->select(['id', 'ata', 'order_type']);
        } else {
            $q->select(['id', 'ata', 'prim_skill', 'order_type']);
        }

        $counts = [];
        $q->chunkById(4000, function ($rows) use (&$counts, $ataLabel, $useMaster) {
            foreach ($rows as $r) {
                $prim = $useMaster ? '' : (string) ($r->prim_skill ?? '');
                $k = $ataLabel($r->ata, $prim);
                $counts[$k] = ($counts[$k] ?? 0) + 1;
            }
        });

        return collect($counts)->sortDesc();
    }

    /**
     * @return list<string>
     */
    private function dashboardDistinctColumn(
        string $table,
        bool $useMaster,
        string $column,
        string $projectFilter,
        string $customerFilter,
        string $aircraftTypeFilter,
        string $tailFilter,
        string $msnFilter,
    ): array {
        return $this->dashboardBaseTableQuery($table, $useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, $msnFilter)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function dashboardDistinctMsnList(
        string $table,
        bool $useMaster,
        string $projectFilter,
        string $customerFilter,
        string $aircraftTypeFilter,
        string $tailFilter,
        string $msnFilter,
    ): array {
        $tailSub = $this->dashboardBaseTableQuery($table, $useMaster, $projectFilter, $customerFilter, $aircraftTypeFilter, $tailFilter, 'all')
            ->selectRaw("DISTINCT UPPER(TRIM(COALESCE(tail_number, ''))) AS tail_number")
            ->whereRaw("TRIM(COALESCE(tail_number, '')) <> ''");

        return DB::table('aircrafts')
            ->joinSub($tailSub, 'src', function ($join) {
                $join->whereRaw("UPPER(TRIM(COALESCE(aircrafts.tail_number, ''))) = src.tail_number");
            })
            ->when($msnFilter !== 'all', fn ($q) => $q->whereRaw("COALESCE(NULLIF(TRIM(aircrafts.serial_number), ''), '—') = ?", [$msnFilter]))
            ->selectRaw("COALESCE(NULLIF(TRIM(aircrafts.serial_number), ''), '—') AS msn")
            ->distinct()
            ->orderBy('msn')
            ->pluck('msn')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function dashboardDistinctCustomerList(
        string $table,
        bool $useMaster,
        string $projectFilter,
        string $customerFilter,
        string $aircraftTypeFilter,
        string $tailFilter,
        string $msnFilter,
    ): array {
        $projectSub = $this->dashboardBaseTableQuery($table, $useMaster, $projectFilter, 'all', $aircraftTypeFilter, $tailFilter, $msnFilter)
            ->selectRaw("DISTINCT UPPER(TRIM(COALESCE(project, ''))) AS project_number");

        return InspectionProject::query()
            ->fromSub($projectSub, 'src')
            ->join('projects', function ($join) {
                $join->whereRaw("UPPER(TRIM(COALESCE(projects.project_number, ''))) = src.project_number");
            })
            ->when($customerFilter !== 'all', fn ($q) => $q->whereRaw("COALESCE(NULLIF(TRIM(projects.customer_name), ''), '—') = ?", [$customerFilter]))
            ->selectRaw("COALESCE(NULLIF(TRIM(projects.customer_name), ''), '—') AS customer_name")
            ->distinct()
            ->orderBy('customer_name')
            ->pluck('customer_name')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Страница создания отказа
     */
    public function createFailureForm()
    {
        $data = $this->getFailureFormReferenceData();
        $data['failure'] = null;

        return view('Modules.Reliability.failures.form', $data);
    }

    /**
     * Страница редактирования отказа
     */
    public function editFailureForm(int $id)
    {
        $failure = ReliabilityFailure::with(['detectionStage', 'consequence', 'attachments'])->findOrFail($id);

        // Подставляем type_code и modification_code из справочника ВС
        if (!empty($failure->aircraft_number)) {
            $aircraft = \App\Models\Aircraft::where('RegN', $failure->aircraft_number)->first();
            if ($aircraft) {
                $failure->setAttribute('type_code', $aircraft->type_code);
                $failure->setAttribute('modification_code', $aircraft->modification_code);
            }
        }

        $data = $this->getFailureFormReferenceData();
        $data['failure'] = $failure;

        return view('Modules.Reliability.failures.form', $data);
    }

    /**
     * Загрузка файла к отказу.
     */
    public function uploadFailureAttachment(Request $request, int $id)
    {
        $failure = ReliabilityFailure::findOrFail($id);

        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $file = $request->file('file');
        $path = $file->store("rel_failures/{$id}", ['disk' => 'public']);

        $maxOrder = (int) RelFailureAttachment::where('failure_id', $id)->max('sort_order');
        $att = RelFailureAttachment::create([
            'failure_id' => $id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'file_id' => (string) $att->id,
            'file' => [
                'id' => (string) $att->id,
                'path' => $att->path,
                'name' => $att->original_name,
                'type' => $att->mime_type,
                'size' => $att->size,
            ],
        ]);
    }

    /**
     * Удаление файла отказа.
     */
    public function deleteFailureAttachment(Request $request, int $id)
    {
        $failure = ReliabilityFailure::findOrFail($id);

        $request->validate([
            'file_id' => 'required',
        ]);

        $fileId = $request->input('file_id');
        $att = RelFailureAttachment::where('failure_id', $id)->where('id', $fileId)->first();

        if (!$att) {
            return response()->json(['success' => false, 'message' => 'Файл не найден'], 404);
        }

        if (Storage::disk('public')->exists($att->path)) {
            Storage::disk('public')->delete($att->path);
        }
        $att->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Просмотр файла отказа (inline).
     */
    public function serveFailureAttachment(Request $request, int $id)
    {
        $failure = ReliabilityFailure::findOrFail($id);
        $path = $request->query('path');
        if (!$path || !is_string($path)) {
            abort(404);
        }
        if (str_contains($path, '..')) {
            abort(404);
        }
        $prefix = "rel_failures/{$id}/";
        if (strpos($path, $prefix) !== 0) {
            abort(404);
        }
        $att = RelFailureAttachment::where('failure_id', $id)->where('path', $path)->first();
        if (!$att || !Storage::disk('public')->exists($path)) {
            abort(404);
        }
        return Storage::disk('public')->response($path, $att->original_name);
    }

    /**
     * Скачивание файла отказа.
     */
    public function downloadFailureAttachment(Request $request, int $id)
    {
        $failure = ReliabilityFailure::findOrFail($id);
        $path = $request->query('path');
        if (!$path || !is_string($path)) {
            abort(404);
        }
        if (str_contains($path, '..')) {
            abort(404);
        }
        $prefix = "rel_failures/{$id}/";
        if (strpos($path, $prefix) !== 0) {
            abort(404);
        }
        $att = RelFailureAttachment::where('failure_id', $id)->where('path', $path)->first();
        if (!$att || !Storage::disk('public')->exists($path)) {
            abort(404);
        }
        return Storage::disk('public')->download($path, $att->original_name);
    }

    /**
     * Получение данных отказа (AJAX, например для экспорта)
     */
    public function getFailure(int $id)
    {
        $failure = ReliabilityFailure::with(['detectionStage', 'consequence'])->findOrFail($id);
        $data = $failure->toArray();

        // Подставляем тип ВС (код) и модификацию (код) из справочника ВС по бортовому номеру
        if (!empty($failure->aircraft_number)) {
            $aircraft = \App\Models\Aircraft::where('RegN', $failure->aircraft_number)->first();
            if ($aircraft) {
                $data['type_code'] = $aircraft->type_code;
                $data['modification_code'] = $aircraft->modification_code;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Обновление отказа (редактирование из модального окна)
     */
    public function updateFailure(Request $request, int $id)
    {
        $failure = ReliabilityFailure::findOrFail($id);

        $data = $request->validate([
            'account_number' => ['nullable', 'string', 'max:100'],
            'failure_date' => ['nullable', 'date'],
            'aircraft_number' => ['nullable', 'string', 'max:50'],
            'aircraft_type' => ['nullable', 'string', 'max:100'],
            'aircraft_serial' => ['nullable', 'string', 'max:100'],
            'aircraft_manufacture_date' => ['nullable', 'date'],
            'aircraft_hours' => ['nullable', 'numeric'],
            'aircraft_landings' => ['nullable', 'integer'],
            'aircraft_ppr_hours' => ['nullable', 'numeric'],
            'aircraft_ppr_landings' => ['nullable', 'integer'],
            'aircraft_repair_date' => ['nullable', 'date'],
            'previous_repair_location' => ['nullable', 'string', 'max:255'],
            'aircraft_repairs_count' => ['nullable', 'integer'],
            'operator' => ['nullable', 'string', 'max:255'],
            'detection_stage' => ['nullable', 'integer'],
            'aircraft_malfunction' => ['nullable', 'string'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'consequence_id' => ['nullable', 'integer'],
            'wo_number' => ['nullable', 'string', 'max:100'],
            'wo_status_id' => ['nullable', 'integer'],
            'work_order_number' => ['nullable', 'string', 'max:100'],
            'mpd' => ['nullable', 'string', 'max:255'],
            'system_name' => ['nullable', 'string', 'max:255'],
            'subsystem_name' => ['nullable', 'string', 'max:255'],
            'component_malfunction' => ['nullable', 'string'],
            'component_cause' => ['nullable', 'string'],
            'taken_measure_id' => ['nullable', 'integer'],
            'resolution_method' => ['nullable', 'string', 'max:100'],
            'resolution_date' => ['nullable', 'date'],
            'aggregate_type' => ['nullable', 'string', 'max:100'],
            'part_number_off' => ['nullable', 'string', 'max:100'],
            'component_serial' => ['nullable', 'string', 'max:100'],
            'part_number_on' => ['nullable', 'string', 'max:100'],
            'serial_number_on' => ['nullable', 'string', 'max:100'],
            'component_hours_unit' => ['nullable', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'removal_date' => ['nullable', 'date'],
            'component_sne_hours' => ['nullable', 'numeric'],
            'component_ppr_hours' => ['nullable', 'numeric'],
            'production_date' => ['nullable', 'date'],
            'component_repairs_count' => ['nullable', 'integer'],
            'previous_installation_date' => ['nullable', 'date'],
            'repair_factory' => ['nullable', 'string', 'max:255'],
            'component_repair_date' => ['nullable', 'date'],
            'engine_type_id' => ['nullable', 'integer'],
            'engine_number_id' => ['nullable', 'integer'],
            'engine_release_date' => ['nullable', 'date'],
            'engine_installation_date' => ['nullable', 'date'],
            'engine_sne_hours' => ['nullable', 'numeric'],
            'engine_ppr_hours' => ['nullable', 'numeric'],
            'engine_sne_cycles' => ['nullable', 'numeric'],
            'engine_ppr_cycles' => ['nullable', 'numeric'],
            'engine_repair_date' => ['nullable', 'date'],
            'engine_repair_location' => ['nullable', 'string', 'max:255'],
            'engine_repairs_count' => ['nullable', 'integer'],
            'owner' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        // Объединяем с существующей записью: обновляем только переданные поля (форма может содержать только 3 поля)
        $payload = array_merge(
            $failure->only($failure->getFillable()),
            [
                'account_number' => $data['account_number'] ?? $failure->account_number,
                'failure_date' => $data['failure_date'] ?? $failure->failure_date,
                'aircraft_number' => $data['aircraft_number'] ?? $failure->aircraft_number,
                'aircraft_type' => $data['aircraft_type'] ?? $failure->aircraft_type,
                'aircraft_serial' => $data['aircraft_serial'] ?? $failure->aircraft_serial,
                'aircraft_manufacture_date' => $data['aircraft_manufacture_date'] ?? $failure->aircraft_manufacture_date,
                'aircraft_hours' => $data['aircraft_hours'] ?? $failure->aircraft_hours,
                'aircraft_landings' => $data['aircraft_landings'] ?? $failure->aircraft_landings,
                'aircraft_ppr_hours' => $data['aircraft_ppr_hours'] ?? $failure->aircraft_ppr_hours,
                'aircraft_ppr_landings' => $data['aircraft_ppr_landings'] ?? $failure->aircraft_ppr_landings,
                'aircraft_repair_date' => $data['aircraft_repair_date'] ?? $failure->aircraft_repair_date,
                'previous_repair_location' => $data['previous_repair_location'] ?? $failure->previous_repair_location,
                'aircraft_repairs_count' => $data['aircraft_repairs_count'] ?? $failure->aircraft_repairs_count,
                'operator' => $data['operator'] ?? $failure->operator,
                'detection_stage_id' => $data['detection_stage'] ?? $failure->detection_stage_id,
                'aircraft_malfunction' => array_key_exists('aircraft_malfunction', $data) ? $data['aircraft_malfunction'] : $failure->aircraft_malfunction,
                'event_location' => $data['event_location'] ?? $failure->event_location,
                'consequence_id' => $data['consequence_id'] ?? $failure->consequence_id,
                'wo_number' => $data['wo_number'] ?? $failure->wo_number,
                'wo_status_id' => $data['wo_status_id'] ?? $failure->wo_status_id,
                'work_order_number' => array_key_exists('work_order_number', $data) ? $data['work_order_number'] : $failure->work_order_number,
                'mpd' => array_key_exists('mpd', $data) ? $data['mpd'] : $failure->mpd,
                'system_name' => $data['system_name'] ?? $failure->system_name,
                'subsystem_name' => $data['subsystem_name'] ?? $failure->subsystem_name,
                'component_malfunction' => $data['component_malfunction'] ?? $failure->component_malfunction,
                'component_cause' => $data['component_cause'] ?? $failure->component_cause,
                'taken_measure_id' => $data['taken_measure_id'] ?? $failure->taken_measure_id,
                'resolution_method' => $data['resolution_method'] ?? $failure->resolution_method,
                'resolution_date' => $data['resolution_date'] ?? $failure->resolution_date,
                'aggregate_type' => $data['aggregate_type'] ?? $failure->aggregate_type,
                'part_number_off' => $data['part_number_off'] ?? $failure->part_number_off,
                'component_serial' => $data['component_serial'] ?? $failure->component_serial,
                'part_number_on' => $data['part_number_on'] ?? $failure->part_number_on,
                'serial_number_on' => $data['serial_number_on'] ?? $failure->serial_number_on,
                'component_hours_unit' => $data['component_hours_unit'] ?? $failure->component_hours_unit,
                'manufacturer' => $data['manufacturer'] ?? $failure->manufacturer,
                'removal_date' => $data['removal_date'] ?? $failure->removal_date,
                'component_sne_hours' => $data['component_sne_hours'] ?? $failure->component_sne_hours,
                'component_ppr_hours' => $data['component_ppr_hours'] ?? $failure->component_ppr_hours,
                'production_date' => $data['production_date'] ?? $failure->production_date,
                'component_repairs_count' => $data['component_repairs_count'] ?? $failure->component_repairs_count,
                'previous_installation_date' => $data['previous_installation_date'] ?? $failure->previous_installation_date,
                'repair_factory' => $data['repair_factory'] ?? $failure->repair_factory,
                'component_repair_date' => $data['component_repair_date'] ?? $failure->component_repair_date,
                'engine_type_id' => $data['engine_type_id'] ?? $failure->engine_type_id,
                'engine_number_id' => $data['engine_number_id'] ?? $failure->engine_number_id,
                'engine_release_date' => $data['engine_release_date'] ?? $failure->engine_release_date,
                'engine_installation_date' => $data['engine_installation_date'] ?? $failure->engine_installation_date,
                'engine_sne_hours' => $data['engine_sne_hours'] ?? $failure->engine_sne_hours,
                'engine_ppr_hours' => $data['engine_ppr_hours'] ?? $failure->engine_ppr_hours,
                'engine_sne_cycles' => $data['engine_sne_cycles'] ?? $failure->engine_sne_cycles,
                'engine_ppr_cycles' => $data['engine_ppr_cycles'] ?? $failure->engine_ppr_cycles,
                'engine_repair_date' => $data['engine_repair_date'] ?? $failure->engine_repair_date,
                'engine_repair_location' => $data['engine_repair_location'] ?? $failure->engine_repair_location,
                'engine_repairs_count' => $data['engine_repairs_count'] ?? $failure->engine_repairs_count,
                'owner' => $data['owner'] ?? $failure->owner,
                'position' => $data['position'] ?? $failure->position,
            ]
        );

        $failure->update($payload);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Отказ успешно обновлён.',
            ]);
        }

        return redirect()
            ->route('modules.reliability.failures.edit', $id)
            ->with('success', 'Отказ успешно обновлён.');
    }

    /**
     * Обновить флаг «В отчёт» (include_in_buf) для отказа (AJAX).
     */
    public function updateIncludeInBuf(Request $request, int $id)
    {
        $failure = ReliabilityFailure::findOrFail($id);
        $value = $request->boolean('include_in_buf');
        $failure->include_in_buf = $value;
        $failure->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'include_in_buf' => $failure->include_in_buf]);
        }

        return redirect()->back();
    }

    /**
     * Асинхронная подгрузка агрегатов для фильтра по выбранной системе и подсистеме
     */
    public function getAggregatesForFilter(Request $request)
    {
        $data = $request->validate([
            'system' => ['required', 'string', 'max:255'],
            'subsystem' => ['required', 'string', 'max:255'],
        ]);

        $subsystemsForFilter = RelFailureSystem::where('system_name', $data['system'])
            ->where('subsystem_name', $data['subsystem'])
            ->pluck('id');

        if ($subsystemsForFilter->isEmpty()) {
            return response()->json([
                'success' => true,
                'aggregates' => [],
            ]);
        }

        $aggregates = \App\Models\RelFailureAggregate::whereIn('failure_system_id', $subsystemsForFilter)
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'aggregates' => $aggregates,
        ]);
    }

    /**
     * Агрегаты для модального окна выбора: все / не в составе систем / по системе+подсистеме, поиск
     */
    public function getAggregatesForModal(Request $request)
    {
        $system = (string) ($request->input('system') ?? '');
        $subsystem = (string) ($request->input('subsystem') ?? '');
        $search = trim((string) ($request->input('search') ?? ''));
        $aircraftTypeId = $request->input('aircraft_type_id') ? (int) $request->input('aircraft_type_id') : null;

        $query = \App\Models\RelFailureAggregate::where('active', true);

        if ($system === '__free__') {
            $query->whereNull('failure_system_id');
        } elseif ($system !== '' && $subsystem !== '') {
            $subsystemsForFilter = RelFailureSystem::where('system_name', $system)
                ->where('subsystem_name', $subsystem)
                ->pluck('id');
            if ($subsystemsForFilter->isEmpty()) {
                return response()->json(['success' => true, 'aggregates' => []]);
            }
            $query->whereIn('failure_system_id', $subsystemsForFilter);
        }

        if ($aircraftTypeId !== null) {
            $query->where('aircraft_type_id', $aircraftTypeId);
        }

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $aggregates = $query->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'aggregates' => $aggregates,
        ]);
    }

    /**
     * Создание агрегата из модального окна выбора (JSON).
     */
    public function storeAggregateFromModal(Request $request)
    {
        $data = $request->validate([
            'aggregate_code' => ['nullable', 'string', 'max:50'],
            'aggregate_name_display' => ['required', 'string', 'max:255'],
            'aircraft_type_id' => ['nullable', 'integer', 'exists:aircrafts_types,id'],
            'failure_system_id' => ['nullable', 'integer'],
        ]);

        $code = trim((string) ($data['aggregate_code'] ?? ''));
        $name = trim((string) $data['aggregate_name_display']);
        $fullName = $code !== '' ? ($code . ' - ' . $name) : $name;

        $failureSystemId = !empty($data['failure_system_id']) ? (int) $data['failure_system_id'] : null;
        $aircraftTypeId = !empty($data['aircraft_type_id']) ? (int) $data['aircraft_type_id'] : null;

        $sortOrderQuery = \App\Models\RelFailureAggregate::query();
        if ($failureSystemId) {
            $sortOrderQuery->where('failure_system_id', $failureSystemId);
        } else {
            $sortOrderQuery->whereNull('failure_system_id');
        }
        $sortOrder = (int) ($sortOrderQuery->max('sort_order') ?? 0) + 1;

        $aggregate = \App\Models\RelFailureAggregate::create([
            'failure_system_id' => $failureSystemId,
            'name' => $fullName,
            'description' => null,
            'active' => true,
            'sort_order' => $sortOrder,
            'aircraft_type_id' => $aircraftTypeId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Агрегат добавлен',
            'aggregate' => ['id' => $aggregate->id, 'name' => $aggregate->name],
        ]);
    }

    /**
     * Выгрузка карты учета неисправности авиатехники в Excel
     */
    public function exportFailureCard($id)
    {
        // Здесь будет получение данных отказа из базы данных
        // Пока используем тестовые данные
        $failureData = [
            'id' => $id,
            'account_number' => '',
            'failure_date' => date('d.m.Y'),
            'aircraft_number' => 'RA-24286',
            'aircraft_type' => 'Ми-8АМТ',
            'aircraft_serial' => '12345',
            'aircraft_manufacture_date' => '01.01.2010',
            'aircraft_hours' => '17289.9',
            'aircraft_landings' => '34287',
            'aircraft_ppr_hours' => '3118.6',
            'aircraft_ppr_landings' => '4815',
            'aircraft_repair_date' => '24.12.2021',
            'previous_repair_location' => 'АО ЮТЭЙР-ИНЖИНИРИНГ',
            'aircraft_repairs_count' => '8',
            'operator' => 'ЮТ-ВУ',
            'detection_stage' => 'ОПЕРАТИВНОЕ ТО',
            'aircraft_malfunction' => 'УСТНОЕ ЗАМЕЧАНИЕ ЭКИПАЖА: НЕ ГОРИТ ЛАМП КОТРОЛЯ ИСПРАВНОСТИ ЛЕВОГО ПВД-6М',
            'event_location' => '',
            'consequences' => '',
            'wo_number' => '',
            'wo_status' => '',
            'work_order_number' => '',
            'system' => '',
            'subsystem' => '',
            'component_malfunction' => '',
            'component_cause' => '',
            'taken_measures' => '',
            'resolution_method' => '',
            'resolution_date' => '',
            'aggregate_type' => '',
            'component_serial' => '',
            'manufacturer' => '',
            'removal_date' => '',
            'component_sne_hours' => '',
            'component_ppr_hours' => '',
            'production_date' => '',
            'component_repairs_count' => '',
            'previous_installation_date' => '',
            'repair_factory' => '',
            'component_repair_date' => '',
            'engine_type' => '',
            'engine_number' => '',
            'engine_release_date' => '',
            'engine_installation_date' => '',
            'engine_sne_hours' => '',
            'engine_ppr_hours' => '',
            'engine_sne_cycles' => '',
            'engine_ppr_cycles' => '',
            'engine_repair_date' => '',
            'engine_repair_location' => '',
            'engine_repairs_count' => '',
            'owner' => '',
            'position' => '',
            'created_by' => auth()->user()->name ?? '',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Заголовок
        $sheet->setCellValue('A1', 'КАРТОЧКА УЧЕТА НЕИСПРАВНОСТИ АВИАТЕХНИКИ');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        $row = 3;
        
        // Раздел: Информация о ВС
        $sheet->setCellValue('A' . $row, 'ИНФОРМАЦИЯ О ВОЗДУШНОМ СУДНЕ');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D3D3D3'],
            ],
        ]);
        $row++;
        
        $fields = [
            ['Учетный номер (КУН)', 'account_number'],
            ['Дата обнаружения', 'failure_date'],
            ['Бортовой № ВС', 'aircraft_number'],
            ['Тип ВС', 'aircraft_type'],
            ['Заводской номер ВС', 'aircraft_serial'],
            ['Дата изготовления ВС', 'aircraft_manufacture_date'],
            ['Наработка ВС в часах', 'aircraft_hours'],
            ['Наработка ВС в посадках', 'aircraft_landings'],
            ['Наработка ВС ППР (час)', 'aircraft_ppr_hours'],
            ['Наработка ВС ППР (посадки)', 'aircraft_ppr_landings'],
            ['Дата ремонта ВС', 'aircraft_repair_date'],
            ['Место предыдущего ремонта', 'previous_repair_location'],
            ['Количество ремонтов ВС', 'aircraft_repairs_count'],
            ['Эксплуатант', 'operator'],
            ['Этап обнаружения отказа', 'detection_stage'],
            ['Проявление неисправности ВС', 'aircraft_malfunction'],
            ['Место события', 'event_location'],
            ['Последствия', 'consequences'],
        ];
        
        foreach ($fields as $field) {
            $sheet->setCellValue('A' . $row, $field[0]);
            $sheet->setCellValue('B' . $row, $failureData[$field[1]] ?? '');
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            $row++;
        }
        
        $row++;
        
        // Раздел: Информация о КИ
        $sheet->setCellValue('A' . $row, 'ИНФОРМАЦИЯ О КОМПЛЕКТУЮЩЕМ ИЗДЕЛИИ');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D3D3D3'],
            ],
        ]);
        $row++;
        
        $componentFields = [
            ['Номер WO для устранения', 'wo_number'],
            ['Статус WO', 'wo_status'],
            ['Номер карты наряд', 'work_order_number'],
            ['Система', 'system'],
            ['Подсистема', 'subsystem'],
            ['Проявление неисправности КИ', 'component_malfunction'],
            ['Причина неисправности КИ', 'component_cause'],
            ['Принятые меры', 'taken_measures'],
            ['Метод устранения', 'resolution_method'],
            ['Дата устранения', 'resolution_date'],
            ['Тип агрегата', 'aggregate_type'],
            ['Заводской № КИ', 'component_serial'],
            ['Завод изготовитель', 'manufacturer'],
            ['Дата демонтажа', 'removal_date'],
            ['Наработка СНЭ', 'component_sne_hours'],
            ['Наработка ППР', 'component_ppr_hours'],
            ['Дата производства', 'production_date'],
            ['Количество ремонтов', 'component_repairs_count'],
            ['Предыдущая дата установки агрегата', 'previous_installation_date'],
            ['Ремонтный завод', 'repair_factory'],
            ['Дата ремонта', 'component_repair_date'],
        ];
        
        foreach ($componentFields as $field) {
            $sheet->setCellValue('A' . $row, $field[0]);
            $sheet->setCellValue('B' . $row, $failureData[$field[1]] ?? '');
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            $row++;
        }
        
        $row++;
        
        // Раздел: Информация о двигателе
        $sheet->setCellValue('A' . $row, 'ИНФОРМАЦИЯ О ДВИГАТЕЛЕ');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D3D3D3'],
            ],
        ]);
        $row++;
        
        $engineFields = [
            ['Тип двигателя', 'engine_type'],
            ['Номер двигателя', 'engine_number'],
            ['Дата выпуска двигателя', 'engine_release_date'],
            ['Дата последней установки на ВС', 'engine_installation_date'],
            ['Наработка двигателя СНЭ (часы)', 'engine_sne_hours'],
            ['Наработка двигателя ППР (часы)', 'engine_ppr_hours'],
            ['Наработка двигателя СНЭ (циклы/отборы)', 'engine_sne_cycles'],
            ['Наработка двигателя ППР (циклы/отборы)', 'engine_ppr_cycles'],
            ['Дата ремонта', 'engine_repair_date'],
            ['Место ремонта', 'engine_repair_location'],
            ['Количество ремонтов', 'engine_repairs_count'],
            ['Собственник', 'owner'],
            ['Позиция', 'position'],
        ];
        
        foreach ($engineFields as $field) {
            $sheet->setCellValue('A' . $row, $field[0]);
            $sheet->setCellValue('B' . $row, $failureData[$field[1]] ?? '');
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            $row++;
        }
        
        $row++;
        
        // Подпись
        $sheet->setCellValue('A' . $row, 'Пользователь создавший КУНАТ:');
        $sheet->setCellValue('B' . $row, $failureData['created_by']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Дата создания:');
        $sheet->setCellValue('B' . $row, date('d.m.Y'));
        
        // Настройка ширины колонок
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(30);
        for ($col = 'C'; $col <= 'H'; $col++) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        
        // Выравнивание
        $sheet->getStyle('A3:A' . $row)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        $sheet->getStyle('B3:B' . $row)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Создаем файл
        $writer = new Xlsx($spreadsheet);
        $filename = 'Карточка_учета_неисправности_' . $id . '_' . date('Y-m-d') . '.xlsx';
        $filepath = storage_path('app/public/' . $filename);
        
        $writer->save($filepath);
        
        return response()->download($filepath, $filename)->deleteFileAfterSend();
    }

    /**
     * Сохранение нового отказа из модального окна
     */
    public function storeFailure(Request $request)
    {
        $data = $request->validate([
            'account_number' => ['nullable', 'string', 'max:100'],
            'failure_date' => ['nullable', 'date'],
            'aircraft_number' => ['nullable', 'string', 'max:50'],
            'aircraft_type' => ['nullable', 'string', 'max:100'],
            'aircraft_serial' => ['nullable', 'string', 'max:100'],
            'aircraft_manufacture_date' => ['nullable', 'date'],
            'aircraft_hours' => ['nullable', 'numeric'],
            'aircraft_landings' => ['nullable', 'integer'],
            'aircraft_ppr_hours' => ['nullable', 'numeric'],
            'aircraft_ppr_landings' => ['nullable', 'integer'],
            'aircraft_repair_date' => ['nullable', 'date'],
            'previous_repair_location' => ['nullable', 'string', 'max:255'],
            'aircraft_repairs_count' => ['nullable', 'integer'],
            'operator' => ['nullable', 'string', 'max:255'],
            'detection_stage' => ['nullable', 'integer'],
            'aircraft_malfunction' => ['nullable', 'string'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'consequence_id' => ['nullable', 'integer'],
            'wo_number' => ['nullable', 'string', 'max:100'],
            'wo_status_id' => ['nullable', 'integer'],
            'work_order_number' => ['nullable', 'string', 'max:100'],
            'mpd' => ['nullable', 'string', 'max:255'],
            'system_name' => ['nullable', 'string', 'max:255'],
            'subsystem_name' => ['nullable', 'string', 'max:255'],
            'component_malfunction' => ['nullable', 'string'],
            'component_cause' => ['nullable', 'string'],
            'taken_measure_id' => ['nullable', 'integer'],
            'resolution_method' => ['nullable', 'string', 'max:100'],
            'resolution_date' => ['nullable', 'date'],
            'aggregate_type' => ['nullable', 'string', 'max:100'],
            'part_number_off' => ['nullable', 'string', 'max:100'],
            'component_serial' => ['nullable', 'string', 'max:100'],
            'part_number_on' => ['nullable', 'string', 'max:100'],
            'serial_number_on' => ['nullable', 'string', 'max:100'],
            'component_hours_unit' => ['nullable', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'removal_date' => ['nullable', 'date'],
            'component_sne_hours' => ['nullable', 'numeric'],
            'component_ppr_hours' => ['nullable', 'numeric'],
            'production_date' => ['nullable', 'date'],
            'component_repairs_count' => ['nullable', 'integer'],
            'previous_installation_date' => ['nullable', 'date'],
            'repair_factory' => ['nullable', 'string', 'max:255'],
            'component_repair_date' => ['nullable', 'date'],
            'engine_type_id' => ['nullable', 'integer'],
            'engine_number_id' => ['nullable', 'integer'],
            'engine_release_date' => ['nullable', 'date'],
            'engine_installation_date' => ['nullable', 'date'],
            'engine_sne_hours' => ['nullable', 'numeric'],
            'engine_ppr_hours' => ['nullable', 'numeric'],
            'engine_sne_cycles' => ['nullable', 'numeric'],
            'engine_ppr_cycles' => ['nullable', 'numeric'],
            'engine_repair_date' => ['nullable', 'date'],
            'engine_repair_location' => ['nullable', 'string', 'max:255'],
            'engine_repairs_count' => ['nullable', 'integer'],
            'owner' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        // маппинг названий полей формы к полям в БД
        $payload = [
            'account_number' => $data['account_number'] ?? null,
            'failure_date' => $data['failure_date'] ?? now()->toDateString(),
            'aircraft_number' => $data['aircraft_number'] ?? '',
            'aircraft_type' => $data['aircraft_type'] ?? null,
            'aircraft_serial' => $data['aircraft_serial'] ?? null,
            'aircraft_manufacture_date' => $data['aircraft_manufacture_date'] ?? null,
            'aircraft_hours' => $data['aircraft_hours'] ?? null,
            'aircraft_landings' => $data['aircraft_landings'] ?? null,
            'aircraft_ppr_hours' => $data['aircraft_ppr_hours'] ?? null,
            'aircraft_ppr_landings' => $data['aircraft_ppr_landings'] ?? null,
            'aircraft_repair_date' => $data['aircraft_repair_date'] ?? null,
            'previous_repair_location' => $data['previous_repair_location'] ?? null,
            'aircraft_repairs_count' => $data['aircraft_repairs_count'] ?? null,
            'operator' => $data['operator'] ?? null,
            'detection_stage_id' => $data['detection_stage'] ?? null,
            'aircraft_malfunction' => $data['aircraft_malfunction'] ?? null,
            'event_location' => $data['event_location'] ?? null,
            'consequence_id' => $data['consequence_id'] ?? null,
            'wo_number' => $data['wo_number'] ?? null,
            'wo_status_id' => $data['wo_status_id'] ?? null,
            'work_order_number' => $data['work_order_number'] ?? null,
            'mpd' => $data['mpd'] ?? null,
            'system_name' => $data['system_name'] ?? null,
            'subsystem_name' => $data['subsystem_name'] ?? null,
            'component_malfunction' => $data['component_malfunction'] ?? null,
            'component_cause' => $data['component_cause'] ?? null,
            'taken_measure_id' => $data['taken_measure_id'] ?? null,
            'resolution_method' => $data['resolution_method'] ?? null,
            'resolution_date' => $data['resolution_date'] ?? null,
            'aggregate_type' => $data['aggregate_type'] ?? null,
            'part_number_off' => $data['part_number_off'] ?? null,
            'component_serial' => $data['component_serial'] ?? null,
            'part_number_on' => $data['part_number_on'] ?? null,
            'serial_number_on' => $data['serial_number_on'] ?? null,
            'component_hours_unit' => $data['component_hours_unit'] ?? null,
            'manufacturer' => $data['manufacturer'] ?? null,
            'removal_date' => $data['removal_date'] ?? null,
            'component_sne_hours' => $data['component_sne_hours'] ?? null,
            'component_ppr_hours' => $data['component_ppr_hours'] ?? null,
            'production_date' => $data['production_date'] ?? null,
            'component_repairs_count' => $data['component_repairs_count'] ?? null,
            'previous_installation_date' => $data['previous_installation_date'] ?? null,
            'repair_factory' => $data['repair_factory'] ?? null,
            'component_repair_date' => $data['component_repair_date'] ?? null,
            'engine_type_id' => $data['engine_type_id'] ?? null,
            'engine_number_id' => $data['engine_number_id'] ?? null,
            'engine_release_date' => $data['engine_release_date'] ?? null,
            'engine_installation_date' => $data['engine_installation_date'] ?? null,
            'engine_sne_hours' => $data['engine_sne_hours'] ?? null,
            'engine_ppr_hours' => $data['engine_ppr_hours'] ?? null,
            'engine_sne_cycles' => $data['engine_sne_cycles'] ?? null,
            'engine_ppr_cycles' => $data['engine_ppr_cycles'] ?? null,
            'engine_repair_date' => $data['engine_repair_date'] ?? null,
            'engine_repair_location' => $data['engine_repair_location'] ?? null,
            'engine_repairs_count' => $data['engine_repairs_count'] ?? null,
            'owner' => $data['owner'] ?? null,
            'position' => $data['position'] ?? null,
            'created_by_id' => auth()->id(),
            'include_in_buf' => $data['include_in_buf'] ?? true,
        ];

        $failure = ReliabilityFailure::create($payload);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Отказ успешно сохранён.',
                'id' => $failure->id,
            ]);
        }

        return redirect()
            ->route('modules.reliability.failures.edit', $failure->id)
            ->with('success', 'Отказ успешно создан.');
    }

    /**
     * Экспорт таблицы отказов в Excel
     */
    public function exportFailuresToExcel(Request $request)
    {
        // Используем те же фильтры, что и в методе index
        $failuresQuery = ReliabilityFailure::query()
            ->with(['detectionStage', 'consequence', 'takenMeasure', 'woStatus', 'engineType', 'engineNumber'])
            ->orderByDesc('failure_date')
            ->orderByDesc('id');

        // Фильтр по дате создания (failure_date)
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        if ($dateFrom) {
            $failuresQuery->whereDate('failure_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $failuresQuery->whereDate('failure_date', '<=', $dateTo);
        }

        // Фильтр по ID
        if ($request->filled('id')) {
            $failuresQuery->where('id', $request->input('id'));
        }

        // Фильтр по описанию (проявление неисправности ВС)
        if ($request->filled('description')) {
            $desc = $request->input('description');
            $failuresQuery->where(function ($q) use ($desc) {
                $q->where('aircraft_malfunction', 'like', '%' . $desc . '%')
                  ->orWhere('component_malfunction', 'like', '%' . $desc . '%');
            });
        }

        $this->applyMultiFilter($failuresQuery, $request, 'aircraft_type', 'aircraft_type');
        $this->applyMultiFilter($failuresQuery, $request, 'aircraft_number', 'aircraft_number');
        $this->applyMultiFilter($failuresQuery, $request, 'system', 'system_name');
        $this->applyMultiFilter($failuresQuery, $request, 'subsystem', 'subsystem_name');
        $this->applyMultiFilter($failuresQuery, $request, 'aggregate_type', 'aggregate_type');
        $this->applyMultiFilter($failuresQuery, $request, 'detection_stage', 'detection_stage_id', 'int');
        $this->applyMultiFilter($failuresQuery, $request, 'engine_type', 'engine_type_id', 'int');
        $this->applyMultiFilter($failuresQuery, $request, 'engine', 'engine_number_id', 'int');

        // Получаем все записи без пагинации
        $failures = $failuresQuery->get();

        // Создаем Excel файл
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Видимость полей формы отказа
        $fieldsList = RelFailureFormSetting::formFieldsList();
        $fieldVisibility = RelFailureFormSetting::getFieldVisibility();
        $visibleFieldKeys = array_keys(array_filter($fieldVisibility));

        // Заголовки: ID + только видимые поля из настроек формы отказа
        $colIndex = 1;
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . '1', 'ID');
        foreach ($visibleFieldKeys as $fieldKey) {
            $colIndex++;
            $label = $fieldsList[$fieldKey] ?? $fieldKey;
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . '1', $label);
        }

        // Стили для заголовков
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E64D4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $resolutionMethodLabels = [
            'repair' => 'Ремонт',
            'replacement' => 'Замена',
            'adjustment' => 'Регулировка',
        ];

        // Данные: ID + только видимые поля
        $row = 2;
        foreach ($failures as $failure) {
            $colIndex = 1;
            // ID
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row, $failure->id);

            foreach ($visibleFieldKeys as $fieldKey) {
                $colIndex++;
                $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row;
                $value = '';

                switch ($fieldKey) {
                    case 'account_number':
                        $value = $failure->account_number ?? '';
                        break;
                    case 'failure_date':
                        $value = $failure->failure_date ? \Carbon\Carbon::parse($failure->failure_date)->format('d.m.Y') : '';
                        break;
                    case 'aircraft_number':
                        $value = $failure->aircraft_number ?? '';
                        break;
                    case 'aircraft_type':
                        $value = $failure->aircraft_type ?? '';
                        break;
                    case 'type_code':
                        $value = $failure->aircraft_type_code ?? ($failure->type_code ?? '');
                        break;
                    case 'modification_code':
                        $value = $failure->modification_code ?? '';
                        break;
                    case 'aircraft_hours':
                        $value = $failure->aircraft_hours;
                        break;
                    case 'aircraft_landings':
                        $value = $failure->aircraft_landings;
                        break;
                    case 'aircraft_ppr_hours':
                        $value = $failure->aircraft_ppr_hours;
                        break;
                    case 'aircraft_ppr_landings':
                        $value = $failure->aircraft_ppr_landings;
                        break;
                    case 'detection_stage':
                        $value = optional($failure->detectionStage)->name ?? '';
                        break;
                    case 'aircraft_malfunction':
                        $value = $failure->aircraft_malfunction ?? '';
                        break;
                    case 'aggregate_type':
                        $value = $failure->aggregate_type ?? '';
                        break;
                    case 'part_number_off':
                        $value = $failure->part_number_off ?? '';
                        break;
                    case 'component_serial':
                        $value = $failure->component_serial ?? '';
                        break;
                    case 'part_number_on':
                        $value = $failure->part_number_on ?? '';
                        break;
                    case 'serial_number_on':
                        $value = $failure->serial_number_on ?? '';
                        break;
                    case 'system_name':
                        $value = $failure->system_name ?? '';
                        break;
                    case 'subsystem_name':
                        $value = $failure->subsystem_name ?? '';
                        break;
                    case 'component_sne_hours':
                        $value = $failure->component_sne_hours;
                        break;
                    case 'component_ppr_hours':
                        $value = $failure->component_ppr_hours;
                        break;
                    case 'component_hours_unit':
                        $value = $failure->component_hours_unit ?? '';
                        break;
                    case 'resolution_date':
                        $value = $failure->resolution_date ? \Carbon\Carbon::parse($failure->resolution_date)->format('d.m.Y') : '';
                        break;
                    case 'component_cause':
                        $value = $failure->component_cause ?? '';
                        break;
                    case 'taken_measure_id':
                        $value = optional($failure->takenMeasure)->name ?? '';
                        break;
                    case 'wo_number':
                        $value = $failure->wo_number ?? '';
                        break;
                    case 'wo_status_id':
                        $value = optional($failure->woStatus)->name ?? '';
                        break;
                    case 'work_order_number':
                        $value = $failure->work_order_number ?? '';
                        break;
                    case 'resolution_method':
                        $value = $resolutionMethodLabels[$failure->resolution_method ?? ''] ?? $failure->resolution_method ?? '';
                        break;
                    case 'aircraft_serial':
                        $value = $failure->aircraft_serial ?? '';
                        break;
                    case 'aircraft_manufacture_date':
                        $value = $failure->aircraft_manufacture_date ? \Carbon\Carbon::parse($failure->aircraft_manufacture_date)->format('d.m.Y') : '';
                        break;
                    case 'aircraft_repair_date':
                        $value = $failure->aircraft_repair_date ? \Carbon\Carbon::parse($failure->aircraft_repair_date)->format('d.m.Y') : '';
                        break;
                    case 'previous_repair_location':
                        $value = $failure->previous_repair_location ?? '';
                        break;
                    case 'aircraft_repairs_count':
                        $value = $failure->aircraft_repairs_count;
                        break;
                    case 'operator':
                        $value = $failure->operator ?? '';
                        break;
                    case 'event_location':
                        $value = $failure->event_location ?? '';
                        break;
                    case 'consequence_id':
                        $value = optional($failure->consequence)->name ?? '';
                        break;
                    case 'component_malfunction':
                        $value = $failure->component_malfunction ?? '';
                        break;
                    case 'manufacturer':
                        $value = $failure->manufacturer ?? '';
                        break;
                    case 'removal_date':
                        $value = $failure->removal_date ? \Carbon\Carbon::parse($failure->removal_date)->format('d.m.Y') : '';
                        break;
                    case 'production_date':
                        $value = $failure->production_date ? \Carbon\Carbon::parse($failure->production_date)->format('d.m.Y') : '';
                        break;
                    case 'component_repairs_count':
                        $value = $failure->component_repairs_count;
                        break;
                    case 'previous_installation_date':
                        $value = $failure->previous_installation_date ? \Carbon\Carbon::parse($failure->previous_installation_date)->format('d.m.Y') : '';
                        break;
                    case 'repair_factory':
                        $value = $failure->repair_factory ?? '';
                        break;
                    case 'component_repair_date':
                        $value = $failure->component_repair_date ? \Carbon\Carbon::parse($failure->component_repair_date)->format('d.m.Y') : '';
                        break;
                    case 'engine_type_id':
                        $value = optional($failure->engineType)->name ?? '';
                        break;
                    case 'engine_number_id':
                        $value = optional($failure->engineNumber)->number ?? '';
                        break;
                    case 'engine_release_date':
                        $value = $failure->engine_release_date ? \Carbon\Carbon::parse($failure->engine_release_date)->format('d.m.Y') : '';
                        break;
                    case 'engine_installation_date':
                        $value = $failure->engine_installation_date ? \Carbon\Carbon::parse($failure->engine_installation_date)->format('d.m.Y') : '';
                        break;
                    case 'engine_sne_hours':
                        $value = $failure->engine_sne_hours;
                        break;
                    case 'engine_ppr_hours':
                        $value = $failure->engine_ppr_hours;
                        break;
                    case 'engine_sne_cycles':
                        $value = $failure->engine_sne_cycles;
                        break;
                    case 'engine_ppr_cycles':
                        $value = $failure->engine_ppr_cycles;
                        break;
                    case 'engine_repair_date':
                        $value = $failure->engine_repair_date ? \Carbon\Carbon::parse($failure->engine_repair_date)->format('d.m.Y') : '';
                        break;
                    case 'engine_repair_location':
                        $value = $failure->engine_repair_location ?? '';
                        break;
                    case 'engine_repairs_count':
                        $value = $failure->engine_repairs_count;
                        break;
                    case 'owner':
                        $value = $failure->owner ?? '';
                        break;
                    case 'position':
                        $value = $failure->position ?? '';
                        break;
                    case 'created_by':
                        $value = $failure->created_by_id ?? null;
                        break;
                }

                $sheet->setCellValue($coord, $value);
            }

            $row++;
        }

        // Ширина колонок: увеличиваем примерно в 3 раза для всех реально существующих колонок
        $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($i = 1; $i <= $lastColIndex; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $dim = $sheet->getColumnDimension($col);
            // Базовая ширина по умолчанию
            $baseWidth = $dim->getWidth();
            if ($baseWidth <= 0) {
                $baseWidth = 10;
            }
            $dim->setAutoSize(false);
            $dim->setWidth($baseWidth * 3);
        }

        // Границы
        if ($row > 2) {
            $sheet->getStyle('A1:' . $lastCol . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        // Создаем файл
        $writer = new Xlsx($spreadsheet);
        $filename = 'otkazy_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = storage_path('app/public/' . $filename);

        $writer->save($filepath);

        return response()->download($filepath, $filename)->deleteFileAfterSend();
    }

    /**
     * Данные для графика мониторинга отказов (AJAX).
     */
    public function monitoringChartData(Request $request)
    {
        $unit = $request->input('unit', 'abs'); // abs | k1000 | k1000h
        $period = $request->input('period', 'month'); // month | quarter | year

        // Этапы обнаружения: первый уровень (parent_id IS NULL) — группы для графика
        $stages = RelFailureDetectionStage::where('active', true)->get();
        $topStages = $stages->whereNull('parent_id')->sortBy('sort_order')->values();

        // Маппинг: stage_id => id родителя первого уровня (для дочерних — parent_id, для корневых — свой id)
        $stageToTopMap = [];
        foreach ($stages as $s) {
            if ($s->parent_id === null) {
                $stageToTopMap[$s->id] = $s->id;
            } else {
                $stageToTopMap[$s->id] = $s->parent_id;
            }
        }

        // Ключи группировки: id этапа первого уровня => slug для datasets
        $topStageKeys = [];
        $topStageNames = [];
        foreach ($topStages as $ts) {
            $slug = 'stage_' . $ts->id;
            $topStageKeys[$ts->id] = $slug;
            $topStageNames[$ts->id] = $ts->name;
        }

        // Отказы с теми же фильтрами, что и на главной (даты, ВС, система, этап и т.д.)
        $failuresQuery = ReliabilityFailure::whereNotNull('failure_date')
            ->orderBy('failure_date');

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        if ($dateFrom) {
            $failuresQuery->whereDate('failure_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $failuresQuery->whereDate('failure_date', '<=', $dateTo);
        }
        if ($request->filled('id')) {
            $failuresQuery->where('id', $request->input('id'));
        }
        if ($request->filled('description')) {
            $desc = $request->input('description');
            $failuresQuery->where(function ($q) use ($desc) {
                $q->where('aircraft_malfunction', 'like', '%' . $desc . '%')
                    ->orWhere('component_malfunction', 'like', '%' . $desc . '%');
            });
        }
        $this->applyMultiFilter($failuresQuery, $request, 'aircraft_type', 'aircraft_type');
        $this->applyMultiFilter($failuresQuery, $request, 'aircraft_number', 'aircraft_number');
        $this->applyMultiFilter($failuresQuery, $request, 'system', 'system_name');
        $this->applyMultiFilter($failuresQuery, $request, 'subsystem', 'subsystem_name');
        $this->applyMultiFilter($failuresQuery, $request, 'aggregate_type', 'aggregate_type');
        $this->applyMultiFilter($failuresQuery, $request, 'detection_stage', 'detection_stage_id', 'int');
        $this->applyMultiFilter($failuresQuery, $request, 'engine_type', 'engine_type_id', 'int');
        $this->applyMultiFilter($failuresQuery, $request, 'engine', 'engine_number_id', 'int');

        $failures = $failuresQuery->get(['id', 'failure_date', 'detection_stage_id']);

        $monthNamesRu = [1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'];

        // Пустой шаблон бакета
        $emptyBucketTemplate = ['label' => ''];
        foreach ($topStageKeys as $tid => $slug) {
            $emptyBucketTemplate[$slug] = 0;
        }
        $emptyBucketTemplate['unknown'] = 0;

        // Диапазон: по фильтру дат или по умолчанию год назад — текущий месяц
        $start = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : now()->subYear()->startOfMonth();
        $end = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : now()->endOfMonth();

        // Строим все интервалы периода (месяц / квартал / год) от start до end
        $buckets = [];
        $cursor = $start->copy();

        if ($period === 'month') {
            $cursor->startOfMonth();
            $endCursor = $end->copy()->startOfMonth();
            while ($cursor->lte($endCursor)) {
                $key = $cursor->format('Y-m');
                $buckets[$key] = array_merge([], $emptyBucketTemplate);
                $buckets[$key]['label'] = $monthNamesRu[(int) $cursor->format('n')] . ' ' . $cursor->format('Y');
                $cursor->addMonth();
            }
        } elseif ($period === 'quarter') {
            $cursor->startOfQuarter();
            $endCursor = $end->copy()->startOfQuarter();
            while ($cursor->lte($endCursor)) {
                $q = (int) ceil((int) $cursor->format('n') / 3);
                $key = $cursor->format('Y') . '-Q' . $q;
                $buckets[$key] = array_merge([], $emptyBucketTemplate);
                $buckets[$key]['label'] = $cursor->format('Y') . ' Q' . $q;
                $cursor->addQuarter();
            }
        } else {
            $cursor->startOfYear();
            $endCursor = $end->copy()->startOfYear();
            while ($cursor->lte($endCursor)) {
                $key = $cursor->format('Y');
                $buckets[$key] = array_merge([], $emptyBucketTemplate);
                $buckets[$key]['label'] = $key;
                $cursor->addYear();
            }
        }

        // Заполняем бакеты по отказам (инкремент счётчиков)
        foreach ($failures as $f) {
            $date = \Carbon\Carbon::parse($f->failure_date);
            $y = (int) $date->format('Y');
            $m = (int) $date->format('n');

            if ($period === 'month') {
                $key = sprintf('%04d-%02d', $y, $m);
            } elseif ($period === 'quarter') {
                $q = (int) ceil($m / 3);
                $key = sprintf('%04d-Q%d', $y, $q);
            } else {
                $key = (string) $y;
            }

            if (!isset($buckets[$key])) {
                continue;
            }

            $sid = $f->detection_stage_id;
            $topId = $sid ? ($stageToTopMap[$sid] ?? null) : null;
            if ($topId && isset($topStageKeys[$topId])) {
                $buckets[$key][$topStageKeys[$topId]]++;
            } else {
                $buckets[$key]['unknown']++;
            }
        }

        ksort($buckets);

        if ($unit === 'k1000' || $unit === 'k1000h') {
            // Множество всех slug'ов (этапы + unknown)
            $allSlugs = array_values($topStageKeys);
            $allSlugs[] = 'unknown';

            if ($unit === 'k1000') {
                // Собираем полёты по месяцам (кол-во полётов)
                $flightsMap = []; // ключ 'YYYY-MM' => суммарное flights_count

                // Данные по полётам убраны (таблицы spi_flight_data / spi_flight_data_weekly удалены)
                $monthlyData = collect([]);
                $weeklyData = collect([]);
                foreach ($monthlyData as $fd) {
                    $fKey = sprintf('%04d-%02d', $fd->year, $fd->month_number);
                    $flightsMap[$fKey] = ($flightsMap[$fKey] ?? 0) + (int) $fd->flights_count;
                }
                foreach ($weeklyData as $wd) {
                    $d = \Carbon\Carbon::parse($wd->week_start_date);
                    $fKey = sprintf('%04d-%02d', $d->year, $d->month);
                    $flightsMap[$fKey] = ($flightsMap[$fKey] ?? 0) + (int) $wd->flights_count;
                }

                foreach ($buckets as $key => &$b) {
                    $totalFlights = 0;
                    if ($period === 'month') {
                        $totalFlights = $flightsMap[$key] ?? 0;
                    } elseif ($period === 'quarter') {
                        preg_match('/(\d{4})-Q(\d)/', $key, $qm);
                        $qYear = (int) ($qm[1] ?? 0);
                        $qNum = (int) ($qm[2] ?? 0);
                        $startM = ($qNum - 1) * 3 + 1;
                        for ($mi = $startM; $mi < $startM + 3; $mi++) {
                            $mKey = sprintf('%04d-%02d', $qYear, $mi);
                            $totalFlights += $flightsMap[$mKey] ?? 0;
                        }
                    } else {
                        $yr = (int) $key;
                        for ($mi = 1; $mi <= 12; $mi++) {
                            $mKey = sprintf('%04d-%02d', $yr, $mi);
                            $totalFlights += $flightsMap[$mKey] ?? 0;
                        }
                    }

                    if ($totalFlights > 0) {
                        foreach ($allSlugs as $slug) {
                            $b[$slug] = round($b[$slug] / $totalFlights * 1000, 2);
                        }
                    } else {
                        foreach ($allSlugs as $slug) {
                            $b[$slug] = 0;
                        }
                    }
                }
                unset($b);
            } elseif ($unit === 'k1000h') {
                // Собираем налёт (часы) по месяцам
                $hoursMap = []; // ключ 'YYYY-MM' => суммарный налёт в часах (float)

                // Данные по налету убраны (таблицы spi_flight_data / spi_flight_data_weekly удалены)
                $monthlyData = collect([]);
                $weeklyData = collect([]);
                foreach ($monthlyData as $fd) {
                    $fKey = sprintf('%04d-%02d', $fd->year, $fd->month_number);
                    $hoursMap[$fKey] = ($hoursMap[$fKey] ?? 0.0) + $this->parseFlightHoursToDecimal($fd->flight_hours);
                }
                foreach ($weeklyData as $wd) {
                    $d = \Carbon\Carbon::parse($wd->week_start_date);
                    $fKey = sprintf('%04d-%02d', $d->year, $d->month);
                    $hoursMap[$fKey] = ($hoursMap[$fKey] ?? 0.0) + $this->parseFlightHoursToDecimal($wd->flight_hours);
                }

                foreach ($buckets as $key => &$b) {
                    $totalHours = 0.0;
                    if ($period === 'month') {
                        $totalHours = $hoursMap[$key] ?? 0.0;
                    } elseif ($period === 'quarter') {
                        preg_match('/(\d{4})-Q(\d)/', $key, $qm);
                        $qYear = (int) ($qm[1] ?? 0);
                        $qNum = (int) ($qm[2] ?? 0);
                        $startM = ($qNum - 1) * 3 + 1;
                        for ($mi = $startM; $mi < $startM + 3; $mi++) {
                            $mKey = sprintf('%04d-%02d', $qYear, $mi);
                            $totalHours += $hoursMap[$mKey] ?? 0.0;
                        }
                    } else {
                        $yr = (int) $key;
                        for ($mi = 1; $mi <= 12; $mi++) {
                            $mKey = sprintf('%04d-%02d', $yr, $mi);
                            $totalHours += $hoursMap[$mKey] ?? 0.0;
                        }
                    }

                    if ($totalHours > 0) {
                        foreach ($allSlugs as $slug) {
                            $b[$slug] = round($b[$slug] / $totalHours * 1000, 2);
                        }
                    } else {
                        foreach ($allSlugs as $slug) {
                            $b[$slug] = 0;
                        }
                    }
                }
                unset($b);
            }
        }

        // Формируем массивы для ответа
        $labels = [];
        $datasets = [];
        foreach ($topStageKeys as $tid => $slug) {
            $datasets[$slug] = ['name' => $topStageNames[$tid], 'data' => []];
        }
        $datasets['unknown'] = ['name' => 'Не выбран', 'data' => []];

        foreach ($buckets as $b) {
            $labels[] = $b['label'];
            foreach ($datasets as $slug => &$ds) {
                $ds['data'][] = $b[$slug] ?? 0;
            }
        }
        unset($ds);

        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets,
            'unit' => $unit,
            'period' => $period,
        ]);
    }

    /**
     * Преобразует значение налёта (строка вида HH:MM или HH:MM:SS) в десятичные часы.
     */
    private function parseFlightHoursToDecimal($raw): float
    {
        if (!$raw) {
            return 0.0;
        }

        if ($raw instanceof \DateTimeInterface) {
            $hours = (int) $raw->format('H');
            $minutes = (int) $raw->format('i');
            return $hours + $minutes / 60.0;
        }

        $str = (string) $raw;
        if (!preg_match('/^(\d{1,3}):(\d{2})(?::\d{2})?$/', $str, $m)) {
            return 0.0;
        }

        $hours = (int) ($m[1] ?? 0);
        $minutes = (int) ($m[2] ?? 0);

        return $hours + $minutes / 60.0;
    }

    private const BUF_CHUNK_SIZE = 100;

    /**
     * Экспорт отказов в текстовый файл BUF (ANSI) по текущим фильтрам.
     * Первый пункт — номер = код организации + id отказа.
     * Не более 100 записей в одном файле; при большем числе — buf1.txt, buf2.txt, … в ZIP-архиве.
     */
    public function exportFailuresToBuf(Request $request)
    {
        $orgCode = (string) SystemSetting::get('reliability_org_code', '');

        $failuresQuery = ReliabilityFailure::query()
            ->where('include_in_buf', true)
            ->with(['detectionStage'])
            ->orderByDesc('failure_date')
            ->orderByDesc('id');

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        if ($dateFrom) {
            $failuresQuery->whereDate('failure_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $failuresQuery->whereDate('failure_date', '<=', $dateTo);
        }

        if ($request->filled('id')) {
            $failuresQuery->where('id', $request->input('id'));
        }

        if ($request->filled('description')) {
            $desc = $request->input('description');
            $failuresQuery->where(function ($q) use ($desc) {
                $q->where('aircraft_malfunction', 'like', '%' . $desc . '%')
                    ->orWhere('component_malfunction', 'like', '%' . $desc . '%');
            });
        }

        $this->applyMultiFilter($failuresQuery, $request, 'aircraft_type', 'aircraft_type');
        $this->applyMultiFilter($failuresQuery, $request, 'aircraft_number', 'aircraft_number');
        $this->applyMultiFilter($failuresQuery, $request, 'system', 'system_name');
        $this->applyMultiFilter($failuresQuery, $request, 'subsystem', 'subsystem_name');
        $this->applyMultiFilter($failuresQuery, $request, 'aggregate_type', 'aggregate_type');
        $this->applyMultiFilter($failuresQuery, $request, 'detection_stage', 'detection_stage_id', 'int');
        $this->applyMultiFilter($failuresQuery, $request, 'engine_type', 'engine_type_id', 'int');
        $this->applyMultiFilter($failuresQuery, $request, 'engine', 'engine_number_id', 'int');

        $failures = $failuresQuery->get();

        $regNs = $failures->pluck('aircraft_number')->unique()->filter()->values()->all();
        $aircrafts = \App\Models\Aircraft::whereIn('RegN', $regNs)->with('aircraftType')->get()->keyBy('RegN');
        $aircraftTypesByName = \App\Models\AircraftsType::all()->keyBy('name_rus');
        $aircraftTypesByIcao = \App\Models\AircraftsType::all()->keyBy('icao');

        $chunks = $failures->chunk(self::BUF_CHUNK_SIZE);

        if ($chunks->count() === 1 && $failures->count() <= self::BUF_CHUNK_SIZE) {
            $lines = $this->buildBufLines($chunks->first(), $orgCode, $aircrafts, $aircraftTypesByName, $aircraftTypesByIcao);
            $contentUtf8 = implode(PHP_EOL, $lines) . PHP_EOL;
            $contentAnsi = @iconv('UTF-8', 'Windows-1251//TRANSLIT', $contentUtf8) ?: $contentUtf8;
            return response($contentAnsi)
                ->header('Content-Type', 'text/plain; charset=Windows-1251')
                ->header('Content-Disposition', 'attachment; filename="buf.txt"');
        }

        $zipPath = storage_path('app/temp/buf_export_' . uniqid() . '.zip');
        if (!is_dir(dirname($zipPath))) {
            @mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Не удалось создать архив');
        }

        $index = 1;
        foreach ($chunks as $chunk) {
            $lines = $this->buildBufLines($chunk, $orgCode, $aircrafts, $aircraftTypesByName, $aircraftTypesByIcao);
            $contentUtf8 = implode(PHP_EOL, $lines) . PHP_EOL;
            $contentAnsi = @iconv('UTF-8', 'Windows-1251//TRANSLIT', $contentUtf8) ?: $contentUtf8;
            $zip->addFromString('buf' . $index . '.txt', $contentAnsi);
            $index++;
        }

        $zip->close();

        $zipContent = file_get_contents($zipPath);
        @unlink($zipPath);

        return response($zipContent)
            ->header('Content-Type', 'application/zip')
            ->header('Content-Disposition', 'attachment; filename="buf.zip"');
    }

    /**
     * Формирует массив строк BUF для переданной коллекции отказов (один пункт = одна строка, запись = 18 строк).
     */
    private function buildBufLines(
        $failures,
        string $orgCode,
        $aircrafts,
        $aircraftTypesByName,
        $aircraftTypesByIcao
    ): array {
        $lines = [];
        foreach ($failures as $failure) {
            // 1. № записи = код организации + id отказа
            $lines[] = $orgCode . (string) $failure->id;

            $lines[] = $failure->failure_date
                ? \Carbon\Carbon::parse($failure->failure_date)->format('Y-m-d H:i:s')
                : '-';

            $lines[] = $this->bufAircraftNumberOnly($failure->aircraft_number ?? '');
            $lines[] = $this->bufAircraftTypeCode($failure, $aircrafts, $aircraftTypesByName, $aircraftTypesByIcao);
            $lines[] = '00';

            $lines[] = $failure->aircraft_malfunction !== null && $failure->aircraft_malfunction !== ''
                ? $failure->aircraft_malfunction
                : '-';

            $stageCode = '-';
            if ($failure->detectionStage) {
                $name = mb_strtolower($failure->detectionStage->name);
                $stageCode = (str_contains($name, 'полет') || str_contains($name, 'полёт')) ? '20' : '10';
            }
            $lines[] = $stageCode;

            $lines[] = $this->bufSubsystemCode($failure->subsystem_name);
            $lines[] = $failure->component_cause !== null && $failure->component_cause !== ''
                ? $failure->component_cause
                : '-';
            $lines[] = $failure->aggregate_type !== null && $failure->aggregate_type !== ''
                ? $failure->aggregate_type
                : '-';

            $componentSerialRaw = (string) ($failure->component_serial ?? '');
            $componentSerialTrimmed = trim($componentSerialRaw);
            $lines[] = ($componentSerialTrimmed === '' || preg_match('/^\s*$/u', $componentSerialRaw)) ? '-' : $componentSerialTrimmed;

            $lines[] = $failure->component_sne_hours !== null ? (string) (int) $failure->component_sne_hours : '-';
            $lines[] = $failure->component_ppr_hours !== null ? (string) (int) $failure->component_ppr_hours : '-';
            $lines[] = '-';
            $lines[] = $failure->aircraft_hours !== null ? (string) (int) $failure->aircraft_hours : '-';
            $lines[] = $failure->aircraft_landings !== null ? (string) (int) $failure->aircraft_landings : '-';
            $lines[] = '-';
            $lines[] = '-';
        }
        return $lines;
    }

    /**
     * Номер ВС для BUF: только цифры без префикса (буквы и дефис). Например RA-02751 → 02751.
     */
    private function bufAircraftNumberOnly(string $regN): string
    {
        $regN = trim($regN);
        if ($regN === '') {
            return '-';
        }
        $pos = strrpos($regN, '-');
        if ($pos !== false) {
            $regN = substr($regN, $pos + 1);
        }
        return $regN !== '' ? $regN : '-';
    }

    /**
     * Код типа ВС для BUF из справочника «Коды типа ВС» (aircrafts_types.rus).
     */
    private function bufAircraftTypeCode(
        ReliabilityFailure $failure,
        $aircrafts,
        $aircraftTypesByName,
        $aircraftTypesByIcao
    ): string {
        $aircraft = $aircrafts->get($failure->aircraft_number ?? '');
        if ($aircraft && $aircraft->aircraftType && ($code = $aircraft->aircraftType->rus) !== null && $code !== '') {
            return $code;
        }
        $typeName = $failure->aircraft_type;
        if ($typeName !== null && $typeName !== '') {
            $type = $aircraftTypesByName->get($typeName) ?? $aircraftTypesByIcao->get($typeName);
            if ($type && ($code = $type->rus) !== null && $code !== '') {
                return $code;
            }
        }
        return '-';
    }

    /**
     * Код подсистемы для BUF (например из "65-11 - Название" берётся 6511, без дефиса).
     */
    private function bufSubsystemCode(?string $subsystemName): string
    {
        if ($subsystemName === null || trim($subsystemName) === '') {
            return '-';
        }
        $s = trim($subsystemName);
        if (str_contains($s, ' - ')) {
            $s = trim(explode(' - ', $s, 2)[0]);
        }
        $s = str_replace('-', '', $s);
        return $s !== '' ? $s : '-';
    }
}

