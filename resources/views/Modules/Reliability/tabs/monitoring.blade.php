<!-- Панель управления графиком -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <select class="form-select form-select-sm" style="width: 120px;" id="monitoringUnitSelect">
                    <option value="abs">АБС</option>
                    <option value="k1000">К1000 (полетов)</option>
                    <option value="k1000h">К1000 налет</option>
                </select>
                <select class="form-select form-select-sm" style="width:150px;" id="monitoringPeriodSelect">
                    <option value="month">Месяц</option>
                    <option value="quarter">Квартал</option>
                    <option value="year">Год</option>
                </select>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="trendCheck">
                    <label class="form-check-label" for="trendCheck">Тренд</label>
                </div>
                <input type="number" class="form-control form-control-sm" value="2" style="width: 60px;" id="polynomialDegree">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="valuesCheck">
                    <label class="form-check-label" for="valuesCheck">Значения</label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm" id="monitoringPdfBtn" title="Сохранить диаграмму в PDF">Отчет PDF</button>
              
               
            </div>
        </div>
    </div>
</div>

<!-- График -->
<div class="card" style="height: 500px;">
    <div class="card-body">
        <canvas id="monitoringChart"></canvas>
    </div>
</div>

<!-- Легенда (динамическая) -->
<div class="mt-3" id="monitoringLegend">
    <div class="d-flex gap-4 flex-wrap"></div>
</div>

