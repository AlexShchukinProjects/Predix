import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/aircraft-schedule-chart.js',
                'resources/js/crew-schedule-chart.js',
                'resources/sass/style.css',
                'resources/sass/svg.svg',
            ],
            refresh: true,
        }),
    ]
});
