<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (auth()->user()->id_rol == 1) {
            return $next($request);
        }

        abort(403, 'No tienes permisos de administrador');
    }
}