<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudianteOTIController extends Controller
{
    public function index()
    {
        return response()->json([
            'estado' => true,
            'datos' => []
        ], 200);
    }

    public function buscar(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|min:1'
        ]);

        $dni = $request->dni;

        $res = DB::connection('mysql_third')->select(
            "SELECT
                est.num_mat,
                est.cod_car,
                car.car_des,
                est.paterno,
                est.materno,
                est.nombres,
                est.num_doc,
                est.fch_nac,
                est.fch_ing AS fecha_ingreso
            FROM estudiante est
            JOIN carrera car ON est.cod_car = car.cod_car
            WHERE (est.num_doc = ? OR est.num_mat = ?)
              AND car.niv_aca = 'U'",
            [$dni, $dni]
        );

        return response()->json([
            'estado' => !empty($res),
            'datos' => $res
        ], 200);
    }
}
