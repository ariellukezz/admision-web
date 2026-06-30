<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tarifa;

class TarifaController extends Controller
{
    public function index()
    {
        return Inertia::render('Tarifas/index');
    }

    public function getTarifas(Request $request)
    {
        $query_where = [];

        if ($request->filled('id_proceso')) {
            $query_where[] = ['tarifas.id_proceso', '=', $request->id_proceso];
        }
        if ($request->filled('id_programa')) {
            $query_where[] = ['tarifas.id_programa', '=', $request->id_programa];
        }
        if ($request->filled('id_modalidad')) {
            $query_where[] = ['tarifas.id_modalidad', '=', $request->id_modalidad];
        }

        $res = Tarifa::select(
            'tarifas.id',
            'tarifas.concepto',
            'tarifas.monto',
            'tarifas.moneda',
            'tarifas.estado',
            'tarifas.id_proceso',
            'tarifas.id_programa',
            'tarifas.id_modalidad',
            'procesos.nombre as proceso',
            'programa.nombre as programa',
            'modalidad.nombre as modalidad'
        )
            ->leftJoin('procesos', 'procesos.id', '=', 'tarifas.id_proceso')
            ->leftJoin('programa', 'programa.id', '=', 'tarifas.id_programa')
            ->leftJoin('modalidad', 'modalidad.id', '=', 'tarifas.id_modalidad')
            ->where($query_where)
            ->where(function ($query) use ($request) {
                return $query
                    ->orWhere('tarifas.concepto', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('procesos.nombre', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('programa.nombre', 'LIKE', '%' . $request->term . '%');
            })
            ->orderBy('tarifas.id', 'DESC')
            ->paginate(50);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function saveTarifa(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'monto'    => 'required|numeric|min:0',
            'moneda'   => 'nullable|string|max:5',
            'id_proceso'   => 'nullable|integer',
            'id_programa'  => 'nullable|integer',
            'id_modalidad' => 'nullable|integer',
            'estado'   => 'nullable|boolean',
        ]);

        if (!$request->id) {
            $tarifa = Tarifa::create([
                'concepto'     => $request->concepto,
                'monto'        => $request->monto,
                'moneda'       => $request->moneda ?: 'PEN',
                'id_proceso'   => $request->id_proceso,
                'id_programa'  => $request->id_programa,
                'id_modalidad' => $request->id_modalidad,
                'estado'       => $request->estado ? 1 : 0,
                'id_usuario'   => auth()->id(),
            ]);

            $this->response['titulo'] = 'REGISTRO NUEVO';
            $this->response['mensaje'] = 'Tarifa "' . $tarifa->concepto . '" creada con éxito';
        } else {
            $tarifa = Tarifa::findOrFail($request->id);
            $tarifa->concepto     = $request->concepto;
            $tarifa->monto        = $request->monto;
            $tarifa->moneda       = $request->moneda ?: 'PEN';
            $tarifa->id_proceso   = $request->id_proceso;
            $tarifa->id_programa  = $request->id_programa;
            $tarifa->id_modalidad = $request->id_modalidad;
            $tarifa->estado       = $request->estado ? 1 : 0;
            $tarifa->id_usuario   = auth()->id();
            $tarifa->save();

            $this->response['titulo'] = '¡REGISTRO MODIFICADO!';
            $this->response['mensaje'] = 'Tarifa "' . $tarifa->concepto . '" actualizada con éxito';
        }

        $this->response['estado'] = true;
        $this->response['datos'] = $tarifa;
        return response()->json($this->response, 200);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $tarifa = Tarifa::findOrFail($id);
        $tarifa->estado = $request->estado ? 1 : 0;
        $tarifa->save();

        $this->response['estado'] = true;
        $this->response['titulo'] = 'ESTADO ACTUALIZADO';
        $this->response['mensaje'] = 'Tarifa ' . $tarifa->concepto . ' ' . ($tarifa->estado ? 'activada' : 'desactivada');
        return response()->json($this->response, 200);
    }

    public function deleteTarifa($id)
    {
        $tarifa = Tarifa::findOrFail($id);
        $concepto = $tarifa->concepto;
        $tarifa->delete();

        $this->response['titulo'] = '!REGISTRO ELIMINADO!';
        $this->response['mensaje'] = 'Tarifa "' . $concepto . '" eliminada con éxito';
        $this->response['estado'] = true;
        return response()->json($this->response, 200);
    }
}
