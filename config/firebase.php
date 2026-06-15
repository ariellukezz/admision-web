<?php

return [
    'project_id' => env('FIREBASE_PROJECT_ID', 'app-admision-2026'),
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS_PATH') ?: storage_path('app/firebase-credentials.json'),
    ],
    'messaging' => [
        'sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '116539912844415710654'),
    ],
];
