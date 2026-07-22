<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class UbigeoController extends Controller
{
    public function index()
    {
        return Inertia::render('Ubigeo/index');
    }

    public function getUbigeos(Request $request)
    {
        $term = $request->term ?? '';

        $res = DB::table('ubigeo AS u')
            ->leftJoin('departamento AS d', 'd.id', '=', 'u.id_departamento')
            ->leftJoin('provincia AS p', 'p.id', '=', 'u.id_provincia')
            ->leftJoin('distritos AS di', 'di.id', '=', 'u.id_distrito')
            ->select(
                'u.id',
                'u.ubigeo',
                'd.nombre AS departamento',
                'p.nombre AS provincia',
                'di.nombre AS distrito',
                'd.id AS id_departamento',
                'p.id AS id_provincia',
                'di.id AS id_distrito'
            )
            ->when($term, function ($q) use ($term) {
                $q->where('u.ubigeo', 'LIKE', "%{$term}%")
                  ->orWhere('d.nombre', 'LIKE', "%{$term}%")
                  ->orWhere('p.nombre', 'LIKE', "%{$term}%")
                  ->orWhere('di.nombre', 'LIKE', "%{$term}%");
            })
            ->orderBy('d.nombre')
            ->orderBy('p.nombre')
            ->orderBy('di.nombre')
            ->paginate(50);

        return response()->json([
            'estado' => true,
            'datos' => $res
        ], 200);
    }

    public function getDepartamentos()
    {
        $departamentos = DB::table('departamento')
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'estado' => true,
            'datos' => $departamentos
        ], 200);
    }

    public function getProvincias($departamentoId)
    {
        $provincias = DB::table('provincia')
            ->where('id_dep', $departamentoId)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'estado' => true,
            'datos' => $provincias
        ], 200);
    }

    public function getDistritos($provinciaId)
    {
        $distritos = DB::table('distritos')
            ->where('id_prov', $provinciaId)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'estado' => true,
            'datos' => $distritos
        ], 200);
    }

    public function saveUbigeo(Request $request)
    {
        $request->validate([
            'ubigeo'           => 'required|string|max:6',
            'id_departamento'   => 'nullable|integer',
            'id_provincia'      => 'nullable|integer',
            'id_distrito'       => 'nullable|integer',
            'id'                => 'nullable|integer',
        ]);

        try {
            if (!$request->id) {
                $exists = DB::table('ubigeo')->where('ubigeo', $request->ubigeo)->exists();
                if ($exists) {
                    return response()->json([
                        'estado' => false,
                        'titulo' => 'ERROR',
                        'mensaje' => 'El código ubigeo ya existe'
                    ], 400);
                }

                $id = DB::table('ubigeo')->insertGetId([
                    'ubigeo'           => $request->ubigeo,
                    'id_departamento'   => $request->id_departamento,
                    'id_provincia'     => $request->id_provincia,
                    'id_distrito'      => $request->id_distrito,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                return response()->json([
                    'estado'  => true,
                    'titulo'  => 'REGISTRO NUEVO',
                    'mensaje' => 'Ubigeo creado con éxito',
                    'datos'   => ['id' => $id]
                ], 200);
            } else {
                DB::table('ubigeo')->where('id', $request->id)->update([
                    'ubigeo'           => $request->ubigeo,
                    'id_departamento'   => $request->id_departamento,
                    'id_provincia'     => $request->id_provincia,
                    'id_distrito'      => $request->id_distrito,
                    'updated_at'        => now(),
                ]);

                return response()->json([
                    'estado'  => true,
                    'titulo'  => 'REGISTRO MODIFICADO',
                    'mensaje' => 'Ubigeo modificado con éxito'
                ], 200);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'estado'  => false,
                'titulo'  => 'ERROR',
                'mensaje' => 'Ocurrió un error al guardar el ubigeo',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function deleteUbigeo($id)
    {
        try {
            DB::table('ubigeo')->where('id', $id)->delete();
            return response()->json([
                'estado'  => true,
                'titulo'  => '!REGISTRO ELIMINADO!',
                'mensaje' => 'Ubigeo eliminado con éxito'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'estado'  => false,
                'titulo'  => 'ERROR',
                'mensaje' => 'No se pudo eliminar el ubigeo'
            ], 500);
        }
    }
}
