/**
 * Конфигурация Chart.js для проекта AVIATIX
 * Дополнительные настройки и плагины для графиков
 */

// Глобальные настройки Chart.js
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = '#6c757d';

// Цветовая палитра для графиков
const chartColors = {
    primary: '#0d6efd',
    secondary: '#6c757d', 
    success: '#198754',
    danger: '#dc3545',
    warning: '#ffc107',
    info: '#0dcaf0',
    light: '#f8f9fa',
    dark: '#212529',
    // Дополнительные цвета для множественных датасетов
    blue: '#007bff',
    indigo: '#6610f2',
    purple: '#6f42c1',
    pink: '#e83e8c',
    red: '#dc3545',
    orange: '#fd7e14',
    yellow: '#ffc107',
    green: '#28a745',
    teal: '#20c997',
    cyan: '#17a2b8'
};

// Настройки для различных типов графиков
const chartOptions = {
    // Общие настройки
    responsive: true,
    maintainAspectRatio: false,
    
    // Настройки легенды
    legend: {
        display: true,
        position: 'bottom',
        labels: {
            usePointStyle: true,
            padding: 20,
            font: {
                size: 11
            }
        }
    },
    
    // Настройки анимации
    animation: {
        duration: 1000,
        easing: 'easeInOutQuart'
    },
    
    // Настройки взаимодействия
    interaction: {
        intersect: false,
        mode: 'index'
    },
    
    // Настройки плагинов
    plugins: {
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: '#dee2e6',
            borderWidth: 1,
            cornerRadius: 6,
            displayColors: true,
            callbacks: {
                title: function(context) {
                    return context[0].label;
                },
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    label += context.parsed.y;
                    return label;
                }
            }
        }
    }
};

// Функция для создания графика с предустановленными настройками
function createChart(canvasId, type, data, customOptions = {}) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    const options = { ...chartOptions, ...customOptions };
    
    return new Chart(ctx, {
        type: type,
        data: data,
        options: options
    });
}

// Функция для создания цветовой палитры
function getColorPalette(count) {
    const colors = Object.values(chartColors);
    const palette = [];
    
    for (let i = 0; i < count; i++) {
        palette.push(colors[i % colors.length]);
    }
    
    return palette;
}

// Функция для обновления данных графика
function updateChartData(chart, newData) {
    chart.data = newData;
    chart.update('active');
}

// Экспорт для использования в других скриптах
window.ChartConfig = {
    colors: chartColors,
    options: chartOptions,
    createChart: createChart,
    getColorPalette: getColorPalette,
    updateChartData: updateChartData
};
