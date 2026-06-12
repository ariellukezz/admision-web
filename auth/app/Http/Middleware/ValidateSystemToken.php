<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\System;

class ValidateSystemToken
{
    public function handle(Request $request, Closure $next)
    {
        $systemToken = $request->header('X-System-Token') ?? $request->input('system_token');

        if (! $systemToken) {
            return response()->json([ 'error' => 'System token is required'], 401);
        }

        $system = System::where('token', $systemToken)->first();

        if (!$system) {
            return response()->json([ 'error' => 'Invalid system token' ], 401);
        }

        $request->merge(['system' => $system]);
        return $next($request);
    }
}
