// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [                
                'public/assets/css/partner.css', 
                'public/assets/js/partner.js'
            ],
            output: 'public/assets', 
            refresh: true,
        }),
    ],
});
