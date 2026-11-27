import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: [
                ...refreshPaths,
                'app/Http/Livewire/**',
            ],
        }),
    ],
    server: {
        // Let Herd sites (http(s)://*.test) and localhost load assets from the dev server; this version of the
        // Laravel Vite plugin does not allow *.test origins on its own, so the browser blocked them (CORS).
        cors: {
            origin: [
                /^https?:\/\/(?:[^/]+\.)?test(?::\d+)?$/,
                /^https?:\/\/(?:(?:[^:]+\.)?localhost|127\.0\.0\.1|\[::1\])(?::\d+)?$/,
            ],
        },
    },
});
