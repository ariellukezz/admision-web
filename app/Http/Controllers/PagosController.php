<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PagoBanco;
use DB;

class PagosController extends Controller
{
    

    public function getPagosBN_OTI($dni)
    {
        $proceso = auth()->user()->id_proceso;

        $res = DB::select("
            SELECT bp.imp_pag as amount, bp.nom_cli AS 'client', bp.num_mat AS 'code',
                bp.fch_pag AS 'date', bp.num_doc AS document, bp.secuencia AS paymentId,
                IF(pg.operacion IS NOT NULL, 1, 0) AS 'status',
                '000000000000000' AS universityId
            FROM banco_pagos bp
            LEFT JOIN pagos_general pg ON pg.operacion = bp.secuencia
            WHERE bp.fch_pag > '2025-12-01'
            AND bp.concepto IN ('00000026', '00000039', '00000028', '00000027')
            AND NOT EXISTS (
                SELECT 1 FROM pagos_general pg2
                WHERE pg2.operacion = bp.secuencia
                AND pg2.proceso != ?
                AND pg2.operacion IS NOT NULL
            )
            AND ? = substr(bp.num_doc, 8, 8)
        ", [$proceso, $dni]);

      return $res;
    }





}
