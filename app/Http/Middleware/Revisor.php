<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Revisor
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // RBAC: verificar permiso revisor.access
        if ($user->hasPermission('revisor.access')) {
            return $next($request);
        }

        abort(403, 'No tienes permisos de revisor');
    }
}