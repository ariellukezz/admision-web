<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Module;
use App\Models\View;
use App\Models\Action;
use App\Models\Permission;

class ModuloController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Modulos/index');
    }

    public function getModulos()
    {
        $modulos = Module::with(['views' => function ($q) {
            $q->orderBy('name')
              ->with(['permissions' => function ($q2) {
                  $q2->select('id', 'view_id', 'action_id', 'status')
                     ->with('action:id,code,description')
                     ->orderBy('id');
              }]);
        }])
            ->orderBy('name')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $modulos;
        return response()->json($this->response, 200);
    }

    public function getAcciones()
    {
        $acciones = Action::select('id', 'code', 'description')->orderBy('code')->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $acciones;
        return response()->json($this->response, 200);
    }

    public function saveAccion(Request $request)
    {
        $request->validate([
            'view_id'   => 'required|integer',
            'action_id' => 'required|integer',
            'status'    => 'nullable|boolean',
        ]);

        $view = View::findOrFail($request->view_id);

        $permiso = Permission::firstOrCreate(
            ['view_id' => $request->view_id, 'action_id' => $request->action_id],
            [
                'module_id' => $view->module_id,
                'status'    => $request->status ?? true,
            ]
        );

        $msg = $permiso->wasRecentlyCreated ? 'Acción asignada a la vista' : 'La acción ya estaba asignada';

        $this->response['estado'] = true;
        $this->response['mensaje'] = $msg;
        $this->response['datos'] = $permiso->load('action:id,code,description');
        return response()->json($this->response, 200);
    }

    public function deleteAccion($id)
    {
        $permiso = Permission::findOrFail($id);
        $permiso->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Acción quitada de la vista';
        return response()->json($this->response, 200);
    }

    public function toggleAccion(Request $request, $id)
    {
        $permiso = Permission::findOrFail($id);
        $permiso->status = !$permiso->status;
        $permiso->save();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Estado de acción actualizado';
        return response()->json($this->response, 200);
    }

    public function saveModulo(Request $request)
    {
        $request->validate([
            'code'        => 'required|string|max:100',
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:255',
            'status'      => 'nullable|boolean',
        ]);

        if ($request->id) {
            $modulo = Module::findOrFail($request->id);
            $modulo->update([
                'code'        => $request->code,
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->status ?? true,
            ]);
            $msg = 'Módulo actualizado';
        } else {
            $modulo = Module::create([
                'code'        => $request->code,
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->status ?? true,
            ]);
            $msg = 'Módulo creado';
        }

        $this->response['estado'] = true;
        $this->response['mensaje'] = $msg;
        $this->response['datos'] = $modulo;
        return response()->json($this->response, 200);
    }

    public function saveView(Request $request)
    {
        $request->validate([
            'module_id'   => 'required|integer',
            'code'        => 'required|string|max:100',
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string|max:255',
            'status'      => 'nullable|boolean',
        ]);

        if ($request->id) {
            $view = View::findOrFail($request->id);
            $view->update([
                'module_id'   => $request->module_id,
                'code'        => $request->code,
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->status ?? true,
            ]);
            $msg = 'Vista actualizada';
        } else {
            $view = View::create([
                'module_id'   => $request->module_id,
                'code'        => $request->code,
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->status ?? true,
            ]);
            $msg = 'Vista creada';
        }

        $this->response['estado'] = true;
        $this->response['mensaje'] = $msg;
        $this->response['datos'] = $view;
        return response()->json($this->response, 200);
    }

    public function deleteModulo($id)
    {
        $modulo = Module::findOrFail($id);
        $modulo->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Módulo eliminado';
        return response()->json($this->response, 200);
    }

    public function deleteView($id)
    {
        $view = View::findOrFail($id);
        $view->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Vista eliminada';
        return response()->json($this->response, 200);
    }
}
