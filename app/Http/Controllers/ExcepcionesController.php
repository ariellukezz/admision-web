<?php

namespace App\Http\Controllers;

use App\Models\Excepciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExcepcionController extends Controller
{

    public function index(Request $request)
    {
        $query = Excepciones::query();
        
        if ($request->has('id_proceso')) {
            $query->where('id_proceso', $request->id_proceso);
        }
        
        $excepciones = $query->orderBy('id', 'desc')->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $excepciones
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nro_pregunta' => 'required|integer',
            'accion' => 'required|string|max:255',
            'cod_examen' => 'required|string|max:50',
            'observacion' => 'nullable|string',
            'claves_validas' => 'nullable|string',
            'puntaje' => 'required|numeric',
            'tipo' => 'required|string|max:50',
            'id_proceso' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $excepcion = Excepciones::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Excepción creada exitosamente',
                'data' => $excepcion
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la excepción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $excepcion = Excepciones::find($id);
        
        if (!$excepcion) {
            return response()->json([
                'success' => false,
                'message' => 'Excepción no encontrada'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $excepcion
        ]);
    }


    public function update(Request $request, $id)
    {
        $excepcion = Excepciones::find($id);
        
        if (!$excepcion) {
            return response()->json([
                'success' => false,
                'message' => 'Excepción no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nro_pregunta' => 'sometimes|integer',
            'accion' => 'sometimes|string|max:255',
            'cod_examen' => 'sometimes|string|max:50',
            'observacion' => 'nullable|string',
            'claves_validas' => 'nullable|string',
            'puntaje' => 'sometimes|numeric',
            'tipo' => 'sometimes|string|max:50',
            'id_proceso' => 'sometimes|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $excepcion->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Excepción actualizada exitosamente',
                'data' => $excepcion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la excepción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $excepcion = Excepciones::find($id);
        
        if (!$excepcion) {
            return response()->json([
                'success' => false,
                'message' => 'Excepción no encontrada'
            ], 404);
        }

        try {
            $excepcion->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Excepción eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la excepción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByProceso($id_proceso)
    {
        $excepciones = Excepciones::where('id_proceso', $id_proceso)
            ->orderBy('nro_pregunta', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $excepciones
        ]);
    }

    public function search(Request $request)
    {
        $query = Excepciones::query();
        
        if ($request->has('id_proceso')) {
            $query->where('id_proceso', $request->id_proceso);
        }
        
        if ($request->has('cod_examen')) {
            $query->where('cod_examen', 'like', '%' . $request->cod_examen . '%');
        }
        
        if ($request->has('nro_pregunta')) {
            $query->where('nro_pregunta', $request->nro_pregunta);
        }
        
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        $excepciones = $query->orderBy('id', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $excepciones
        ]);
    }
}