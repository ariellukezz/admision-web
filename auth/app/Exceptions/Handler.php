<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Para todas las rutas API o si espera JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'No autorizado',
                'error' => 'Token inválido o expirado'
            ], 401);
        }

        // Solo para web, redirige a URL fija, no a route('login')
        return redirect()->guest('/login');
    }
}
