<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $authHeader = $request->header('Authorization');

            if ($authHeader && preg_match('/Bearer\s+(\d+)\|(.*)/', $authHeader, $matches)) {
                $systemId = $matches[1];
                $cleanToken = $matches[2];

                $request->merge(['system_id' => $systemId]);
                $request->headers->set('Authorization', 'Bearer ' . $cleanToken);
            }

            $user = Auth::guard('api')->user();

            if (!$user) {
                throw new AuthenticationException('Token inválido o expirado');
            }

            $request->setUserResolver(fn() => $user);

            return $next($request);

        } catch (AuthenticationException $e) {
            return response()->json([
                'message' => 'No autorizado',
                'error'   => $e->getMessage(),
            ], 401);
        }
    }
}
