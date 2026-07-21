<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'calificacion/carga-ide',
        'calificacion/carga-res',
        'api/calificacion/lecturas/carga-ide',
        'api/calificacion/lecturas/carga-res',
        'api/calificacion/lecturas/carga-pat',
        'api/calificacion/examenes-tipos-archivo/*',
        'api/calificacion/examenes-res/*',
        'carpetas/crear-carpeta',
        'subir-pdf/*'
    ];
}
