<!-- Панель управления -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2 align-items-center">
                <select class="form-select form-select-sm" style="width: auto;">
                    <option>АБС</option>
                </select>
                <select class="form-select form-select-sm" style="width: auto;">
                    <option>Л.Ч.</option>
                </select>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="valuesCheckSystems">
                    <label class="form-check-label" for="valuesCheckSystems">Значения</label>
                </div>
            </div>
            <button class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-cog"></i>
            </button>
        </div>
    </div>
</div>

<!-- Заголовок таблицы -->
<div class="card mb-2">
    <div class="card-body p-2">
        <div class="row align-items-center">
            <div class="col-6">
                <strong>СИСТЕМЫ</strong>
            </div>
            <div class="col-6 text-end">
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Список систем с графиками -->
<div class="card">
    <div class="card-body p-0">
        @php
            $systems = [
                ['code' => '034', 'name' => 'ПИЛОТАЖНО-НАВИГАЦИОННОЕ ОБОРУДОВАНИЕ', 'green' => 85, 'blue' => 45],
                ['code' => '023', 'name' => 'СВЯЗНОЕ ОБОРУДОВАНИЕ', 'green' => 70, 'blue' => 60],
                ['code' => '142', 'name' => 'БОРТОВЫЕ СРЕДСТВА КОНТРОЛЯ И РЕГИСТРАЦИИ ПОЛЕТНЫХ ДАННЫХ', 'green' => 50, 'blue' => 30],
                ['code' => '065', 'name' => 'ВИНТЫ ВЕРТОЛЕТОВ', 'green' => 90, 'blue' => 80],
                ['code' => '072', 'name' => 'ГАЗОТУРБИННЫЙ ДВИГАТЕЛЬ', 'green' => 75, 'blue' => 70],
                ['code' => '024', 'name' => 'ЭЛЕКТРОСНАБЖЕНИЕ', 'green' => 60, 'blue' => 55],
                ['code' => '028', 'name' => 'ТОПЛИВНАЯ СИСТЕМА', 'green' => 40, 'blue' => 65],
            ];
        @endphp
        
        @foreach($systems as $system)
        <div class="system-row" style="border-bottom: 1px solid #dee2e6; padding: 15px;">
            <div class="row align-items-center">
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-plus-circle text-muted me-2" style="cursor: pointer;"></i>
                        <span class="fw-bold">{{ $system['code'] }}-{{ $system['name'] }}</span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex flex-column gap-2">
                        <!-- Зеленый график -->
                        <div class="position-relative" style="height: 25px; background-color: #f8f9fa; border-radius: 4px; overflow: hidden;">
                            <div class="position-absolute top-0 start-0 h-100" style="background-color: #28a745; width: {{ $system['green'] }}%;"></div>
                        </div>
                        <!-- Синий график -->
                        <div class="position-relative" style="height: 25px; background-color: #f8f9fa; border-radius: 4px; overflow: hidden;">
                            <div class="position-absolute top-0 start-0 h-100" style="background-color: #0d6efd; width: {{ $system['blue'] }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
.system-row {
    transition: background-color 0.2s;
}

.system-row:hover {
    background-color: #f8f9fa;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}
</style>







