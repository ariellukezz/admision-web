<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedireccionarARol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $userRole = auth()->user()->id_rol;

            if ($userRole == 7) {
                return redirect('/calificacion');
            } elseif ($userRole == 6) {
                return redirect('/simulacro');
            } elseif ($userRole == 1) {
                return redirect('/admin/dashboard');
            } elseif ($userRole == 2) {
                return redirect('/revisor');
            }elseif ($userRole == 3) {
                return redirect('/segundas');
            }
        }

        return $next($request);
    }
}
