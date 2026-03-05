<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мониторинг показателей по БП</title>
    @vite(['resources/sass/app.scss', 'resources/sass/style.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .spi-report-toolbar { padding: 0.75rem 0; }
        .spi-report-page-header {
            display: flex;
            width: 100%;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .spi-report-page-header-logo { width: 20%; font-size: 0.875rem; color: #6c757d; }
        .spi-report-page-header-title { width: 80%; font-weight: 600; font-size: 1rem; padding-left: 12px; }
        @media print {
            body { padding: 0; background: #fff !important; }
            html, body { background: #fff !important; }
            .spi-report-toolbar { display: none !important; }
            @page { size: A4 landscape; margin: 12mm; }
            /* Один блок (диаграмма или диаграмма+таблица) на страницу */
            .spi-report-page {
                page-break-after: always;
                page-break-inside: avoid;
                min-height: 0;
            }
            .spi-report-page:last-child { page-break-after: auto; }
            /* Уместить диаграмму в страницу */
            .spi-report-page .spi-report-chart-card {
                height: 320px !important;
                max-height: 320px !important;
            }
            .spi-report-page .spi-report-chart-card .card-body {
                height: calc(320px - 56px) !important;
                overflow: hidden;
            }
            .spi-report-page .spi-report-chart-card .card-body canvas {
                max-height: 100% !important;
                width: 100% !important;
            }
            /* Таблица на той же странице — компактно */
            .spi-report-page .spi-report-table-card .table,
            .spi-report-page .spi-report-table-card .table th,
            .spi-report-page .spi-report-table-card .table td {
                font-size: 11px;
            }
            .spi-report-page .spi-report-table-card {
                margin-top: 0.5rem !important;
            }
            .spi-report-page-header { border-bottom-color: #ccc; }
            .spi-report-page-header-title { font-size: 12pt; }
            /* Авиационные события: на всю ширину, без прокрутки, все строки */
            .spi-report-page-aviation .aviation-events-card { height: auto !important; max-height: none !important; }
            .spi-report-page-aviation .aviation-events-card-body { overflow: visible !important; }
            .spi-report-page-aviation .table-responsive { overflow: visible !important; }
            .spi-report-page-aviation #aviation-events-table { width: 100% !important; }
        }
    </style>
</head>
<body class="bg-white">
    <div class="container-fluid spi-report-toolbar d-print-none border-bottom bg-white shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Отчет по показателям безопасности полётов</h5>
            <button type="button" class="btn btn-primary" onclick="window.print();">
                <i class="fas fa-print me-2"></i>Печать / Сохранить как PDF
            </button>
        </div>
    </div>
    <div class="container-fluid py-3">
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function() {
        if (window.location.search.indexOf('report=1') === -1) return;
        // Не открывать диалог печати при экспорте в архив PNG (iframe или параметр export=1)
        if (window.self !== window.top) return;
        if (window.location.search.indexOf('export=1') !== -1) return;
        var toolbar = document.querySelector('.spi-report-toolbar');
        if (toolbar) toolbar.style.display = 'none';
        function doPrint() {
            window.print();
            window.onafterprint = function() { window.close(); };
        }
        if (document.readyState === 'complete') setTimeout(doPrint, 2200);
        else window.addEventListener('load', function() { setTimeout(doPrint, 2200); });
    })();
    </script>
</body>
</html>
