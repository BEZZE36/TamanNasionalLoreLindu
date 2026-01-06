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
        // Advanced chunking for optimal caching and smaller initial load
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // Vendor chunks - separate for long-term caching
                    if (id.includes('node_modules')) {
                        // Vue ecosystem - core
                        if (id.includes('vue') || id.includes('@vue') || id.includes('@inertiajs')) {
                            return 'vendor-vue';
                        }
                        // Lucide icons (vue version)
                        if (id.includes('lucide-vue-next')) {
                            return 'vendor-lucide';
                        }
                        // PDFMake - large, lazy load only when needed for e-tickets
                        if (id.includes('pdfmake')) {
                            return 'vendor-pdfmake';
                        }
                        // Chart.js - only for analytics pages
                        if (id.includes('chart.js') || id.includes('vue-chartjs')) {
                            return 'vendor-chart';
                        }
                        // GSAP - animation library
                        if (id.includes('gsap')) {
                            return 'vendor-gsap';
                        }
                        // Alpine.js - used for some interactive elements
                        if (id.includes('alpinejs') || id.includes('@alpinejs')) {
                            return 'vendor-alpine';
                        }
                        // Axios - HTTP client
                        if (id.includes('axios')) {
                            return 'vendor-axios';
                        }
                        // Vanilla lucide (if still in use) - skip, not needed for Vue
                        if (id.includes('lucide') && !id.includes('lucide-vue')) {
                            return 'vendor-lucide-vanilla';
                        }
                        // Other small node_modules
                        return 'vendor-misc';
                    }

                    // Split by page area for better code splitting
                    if (id.includes('/Pages/Admin/')) {
                        // Heavy admin pages get their own chunks
                        if (id.includes('Dashboard') || id.includes('Analytics') || id.includes('Statistics')) {
                            return 'admin-analytics';
                        }
                        if (id.includes('Booking') || id.includes('Ticket')) {
                            return 'admin-bookings';
                        }
                        return 'admin-pages';
                    }

                    if (id.includes('/Pages/Public/')) {
                        if (id.includes('Destinations')) {
                            return 'public-destinations';
                        }
                        if (id.includes('Gallery')) {
                            return 'public-gallery';
                        }
                        return 'public-pages';
                    }

                    if (id.includes('/Pages/User/')) {
                        return 'user-pages';
                    }

                    // Layouts
                    if (id.includes('/Layouts/')) {
                        return 'layouts';
                    }

                    // Components
                    if (id.includes('/Components/')) {
                        return 'components';
                    }
                },
            },
        },
        // Reduce chunk size warnings
        chunkSizeWarningLimit: 600,
        // Enable CSS code splitting
        cssCodeSplit: true,
        // Target modern browsers
        target: 'es2020',
        // Use terser for better minification with console removal
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,  // Remove all console.log in production
                drop_debugger: true, // Remove debugger statements
                pure_funcs: ['console.log', 'console.info', 'console.debug', 'console.warn'],
            },
            format: {
                comments: false, // Remove comments
            },
        },
        // Disable source maps in production
        sourcemap: false,
        // Enable gzip-size reporting
        reportCompressedSize: true,
    },
    // Optimize dependencies for faster dev startup
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'lucide-vue-next', 'axios', 'gsap'],
        // Exclude heavy libs from pre-bundling - load only when needed
        exclude: ['pdfmake', 'chart.js'],
    },
    // Enable caching for faster builds
    cacheDir: 'node_modules/.vite',
});
