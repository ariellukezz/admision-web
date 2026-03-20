<?php
namespace App\Http\Controllers\Segundas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Preinscripcion;
use App\Models\Programa;
use Illuminate\Support\Facades\DB;

class ResultadosSegundaController extends Controller
{

    public function getResultados(Request $request)
    {
        $query = Preinscripcion::select(
            'postulante.id',
            'postulante.nro_doc',
            'postulante.primer_apellido',
            'postulante.segundo_apellido',
            'postulante.nombres',
            'pre_inscripcion.id AS id_pre_inscripcion',
            'resultados.id AS id_resultado',
            'resultados.puntaje',
            'resultados.puesto',
            'resultados.apto',
            'programa.nombre AS programa'
        )
        ->join('postulante', 'postulante.id', 'pre_inscripcion.id_postulante')
        ->join('programa', 'programa.id', 'pre_inscripcion.id_programa')
        ->leftJoin('resultados_segundas as resultados', 'resultados.id_pre_inscripcion', 'pre_inscripcion.id')
        ->where('pre_inscripcion.id_proceso', auth()->user()->id_proceso);

        if ($request->filled('id_programa')) {
            $query->where('pre_inscripcion.id_programa', $request->id_programa);
        }

        if ($request->filled('term')) {
            $term = '%' . $request->term . '%';
            $query->where(function ($q) use ($term) {
                $q->where('postulante.nro_doc', 'LIKE', $term)
                ->orWhere(DB::raw("CONCAT_WS(' ', postulante.nombres, postulante.primer_apellido, postulante.segundo_apellido)"), 'LIKE', $term);
            });
        }

        $query->orderBy('postulante.primer_apellido', 'asc');

        return response()->json([
            'estado' => true,
            'datos' => $query->paginate(1000)
        ]);
    }

    public function guardarPuntaje(Request $request)
    {
        try {
            $data = $request->validate([
                'id_pre_inscripcion' => 'required|exists:pre_inscripcion,id',
                'puntaje' => 'required|numeric|min:0|max:20',
            ]);

            $pre = DB::table('pre_inscripcion')
                ->where('id', $data['id_pre_inscripcion'])
                ->first();

            $vacante = DB::table('vacantes')
                ->where('id_proceso', auth()->user()->id_proceso)
                ->where('id_programa', $pre->id_programa)
                ->first();

            if ($vacante && $vacante->publicado == 1) {
                return response()->json([
                    'estado' => false,
                    'mensaje' => 'El programa ya fue publicado, no se puede editar'
                ], 403);
            }

            DB::table('resultados_segundas')->updateOrInsert(
                ['id_pre_inscripcion' => $data['id_pre_inscripcion']],
                [
                    'puntaje' => $data['puntaje'],
                    'updated_at' => now(),
                ]
            );

            return response()->json([
                'estado' => true,
                'mensaje' => 'Puntaje guardado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estado' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }


    public function publicar(Request $request)
    {
        try {

            $request->validate([
                'id_programa' => 'required',
                'vacantes' => 'required|integer|min:1',
                'lista' => 'required|array',
                'lista.*.id_pre_inscripcion' => 'required|integer',
                'lista.*.puntaje' => 'required|numeric',
                'lista.*.puesto' => 'required|integer',
                'lista.*.apto' => 'required|in:SI,NO',
            ]);

            \App\Jobs\PublicarResultadosJob::dispatch([
                'id_proceso' => auth()->user()->id_proceso,
                'id_programa' => $request->id_programa,
                'vacantes' => $request->vacantes,
                'lista' => $request->lista
            ]);

            return response()->json([
                'estado' => true,
                'mensaje' => 'Se está procesando en segundo plano'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'estado' => false,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }





    public function getVacantePrograma(Request $request)
    {
        $vacantes = DB::table('vacantes')
            ->where('id_proceso', auth()->user()->id_proceso)
            ->where('id_programa', $request->id_programa)
            ->select('id','vacantes','estado','asignados','publicado')
            ->first();

        return response()->json([
            'estado' => true,
            'datos' => $vacantes
        ]);
    }

    public function getSelectProgramasAsignados()
    {
        $programas = Programa::where('nivel', 2)
            ->where('estado', 1)
            ->get(['id', 'nombre']);

        return response()->json([
            'success' => true,
            'data' => $programas->map(fn($p) => [
                'value' => $p->id,
                'label' => $p->nombre
            ])
        ]);
    }
}