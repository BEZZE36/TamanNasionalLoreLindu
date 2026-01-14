import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss({
            content: [
                './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
                './storage/framework/views/*.php',
                './resources/**/*.blade.php',
                './resources/**/*.js',
                './resources/**/*.vue',
            ],
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        // Simplified chunking to avoid circular dependency issues
        rollupOptions: {
            output: {
                manualChunks: {
                    // Only split the largest vendor packages
                    'vendor-vue': ['vue', '@vue/runtime-dom', '@vue/runtime-core', '@vue/reactivity', '@vue/shared', '@inertiajs/vue3', '@inertiajs/core'],
                    'vendor-gsap': ['gsap'],
                    'vendor-chart': ['chart.js', 'vue-chartjs'],
                    'vendor-pdfmake': ['pdfmake'],
                },
            },
        },
        // Reduce chunk size warnings
        chunkSizeWarningLimit: 1000,
        // Enable CSS code splitting
        cssCodeSplit: true,
        // Target modern browsers
        target: 'es2020',
        // Use esbuild for faster builds (default)
        minify: 'esbuild',
        // Disable source maps in production
        sourcemap: false,
    },
    // Optimize dependencies for faster dev startup
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'lucide-vue-next', 'axios', 'gsap'],
        exclude: ['pdfmake'],
    },
    // Enable caching for faster builds
    cacheDir: 'node_modules/.vite',
});
