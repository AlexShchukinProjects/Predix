<!-- Панель управления графиком -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <select class="form-select form-select-sm" style="width: auto;">
                    <option>Л.Ч.</option>
                </select>
                <select class="form-select form-select-sm" style="width: auto;">
                    <option>100</option>
                    <option>50</option>
                    <option>200</option>
                </select>
                <select class="form-select form-select-sm" style="width: auto;">
                    <option>1 из 3</option>
                    <option>2 из 3</option>
                    <option>3 из 3</option>
                </select>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="trendCheckCI">
                    <label class="form-check-label" for="trendCheckCI">Тренд</label>
                </div>
                <input type="number" class="form-control form-control-sm" value="2" style="width: 60px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="polynomialCheckCI">
                    <label class="form-check-label" for="polynomialCheckCI">Степень полинома</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="fleetSizeCheckCI">
                    <label class="form-check-label" for="fleetSizeCheckCI">С учетом размера парка ВС</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="valuesCheckCI">
                    <label class="form-check-label" for="valuesCheckCI">Значения</label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm">Отчет PDF</button>
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- График -->
<div class="card" style="height: 500px;">
    <div class="card-body">
        <canvas id="agingComponentsChart"></canvas>
    </div>
</div>

<!-- Легенда -->
<div class="mt-3">
    <div class="d-flex gap-4 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <div style="width: 20px; height: 20px; background-color: #ffc107; border: 1px solid #dee2e6;"></div>
            <span>оперативное ТО</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div style="width: 20px; height: 20px; background-color: #dc3545; border: 1px solid #dee2e6;"></div>
            <span>в полете</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div style="width: 20px; height: 20px; background-color: #0dcaf0; border: 1px solid #dee2e6;"></div>
            <span>периодическое ТО</span>
        </div>
    </div>
</div>

<!-- Chart.js уже загружен в главном файле -->
<script>
(function() {
    function initAgingComponentsChart() {
        // Проверяем, не инициализирован ли уже график
        if (window.agingComponentsChartInstance) {
            return;
        }
        
        // Генерируем данные для графика по наработке КИ
        const labels = [];
        const operationalData = [];
        const flightData = [];
        const periodicData = [];
        
        // Создаем интервалы по наработке (от 100 до 47700)
        const intervals = [100, 3500, 6900, 10300, 13700, 17100, 20500, 23900, 27300, 30700, 34100, 37500, 40900, 44300, 47700];
        
        intervals.forEach((value, index) => {
            labels.push(value.toString());
            
            // Генерируем данные с убывающим трендом
            const base = Math.max(0, 250 - index * 15);
            operationalData.push(Math.round(base * 0.6 + Math.random() * 15));
            flightData.push(Math.round(base * 0.3 + Math.random() * 12));
            periodicData.push(Math.round(base * 0.1 + Math.random() * 8));
        });
        
        const agingComponentsChart = ChartConfig.createChart('agingComponentsChart', 'bar', {
        labels: labels,
        datasets: [
            {
                label: 'оперативное ТО',
                data: operationalData,
                backgroundColor: '#ffc107',
                borderColor: '#ffc107',
                borderWidth: 1
            },
            {
                label: 'в полете',
                data: flightData,
                backgroundColor: '#dc3545',
                borderColor: '#dc3545',
                borderWidth: 1
            },
            {
                label: 'периодическое ТО',
                data: periodicData,
                backgroundColor: '#0dcaf0',
                borderColor: '#0dcaf0',
                borderWidth: 1
            }
        ]
    }, {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                stacked: true,
                title: {
                    display: true,
                    text: 'Наработка КИ (часы)'
                }
            },
            y: {
                stacked: true,
                beginAtZero: true,
                max: 250,
                ticks: {
                    stepSize: 50
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        }
    });
    
    // Сохраняем ссылку на экземпляр графика
    window.agingComponentsChartInstance = agingComponentsChart;
    }
    
    // Делаем функцию глобальной для вызова при переключении табов
    window.initAgingComponentsChart = initAgingComponentsChart;
    
    // Инициализируем график, если таб уже активен при загрузке страницы
    const agingComponentsPane = document.getElementById('aging-components');
    if (agingComponentsPane && agingComponentsPane.classList.contains('active')) {
        if (typeof waitForChartConfig !== 'undefined') {
            waitForChartConfig(initAgingComponentsChart);
        } else {
            // Если функция еще не определена, ждем её появления
            const checkInterval = setInterval(function() {
                if (typeof waitForChartConfig !== 'undefined') {
                    clearInterval(checkInterval);
                    waitForChartConfig(initAgingComponentsChart);
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

