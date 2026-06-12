<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(\d+)\|(.*)/', $authHeader, $matches)) {
            $systemId = $matches[1];
            $cleanToken = $matches[2];
            $request->merge(['system_id' => $systemId]);
            $request->headers->set('Authorization', 'Bearer ' . $cleanToken);
        }

        $systemId = $request->get('system_id');
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rolePermissions = DB::table('role_permissions as rper')
            ->join('permissions as per', 'per.id', '=', 'rper.permission_id')
            ->join('systems as sy', 'sy.id', '=', 'per.system_id')
            ->join('modules as mo', 'mo.id', '=', 'per.module_id')
            ->join('views as vi', 'vi.id', '=', 'per.view_id')
            ->join('actions as act', 'act.id', '=', 'per.action_id')
            ->join('roles as rol', 'rol.id', '=', 'rper.role_id')
            ->where('sy.id', $systemId)
            ->where('rol.id',  $user->role_id)
            ->where('rper.status', 1)
            ->select(DB::raw("CONCAT(vi.code, '.', act.code) as code"))
            ->pluck('code')
            ->unique();

        // return response()->json(['permisos' => $rolePermissions], 201);

        if (!$rolePermissions->contains($permission)) {
            return response()->json(['error' => 'You do not have permission for this action.'], 403);
        }

        return $next($request);
    }

}