<!-- Chart.js уже загружен в главном файле -->
<script>
(function() {
    var chartDataUrl = @json(route('modules.reliability.monitoring-chart-data'));
    var chartInstance = null;

    var palette = [
        '#dc3545', '#ffc107', '#0dcaf0', '#198754', '#6f42c1',
        '#fd7e14', '#20c997', '#0d6efd', '#d63384', '#adb5bd',
        '#6c757d'
    ];

    var lastData = null;

    function loadAndRender() {
        var unit = document.getElementById('monitoringUnitSelect').value;
        var period = document.getElementById('monitoringPeriodSelect').value;
        var params = new URLSearchParams();
        var form = document.getElementById('filtersForm');
        if (form) {
            var formData = new FormData(form);
            formData.forEach(function(value, key) {
                if (key && value !== undefined && value !== '') {
                    params.append(key, value);
                }
            });
        } else {
            new URLSearchParams(window.location.search).forEach(function(value, key) {
                if (key && value) params.append(key, value);
            });
        }
        params.set('unit', unit);
        params.set('period', period);
        var url = chartDataUrl + '?' + params.toString();

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) { lastData = data; renderChart(data); })
            .catch(function(e) { console.error('Monitoring chart error:', e); });
    }

    // Полиномиальная регрессия: возвращает массив y-значений для x = 0..n-1
    function polyFit(yValues, degree) {
        var n = yValues.length;
        if (n === 0) return [];
        degree = Math.min(degree, n - 1);

        // Строим матрицу нормальных уравнений (метод наименьших квадратов)
        var size = degree + 1;
        var mat = [];
        var rhs = [];
        for (var i = 0; i < size; i++) {
            mat[i] = [];
            rhs[i] = 0;
            for (var j = 0; j < size; j++) {
                mat[i][j] = 0;
            }
        }
        for (var k = 0; k < n; k++) {
            var x = k;
            var y = yValues[k];
            for (var i = 0; i < size; i++) {
                for (var j = 0; j < size; j++) {
                    mat[i][j] += Math.pow(x, i + j);
                }
                rhs[i] += y * Math.pow(x, i);
            }
        }

        // Гаусс
        for (var col = 0; col < size; col++) {
            var maxRow = col;
            for (var row = col + 1; row < size; row++) {
                if (Math.abs(mat[row][col]) > Math.abs(mat[maxRow][col])) maxRow = row;
            }
            var tmp = mat[col]; mat[col] = mat[maxRow]; mat[maxRow] = tmp;
            var tmpR = rhs[col]; rhs[col] = rhs[maxRow]; rhs[maxRow] = tmpR;

            if (Math.abs(mat[col][col]) < 1e-12) continue;
            for (var row = col + 1; row < size; row++) {
                var factor = mat[row][col] / mat[col][col];
                for (var j = col; j < size; j++) {
                    mat[row][j] -= factor * mat[col][j];
                }
                rhs[row] -= factor * rhs[col];
            }
        }

        var coeffs = new Array(size).fill(0);
        for (var i = size - 1; i >= 0; i--) {
            if (Math.abs(mat[i][i]) < 1e-12) continue;
            var sum = rhs[i];
            for (var j = i + 1; j < size; j++) {
                sum -= mat[i][j] * coeffs[j];
            }
            coeffs[i] = sum / mat[i][i];
        }

        var result = [];
        for (var x = 0; x < n; x++) {
            var val = 0;
            for (var p = 0; p < coeffs.length; p++) {
                val += coeffs[p] * Math.pow(x, p);
            }
            result.push(Math.round(val * 100) / 100);
        }
        return result;
    }

    function renderChart(data) {
        var canvas = document.getElementById('monitoringChart');
        if (!canvas) return;

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
            window.monitoringChartInstance = null;
        }

        var showValues = document.getElementById('valuesCheck').checked;
        var showTrend = document.getElementById('trendCheck').checked;
        var polyDegree = parseInt(document.getElementById('polynomialDegree').value) || 2;

        var yLabel;
        if (data.unit === 'k1000') {
            yLabel = 'К1000 (отказов на 1000 полётов)';
        } else if (data.unit === 'k1000h') {
            yLabel = 'К1000 налет (отказов на 1000 часов налёта)';
        } else {
            yLabel = 'Количество отказов';
        }

        var datasets = [];
        var legendItems = [];
        var idx = 0;
        var keys = Object.keys(data.datasets);

        // Считаем суммы по столбцам для тренда
        var totals = [];
        var n = data.labels.length;
        for (var i = 0; i < n; i++) totals[i] = 0;

        keys.forEach(function(slug) {
            var ds = data.datasets[slug];
            var color = palette[idx % palette.length];
            datasets.push({
                label: ds.name,
                data: ds.data,
                backgroundColor: color,
                borderColor: color,
                borderWidth: 1,
                datalabels: { display: showValues, color: '#333', font: { size: 10, weight: 'bold' }, anchor: 'center', align: 'center',
                    formatter: function(v) { return v > 0 ? v : ''; }
                }
            });
            legendItems.push({ name: ds.name, color: color });
            for (var i = 0; i < ds.data.length; i++) {
                totals[i] += (ds.data[i] || 0);
            }
            idx++;
        });

        // Тренд — полиномиальная кривая по суммам
        if (showTrend && n > 1) {
            var trendData = polyFit(totals, polyDegree);
            datasets.push({
                label: 'Тренд (полином ' + polyDegree + ')',
                data: trendData,
                type: 'line',
                borderColor: '#000',
                borderWidth: 2,
                borderDash: [6, 3],
                backgroundColor: 'transparent',
                pointRadius: 0,
                fill: false,
                stack: false,
                datalabels: { display: false }
            });
            legendItems.push({ name: 'Тренд (полином ' + polyDegree + ')', color: '#000' });
        }

        var pluginsList = {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        };

        // Если нет плагина datalabels, подписываем значения через свой плагин
        var customPlugins = [];
        if (showValues) {
            customPlugins.push({
                id: 'monitoringValuesPlugin',
                afterDatasetsDraw: function(chart) {
                    var ctx = chart.ctx;
                    chart.data.datasets.forEach(function(dataset, dsIdx) {
                        if (dataset.type === 'line') return;
                        var meta = chart.getDatasetMeta(dsIdx);
                        if (!meta || meta.hidden) return;
                        meta.data.forEach(function(bar, index) {
                            var val = dataset.data[index];
                            if (val === 0 || val === null || val === undefined) return;
                            ctx.save();
                            ctx.fillStyle = '#333';
                            ctx.font = 'bold 10px sans-serif';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            var y = (bar.y + (bar.base !== undefined ? bar.base : bar.y)) / 2;
                            var barHeight = Math.abs((bar.base !== undefined ? bar.base : bar.y) - bar.y);
                            if (barHeight >= 14) {
                                ctx.fillText(val, bar.x, y);
                            }
                            ctx.restore();
                        });
                    });
                }
            });
        }

        chartInstance = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: { labels: data.labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true, ticks: { maxRotation: 45, minRotation: 45 } },
                    y: { stacked: true, beginAtZero: true, title: { display: true, text: yLabel } }
                },
                plugins: pluginsList
            },
            plugins: customPlugins
        });
        window.monitoringChartInstance = chartInstance;

        // Легенда
        var legendWrap = document.querySelector('#monitoringLegend .d-flex');
        if (legendWrap) {
            legendWrap.innerHTML = '';
            legendItems.forEach(function(item) {
                var el = document.createElement('div');
                el.className = 'd-flex align-items-center gap-2';
                var swatch = item.color === '#000'
                    ? '<div style="width:20px;height:3px;background:#000;border:none;margin:8px 0;"></div>'
                    : '<div style="width:20px;height:20px;background-color:' + item.color + ';border:1px solid #dee2e6;"></div>';
                el.innerHTML = swatch + '<span>' + item.name + '</span>';
                legendWrap.appendChild(el);
            });
        }
    }

    function initMonitoringChart() {
        loadAndRender();
    }

    window.initMonitoringChart = initMonitoringChart;

    var unitSel = document.getElementById('monitoringUnitSelect');
    var periodSel = document.getElementById('monitoringPeriodSelect');
    var trendCheck = document.getElementById('trendCheck');
    var valuesCheck = document.getElementById('valuesCheck');
    var polyInput = document.getElementById('polynomialDegree');

    if (unitSel) unitSel.addEventListener('change', loadAndRender);
    if (periodSel) periodSel.addEventListener('change', loadAndRender);
    if (trendCheck) trendCheck.addEventListener('change', function() { if (lastData) renderChart(lastData); });
    if (valuesCheck) valuesCheck.addEventListener('change', function() { if (lastData) renderChart(lastData); });
    if (polyInput) polyInput.addEventListener('change', function() { if (lastData && trendCheck.checked) renderChart(lastData); });

    function exportChartToPdf() {
        var chart = window.monitoringChartInstance;
        if (!chart || typeof chart.toBase64Image !== 'function') {
            alert('График ещё не загружен. Дождитесь отображения диаграммы.');
            return;
        }
        function doExport() {
            var JsPDF = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : (window.jsPDF || null);
            if (!JsPDF) {
                alert('Библиотека PDF не загружена.');
                return;
            }
            var canvas = document.getElementById('monitoringChart');
            if (!canvas) return;
            var imgData = chart.toBase64Image('image/png');

            var legendItems = [];
            if (chart.data && chart.data.datasets) {
                chart.data.datasets.forEach(function(ds) {
                    var color = ds.backgroundColor || ds.borderColor || '#000';
                    var colorStr = typeof color === 'string' ? color : (color[0] || '#000');
                    legendItems.push({ name: ds.label || '', color: colorStr });
                });
            }

            var unitLabel;
            if (lastData && lastData.unit === 'k1000') {
                unitLabel = 'К1000 (отказов на 1000 полётов)';
            } else if (lastData && lastData.unit === 'k1000h') {
                unitLabel = 'К1000 налет (отказов на 1000 часов налёта)';
            } else {
                unitLabel = 'АБС (количество отказов)';
            }
            var periodLabel = (lastData && lastData.period === 'quarter') ? 'по кварталам' : ((lastData && lastData.period === 'year') ? 'по годам' : 'по месяцам');

            // Заголовок и подзаголовок на canvas (с отступом между ними)
            var headerW = 1000;
            var headerH = 70;
            var headerCanvas = document.createElement('canvas');
            headerCanvas.width = headerW;
            headerCanvas.height = headerH;
            var ctx = headerCanvas.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, headerW, headerH);
            ctx.fillStyle = '#000';
            ctx.font = 'bold 28px sans-serif';
            ctx.fillText('Мониторинг отказов', 0, 32);
            ctx.font = '14px sans-serif';
            ctx.fillStyle = '#333';
            ctx.fillText(unitLabel + ', ' + periodLabel, 0, 58);
            var headerData = headerCanvas.toDataURL('image/png');

            // Легенда — отдельный canvas под диаграммой
            var legendW = 1000;
            var legendCanvas = document.createElement('canvas');
            legendCanvas.width = legendW;
            var ctxLeg = legendCanvas.getContext('2d');
            ctxLeg.font = '13px sans-serif';
            var xLeg = 0;
            var yLeg = 22;
            var legRowHeight = 22;
            legendItems.forEach(function(item) {
                var textW = ctxLeg.measureText(item.name).width + 28;
                if (xLeg + textW > legendW - 20) {
                    xLeg = 0;
                    yLeg += legRowHeight;
                }
                xLeg += textW;
            });
            var legendH = yLeg + 14;
            legendCanvas.height = legendH;
            ctxLeg = legendCanvas.getContext('2d');
            ctxLeg.fillStyle = '#fff';
            ctxLeg.fillRect(0, 0, legendW, legendH);
            xLeg = 0;
            yLeg = 22;
            ctxLeg.font = '13px sans-serif';
            legendItems.forEach(function(item) {
                var textW = ctxLeg.measureText(item.name).width + 28;
                if (xLeg + textW > legendW - 20) {
                    xLeg = 0;
                    yLeg += legRowHeight;
                }
                ctxLeg.fillStyle = item.color;
                ctxLeg.fillRect(xLeg, yLeg - 10, 14, 14);
                ctxLeg.strokeStyle = '#ddd';
                ctxLeg.strokeRect(xLeg, yLeg - 10, 14, 14);
                ctxLeg.fillStyle = '#222';
                ctxLeg.fillText(item.name, xLeg + 20, yLeg);
                xLeg += textW;
            });
            var legendData = legendCanvas.toDataURL('image/png');

            var margin = 10;
            var pageW = 297;
            var pageH = 210;
            var doc = new JsPDF('landscape', 'mm', 'a4');
            var contentW = pageW - 2 * margin;

            var yPos = margin;
            var headerImgH = (headerH * contentW) / headerW;
            doc.addImage(headerData, 'PNG', margin, yPos, contentW, headerImgH);
            yPos += headerImgH + 4;

            var legendImgH = (legendH * contentW) / legendW;
            var maxImgH = pageH - yPos - margin - legendImgH - 6;
            var imgW = contentW;
            var imgH = (canvas.height * imgW) / canvas.width;
            if (imgH > maxImgH) {
                imgH = maxImgH;
                imgW = (canvas.width * imgH) / canvas.height;
            }
            var imgX = margin + (contentW - imgW) / 2;
            doc.addImage(imgData, 'PNG', imgX, yPos, imgW, imgH);
            yPos += imgH + 4;

            doc.addImage(legendData, 'PNG', margin, yPos, contentW, legendImgH);
            doc.save('мониторинг-отказов-' + (new Date().toISOString().slice(0, 10)) + '.pdf');
        }
        if ((window.jspdf && window.jspdf.jsPDF) || window.jsPDF) {
            doExport();
            return;
        }
        var s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        s.onload = function() {
            doExport();
        };
        s.onerror = function() {
            alert('Не удалось загрузить библиотеку для экспорта PDF.');
        };
        document.head.appendChild(s);
    }

    var pdfBtn = document.getElementById('monitoringPdfBtn');
    if (pdfBtn) pdfBtn.addEventListener('click', exportChartToPdf);

    var monitoringPane = document.getElementById('monitoring');
    if (monitoringPane && monitoringPane.classList.contains('active')) {
        if (typeof waitForChartConfig !== 'undefined') {
            waitForChartConfig(initMonitoringChart);
        } else {
            var checkInterval = setInterval(function() {
                if (typeof waitForChartConfig !== 'undefined') {
                    clearInterval(checkInterval);
                    waitForChartConfig(initMonitoringChart);
                }
            }, 50);
        }
    }
})();
</script>

<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}
</style>
