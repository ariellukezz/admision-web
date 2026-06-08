<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auditoría activada/desactivada
    |--------------------------------------------------------------------------
    |
    | Cambia AUDIT_ENABLED en .env a false para desactivar toda la auditoría.
    | No se ejecuta middleware, no se registran observers, cero overhead.
    |
    */
    'enabled' => env('AUDIT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Cola para procesar los logs
    |--------------------------------------------------------------------------
    */
    'queue' => env('AUDIT_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Rutas excluidas (nombre exacto)
    |--------------------------------------------------------------------------
    |
    | Estas rutas NO se auditan ni aunque sean POST/DELETE.
    |
    */
    'excluded_routes' => [
        'login',
        'logout',
        'password.*',
        'google.redirect',
        'google.callback',
        'postulante.api.buscar-ubigeo',
        'postulante.confirmacion',
        'users.export',
    ],

    /*
    |--------------------------------------------------------------------------
    | Prefijos de rutas excluidas
    |--------------------------------------------------------------------------
    |
    | Rutas cuyo nombre empiece con estos prefijos NO se auditan.
    | Útil para excluir búsquedas, selects, gets de catálogos, etc.
    |
    */
    'excluded_prefixes' => [
        'get-',
        'select-',
        'buscar-',
        'api.',
        'admin.get-',
        'admin.select-',
        'admin.buscar-',
        'postulante.api.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Modelos auditados automáticamente via Eloquent events
    |--------------------------------------------------------------------------
    |
    | Los modelos listados aquí tendrán observers que capturan
    | created, updated, deleted automáticamente con old/new values.
    | Agrega o quita modelos aquí para controlar qué se audita.
    |
    */
    'watched_models' => [
        \App\Models\Postulante::class,
        \App\Models\Documento::class,
        \App\Models\Vacante::class,
        \App\Models\Inscripcion::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Atributos sensibles (no se guardan en old/new values)
    |--------------------------------------------------------------------------
    */
    'hidden_attributes' => [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'google_id',
    ],

];
