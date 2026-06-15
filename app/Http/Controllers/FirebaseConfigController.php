<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FirebaseConfigController extends Controller
{
    /**
     * Devuelve la configuración Firebase para el frontend web.
     * Estos valores son PÚBLICOS (no son secretos) — Firebase los expone
     * en el cliente por diseño. Solo los lee del .env del backend.
     */
    public function config()
    {
        $config = [
            'apiKey' => env('VITE_FIREBASE_API_KEY', env('FIREBASE_API_KEY')),
            'authDomain' => env('VITE_FIREBASE_AUTH_DOMAIN', env('FIREBASE_AUTH_DOMAIN', '')),
            'projectId' => env('VITE_FIREBASE_PROJECT_ID', env('FIREBASE_PROJECT_ID', '')),
            'storageBucket' => env('VITE_FIREBASE_STORAGE_BUCKET', env('FIREBASE_STORAGE_BUCKET', '')),
            'messagingSenderId' => env('VITE_FIREBASE_MESSAGING_SENDER_ID', env('FIREBASE_MESSAGING_SENDER_ID', '')),
            'appId' => env('VITE_FIREBASE_APP_ID', env('FIREBASE_APP_ID')),
            'vapidKey' => env('FIREBASE_VAPID_KEY'),
        ];

        // Si no hay apiKey, Firebase no está configurado
        if (empty($config['apiKey'])) {
            return response()->json(['configured' => false]);
        }

        return response()->json([
            'configured' => true,
            'config' => $config,
        ]);
    }

    /**
     * Genera dinámicamente el Service Worker de Firebase con la config inyectada.
     * Reemplaza el archivo estático public/firebase-messaging-sw.js.
     */
    public function serviceWorker()
    {
        $apiKey = env('VITE_FIREBASE_API_KEY', env('FIREBASE_API_KEY'));
        $authDomain = env('VITE_FIREBASE_AUTH_DOMAIN', env('FIREBASE_AUTH_DOMAIN', ''));
        $projectId = env('VITE_FIREBASE_PROJECT_ID', env('FIREBASE_PROJECT_ID', ''));
        $storageBucket = env('VITE_FIREBASE_STORAGE_BUCKET', env('FIREBASE_STORAGE_BUCKET', ''));
        $messagingSenderId = env('VITE_FIREBASE_MESSAGING_SENDER_ID', env('FIREBASE_MESSAGING_SENDER_ID', ''));
        $appId = env('VITE_FIREBASE_APP_ID', env('FIREBASE_APP_ID'));

        $sw = <<<JS
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "{$apiKey}",
    authDomain: "{$authDomain}",
    projectId: "{$projectId}",
    storageBucket: "{$storageBucket}",
    messagingSenderId: "{$messagingSenderId}",
    appId: "{$appId}"
});

const messaging = firebase.messaging();

function buildNotification(payload) {
    const title = payload.notification?.title || payload.data?.title || 'Nueva notificación';
    const options = {
        body: payload.notification?.body || payload.data?.body || '',
        icon: '/favicon.ico',
        tag: payload.data?.tipo || 'default',
        data: payload.data || {},
        requireInteraction: true,
    };
    return { title, options };
}

messaging.onBackgroundMessage(function(payload) {
    const { title, options } = buildNotification(payload);
    self.registration.showNotification(title, options);
});

self.addEventListener('push', function(event) {
    if (!event.data) {
        return;
    }

    let payload;
    try {
        payload = event.data.json();
    } catch (err) {
        payload = { notification: { title: 'Nueva notificación', body: event.data.text() } };
    }

    const { title, options } = buildNotification(payload);
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    const urlToOpen = event.notification.data?.url;

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            if (clientList.length > 0) {
                let client = clientList[0];
                if (urlToOpen) {
                    client.navigate(urlToOpen);
                }
                return client.focus();
            }
            if (urlToOpen) {
                return self.clients.openWindow(urlToOpen);
            }
        })
    );
});
JS;

        return response($sw)
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/');
    }
}
