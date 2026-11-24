<?php

return [
    'auth_url' => env('DNID_AUTH_URL', 'https://test-admision.unap.edu.pe/service_dnid'),
    'client_id' => env('DNID_CLIENT_ID', 'admision'),
    'callback_url' => env('DNID_CALLBACK_URL', 'http://admision.test/auth/dnid/callback'),
    'timeout' => env('DNID_TIMEOUT', 20),
];
