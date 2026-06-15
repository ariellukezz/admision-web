<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Module;
use App\Models\View;
use App\Models\Action;
use App\Models\Permission;
use App\Models\Rol;
use App\Models\User;

class PermisoController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Permisos/index');
    }

    public function getModulos()
    {
        $modulos = Module::with(['views' => function ($q) {
            $q->orderBy('name');
        }])
            ->orderBy('name')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $modulos;
        return response()->json($this->response, 200);
    }

    public function getPermisos()
    {
        $permisos = Permission::with([
            'module:id,code,name',
            'view:id,code,name',
            'action:id,code,description',
        ])
            ->orderBy('module_id')
            ->orderBy('view_id')
            ->orderBy('action_id')
            ->get()
            ->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'module_code' => $p->module?->code,
                    'module_name' => $p->module?->name,
                    'view_code'   => $p->view?->code,
                    'view_name'   => $p->view?->name,
                    'action_code' => $p->action?->code,
                    'action_desc' => $p->action?->description,
                    'code'        => $p->view?->code . '.' . $p->action?->code,
                    'status'      => $p->status,
                ];
            });

        $this->response['estado'] = true;
        $this->response['datos'] = $permisos;
        return response()->json($this->response, 200);
    }

    public function savePermiso(Request $request)
    {
        $request->validate([
            'module_id'  => 'required|integer',
            'view_id'    => 'required|integer',
            'action_id'  => 'required|integer',
            'status'     => 'nullable|boolean',
        ]);

        $data = [
            'module_id' => $request->module_id,
            'view_id'   => $request->view_id,
            'action_id' => $request->action_id,
            'status'    => $request->status ?? true,
        ];

        if ($request->id) {
            $permiso = Permission::findOrFail($request->id);
            $permiso->update($data);
            $msg = 'Permiso actualizado';
        } else {
            $permiso = Permission::firstOrCreate(
                ['view_id' => $request->view_id, 'action_id' => $request->action_id],
                $data
            );
            $msg = $permiso->wasRecentlyCreated ? 'Permiso creado' : 'El permiso ya existía';
        }

        $this->response['estado'] = true;
        $this->response['mensaje'] = $msg;
        $this->response['datos'] = $permiso;
        return response()->json($this->response, 200);
    }

    public function deletePermiso($id)
    {
        $permiso = Permission::findOrFail($id);
        $permiso->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Permiso eliminado';
        return response()->json($this->response, 200);
    }

    // ── Asignación Rol ↔ Permiso ──────────────────────────

    public function getPermisosRol(Request $request)
    {
        $request->validate(['role_id' => 'required|integer']);

        $permisos = DB::table('rbac_role_permissions')
            ->where('role_id', $request->role_id)
            ->where('status', true)
            ->pluck('permission_id')
            ->toArray();

        $this->response['estado'] = true;
        $this->response['datos'] = $permisos;
        return response()->json($this->response, 200);
    }

    public function savePermisoRol(Request $request)
    {
        $request->validate([
            'role_id'       => 'required|integer',
            'permission_id' => 'required|integer',
            'status'        => 'required|boolean',
        ]);

        $existing = DB::table('rbac_role_permissions')
            ->where('role_id', $request->role_id)
            ->where('permission_id', $request->permission_id)
            ->first();

        if ($existing) {
            DB::table('rbac_role_permissions')
                ->where('role_id', $request->role_id)
                ->where('permission_id', $request->permission_id)
                ->update(['status' => $request->status, 'updated_at' => now()]);
        } else {
            DB::table('rbac_role_permissions')->insert([
                'role_id'       => $request->role_id,
                'permission_id' => $request->permission_id,
                'status'        => $request->status,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Permiso ' . ($request->status ? 'asignado' : 'revocado');
        return response()->json($this->response, 200);
    }

    // ── Overrides Usuario ↔ Permiso ───────────────────────

    public function getPermisosUsuario(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);

        $overrides = DB::table('rbac_user_permissions')
            ->where('user_id', $request->user_id)
            ->get(['permission_id', 'type', 'status']);

        $this->response['estado'] = true;
        $this->response['datos'] = $overrides;
        return response()->json($this->response, 200);
    }

    public function savePermisoUsuario(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|integer',
            'permission_id' => 'required|integer',
            'type'          => 'required|in:add,remove',
            'status'        => 'nullable|boolean',
        ]);

        $existing = DB::table('rbac_user_permissions')
            ->where('user_id', $request->user_id)
            ->where('permission_id', $request->permission_id)
            ->first();

        if ($existing) {
            DB::table('rbac_user_permissions')
                ->where('user_id', $request->user_id)
                ->where('permission_id', $request->permission_id)
                ->update([
                    'type'   => $request->type,
                    'status' => $request->status ?? true,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('rbac_user_permissions')->insert([
                'user_id'       => $request->user_id,
                'permission_id' => $request->permission_id,
                'type'          => $request->type,
                'status'        => $request->status ?? true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Override guardado';
        return response()->json($this->response, 200);
    }

    // ── Acciones ───────────────────────────────────────────

    public function getAcciones()
    {
        $acciones = Action::leftJoin('rbac_permissions', 'rbac_actions.id', '=', 'rbac_permissions.action_id')
            ->select('rbac_actions.id', 'rbac_actions.code', 'rbac_actions.description')
            ->selectRaw('COUNT(rbac_permissions.id) as permisos_count')
            ->groupBy('rbac_actions.id', 'rbac_actions.code', 'rbac_actions.description')
            ->orderBy('rbac_actions.code')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $acciones;
        return response()->json($this->response, 200);
    }

    public function saveAccion(Request $request)
    {
        $request->validate([
            'code'        => 'required|string|max:50',
            'description' => 'nullable|string|max:150',
            'id'          => 'nullable|integer',
        ]);

        $code = strtolower(trim($request->code));

        if ($request->id) {
            $accion = Action::findOrFail($request->id);
            $exists = Action::where('code', $code)->where('id', '!=', $request->id)->exists();
            if ($exists) {
                return response()->json(['estado' => false, 'mensaje' => 'Ya existe una acción con ese código'], 422);
            }
            $accion->update([
                'code'        => $code,
                'description' => $request->description,
            ]);
            $msg = 'Acción actualizada';
        } else {
            $exists = Action::where('code', $code)->exists();
            if ($exists) {
                return response()->json(['estado' => false, 'mensaje' => 'Ya existe una acción con ese código'], 422);
            }
            $accion = Action::create([
                'code'        => $code,
                'description' => $request->description,
            ]);
            $msg = 'Acción creada';
        }

        $this->response['estado'] = true;
        $this->response['mensaje'] = $msg;
        $this->response['datos'] = $accion;
        return response()->json($this->response, 200);
    }

    public function deleteAccion($id)
    {
        $enUso = Permission::where('action_id', $id)->exists();
        if ($enUso) {
            return response()->json(['estado' => false, 'mensaje' => 'No se puede eliminar: la acción está en uso por permisos'], 422);
        }

        $accion = Action::findOrFail($id);
        $accion->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Acción eliminada';
        return response()->json($this->response, 200);
    }

    // ── Listar roles y usuarios para los selects ───────────

    public function getRoles()
    {
        $roles = Rol::select('id', 'name')->orderBy('name')->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $roles;
        return response()->json($this->response, 200);
    }

    public function getUsuarios(Request $request)
    {
        $query = User::select('id', 'dni', 'name', 'paterno', 'materno', 'email', 'id_rol');

        if ($request->filled('term')) {
            $term = '%' . $request->term . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', $term)
                  ->orWhere('paterno', 'LIKE', $term)
                  ->orWhere('materno', 'LIKE', $term)
                  ->orWhere('email', 'LIKE', $term)
                  ->orWhere('dni', 'LIKE', $term);
            });
        }

        $usuarios = $query->orderBy('paterno')->limit(50)->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $usuarios;
        return response()->json($this->response, 200);
    }
}
