<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Verifica si el usuario autenticado tiene el permiso especificado.
     * Uso en rutas: ->middleware('rbac:postulantes.create')
     */
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login');
        }

        // Si se pasan varios permisos, basta con tener uno
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para acceder a este recurso.');
    }
}
