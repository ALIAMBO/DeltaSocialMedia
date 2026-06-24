import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/profile-edit.js',
                'resources/js/pages/feed-index.js',
                'resources/js/pages/post-card.js',
                'resources/js/pages/app.js',
                'resources/js/pages/chat-show.js',
            ],
            refresh: true,
        }),
    ],
});
