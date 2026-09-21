import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import path from 'path';
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        rollupOptions: {
            onwarn(warning, warn) {
                // Ignore the specific Rollup warning for PURE annotations
                if (warning.code === 'INVALID_ANNOTATION') return;

                // For all other warnings, show them as usual
                warn(warning);
            },
            output: {
                manualChunks(id) {
                    // 1. Group the core framework (Inertia, Vue, Pinia)
                    if (id.includes('node_modules/vue') ||
                        id.includes('node_modules/@inertiajs') ||
                        id.includes('node_modules/pinia')) {
                        return 'vendor-vue';
                    }

                    // 2. Separate PrimeVue and its new theme engine
                    if (id.includes('node_modules/primevue') ||
                        id.includes('node_modules/@primeuix')) {
                        return 'vendor-primevue';
                    }

                    // 3. Handle Chart.js
                    if (id.includes('node_modules/chart.js')) {
                        return 'vendor-chartjs';
                    }

                    // 4. Tiptap core libraries (separate from the Vue component)
                    if (id.includes('node_modules/@tiptap') ||
                        id.includes('node_modules/prosemirror') ||
                        id.includes('node_modules/tiptap-extension')) {
                        return 'vendor-tiptap';
                    }

                    // 5. The editor Vue component itself
                    if (id.includes('EditorToolbar') || id.includes('ResizableYoutube')) {
                        return 'vendor-editor';
                    }
                }
            }
        }
    }
});
