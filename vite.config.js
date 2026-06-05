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
                'resources/css/reports.css',
                'resources/css/appearance.css',
                'resources/css/edit-profile.css',
                'resources/css/security.css',
                'resources/css/edit-tag.css',
                'resources/css/profile-detail.css',
                'resources/js/profile-detail.js',
                'resources/css/post-detail.css',
                'resources/js/post-detail.js',
            ],
            refresh: true,
        }),
    ],
});
