import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

import '../css/custom-theme.css';
import Antd from 'ant-design-vue';

import { useNotificaciones } from '@/composables/useFcm.js';



const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Admision 2023';

const baseUrl = 'http://admision-web.test'; 

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        props.baseUrl = baseUrl;

        // Inicializar FCM para todos los usuarios autenticados
        if (props.initialPage?.props?.auth?.user) {
            // Detectar cambio de usuario: si el ID en sessionStorage difiere del actual,
            // limpiar el token FCM guardado para forzar re-registro con el nuevo usuario
            const currentUserId = props.initialPage.props.auth.user.id;
            const storedUserId = sessionStorage.getItem('fcm_user_id');

            if (storedUserId && storedUserId != currentUserId) {
                console.log('[FCM] Cambio de usuario detectado, limpiando token previo');
                localStorage.removeItem('fcm_token');
            }
            sessionStorage.setItem('fcm_user_id', currentUserId);

            const fcm = useNotificaciones();
            fcm.init().catch(e => console.warn('FCM init skipped:', e));
        } else {
            // No autenticado: limpiar referencias
            sessionStorage.removeItem('fcm_user_id');
        }

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(Antd)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
        '@primary-color': '#2d2880',
    },
});