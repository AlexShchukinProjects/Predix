@extends('layout.main')

@section('content')
@php
    $f = $failure ?? null;
    $isEdit = $f !== null;
@endphp
@if($isEdit)
<style>
.modern-file-upload { border: 2px dashed #d1d5db; border-radius: 8px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); overflow: hidden; transition: all 0.3s ease; margin-bottom: 16px; }
.modern-file-upload:hover { border-color: #3b82f6; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); }
.file-upload-header { display: flex; align-items: center; justify-content: center; padding: 1rem 0.75rem; cursor: pointer; user-select: none; }
.file-upload-text h4 { margin: 0 0 0.125rem 0; font-size: 0.875rem; font-weight: 600; color: #1f2937; }
.file-upload-text p { margin: 0; font-size: 0.75rem; color: #6b7280; }
.file-upload-content { padding: 0.75rem; background: #fff; min-height: 60px; display: flex; flex-direction: column; align-items: stretch; }
.file-upload-placeholder { text-align: center; color: #9ca3af; display: flex; flex-direction: column; align-items: center; gap: 0.25rem; }
.file-upload-placeholder svg { width: 1.5rem; height: 1.5rem; }
.file-preview-item { display: flex; align-items: center; padding: 0.5rem; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 0.375rem; width: 100%; cursor: pointer; }
.file-preview-item:hover { background: #f1f5f9; border-color: #3b82f6; }
.file-preview-icon { width: 1.75rem; height: 1.75rem; margin-right: 0.5rem; display: flex; align-items: center; justify-content: center; background: #fff; border-radius: 4px; border: 1px solid #e5e7eb; flex: 0 0 auto; overflow: hidden; }
.file-preview-icon svg { width: 1.25rem; height: 1.25rem; color: #4b5563; }
.file-preview-icon img { width: 100%; height: 100%; object-fit: contain; }
.file-preview-icon img.file-preview-thumb { object-fit: cover; }
.file-preview-info { flex: 1; min-width: 0; }
.file-preview-name { font-size: 0.75rem; font-weight: 500; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-preview-size { font-size: 0.625rem; color: #6b7280; }
.file-preview-remove { margin-left: 0.5rem; background: transparent; color: #dc2626; border: none; padding: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
.file-preview-remove:hover { color: #b91c1c; }
.multi-file-upload .file-upload-content { min-height: 80px; }
.modern-file-upload.drag-over { border-color: #1E64D4; background: #dbeafe; }


</style>
@endif
<div class="container-fluid">
    <div class="d-flex justify-content-start align-items-center mb-2">
        <a href="{{ route('modules.reliability.index', ['tab' => 'failures']) }}" class="back-button">
            <i class="fas fa-arrow-left me-2"></i>К списку отказов
        </a>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            {{ $isEdit ? 'Редактирование отказа' : 'Добавить отказ' }}
        </h1>
   {{--
   @if($isEdit)
         <a href="{{ route('modules.reliability.failures.export-card', $f?->id) }}" class="btn efds-btn efds-btn--primary" target="_blank">
                <i class="fas fa-download me-1"></i>Выгрузить карту
            </a>
        @endif
   --}}
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <form id="failureForm" method="POST" action="{{ $isEdit ? route('modules.reliability.failures.update', $f) : route('modules.reliability.failures.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="row mb-3" data-form-field="account_number">
                    <div class="col-4">
                        <label class="form-label mb-0">Учетный номер (Код предприятия номер КУН)</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="account_number" maxlength="100" placeholder="—" value="{{ old('account_number', $f?->account_number ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата обнаружения <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="failure_date" required value="{{ old('failure_date', $f?->failure_date ?? '') }}">
                    </div>
                </div>

                <div class="row mb-3" data-form-field="aircraft_number">
                    <div class="col-4">
                        <label class="form-label mb-0">Бортовой № ВС</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="aircraft_number" id="failure_aircraft_number">
                            <option value="">Выберите бортовой номер</option>
                            @if($isEdit && $f?->aircraft_number && !($aircraftList ?? collect())->contains('RegN', $f?->aircraft_number))
                                <option value="{{ $f?->aircraft_number }}" selected>{{ $f?->aircraft_number }}</option>
                            @endif
                            @foreach(($aircraftList ?? []) as $ac)
                                <option value="{{ $ac->RegN }}"
                                    data-type="{{ $ac->Type }}"
                                    data-type-code="{{ $ac->type_code ?? '' }}"
                                    data-modification-code="{{ $ac->modification_code ?? '' }}"
                                    data-serial="{{ $ac->FactoryNumber }}"
                                    data-manufacture="{{ $ac->Date_manufacture ?? '' }}"
                                    {{ old('aircraft_number', $f?->aircraft_number ?? '') == $ac->RegN ? 'selected' : '' }}>
                                    {{ $ac->RegN }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3" data-form-field="aircraft_type">
                    <div class="col-4">
                        <label class="form-label mb-0">Тип ВС</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control readonly-field" name="aircraft_type" id="failure_aircraft_type" readonly value="{{ old('aircraft_type', $f?->aircraft_type ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="type_code">
                    <div class="col-4">
                        <label class="form-label mb-0">Тип ВС (код)</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control readonly-field" id="failure_type_code" readonly placeholder="—" value="{{ old('type_code', $f ? ($f?->getAttribute('type_code') ?? $f?->type_code ?? '') : '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="modification_code">
                    <div class="col-4">
                        <label class="form-label mb-0">Модификация (код)</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control readonly-field" id="failure_modification_code" readonly placeholder="—" value="{{ old('modification_code', $f ? ($f?->getAttribute('modification_code') ?? $f?->modification_code ?? '') : '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aircraft_hours">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка ВС в часах</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="aircraft_hours" step="1" value="{{ old('aircraft_hours', $f?->aircraft_hours !== null ? (int)$f->aircraft_hours : '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aircraft_landings">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка ВС в посадках</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="aircraft_landings" value="{{ old('aircraft_landings', $f?->aircraft_landings ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aircraft_ppr_hours">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка ВС ППР (час)</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="aircraft_ppr_hours" step="0.1" value="{{ old('aircraft_ppr_hours', $f?->aircraft_ppr_hours ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aircraft_ppr_landings">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка ВС ППР (посадки)</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="aircraft_ppr_landings" value="{{ old('aircraft_ppr_landings', $f?->aircraft_ppr_landings ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="detection_stage">
                    <div class="col-4">
                        <label class="form-label mb-0">Этап обнаружения отказа</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="detection_stage">
                            <option value="">Выберите этап</option>
                            @foreach(($detectionStages ?? []) as $stage)
                                @if(empty($stage->parent_id))
                                    <option value="{{ $stage->id }}" {{ (string)old('detection_stage', $f?->detection_stage_id ?? '') === (string)$stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                    @foreach($detectionStages as $child)
                                        @if($child->parent_id === $stage->id)
                                            <option value="{{ $child->id }}" {{ (string)old('detection_stage', $f?->detection_stage_id ?? '') === (string)$child->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;{{ $child->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aircraft_malfunction">
                    <div class="col-4">
                        <label class="form-label mb-0">Проявление неисправности ВС</label>
                    </div>
                    <div class="col-8">
                        <textarea class="form-control" name="aircraft_malfunction" rows="3">{{ old('aircraft_malfunction', $f?->aircraft_malfunction ?? '') }}</textarea>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aggregate_type">
                    <div class="col-4">
                        <label class="form-label mb-0">Тип агрегата</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="aggregate_type" id="failure_aggregate_type" value="{{ old('aggregate_type', $f?->aggregate_type ?? '') }}" placeholder="Выберите тип агрегата" readonly autocomplete="off" data-bs-toggle="modal" data-bs-target="#aggregateSelectModal">
                    </div>
                </div>

                <!-- Модальное окно выбора агрегата -->
                <div class="modal fade" id="aggregateSelectModal" tabindex="-1" aria-labelledby="aggregateSelectModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width: 960px; min-height: 80vh;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="aggregateSelectModalLabel">Выбор типа агрегата</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="agg_modal_btn_add">Добавить агрегат</button>
                                </div>
                                <div id="agg_modal_add_form" class="border rounded p-3 mb-3 bg-light" style="display: none;">
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-0">Код</label>
                                            <input type="text" class="form-control form-control-sm" id="agg_add_code" maxlength="50" placeholder="Код">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-0">Наименование <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" id="agg_add_name" maxlength="255" placeholder="Наименование">
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-0">Тип ВС</label>
                                            <select class="form-select form-select-sm" id="agg_add_aircraft_type">
                                                <option value="">—</option>
                                                @foreach(($aircraftTypes ?? []) as $at)
                                                    <option value="{{ $at->id }}">{{ $at->name_rus ?? $at->icao ?? $at->id }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-0">Система</label>
                                            <select class="form-select form-select-sm" id="agg_add_system">
                                                <option value="">—</option>
                                                <option value="__free__">Не в составе систем</option>
                                                @foreach(($failureSystems ?? []) as $sys)
                                                    <option value="{{ $sys->system_name }}">{{ $sys->system_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-0">Подсистема</label>
                                            <select class="form-select form-select-sm" id="agg_add_subsystem" disabled>
                                                <option value="">Выберите систему</option>
                                                @foreach(($failureSubsystems ?? []) as $sub)
                                                    <option value="{{ $sub->id }}" data-system="{{ $sub->system_name }}">{{ $sub->subsystem_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="" style="margin-top:20px">
                                            <button type="button" class="btn efds-btn efds-btn--primary" id="agg_add_save">Сохранить</button>
                                            <button type="button" class="btn efds-btn efds-btn--outline-primary" id="agg_add_cancel">Отмена</button>
                                        </div>
                                    </div>
                                    <div id="agg_add_message" class="small mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small mb-1">Поиск</label>
                                    <input type="text" class="form-control form-control-sm" id="agg_modal_search" placeholder="Введите название...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small mb-1">Тип ВС</label>
                                    <select class="form-select form-select-sm" id="agg_modal_aircraft_type">
                                        <option value="">Все типы</option>
                                        @foreach(($aircraftTypes ?? []) as $at)
                                            <option value="{{ $at->id }}">{{ $at->name_rus ?? $at->icao ?? $at->id }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small mb-1">Система</label>
                                    <select class="form-select form-select-sm" id="agg_modal_system">
                                        <option value="">Все агрегаты</option>
                                        <option value="__free__">Не в составе систем</option>
                                        @foreach(($failureSystems ?? []) as $sys)
                                            <option value="{{ $sys->system_name }}">{{ $sys->system_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small mb-1">Подсистема</label>
                                    <select class="form-select form-select-sm" id="agg_modal_subsystem" disabled>
                                        <option value="">Выберите систему</option>
                                        @foreach(($failureSubsystems ?? []) as $sub)
                                            <option value="{{ $sub->subsystem_name }}" data-system="{{ $sub->system_name }}">{{ $sub->subsystem_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small mb-1">Агрегаты</label>
                                    <div id="agg_modal_list" class="border rounded" style="max-height: 65vh; min-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0" id="agg_modal_table">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th style="width: 60px;">№</th>
                                                    <th>Наименование</th>
                                                </tr>
                                            </thead>
                                            <tbody id="agg_modal_tbody">
                                                <tr><td colspan="2" class="text-muted small">Загрузка...</td></tr>
                                            </tbody>
                                        </table>
                                        <div id="agg_modal_placeholder" class="p-2 text-muted small" style="display: none;">Выберите фильтр или введите поиск</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="part_number_off">
                    <div class="col-4">
                        <label class="form-label mb-0">P/N OFF</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="part_number_off" maxlength="100" value="{{ old('part_number_off', $f?->part_number_off ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="component_serial">
                    <div class="col-4">
                        <label class="form-label mb-0">S/N OFF</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="component_serial" value="{{ old('component_serial', $f?->component_serial ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="part_number_on">
                    <div class="col-4">
                        <label class="form-label mb-0">P/N ON</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="part_number_on" maxlength="100" value="{{ old('part_number_on', $f?->part_number_on ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="serial_number_on">
                    <div class="col-4">
                        <label class="form-label mb-0">S/N ON</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="serial_number_on" maxlength="100" value="{{ old('serial_number_on', $f?->serial_number_on ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="system_name">
                    <div class="col-4">
                        <label class="form-label mb-0">Система</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="system_name" id="failure_system_select">
                            <option value="">Выберите систему</option>
                            @foreach(($failureSystems ?? []) as $sys)
                                <option value="{{ $sys->system_name }}" {{ old('system_name', $f?->system_name ?? '') == $sys->system_name ? 'selected' : '' }}>{{ $sys->system_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="subsystem_name">
                    <div class="col-4">
                        <label class="form-label mb-0">Подсистема</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="subsystem_name" id="failure_subsystem_select">
                            <option value="">Выберите подсистему</option>
                            @foreach(($failureSubsystems ?? []) as $sub)
                                <option value="{{ $sub->subsystem_name }}" data-system="{{ $sub->system_name }}" {{ old('subsystem_name', $f?->subsystem_name ?? '') == $sub->subsystem_name ? 'selected' : '' }}>{{ $sub->subsystem_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="component_sne_hours">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка СНЭ</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="component_sne_hours" step="1" value="{{ old('component_sne_hours', $f?->component_sne_hours !== null ? (int)$f->component_sne_hours : '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="component_ppr_hours">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка ППР</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="component_ppr_hours" step="1" value="{{ old('component_ppr_hours', $f?->component_ppr_hours !== null ? (int)$f->component_ppr_hours : '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="component_hours_unit">
                    <div class="col-4">
                        <label class="form-label mb-0">Единица измерения наработки КИ</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="component_hours_unit">
                            <option value="">—</option>
                            <option value="Часы" {{ old('component_hours_unit', $f?->component_hours_unit ?? '') == 'Часы' ? 'selected' : '' }}>Часы</option>
                            <option value="Циклы" {{ old('component_hours_unit', $f?->component_hours_unit ?? '') == 'Циклы' ? 'selected' : '' }}>Циклы</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="resolution_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата устранения</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="resolution_date" value="{{ old('resolution_date', $f?->resolution_date ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="component_cause">
                    <div class="col-4">
                        <label class="form-label mb-0">Причина неисправности КИ</label>
                    </div>
                    <div class="col-8">
                        <textarea class="form-control" name="component_cause" rows="3">{{ old('component_cause', $f?->component_cause ?? '') }}</textarea>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="taken_measure_id">
                    <div class="col-4">
                        <label class="form-label mb-0">Принятые меры</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="taken_measure_id">
                            <option value="">Выберите меру</option>
                            @foreach(($takenMeasures ?? []) as $measure)
                                <option value="{{ $measure->id }}" {{ (string)old('taken_measure_id', $f?->taken_measure_id ?? '') === (string)$measure->id ? 'selected' : '' }}>{{ $measure->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="wo_number">
                    <div class="col-4">
                        <label class="form-label mb-0">Work orders</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="wo_number" value="{{ old('wo_number', $f?->wo_number ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="wo_status_id">
                    <div class="col-4">
                        <label class="form-label mb-0">Статус WO</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="wo_status_id">
                            <option value="">Выберите статус</option>
                            @foreach(($woStatuses ?? []) as $status)
                                <option value="{{ $status->id }}" {{ (string)old('wo_status_id', $f?->wo_status_id ?? '') === (string)$status->id ? 'selected' : '' }}>{{ $status->code ? $status->code . ' - ' : '' }}{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="work_order_number">
                    <div class="col-4">
                        <label class="form-label mb-0">Номер карты наряд</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="work_order_number" value="{{ old('work_order_number', $f?->work_order_number ?? '') }}">
                    </div>
                </div>

                <hr class="my-3">
                <div class="form-label mb-2">Прочие поля</div>
                <div class="row mb-3" data-form-field="resolution_method">
                    <div class="col-4">
                        <label class="form-label mb-0">Метод устранения</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="resolution_method">
                            <option value="">Выберите метод</option>
                            <option value="repair" {{ old('resolution_method', $f?->resolution_method ?? '') == 'repair' ? 'selected' : '' }}>Ремонт</option>
                            <option value="replacement" {{ old('resolution_method', $f?->resolution_method ?? '') == 'replacement' ? 'selected' : '' }}>Замена</option>
                            <option value="adjustment" {{ old('resolution_method', $f?->resolution_method ?? '') == 'adjustment' ? 'selected' : '' }}>Регулировка</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aircraft_serial">
                    <div class="col-4">
                        <label class="form-label mb-0">Заводской номер ВС</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control readonly-field" name="aircraft_serial" id="failure_aircraft_serial" readonly value="{{ old('aircraft_serial', $f?->aircraft_serial ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aircraft_manufacture_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата изготовления ВС</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control readonly-field" name="aircraft_manufacture_date" id="failure_aircraft_manufacture_date" readonly value="{{ old('aircraft_manufacture_date', $f?->aircraft_manufacture_date ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aircraft_repair_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата ремонта ВС</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="aircraft_repair_date" value="{{ old('aircraft_repair_date', $f?->aircraft_repair_date ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="previous_repair_location">
                    <div class="col-4">
                        <label class="form-label mb-0">Место предыдущего ремонта</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="previous_repair_location" value="{{ old('previous_repair_location', $f?->previous_repair_location ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="aircraft_repairs_count">
                    <div class="col-4">
                        <label class="form-label mb-0">Количество ремонтов ВС</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="aircraft_repairs_count" value="{{ old('aircraft_repairs_count', $f?->aircraft_repairs_count ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="operator">
                    <div class="col-4">
                        <label class="form-label mb-0">Эксплуатант</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="operator">
                            <option value="">Выберите эксплуатанта</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="event_location">
                    <div class="col-4">
                        <label class="form-label mb-0">Место события</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="event_location" value="{{ old('event_location', $f?->event_location ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="consequence_id">
                    <div class="col-4">
                        <label class="form-label mb-0">Последствия</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="consequence_id">
                            <option value="">Выберите последствие</option>
                            @foreach(($failureConsequences ?? []) as $cons)
                                <option value="{{ $cons->id }}" {{ (string)old('consequence_id', $f?->consequence_id ?? '') === (string)$cons->id ? 'selected' : '' }}>{{ $cons->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="component_malfunction">
                    <div class="col-4">
                        <label class="form-label mb-0">Проявление неисправности КИ</label>
                    </div>
                    <div class="col-8">
                        <textarea class="form-control" name="component_malfunction" rows="3">{{ old('component_malfunction', $f?->component_malfunction ?? '') }}</textarea>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="manufacturer">
                    <div class="col-4">
                        <label class="form-label mb-0">Завод изготовитель</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="manufacturer" value="{{ old('manufacturer', $f?->manufacturer ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="removal_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата демонтажа</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="removal_date" value="{{ old('removal_date', $f?->removal_date ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="production_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата производства</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="production_date" value="{{ old('production_date', $f?->production_date ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="component_repairs_count">
                    <div class="col-4">
                        <label class="form-label mb-0">Количество ремонтов КИ</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="component_repairs_count" value="{{ old('component_repairs_count', $f?->component_repairs_count ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="previous_installation_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Предыдущая дата установки агрегата</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="previous_installation_date" value="{{ old('previous_installation_date', $f?->previous_installation_date ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="repair_factory">
                    <div class="col-4">
                        <label class="form-label mb-0">Ремонтный завод</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="repair_factory" value="{{ old('repair_factory', $f?->repair_factory ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="component_repair_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата ремонта КИ</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="component_repair_date" value="{{ old('component_repair_date', $f?->component_repair_date ?? '') }}">
                    </div>
                </div>

                <div class="row mb-3" data-form-field="engine_type_id">
                    <div class="col-4">
                        <label class="form-label mb-0">Тип двигателя</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="engine_type_id" id="failure_engine_type">
                            <option value="">Выберите тип двигателя</option>
                            @foreach(($engineTypes ?? []) as $etype)
                                <option value="{{ $etype->id }}" {{ (string)old('engine_type_id', $f?->engine_type_id ?? '') === (string)$etype->id ? 'selected' : '' }}>{{ $etype->code ? $etype->code . ' - ' : '' }}{{ $etype->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_number_id">
                    <div class="col-4">
                        <label class="form-label mb-0">Номер двигателя</label>
                    </div>
                    <div class="col-8">
                        <select class="form-select" name="engine_number_id" id="failure_engine_number">
                            <option value="">Выберите номер двигателя</option>
                            @foreach(($engineNumbers ?? []) as $enum)
                                <option value="{{ $enum->id }}" data-engine-type-id="{{ $enum->engine_type_id }}" {{ (string)old('engine_number_id', $f?->engine_number_id ?? '') === (string)$enum->id ? 'selected' : '' }}>
                                    {{ $enum->number }}
                                    @if($enum->engineType)
                                        ({{ $enum->engineType->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_release_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата выпуска двигателя</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="engine_release_date" value="{{ old('engine_release_date', $f?->engine_release_date ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_installation_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата последней установки на ВС</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="engine_installation_date" value="{{ old('engine_installation_date', $f?->engine_installation_date ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_sne_hours">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка двигателя СНЭ (часы)</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="engine_sne_hours" step="0.1" value="{{ old('engine_sne_hours', $f?->engine_sne_hours ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_ppr_hours">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка двигателя ППР (часы)</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="engine_ppr_hours" step="0.1" value="{{ old('engine_ppr_hours', $f?->engine_ppr_hours ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_sne_cycles">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка двигателя СНЭ (циклы/отборы)</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="engine_sne_cycles" step="0.1" value="{{ old('engine_sne_cycles', $f?->engine_sne_cycles ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_ppr_cycles">
                    <div class="col-4">
                        <label class="form-label mb-0">Наработка двигателя ППР (циклы/отборы)</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="engine_ppr_cycles" step="0.1" value="{{ old('engine_ppr_cycles', $f?->engine_ppr_cycles ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_repair_date">
                    <div class="col-4">
                        <label class="form-label mb-0">Дата ремонта</label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control" name="engine_repair_date" value="{{ old('engine_repair_date', $f?->engine_repair_date ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_repair_location">
                    <div class="col-4">
                        <label class="form-label mb-0">Место ремонта</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="engine_repair_location" value="{{ old('engine_repair_location', $f?->engine_repair_location ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="engine_repairs_count">
                    <div class="col-4">
                        <label class="form-label mb-0">Количество ремонтов</label>
                    </div>
                    <div class="col-8">
                        <input type="number" class="form-control" name="engine_repairs_count" value="{{ old('engine_repairs_count', $f?->engine_repairs_count ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="owner">
                    <div class="col-4">
                        <label class="form-label mb-0">Собственник</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="owner" value="{{ old('owner', $f?->owner ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="position">
                    <div class="col-4">
                        <label class="form-label mb-0">Позиция</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" name="position" value="{{ old('position', $f?->position ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3" data-form-field="created_by">
                    <div class="col-4">
                        <label class="form-label mb-0">Пользователь создавший КУНАТ</label>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control" readonly value="{{ auth()->user()->name ?? '' }}">
                    </div>
                </div>
                @if($isEdit)
                <hr class="my-3">
                <div class="form-label mb-2">Прикреплённые файлы</div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div id="failure-attachments-list" class="failure-attachments-list mb-3">
                            @foreach($f->attachments ?? [] as $att)
                            <div class="file-preview-item" data-file-id="{{ $att->id }}" data-file-path="{{ e($att->path) }}" data-file-name="{{ e($att->original_name) }}" data-file-type="{{ e($att->mime_type ?? '') }}" data-file-size="{{ $att->size ?? 0 }}">
                                <div class="file-preview-icon">
                                    @if(Str::startsWith($att->mime_type ?? '', 'image/'))
                                    <img class="file-preview-thumb" src="{{ Storage::disk('public')->url($att->path) }}" alt="" loading="lazy">
                                    @else
                                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @endif
                                </div>
                                <div class="file-preview-info">
                                    <div class="file-preview-name" title="{{ $att->original_name }}">{{ $att->original_name }}</div>
                                    <div class="file-preview-size">{{ $att->size ? number_format((float)$att->size / 1024, 1) . ' KB' : '—' }}</div>
                                </div>
                                <button type="button" class="file-preview-remove" title="Удалить" aria-label="Удалить файл"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </div>
                            @endforeach
                        </div>
                        <div class="modern-file-upload multi-file-upload" id="failure-attachments-upload">
                            <div class="file-upload-header">
                                <div class="file-upload-text">
                                    <h4>Добавить файлы</h4>
                                    <p>несколько штук • макс. 50 MB</p>
                                </div>
                            </div>
                            <div class="file-upload-content multi-file-content" id="failure-attachments-preview">
                                <div class="file-upload-placeholder">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <span>Перетащите файлы сюда или нажмите для выбора</span>
                                </div>
                            </div>
                            <input type="file" multiple style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.zip,.rar,image/*" id="failure-attachments-input">
                        </div>
                    </div>
                </div>
                @else
                <div class="row mb-3">
                    <div class="col-4"><label class="form-label mb-0">Вложения</label></div>
                    <div class="col-8"><p class="form-control-plaintext small text-muted mb-0">Файлы можно прикрепить после сохранения отказа.</p></div>
                </div>
                @endif
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="efds-actions mb-0">
                    <button type="submit" class="btn efds-btn efds-btn--primary">Сохранить</button>
                    <a href="{{ route('modules.reliability.index', ['tab' => 'failures']) }}" class="btn efds-btn efds-btn--outline-primary">Отмена</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var hiddenFormFields = @json($hiddenFormFields ?? []);
    hiddenFormFields.forEach(function(key) {
        document.querySelectorAll('[data-form-field="' + key + '"]').forEach(function(el) { el.classList.add('d-none'); });
    });

    var acSelect = document.getElementById('failure_aircraft_number');
    var typeInput = document.getElementById('failure_aircraft_type');
    var typeCodeInput = document.getElementById('failure_type_code');
    var modificationCodeInput = document.getElementById('failure_modification_code');
    var serialInput = document.getElementById('failure_aircraft_serial');
    var manufactureInput = document.getElementById('failure_aircraft_manufacture_date');

    if (acSelect && typeInput && typeCodeInput && modificationCodeInput && serialInput && manufactureInput) {
        acSelect.addEventListener('change', function() {
            var option = acSelect.selectedOptions[0];
            if (!option || !option.value) {
                typeInput.value = '';
                typeCodeInput.value = '';
                modificationCodeInput.value = '';
                serialInput.value = '';
                manufactureInput.value = '';
                return;
            }
            typeInput.value = option.getAttribute('data-type') || '';
            typeCodeInput.value = option.getAttribute('data-type-code') || '';
            modificationCodeInput.value = option.getAttribute('data-modification-code') || '';
            serialInput.value = option.getAttribute('data-serial') || '';
            manufactureInput.value = option.getAttribute('data-manufacture') || '';
        });
        acSelect.dispatchEvent(new Event('change'));
    }

    (function initAggregateModal() {
        var modalEl = document.getElementById('aggregateSelectModal');
        var aggInput = document.getElementById('failure_aggregate_type');
        var sysSelect = document.getElementById('agg_modal_system');
        var subSelect = document.getElementById('agg_modal_subsystem');
        var aircraftTypeSelect = document.getElementById('agg_modal_aircraft_type');
        var searchInput = document.getElementById('agg_modal_search');
        var tbody = document.getElementById('agg_modal_tbody');
        var placeholder = document.getElementById('agg_modal_placeholder');
        var tableWrap = document.getElementById('agg_modal_table');
        var addForm = document.getElementById('agg_modal_add_form');
        var btnAdd = document.getElementById('agg_modal_btn_add');
        var addCancel = document.getElementById('agg_add_cancel');
        var addSave = document.getElementById('agg_add_save');
        var addCode = document.getElementById('agg_add_code');
        var addName = document.getElementById('agg_add_name');
        var addAircraftType = document.getElementById('agg_add_aircraft_type');
        var addSystem = document.getElementById('agg_add_system');
        var addSubsystem = document.getElementById('agg_add_subsystem');
        var addMessage = document.getElementById('agg_add_message');
        if (!modalEl || !aggInput || !sysSelect || !subSelect || !searchInput || !tbody || !tableWrap || !placeholder) return;

        var allSubOpts = Array.from(subSelect.querySelectorAll('option[data-system]'));
        var allAddSubOpts = addSubsystem ? Array.from(addSubsystem.querySelectorAll('option[data-system]')) : [];
        var searchTimeout = null;
        var modalApiBase = '{{ url()->route('modules.reliability.aggregates.modal') }}';
        var storeFromModalUrl = '{{ route('modules.reliability.aggregates.store-from-modal') }}';
        var csrfToken = document.querySelector('input[name="_token"]') && document.querySelector('input[name="_token"]').value;

        function buildSubsystemOptions(systemVal, targetSelect, opts) {
            if (!targetSelect) return;
            targetSelect.innerHTML = '<option value="">Выберите подсистему</option>';
            if (!systemVal || systemVal === '__free__') {
                targetSelect.disabled = true;
                return;
            }
            targetSelect.disabled = false;
            (opts || allSubOpts).forEach(function(opt) {
                if (opt.getAttribute('data-system') === systemVal) {
                    targetSelect.appendChild(opt.cloneNode(true));
                }
            });
        }

        function loadModalAggregates() {
            var system = sysSelect.value;
            var subsystem = subSelect.value;
            var search = searchInput.value.trim();
            var aircraftTypeId = aircraftTypeSelect ? aircraftTypeSelect.value : '';
            var canLoad = system === '' || system === '__free__' || (system && subsystem);
            if (!canLoad) {
                tableWrap.style.display = 'none';
                placeholder.style.display = 'block';
                placeholder.textContent = 'Выберите систему и подсистему для фильтра';
                return;
            }
            tableWrap.style.display = 'table';
            placeholder.style.display = 'none';
            tbody.innerHTML = '<tr><td colspan="2" class="text-muted small">Загрузка...</td></tr>';
            var url = modalApiBase + '?system=' + encodeURIComponent(system || '') + '&subsystem=' + encodeURIComponent(subsystem || '') + '&search=' + encodeURIComponent(search);
            if (aircraftTypeId) url += '&aircraft_type_id=' + encodeURIComponent(aircraftTypeId);
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function(data) {
                    var items = (data && data.aggregates) ? data.aggregates : [];
                    if (items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="2" class="text-muted small">Ничего не найдено</td></tr>';
                        return;
                    }
                    tbody.innerHTML = '';
                    items.forEach(function(a, idx) {
                        var tr = document.createElement('tr');
                        tr.style.cursor = 'pointer';
                        tr.dataset.name = (a && a.name) ? String(a.name) : '';
                        tr.innerHTML = '<td>' + (idx + 1) + '</td><td>' + escapeHtml(tr.dataset.name) + '</td>';
                        tr.addEventListener('click', function() {
                            aggInput.value = this.dataset.name || '';
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        });
                        tbody.appendChild(tr);
                    });
                })
                .catch(function(err) {
                    tbody.innerHTML = '<tr><td colspan="2" class="text-danger small">Ошибка загрузки</td></tr>';
                });
        }
        function escapeHtml(s) {
            var div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }

        if (btnAdd && addForm) {
            btnAdd.addEventListener('click', function() {
                addForm.style.display = addForm.style.display === 'none' ? 'block' : 'none';
                if (addForm.style.display === 'block') {
                    addMessage.style.display = 'none';
                    addCode.value = '';
                    addName.value = '';
                    if (addAircraftType) addAircraftType.value = '';
                    if (addSystem) addSystem.value = '';
                    if (addSubsystem) { addSubsystem.innerHTML = '<option value="">Выберите систему</option>'; addSubsystem.disabled = true; }
                }
            });
        }
        if (addCancel && addForm) {
            addCancel.addEventListener('click', function() {
                addForm.style.display = 'none';
                addMessage.style.display = 'none';
            });
        }
        if (addSystem && addSubsystem && allAddSubOpts.length) {
            addSystem.addEventListener('change', function() {
                buildSubsystemOptions(this.value, addSubsystem, allAddSubOpts);
                addSubsystem.value = '';
            });
        }
        if (addSave && storeFromModalUrl && csrfToken) {
            addSave.addEventListener('click', function() {
                var name = (addName && addName.value) ? addName.value.trim() : '';
                if (!name) {
                    if (addMessage) { addMessage.style.display = 'block'; addMessage.className = 'small mt-1 text-danger'; addMessage.textContent = 'Укажите наименование.'; }
                    return;
                }
                var payload = {
                    aggregate_code: (addCode && addCode.value) ? addCode.value.trim() : '',
                    aggregate_name_display: name,
                    aircraft_type_id: (addAircraftType && addAircraftType.value) ? parseInt(addAircraftType.value, 10) : null,
                    failure_system_id: (addSubsystem && addSubsystem.value) ? parseInt(addSubsystem.value, 10) : null
                };
                if (addMessage) { addMessage.style.display = 'none'; addMessage.textContent = ''; }
                addSave.disabled = true;
                fetch(storeFromModalUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(payload),
                    credentials: 'same-origin'
                })
                    .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
                    .then(function(res) {
                        if (res.ok && res.data && res.data.success) {
                            if (addForm) addForm.style.display = 'none';
                            if (addName) addName.value = '';
                            if (addCode) addCode.value = '';
                            if (aggInput && res.data.aggregate && res.data.aggregate.name) aggInput.value = res.data.aggregate.name;
                            loadModalAggregates();
                            if (addMessage) { addMessage.style.display = 'block'; addMessage.className = 'small mt-1 text-success'; addMessage.textContent = res.data.message || 'Агрегат добавлен.'; setTimeout(function() { addMessage.style.display = 'none'; }, 2000); }
                        } else {
                            var msg = (res.data && res.data.message) ? res.data.message : (res.data && res.data.errors) ? Object.values(res.data.errors).flat().join(' ') : 'Ошибка сохранения';
                            if (addMessage) { addMessage.style.display = 'block'; addMessage.className = 'small mt-1 text-danger'; addMessage.textContent = msg; }
                        }
                    })
                    .catch(function() {
                        if (addMessage) { addMessage.style.display = 'block'; addMessage.className = 'small mt-1 text-danger'; addMessage.textContent = 'Ошибка сети'; }
                    })
                    .finally(function() { addSave.disabled = false; });
            });
        }

        sysSelect.addEventListener('change', function() {
            buildSubsystemOptions(this.value, subSelect, allSubOpts);
            subSelect.value = '';
            loadModalAggregates();
        });
        subSelect.addEventListener('change', loadModalAggregates);
        if (aircraftTypeSelect) aircraftTypeSelect.addEventListener('change', loadModalAggregates);
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadModalAggregates, 300);
        });

        modalEl.addEventListener('show.bs.modal', function() {
            searchInput.value = '';
            if (addForm) addForm.style.display = 'none';
            buildSubsystemOptions(sysSelect.value, subSelect, allSubOpts);
            loadModalAggregates();
        });

        setTimeout(function() { loadModalAggregates(); }, 100);
    })();

    var engineTypeSelect = document.getElementById('failure_engine_type');
    var engineNumberSelect = document.getElementById('failure_engine_number');
    if (engineTypeSelect && engineNumberSelect) {
        var allEngineNumberOptions = Array.from(engineNumberSelect.options);
        engineTypeSelect.addEventListener('change', function() {
            var typeId = this.value;
            engineNumberSelect.innerHTML = '<option value="">Выберите номер двигателя</option>';
            allEngineNumberOptions.forEach(function(opt) {
                if (opt.value === '') return;
                var optTypeId = opt.getAttribute('data-engine-type-id');
                if (!optTypeId || !typeId || optTypeId === typeId) {
                    engineNumberSelect.appendChild(opt.cloneNode(true));
                }
            });
        });
    }

    @if($isEdit && $f)
    (function failureAttachmentsInit() {
        var failureId = {{ (int) $f->id }};
        var uploadUrl = @json(route('modules.reliability.failures.attachments.upload', $f));
        var deleteUrl = @json(route('modules.reliability.failures.attachments.delete', $f));
        var serveBase = @json(url()->route('modules.reliability.failures.attachments.serve', $f));
        var downloadBase = @json(url()->route('modules.reliability.failures.attachments.download', $f));
        var csrf = (document.querySelector('input[name="_token"]') || {}).value || '';
        var listEl = document.getElementById('failure-attachments-list');
        var uploadBlock = document.getElementById('failure-attachments-upload');
        var previewEl = document.getElementById('failure-attachments-preview');
        var inputEl = document.getElementById('failure-attachments-input');
        var placeholderHtml = '<div class="file-upload-placeholder"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg><span>Перетащите файлы сюда или нажмите для выбора</span></div>';

        function formatSize(bytes) {
            if (!bytes) return '—';
            var u = ['B','KB','MB','GB'], i = 0, s = bytes;
            while (s >= 1024 && i < u.length - 1) { s /= 1024; i++; }
            return s.toFixed(1) + ' ' + u[i];
        }

        if (!uploadBlock || !previewEl || !inputEl) return;

        listEl && listEl.querySelectorAll('.file-preview-item').forEach(function(item) {
            var path = item.getAttribute('data-file-path');
            var name = item.getAttribute('data-file-name') || '';
            var type = (item.getAttribute('data-file-type') || '').toLowerCase();
            var fileId = item.getAttribute('data-file-id');
            var serveUrl = serveBase + (path ? '?path=' + encodeURIComponent(path) : '');
            var downloadUrl = downloadBase + (path ? '?path=' + encodeURIComponent(path) : '');
            item.addEventListener('click', function(e) {
                if (e.target.closest('.file-preview-remove')) return;
                if (type.indexOf('image/') === 0) { window.open(serveUrl, '_blank'); return; }
                window.open(downloadUrl, '_blank');
            });
            item.querySelector('.file-preview-remove').addEventListener('click', function(e) {
                e.stopPropagation();
                if (!fileId || !confirm('Удалить этот файл?')) return;
                fetch(deleteUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ file_id: fileId }) })
                    .then(function(r) { if (r.ok) item.remove(); });
            });
        });

        function addFileToPreview(file, fileId, path, name, size, type) {
            var wrap = document.createElement('div');
            wrap.className = 'file-preview-item';
            if (fileId) wrap.setAttribute('data-file-id', fileId);
            if (path) wrap.setAttribute('data-file-path', path);
            wrap.setAttribute('data-file-name', name || '');
            wrap.setAttribute('data-file-type', type || '');
            wrap.innerHTML = '<div class="file-preview-icon"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div><div class="file-preview-info"><div class="file-preview-name" title="' + (name || '').replace(/"/g, '&quot;') + '">' + (name || 'файл') + '</div><div class="file-preview-size">' + formatSize(size) + '</div></div><button type="button" class="file-preview-remove" title="Удалить"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>';
            var serveUrl = serveBase + (path ? '?path=' + encodeURIComponent(path) : '');
            var downloadUrl = downloadBase + (path ? '?path=' + encodeURIComponent(path) : '');
            wrap.addEventListener('click', function(e) { if (e.target.closest('.file-preview-remove')) return; window.open(path ? downloadUrl : serveUrl, '_blank'); });
            wrap.querySelector('.file-preview-remove').addEventListener('click', function(e) {
                e.stopPropagation();
                if (!fileId) { wrap.remove(); if (previewEl.children.length === 0) previewEl.innerHTML = placeholderHtml; return; }
                if (!confirm('Удалить этот файл?')) return;
                fetch(deleteUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ file_id: fileId }) }).then(function(r) { if (r.ok) { wrap.remove(); if (previewEl.children.length === 0) previewEl.innerHTML = placeholderHtml; } });
            });
            if (listEl) listEl.appendChild(wrap); else previewEl.appendChild(wrap);
        }

        uploadBlock.querySelector('.file-upload-header') && uploadBlock.querySelector('.file-upload-header').addEventListener('click', function() { inputEl.click(); });
        uploadBlock.addEventListener('dragover', function(e) { e.preventDefault(); uploadBlock.classList.add('drag-over'); });
        uploadBlock.addEventListener('dragleave', function(e) { e.preventDefault(); uploadBlock.classList.remove('drag-over'); });
        uploadBlock.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadBlock.classList.remove('drag-over');
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) { inputEl.files = e.dataTransfer.files; inputEl.dispatchEvent(new Event('change', { bubbles: true })); }
        });
        inputEl.addEventListener('change', function() {
            var files = inputEl.files;
            if (!files || !files.length) return;
            for (var i = 0; i < files.length; i++) {
                (function(file) {
                    var item = document.createElement('div');
                    item.className = 'file-preview-item';
                    item.innerHTML = '<div class="file-preview-icon"><span class="spinner-border spinner-border-sm text-primary"></span></div><div class="file-preview-info"><div class="file-preview-name">' + (file.name || '').replace(/</g, '&lt;') + '</div><div class="file-preview-size">' + formatSize(file.size) + '</div></div>';
                    if (previewEl.querySelector('.file-upload-placeholder')) previewEl.innerHTML = '';
                    previewEl.appendChild(item);
                    var fd = new FormData();
                    fd.append('file', file);
                    fetch(uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: fd })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            item.remove();
                            if (data.success && data.file) {
                                addFileToPreview(null, data.file.id, data.file.path, data.file.name, data.file.size, data.file.type);
                            }
                        })
                        .catch(function() { item.remove(); if (previewEl.children.length === 0) previewEl.innerHTML = placeholderHtml; });
                })(files[i]);
            }
            inputEl.value = '';
        });
    })();
    @endif
});
</script>

<style>
.back-button {
    color: #007bff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 500;
    transition: color 0.3s ease;
    border: none;
    background: none;
    padding: 8px 0;

    
}


.main_screen
    {width:1000px;
    background-color: #fff !important;
  
}
    .form-control {
        width:100% !important;
        background-color: #fff !important;
        }


.back-button:hover {
    color: #0056b3;
    text-decoration: none;
}
.back-button i {
    font-size: 14px;
}
</style>
@endsection
