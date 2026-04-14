@extends('layout.main')

@section('content')
<div class="container-fluid reliability-dashboards">
    <div id="dashboards-page-loader" class="dashboards-page-loader">
        <div class="dashboards-page-loader__box">
            <div class="spinner-border text-light" role="status" aria-hidden="true"></div>
            <div class="dashboards-page-loader__text">Loading dashboard...</div>
        </div>
    </div>
    {{-- Header: Project + KPIs + Filters --}}
    <div class="dashboard-header mb-4" style="background: #1E64D4 !important; color: #fff; padding: 1rem 1.5rem; border-radius: 8px;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-4">
                <div>
                    <div class="text-uppercase small opacity-75">PROJECT</div>
                    <div class="fw-bold fs-5">{{ $selectedProject === 'all' ? 'All' : $selectedProject }}</div>
                </div>
                <div class="d-flex gap-3">
                    <div class="kpi-box px-3 py-2 rounded" style="background: rgba(254, 205, 69, 0.25); color: white;">
                        <span class="small text-uppercase opacity-75">TASK</span>
                        <div class="fw-bold">{{ number_format($totalTask / 1000, 2) }}K</div>
                    </div>
                    <div class="kpi-box px-3 py-2 rounded" style="background: rgba(254, 205, 69, 0.25); color: white;">
                        <span class="small text-uppercase opacity-75">MANHOURS</span>
                        <div class="fw-bold">{{ $totalMhrs >= 1e6 ? number_format($totalMhrs / 1e6, 0) . 'M' : number_format($totalMhrs / 1000, 2) . 'K' }}</div>
                    </div>
                    <div class="kpi-box px-3 py-2 rounded" style="background: rgba(254, 205, 69, 0.25); color: white;">
                        <span class="small text-uppercase opacity-75">EEF</span>
                        <div class="fw-bold">{{ $totalEef }}</div>
                    </div>
                </div>
            </div>
            <form method="GET" action="{{ route('modules.reliability.dashboards') }}" class="d-flex flex-wrap gap-2 align-items-end">
                <div>
                    <label class="form-label small mb-0 text-white opacity-75">PROJECT</label>
                    <select name="project" class="form-select form-select-sm" style="width: 180px;">
                        <option value="all" {{ $selectedProject === 'all' ? 'selected' : '' }}>All</option>
                        @foreach($projectList as $project)
                            <option value="{{ $project }}" {{ $selectedProject === $project ? 'selected' : '' }}>{{ $project }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-0 text-white opacity-75">CUSTOMER</label>
                    <select name="customer_name" class="form-select form-select-sm" style="width: 180px;">
                        <option value="all" {{ $selectedCustomer === 'all' ? 'selected' : '' }}>All</option>
                        @foreach($customerList as $customer)
                            <option value="{{ $customer }}" {{ $selectedCustomer === $customer ? 'selected' : '' }}>{{ $customer }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-0 text-white opacity-75">AIRCRAFT TYPE</label>
                    <select name="aircraft_type" class="form-select form-select-sm" style="width: 140px;">
                        <option value="all" {{ $selectedAircraftType === 'all' ? 'selected' : '' }}>All</option>
                        @foreach($aircraftTypes as $t)
                            <option value="{{ $t }}" {{ $selectedAircraftType === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-0 text-white opacity-75">TAIL NUMBER</label>
                    <select name="tail_number" class="form-select form-select-sm" style="width: 140px;">
                        <option value="all" {{ $selectedTailNumber === 'all' ? 'selected' : '' }}>All</option>
                        @foreach($tailNumbers as $t)
                            <option value="{{ $t }}" {{ $selectedTailNumber === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-0 text-white opacity-75">MSN</label>
                    <select name="msn" class="form-select form-select-sm" style="width: 140px;">
                        <option value="all" {{ ($selectedMsn ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                        @foreach($msnList as $msn)
                            <option value="{{ $msn }}" {{ ($selectedMsn ?? 'all') === $msn ? 'selected' : '' }}>{{ $msn }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-light">Apply</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- Project table --}}
        <div class="col-12">
            <div class="card h-100">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3">PROJECT</th>
                                    <th>PROJECT COUNT</th>
                                    <th>TASK</th>
                                    <th>MHRS</th>
                                    <th>EEF</th>
                                    <th style="width: 150px; min-width: 150px; max-width: 150px;">AIRCRAFT TYPE</th>
                                    <th style="width: 150px; min-width: 150px; max-width: 150px;">TAIL NUMBER</th>
                                    <th style="width: 400px; min-width: 400px; max-width: 400px;">SCOPE</th>
                                    <th>AGE</th>
                                    <th>AIRCRAFT TSN</th>
                                    <th class="pe-3">AIRCRAFT CSN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $row)
                                <tr>
                                    <td class="ps-3">{{ $row['project'] }}</td>
                                    <td>{{ $row['project_count'] }}</td>
                                    <td>{{ number_format($row['task']) }}</td>
                                    <td>{{ number_format($row['mhrs']) }}</td>
                                    <td>{{ $row['eef'] !== null ? $row['eef'] : '' }}</td>
                                    <td style="width: 150px; min-width: 150px; max-width: 150px;">{{ $row['aircraft_type'] ?? '' }}</td>
                                    <td style="width: 150px; min-width: 150px; max-width: 150px;">{{ $row['tail_number'] ?? '' }}</td>
                                    <td style="width: 400px; min-width: 400px; max-width: 400px; white-space: normal; word-break: break-word; overflow-wrap: anywhere;">{{ $row['scope'] ?? '' }}</td>
                                    <td>{{ $row['age'] ?? '' }}</td>
                                    <td>{{ $row['aircraft_tsn'] ?? '' }}</td>
                                    <td class="pe-3">{{ $row['aircraft_csn'] ?? '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td class="ps-3">Total</td>
                                    <td>{{ $totalProjectCount }}</td>
                                    <td>{{ number_format($totalTask) }}</td>
                                    <td>{{ number_format($totalMhrs) }}</td>
                                    <td>{{ $totalEef }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="pe-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        {{-- Task and manhours --}}
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title mb-3">TASK COUNT AND MANHOURS</h6>
                    <div style="height: 400px;">
                        <canvas id="barChart"></canvas>
                    </div>
                    <div class="d-flex gap-3 justify-content-center mt-2 small">
                        <span><span class="d-inline-block rounded-circle bg-primary me-1" style="width:10px;height:10px;"></span> MANHOURS</span>
                        <span><span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px; background:#1E64D4 !important;"></span> TASK</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Routine card by trade --}}
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title mb-3">ROUTINE CARD BY TRADE</h6>
                    <div style="height: 280px;">
                        <canvas id="pieRoutine"></canvas>
                    </div>
                    <div id="pieRoutineLegend" class="pie-legend-list mt-3"></div>
                </div>
            </div>
        </div>

        {{-- Nonroutine card by trade --}}
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title mb-3">NONROUTINE CARD BY TRADE</h6>
                    <div style="height: 280px;">
                        <canvas id="pieNonroutine"></canvas>
                    </div>
                    <div id="pieNonroutineLegend" class="pie-legend-list mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3 d-flex justify-content-between align-items-center">
                        <span>ATA VS NRC COUNT</span>
                        <span class="small text-muted">Total NRC Count: {{ $nrcAtaDistribution['total'] ?? 0 }}</span>
                    </h6>
                    <div style="height: 320px;">
                        <canvas id="nrcAtaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3 d-flex justify-content-between align-items-center">
                        <span>ATA VS EEF COUNT</span>
                        <span class="small text-muted">Total EEF Count: {{ $eefAtaDistribution['total'] ?? 0 }}</span>
                    </h6>
                    <div style="height: 320px;">
                        <canvas id="eefAtaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboards-page-loader {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(20, 33, 56, 0.55);
    z-index: 2000;
}

.dashboards-page-loader.is-visible {
    display: flex;
}

.dashboards-page-loader__box {
    min-width: 220px;
    padding: 18px 20px;
    border-radius: 10px;
    background: rgba(15, 23, 42, 0.9);
    color: #fff;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
}

.dashboards-page-loader__text {
    margin-top: 10px;
    font-size: 13px;
    letter-spacing: 0.2px;
}

.pie-legend-list {
    max-height: 180px;
    overflow-y: auto;
    font-size: 12px;
}

.pie-legend-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 2px 0;
}

.pie-legend-item__left {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
}

.pie-legend-item__dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex: 0 0 10px;
}

.pie-legend-item__label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pie-legend-item__value {
    color: #64748b;
    flex: 0 0 auto;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var pageLoader = document.getElementById('dashboards-page-loader');
    var filterForm = document.querySelector('.dashboard-header form[action]');

    function showPageLoader() {
        if (pageLoader) {
            pageLoader.classList.add('is-visible');
        }
    }

    function hidePageLoader() {
        if (pageLoader) {
            pageLoader.classList.remove('is-visible');
        }
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            showPageLoader();
        });
    }

    window.addEventListener('beforeunload', function() {
        showPageLoader();
    });

    var barData = @json($barChart);
    var routineByTrade = @json($routineByTrade);
    var nonroutineByTrade = @json($nonroutineByTrade);
    var nrcAta = @json($nrcAtaDistribution);
    var eefAta = @json($eefAtaDistribution);

    var colors = ['#5b9bd5', '#1e3a5f', '#ed7d31', '#7030a0', '#70ad47', '#44546a', '#2f5496', '#c55a11', '#5b9bd5', '#ffc000'];

    if (document.getElementById('barChart')) {
        var mh = (barData.manhours || []).map(Number);
        var tk = (barData.task || []).map(Number);
        var allPositive = mh.concat(tk).every(function (v) { return v > 0; });
        var yScale = allPositive
            ? { type: 'logarithmic', min: 1 }
            : { beginAtZero: true, ticks: { precision: 0 } };
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: barData.labels,
                datasets: [
                    { label: 'MANHOURS', data: mh, backgroundColor: 'rgba(91, 155, 213, 0.8)' },
                    { label: 'TASK', data: tk, backgroundColor: '#1E64D4' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: yScale },
                plugins: { legend: { display: false } }
            }
        });
    }

    function renderPieLegend(legendId, labels, values, bg) {
        var legendEl = document.getElementById(legendId);
        if (!legendEl) {
            return;
        }

        var total = values.reduce(function(sum, val) {
            return sum + Number(val || 0);
        }, 0);

        legendEl.innerHTML = labels.map(function(label, i) {
            var raw = Number(values[i] || 0);
            var percent = total > 0 ? ((raw / total) * 100).toFixed(2) : '0.00';
            return '<div class="pie-legend-item">'
                + '<span class="pie-legend-item__left">'
                + '<span class="pie-legend-item__dot" style="background:' + bg[i] + ';"></span>'
                + '<span class="pie-legend-item__label" title="' + String(label).replace(/"/g, '&quot;') + '">' + label + '</span>'
                + '</span>'
                + '<span class="pie-legend-item__value">' + raw + ' (' + percent + '%)</span>'
                + '</div>';
        }).join('');
    }

    function buildPieChart(id, data, legendId) {
        var labels = Object.keys(data || {});
        var values = Object.values(data || {});
        if (labels.length === 0) {
            labels = ['Нет данных'];
            values = [1];
        }
        var bg = labels.map(function(_, i) { return colors[i % colors.length]; });
        renderPieLegend(legendId, labels, values, bg);
        if (document.getElementById(id)) {
            new Chart(document.getElementById(id), {
                type: 'pie',
                data: { labels: labels, datasets: [{ data: values, backgroundColor: bg }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    var t = ctx.dataset.data.reduce(function(a,b){ return a+b; }, 0);
                                    var p = t ? (ctx.raw / t * 100).toFixed(2) : 0;
                                    return (ctx.raw >= 1000 ? (ctx.raw/1000).toFixed(2) + 'K' : ctx.raw) + ' (' + p + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    buildPieChart('pieRoutine', routineByTrade, 'pieRoutineLegend');
    buildPieChart('pieNonroutine', nonroutineByTrade, 'pieNonroutineLegend');

    function buildAtaBarChart(id, data, color) {
        var labels = (data && data.labels) ? data.labels : [];
        var values = (data && data.counts) ? data.counts.map(Number) : [];
        if (labels.length === 0) {
            labels = ['No data'];
            values = [0];
        }
        if (document.getElementById(id)) {
            new Chart(document.getElementById(id), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ data: values, backgroundColor: color, borderColor: color, borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { maxRotation: 0, autoSkip: false } }
                    }
                }
            });
        }
    }

    buildAtaBarChart('nrcAtaChart', nrcAta, '#1E64D4');
    buildAtaBarChart('eefAtaChart', eefAta, '#1E64D4');

    hidePageLoader();
});
</script>
@endsection
