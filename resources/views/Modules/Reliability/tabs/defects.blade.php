<!-- Хидер над таблицей (дизайн-система) -->
<div class="efds-table-header">
    <div class="efds-table-header__stats text-muted">
        <span class="me-2">На странице:</span>
        <select class="form-select form-select-sm d-inline-block" style="width: auto;" aria-label="Записей на странице">
            <option value="50">50</option>
            <option value="100" selected>100</option>
            <option value="200">200</option>
        </select>
        <span class="ms-2">Всего записей: 702</span>
    </div>
    <div class="efds-table-header__actions">
        <button type="button" class="btn efds-btn efds-btn--primary" disabled>
            <i class="fas fa-plus me-1"></i>Добавить дефект
        </button>
    </div>
</div>

<!-- Таблица дефектов -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0" style="font-size: 0.875rem;">
                <thead style="background: #1E64D4; color: white;">
                    <tr>
                        <th style="padding: 12px; width: 40px;"></th>
                        <th style="padding: 12px;">ID</th>
                        <th style="padding: 12px;">ДАТА</th>
                        <th style="padding: 12px;">БОРТОВОЙ № ВС</th>
                        <th style="padding: 12px;">НАРАБОТКА ВС В ЧАСАХ</th>
                        <th style="padding: 12px;">НАРАБОТКА ВС В ПОСАДКАХ</th>
                        <th style="padding: 12px;">НАРАБОТКА ВС ППР (ЧАС)</th>
                        <th style="padding: 12px;">НАРАБОТКА ВС ППР (ПОСАДКИ)</th>
                        <th style="padding: 12px;">ДАТА РЕМОНТА ВС</th>
                        <th style="padding: 12px;">МЕСТО ПРЕДЫДУЩЕГО РЕМОНТА</th>
                        <th style="padding: 12px;">КОЛИЧЕСТВО РЕМОНТОВ ВС</th>
                        <th style="padding: 12px;">ЭКСПЛУАТАНТ</th>
                        <th style="padding: 12px;">ПРОЯВЛЕНИЕ НЕИСПРАВНОСТИ</th>
                        <th style="padding: 12px;">ДЛЯ ВВОДА В<br>БДНАТ</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < 6; $i++)
                    <tr style="cursor: pointer;" onclick="viewDefectDetails({{ $i }})">
                        <td style="padding: 8px;">
                            <i class="fas fa-search text-muted"></i>
                        </td>
                        <td style="padding: 8px;">70{{ 7 - $i }}</td>
                        <td style="padding: 8px;">{{ \Carbon\Carbon::now()->subDays($i * 5)->format('d.m.Y') }}</td>
                        <td style="padding: 8px;">RA-2515{{ 4 - $i }}</td>
                        <td style="padding: 8px;">{{ 14934 - ($i * 200) }}</td>
                        <td style="padding: 8px;">{{ 20983 - ($i * 300) }}</td>
                        <td style="padding: 8px;">{{ 1492 - ($i * 50) }}</td>
                        <td style="padding: 8px;">{{ 1773 - ($i * 100) }}</td>
                        <td style="padding: 8px;">{{ $i % 2 == 0 ? \Carbon\Carbon::now()->subYears(2)->subMonths($i)->format('d.m.Y') : '' }}</td>
                        <td style="padding: 8px;">{{ $i % 2 == 0 ? 'АО ЮТЭЙР-ИНЖИНИРИНГ' : '' }}</td>
                        <td style="padding: 8px;">{{ 7 - $i }}</td>
                        <td style="padding: 8px;">{{ $i % 2 == 0 ? 'ЮТ-ВУ' : 'Ямал' }}</td>
                        <td style="padding: 8px;">{{ $i % 3 == 0 ? 'Коррозия, бринеллирование на рабочей поверхности' : ($i % 3 == 1 ? 'Коррозионные раковины на внутренней поверхности' : 'Выработка рабочей поверхности') }}</td>
                        <td style="padding: 8px;">
                            <div class="d-flex gap-2 align-items-center">
                                <input type="checkbox" class="form-check-input" {{ $i % 2 == 0 ? 'checked' : '' }}>
                                <i class="fas fa-file-alt text-muted"></i>
                            </div>
                        </td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function viewDefectDetails(id) {
    console.log('View defect details for ID:', id);
    // Здесь будет логика открытия детальной информации
}
</script>

<style>
.table thead th {
    font-weight: bold;
    font-size: 0.75rem;
    text-transform: uppercase;
    white-space: nowrap;
}

.table tbody td {
    vertical-align: middle;
    border-top: 1px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.pagination-sm .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.pagination .page-item.active .page-link {
    background-color: #1E64D4;
    border-color: #1E64D4;
}
</style>

