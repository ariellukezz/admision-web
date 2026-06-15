import { ref } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';
import { messaging } from '@/firebase';
import { getToken, deleteToken, onMessage } from 'firebase/messaging';

const VAPID_KEY = "BKYJGo1CC3swVWZ49PnULvtwbgG1s45UKoRKtfy43fe9LcmYO2ni4hltrI7YkLRIIn-N17NvAIAw8oX-3FA7fDk";

// Event emitter simple para notificar al layout cuando llega un mensaje en foreground
const fcmEventTarget = new EventTarget();

export function useNotificaciones() {
    const token = ref(null);
    const activado = ref(false);

    // Registrar listener de foreground INMEDIATAMENTE
    onMessage(messaging, async (payload) => {
        // Notificar al layout que llego un mensaje para que recargue notificaciones
        fcmEventTarget.dispatchEvent(new Event('fcm-message'));

        const title = payload.data?.title || payload.notification?.title || 'Nueva notificación';
        const body = payload.data?.body || payload.notification?.body || '';
        const url = payload.data?.url || '';

        // Usar Service Worker para mostrar la notificación
        try {
            const registration = await navigator.serviceWorker.ready;
            registration.showNotification(title, {
                body,
                icon: '/favicon.ico',
                requireInteraction: true,
                tag: payload.data?.tipo || 'default',
                data: { ...payload.data, url },
            });
        } catch (e) {
            const notification = new Notification(title, {
                body,
                icon: '/favicon.ico',
                requireInteraction: true,
                tag: payload.data?.tipo || 'default',
                data: payload.data || {},
            });
            notification.onclick = function () {
                window.focus();
                if (url) window.location.href = url;
                notification.close();
            };
        }
    });

    const activar = async () => {
        console.log('[FCM] activar() llamado');
        const permiso = await Notification.requestPermission();
        console.log('[FCM] Permiso:', permiso);

        if (permiso !== 'granted') {
            message.warning('Permiso de notificaciones denegado');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
            console.log('[FCM] SW registrado:', registration.scope);

            const currentToken = await getToken(messaging, {
                vapidKey: VAPID_KEY,
                serviceWorkerRegistration: registration,
            });

            console.log('[FCM] Token obtenido:', currentToken ? currentToken.substring(0, 30) + '...' : 'NULL');

            if (!currentToken) {
                message.error('No se generó el token FCM');
                return;
            }

            token.value = currentToken;

            await axios.post('/fcm-token', {
                token: currentToken,
                device_type: 'web',
            });

            localStorage.setItem('fcm_token', currentToken);
            activado.value = true;

        } catch (error) {
            console.error('[FCM] Error activando:', error);
            message.error('Error al activar notificaciones');
        }
    };

    const init = async () => {
        console.log('[FCM] init() llamado');

        if ('serviceWorker' in navigator) {
            try {
                const reg = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                console.log('[FCM] SW registrado en init:', reg.scope);
            } catch (e) {
                console.warn('[FCM] SW registration failed:', e);
            }
        }

        if (Notification.permission === 'granted') {
            console.log('[FCM] Permiso ya concedido, obteniendo token...');
            try {
                const registration = await navigator.serviceWorker.ready;
                const currentToken = await getToken(messaging, {
                    vapidKey: VAPID_KEY,
                    serviceWorkerRegistration: registration,
                });

                console.log('[FCM] Token en init:', currentToken ? currentToken.substring(0, 30) + '...' : 'NULL');

                if (currentToken) {
                    token.value = currentToken;
                    activado.value = true;

                    // Siempre re-registrar el token al iniciar sesión
                    // (garantiza que el token esté asociado al usuario actual)
                    const savedToken = localStorage.getItem('fcm_token');
                    if (savedToken !== currentToken) {
                        console.log('[FCM] Token nuevo, enviando al backend...');
                        await axios.post('/fcm-token', {
                            token: currentToken,
                            device_type: 'web',
                        });
                        localStorage.setItem('fcm_token', currentToken);
                    } else {
                        // Token igual al guardado, pero re-enviar para asegurar asociación
                        console.log('[FCM] Re-registrando token para usuario actual...');
                        await axios.post('/fcm-token', {
                            token: currentToken,
                            device_type: 'web',
                        });
                    }
                }
            } catch (e) {
                console.warn('[FCM] Token restore failed:', e);
            }
        } else if (Notification.permission !== 'denied') {
            console.log('[FCM] Permiso no concedido, pidiendo...');
            await activar();
        } else {
            console.log('[FCM] Permiso denegado, no se puede inicializar');
        }
    };

    const logout = async () => {
        console.log('[FCM] logout() - eliminando token');
        const savedToken = localStorage.getItem('fcm_token');

        if (savedToken) {
            try {
                await axios.delete('/fcm-token', {
                    data: { token: savedToken },
                });
            } catch (e) {
                console.warn('[FCM] Error eliminando token del backend:', e);
            }

            try {
                await deleteToken(messaging);
            } catch (e) {
                console.warn('[FCM] Error eliminando token de Firebase:', e);
            }

            localStorage.removeItem('fcm_token');
        }

        token.value = null;
        activado.value = false;
    };

    return {
        token,
        activado,
        activar,
        init,
        logout,
        fcmEventTarget,
    };
}
