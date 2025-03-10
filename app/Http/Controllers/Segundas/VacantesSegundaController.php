<?php
namespace App\Http\Controllers\Segundas;
use DB;
use App\Models\Vacante;
use App\Models\Programa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VacantesSegundaController extends Controller
{
    
    public function getVacantes( Request $request) {
        $query_where = [];

        if (auth()->user()->programas != null) {
            $array = json_decode(auth()->user()->programas, true);
            if (!empty($array)) { $query_where[] = ['programa.id', $array]; }
        }

        $res = Programa::select(
            'programa.id as id_programa',
            'programa.codigo_sunedu',
            'programa.nombre as programa',
            'vacantes.id as id_vacante',
            'vacantes.vacantes',
            'vacantes.estado'
        )
        ->leftJoin('vacantes', function($join) {
            $join->on('vacantes.id_programa', '=', 'programa.id')
                ->where('vacantes.id_proceso', auth()->user()->id_proceso)
                ->where('vacantes.id_modalidad', 1);
        })
        ->when(!empty($query_where), function ($query) use ($query_where) {
            foreach ($query_where as $condition) {
                $query->whereIn($condition[0], $condition[1]);
            }
        })
        ->where('nivel_academico','Segunda Especialidad')
        ->where(function ($query) use ($request) {
            return $query
                ->orWhere('programa.codigo_sunedu', 'LIKE', '%' . $request->term . '%')
                ->orWhere('programa.nombre', 'LIKE', '%' . $request->term . '%');
        })->orderBy('programa.id', 'DESC')
        ->paginate(50);
        
        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);

    }

    public function saveNumeroVacantes(Request $request ) {

        $vacante = null;
        if (!$request->id_vacante) {
            $vacante = Vacante::create([
                'id_programa' => $request->id_programa,
                'vacantes' => $request->vacantes,
                'id_proceso' => auth()->user()->id_proceso,
                'id_modalidad' => $request->id_modalidad,
                'estado' => 1,
                'id_usuario' => auth()->id()
            ]);
            $this->response['titulo'] = 'REGISTRO NUEVO';
            $this->response['mensaje'] = 'Programa '.$vacante->nombre.' creada con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $vacante;
        } else {

            $vacante = Vacante::find($request->id_vacante);
            $vacante->vacantes = $request->vacantes;
            $vacante->estado = 1;
            $vacante->id_usuario = auth()->id();
            $vacante->save();

            $this->response['titulo'] = '!REGISTRO MODIFICADO!';
            $this->response['mensaje'] = 'Filial '.$vacante->nombre.' modificado con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $vacante;
        }

        return response()->json($this->response, 200);
    
    }

    public function eliminar(Request $request ) {

        $vacante = Vacante::find($request->id_vacante);
        $vacante->delete();

        $this->response['titulo'] = '!REGISTRO ELIMINADO!';
        $this->response['mensaje'] = '';
        $this->response['estado'] = true;
        return response()->json($this->response, 200);
    }
    
    public function getSelectModalidadesProceso($id_proceso) {
        $res = DB::select("SELECT mo.id AS value, nombre AS label FROM (SELECT distinct id_modalidad FROM vacantes
        WHERE id_proceso = $id_proceso) AS pp
        JOIN modalidad mo ON mo.id = pp.id_modalidad");

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getSelectProgramasProceso(Request $request) {
        $res = DB::select("SELECT programa.id AS value, concat(programa.nombre,' - ',facultad.facultad) AS label  FROM (SELECT id_programa FROM vacantes
            WHERE id_modalidad = $request->id_modalidad AND id_proceso = $request->id_proceso AND vacantes.estado = 1) AS programas_proceso
            JOIN programa ON programa.id = programas_proceso.id_programa
            JOIN facultad ON facultad.id = programa.id_facultad
            ");

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }


}
