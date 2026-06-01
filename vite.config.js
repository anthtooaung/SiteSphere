import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/auth.css',
                'resources/js/auth.js',
                'resources/js/reset-password.js',
                'resources/css/welcome.css',
                'resources/js/welcome.js',
                'resources/css/homepage.css',
                'resources/js/homepage.js',
                'resources/css/upload-post.css',
                'resources/js/upload-post.js',
            ],
            refresh: true,
        }),
    ],
});
