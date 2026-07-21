<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class PuntajeController extends Controller
{

    public function getPunajesMaximos($proceso){

        $res = DB::select("SELECT pun.fecha, pun.AREA, maximos.max_puntaje, pun.dni, pun.paterno, pun.materno, pun.nombres, pun.programa
        FROM (
            SELECT  AREA, MAX(puntaje + puntaje_vocacional) AS max_puntaje FROM puntajes
            WHERE id_proceso = ? AND AREA IN ('BIOMEDICAS', 'SOCIALES', 'INGENIERIAS') AND apto = 'SI'
            GROUP BY AREA
        ) AS maximos
        JOIN puntajes pun  ON maximos.max_puntaje = (pun.puntaje + pun.puntaje_vocacional)  AND pun.area = maximos.area
        WHERE pun.id_proceso = ?  AND pun.apto = 'SI';"
       , [$proceso, $proceso]);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);

    }

}
