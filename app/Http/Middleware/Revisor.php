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

        if (auth()->user()->id_rol == 2) {
            return $next($request);
        }

        abort(403, 'No tienes permisos de revisor');
    }
}