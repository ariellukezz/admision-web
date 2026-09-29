<?php

return [
    /*
    | URL base del servicio de autenticación con DNIe.
    */
    'url' => env('DNIAUTH_URL', 'https://auth.tudominio.pe'),

    /*
    | Identificador público de este sitio, tal como está registrado en el servicio.
    | Es el mismo valor que usa el frontend (VITE_DNIAUTH_CLIENT_ID).
    */
    'client_id' => env('DNIAUTH_CLIENT_ID'),

    /*
    | Secreto de cliente. SÓLO en el servidor: nunca en el frontend, nunca en el repositorio.
    | El servicio guarda su hash PBKDF2, no el secreto.
    */
    'client_secret' => env('DNIAUTH_CLIENT_SECRET'),

    /*
    | Tiempo máximo de la llamada de canje, en segundos.
    */
    'timeout' => env('DNIAUTH_TIMEOUT', 15),

    /*
    | A dónde se envía al usuario tras un ingreso correcto.
    */
    'redirect_after_login' => env('DNIAUTH_REDIRECT', '/dashboard'),

    /*
    | Si es true, sólo pueden entrar los DNI que ya existen en la tabla de usuarios.
    | Si es false, el primer ingreso crea el usuario.
    */
    'solo_usuarios_existentes' => env('DNIAUTH_SOLO_EXISTENTES', false),
];
