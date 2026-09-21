import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

import { createPersistedState } from 'pinia-plugin-persistedstate';
import ToastService from 'primevue/toastservice';
import Toast from 'primevue/toast';

const appName = import.meta.env.VITE_APP_NAME || 'Lukaz';

import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';

const pinia = createPinia()
const persiste = createPersistedState();


pinia.use(persiste);

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(PrimeVue, {
                theme: {
                    preset: Aura
                }
            })
            .use(pinia)
            .use(plugin)
            .use(ZiggyVue)
            .use(ToastService)
            .component('Toast', Toast)
            .mount(el);
    },
    progress: {
        color: '#47B083',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
