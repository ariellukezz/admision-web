<?php

namespace App\Http\Controllers;
use App\Models\PagoBanco;
use Illuminate\Http\Request;
use App\Models\PagoOTI;
use DB;

class PagoBancoController extends Controller
{

  public function getComprobantesDNI(Request $request){
    $res = PagoBanco::select([
        'banco_pagos.secuencia',
        \DB::raw('SUBSTRING(banco_pagos.num_doc, 8, 8) AS dni'),
        \DB::raw('SUBSTRING(banco_pagos.concepto, 7, 2) AS concepto'),
        'banco_pagos.imp_pag', 'banco_pagos.fch_pag', 'banco_pagos.nom_cli',
        'banco_pagos.estado', 'banco_pagos.fecha_usado', 'banco_pagos.id_usuario', 'banco_pagos.id_proceso',
        'procesos.nombre as proceso', 'procesos.id_modalidad_proceso'

    ])
    ->leftjoin('procesos','procesos.id','banco_pagos.id_proceso')
    ->where(\DB::raw('SUBSTRING(num_doc, 8, 8)'), '=', $request->dni)
    ->where('banco_pagos.fch_pag', '>', '2025-01-01')
    ->get();

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  }


  public function getComprobantesSecuencia(Request $request){
    $res = PagoBanco::select([
        'banco_pagos.secuencia',
        \DB::raw('SUBSTRING(banco_pagos.num_doc, 8, 8) AS dni'),
        \DB::raw('SUBSTRING(banco_pagos.concepto, 7, 2) AS concepto'),
        'banco_pagos.imp_pag', 'banco_pagos.fch_pag', 'banco_pagos.nom_cli',
        'banco_pagos.estado', 'banco_pagos.fecha_usado', 'banco_pagos.id_usuario', 'banco_pagos.id_proceso',
        'procesos.nombre as proceso', 'procesos.id_modalidad_proceso'

    ])
    ->leftjoin('procesos','procesos.id','banco_pagos.id_proceso')
    ->where('banco_pagos.secuencia', '=', $request->secuencia)
    ->where('banco_pagos.fch_pag', '>', '2025-01-01')
    ->get();

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  }


  public function verificarComprobanteProceso(Request $request){

    $comprobante = PagoBanco::where('fch_pag', $request['comp']['fch_pag'])
    ->where('secuencia', $request['comp']['secuencia'])
    ->first();

    if (!$comprobante) {
      $this->response['type'] = 'error';
      $this->response['titulo'] = '!ERROR!';
      $this->response['mensaje'] = 'No se encontró el comprobante';
      $this->response['estado'] = false;
      return response()->json($this->response, 404);
    }

    if($comprobante->id_usuario == auth()->id() || !$comprobante->id_usuario){

      if( $request['comp']['estado'] == 1 ){
        $comprobante->estado = 0;
        $comprobante->fecha_usado = null;
        $comprobante->id_proceso =  null;
        $comprobante->id_usuario = auth()->id();
        $comprobante->dni_postulante = null;
      }else{
        if( $request['comp']['estado'] == 0 ){
          $comprobante->estado = 1;
          $comprobante->fecha_usado = date('Y-m-d H:i:s');
          $comprobante->id_proceso = auth()->user()->id_proceso;
          $comprobante->id_usuario = auth()->id();
          $comprobante->dni_postulante = $request['comp']['dni'];
        }
      }

      $comprobante->save();

      $this->response['type'] = 'success';
      $this->response['titulo'] = '!COMPROBANTE ACTUALIZADO!';
      $this->response['mensaje'] = '';
      $this->response['estado'] = true;
      $this->response['datos'] = $comprobante;
    }else{
      $this->response['type'] = 'error';
      $this->response['titulo'] = '!ERROR AL ACTUALIZAR!';
      $this->response['mensaje'] = 'OTRO USUARIO YA MODIFICÓ ESTE COMPROBANTE';
      $this->response['estado'] = true;
      $this->response['datos'] = $comprobante;
    }

    return response()->json($this->response, 200);
  }




  public function getComprobanteConcepto($secuencia)
  {
      $database2 = 'mysql_secondary';

      // Usar bindings para evitar inyección SQL
      $res = DB::connection($database2)->select("SELECT * FROM banco_pagos_admision WHERE secuencia = ?", [$secuencia]);

      // Preparar la respuesta
      $this->response['estado'] = true;
      $this->response['datos'] = $res;

      // Devolver la respuesta en formato JSON
      return response()->json($this->response, 200);
  }


  public function getPagosAdmision(Request $request)
  {
    $query_where = [];
   // array_push($query_where, ['filial.cod_dep', '=', 'provincia.cod_dep']);
    $res = PagoBanco::select('num_mat','num_doc', 'secuencia', DB::raw("SUBSTRING(concepto, -3) AS concepto"), DB::raw("SUBSTRING(num_doc, -8) AS dni"), 'imp_pag', 'fch_pag', 'nom_cli', 'id', )
      ->where($query_where)
      ->where(function ($query) use ($request) {
          return $query
              ->orWhere('secuencia', 'LIKE', '%' . $request->term . '%')
              ->orWhere('nom_cli', 'LIKE', '%' . $request->term . '%')
              ->orWhere(DB::raw("SUBSTRING(num_doc, -8)"), 'LIKE', '%' . $request->term . '%');
      })->orderBy('id', 'DESC')
      ->paginate(50);

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);

    

  }

  public function getPagosOTI(Request $request)
  {
    $query_where = [];
   // array_push($query_where, ['filial.cod_dep', '=', 'provincia.cod_dep']);
    $res = PagoOTI::on('mysql_secondary')
    ->where(function ($query) use ($request) {
        $query->orWhere('secuencia', 'LIKE', '%' . $request->term . '%')
              ->orWhereRaw("RIGHT(num_doc, 8) LIKE ?", ["%{$request->term}%"])
              ->orWhere('nom_cli', 'LIKE', '%' . $request->term . '%');
    })
    ->paginate(50);

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);

    $estudiantes = PagoOTI::on('mysql_secondary')->get();

  }








}
