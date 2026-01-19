<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProgramaProceso;
use App\Models\Programa;
use DB;

class ProgramaProcesoController extends Controller
{



    public function getProgramaProceso() {
        $res = DB::select('SELECT
            pro.nombre_corto AS programa,
            MAX(CASE WHEN pp.id_modalidad = 8 THEN "SI" ELSE "-" END) AS "8",
            MAX(CASE WHEN pp.id_modalidad = 7 THEN "SI" ELSE "-" END) AS "7",
            MAX(CASE WHEN pp.id_modalidad = 9 THEN "SI" ELSE "-" END) AS "9",
            MAX(CASE WHEN pp.id_modalidad = 1 THEN "SI" ELSE "-" END) AS "1",
            MAX(CASE WHEN pp.id_modalidad = 2 THEN "SI" ELSE "-" END) AS "2",
            MAX(CASE WHEN pp.id_modalidad = 3 THEN "SI" ELSE "-" END) AS "3",
            MAX(CASE WHEN pp.id_modalidad = 4 THEN "SI" ELSE "-" END) AS "4",
            MAX(CASE WHEN pp.id_modalidad = 5 THEN "SI" ELSE "-" END) AS "5"
        FROM vacantes pp
        JOIN programa pro ON pro.id = pp.id_programa
        WHERE pp.id_proceso = '. auth()->user()->id_proceso.'  and pp.estado = 1 GROUP BY pro.nombre_corto;');

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
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
        $res = DB::select("SELECT programa.id AS value, programa.nombre AS label  FROM (SELECT id_programa FROM vacantes
            WHERE id_modalidad = $request->id_modalidad AND id_proceso = $request->id_proceso AND vacantes.estado = 1) AS vacantes
            JOIN programa ON programa.id = vacantes.id_programa");

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }


    public function getSelectProgramasProcesoAdmin() {

        $res = DB::select("SELECT id AS value, nombre AS label  FROM programa
        WHERE id IN ( SELECT DISTINCT id_programa  FROM vacantes  WHERE id_proceso = ".auth()->user()->id_proceso.");");

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getSelectProgramasProcesoArea(Request $request) {
        try {
            if (!$request->id_modalidad || !$request->id_proceso || !$request->area) {
                $this->response['estado'] = false;
                $this->response['mensaje'] = 'Faltan parámetros requeridos';
                return response()->json($this->response, 400);
            }

            $res = DB::table('programas_proceso')
                ->select('programa.id as value', 'programa.nombre as label')
                ->join('programa', 'programa.id', '=', 'programas_proceso.id_programa')
                ->where('programas_proceso.id_modalidad', $request->id_modalidad)
                ->where('programas_proceso.id_proceso', $request->id_proceso)
                ->where('programas_proceso.estado', 1)
                ->where('programa.area', $request->area)
                ->distinct()
                ->get();

            $this->response['estado'] = true;
            $this->response['datos'] = $res;
            return response()->json($this->response, 200);

        } catch (\Exception $e) {
            $this->response['estado'] = false;
            $this->response['mensaje'] = 'Error en la consulta: ' . $e->getMessage();
            return response()->json($this->response, 500);
        }
    }




    public function saveProceso(Request $request ) {

        $proceso = null;
        if (!$request->id) {
            $proceso = ProgramaProceso::create([
                'id_modalidad' => $request->id_modalidad,
                'id_programa' => $request->id_programa,
                'estado' => $request->estado,
                'id_proceso' => auth()->user()->id_proceso,
                'id_usuario' => auth()->id()
            ]);
            $this->response['titulo'] = 'REGISTRO NUEVO';
            $this->response['mensaje'] = 'Proceso '.$proceso->nombre.' creado con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $proceso;

        }


        return response()->json($this->response, 200);
    }

    public function getAreaByCodigoCarrera($codigo)
    {
        $mapeoAreas = [
            '07' => 'SOCIALES', '13' => 'SOCIALES', '33' => 'INGENIERÍAS',
            '56' => 'SOCIALES', '15' => 'BIOMÉDICAS', '06' => 'SOCIALES',
            '14' => 'SOCIALES', '34' => 'INGENIERÍAS', '25' => 'SOCIALES',
            '18' => 'SOCIALES', '21' => 'SOCIALES', '20' => 'SOCIALES',
            '16' => 'SOCIALES', '17' => 'SOCIALES', '08' => 'BIOMÉDICAS',
            '35' => 'INGENIERÍAS', '02' => 'INGENIERÍAS', '01' => 'INGENIERÍAS',
            '32' => 'INGENIERÍAS', '10' => 'INGENIERÍAS', '23' => 'INGENIERÍAS',
            '05' => 'INGENIERÍAS', '24' => 'INGENIERÍAS', '22' => 'INGENIERÍAS',
            '31' => 'INGENIERÍAS', '36' => 'INGENIERÍAS', '30' => 'INGENIERÍAS',
            '26' => 'INGENIERÍAS', '03' => 'INGENIERÍAS', '27' => 'BIOMÉDICAS',
            '04' => 'BIOMÉDICAS', '28' => 'BIOMÉDICAS', '29' => 'BIOMÉDICAS',
            '11' => 'SOCIALES', '09' => 'SOCIALES', '12' => 'SOCIALES',
            '178' => 'SOCIALES', '180' => 'INGENIERÍAS', '181' => 'INGENIERÍAS',
            '182' => 'INGENIERÍAS', '183' => 'SOCIALES', '184' => 'INGENIERÍAS',
            '185' => 'INGENIERÍAS',
        ];

        if (isset($mapeoAreas[(string)$codigo])) {
            $this->response['estado'] = true;
            $this->response['datos'] = (object)['area' => $mapeoAreas[(string)$codigo]];
            return response()->json($this->response, 200);
        }

        $this->response['estado'] = false;
        $this->response['mensaje'] = 'Área no encontrada';
        $this->response['datos'] = (object)[];
        return response()->json($this->response, 404);
    }


    public function getAreaByCodigo($numMat)
    {
        $estudiante = DB::connection('mysql_third')
            ->table('estudiante')
            ->where('num_mat', $numMat)
            ->orderByRaw('COALESCE(fecha_actualizacion, fch_reg) DESC')
            ->first();

        if (!$estudiante) {
            $this->response['estado'] = false;
            $this->response['mensaje'] = 'Estudiante no encontrado';
            $this->response['datos'] = (object)[];
            return response()->json($this->response, 404);
        }
        return $this->getAreaByCodigoCarrera($estudiante->cod_car);
    }
}
