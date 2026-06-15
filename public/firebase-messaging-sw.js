importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyCQh0hM9glpqQcYMxmygzpz6bv_D0ZBLbg",
    authDomain: "admision-unap.firebaseapp.com",
    projectId: "admision-unap",
    storageBucket: "admision-unap.firebasestorage.app",
    messagingSenderId: "75683577446",
    appId: "1:75683577446:web:9bc3ccc3f50740e300f7a7"
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

self.addEventListener('install', function(event) {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function(event) {
    event.waitUntil(self.clients.claim());
});

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
