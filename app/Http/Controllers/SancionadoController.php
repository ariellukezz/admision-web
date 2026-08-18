<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sancionado;
use App\Exports\TablaGenericaExport;
use Excel;
use DB;

class SancionadoController extends Controller
{
    public function getSancionado($dni, $proceso) {

        $sancionado = Sancionado::where('dni', $dni)->where('id_proceso', $proceso)->first();

        if($sancionado){
            $this->response['estado'] = true;
            $this->response['datos'] = $sancionado;
            return response()->json($this->response, 200);
          } else {
            $this->response['estado'] = false;
            return response()->json($this->response, 201);
          }

    }


    public function getObservados($proceso, $dni) {

      $sancionado = Sancionado::select('dni','nombres','paterno','materno','motivo')->where('dni', $dni)
                  ->where('id_proceso', $proceso)->first();

      if($sancionado){
        $this->response['mensaje'] = "Está Observado";
          $this->response['estado'] = true;
          $this->response['datos'] = $sancionado;
          return response()->json($this->response, 200);
      } else {
          $this->response['mensaje'] = "No está observado";
          $this->response['estado'] = false;
          return response()->json($this->response, 201);
      }
    }

    public function getSancionadoCepre($dni)
    {
        $sancionado = DB::table('observados_cepre')
                        ->where('dni', $dni)
                        ->where('estado', 1)
                        ->first();

        if ($sancionado) {
            return response()->json(['motivo'=>$sancionado->motivo, 'estado' => true], 200);
        } else {
            // Si no se encuentra ningún registro, devuelve la respuesta con estado false
            return response()->json(['estado' => false], 404);
        }
    }


    public function getObservadosLista(Request $request)
    {
      $query_where = [];

     // array_push($query_where, ['filial.cod_dep', '=', 'provincia.cod_dep']);

      $res = Sancionado::select('sancionados.id','sancionados.dni','sancionados.nombres','sancionados.paterno','sancionados.materno','sancionados.motivo','procesos.nombre as nombre_proceso','procesos.id as id_proceso')
        ->join ('procesos', 'procesos.id', '=','sancionados.id_proceso')
        ->where($query_where)
        ->where('procesos.id','=',auth()->user()->id_proceso)
        ->where(function ($query) use ($request) {
            return $query
                ->orWhere('sancionados.dni', 'LIKE', '%' . $request->term . '%')
                ->orWhere('sancionados.nombres', 'LIKE', '%' . $request->term . '%')
                ->orWhere('sancionados.paterno', 'LIKE', '%' . $request->term . '%')
                ->orWhere('sancionados.materno', 'LIKE', '%' . $request->term . '%')
                ->orWhere(DB::raw("CONCAT(sancionados.paterno,' ',sancionados.materno,' ',sancionados.nombres)"), 'LIKE', '%' . $request->term . '%')
                ->orWhere(DB::raw("CONCAT(sancionados.nombres,' ',sancionados.paterno, ' ', sancionados.materno)"), 'LIKE', '%' . $request->term . '%')
                ->orWhere('procesos.nombre', 'LIKE', '%' . $request->term . '%');
        })->orderBy('sancionados.id', 'DESC')
        ->paginate(50);

      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }

    public function save(Request $request ) {

      $request->validate([
        'dni' => 'required|string|max:20',
        'nombres' => 'required|string|max:255',
        'paterno' => 'required|string|max:255',
        'materno' => 'required|string|max:255',
        'motivo' => 'required|string|max:255',
        'observacion' => 'nullable|string|max:500',
        'id_proceso' => 'required|integer',
      ]);

      $observado = null;
      if (!$request->id) {
          $observado = Sancionado::create([
              'dni' => $request->dni,
              'nombres' => $request->nombres,
              'paterno' => $request->paterno,
              'materno' => $request->materno,
              'motivo' => $request->motivo,
              'observacion' => $request->observacion,
              'id_proceso' => $request->id_proceso,
          ]);

          $this->response['tipo'] = 'success';
          $this->response['titulo'] = '!REGISTRO CREADO CON EXITO!';
          $this->response['mensaje'] = 'Proceso '.$observado->nombres.' modificado con exito';
          $this->response['estado'] = true;
          $this->response['datos'] = $observado;

      } else {

          $observado = Sancionado::find($request->id);
          $observado->dni = $request->dni;
          $observado->nombres = $request->nombres;
          $observado->paterno = $request->paterno;
          $observado->materno = $request->materno;
          $observado->motivo = $request->motivo;
          $observado->observacion = $request->observacion;
          $observado->id_proceso = $request->id_proceso;
          $observado->save();

          $this->response['tipo'] = 'info';
          $this->response['titulo'] = '!REGISTRO MODIFICADO!';
          $this->response['mensaje'] = 'Proceso '.$observado->nombres.' modificado con exito';
          $this->response['estado'] = true;
          $this->response['datos'] = $observado;

        }

        return response()->json($this->response, 200);
    }

    public function exportarExcel(Request $request)
    {
        $datos = Sancionado::select('sancionados.id','sancionados.dni','sancionados.nombres','sancionados.paterno','sancionados.materno','sancionados.motivo','procesos.nombre as nombre_proceso','procesos.id as id_proceso')
            ->join('procesos', 'procesos.id', '=', 'sancionados.id_proceso')
            ->where('procesos.id', '=', auth()->user()->id_proceso)
            ->where(function ($query) use ($request) {
                if ($request->filled('term')) {
                    $query->orWhere('sancionados.dni', 'LIKE', '%' . $request->term . '%')
                        ->orWhere('sancionados.nombres', 'LIKE', '%' . $request->term . '%')
                        ->orWhere('sancionados.paterno', 'LIKE', '%' . $request->term . '%')
                        ->orWhere('sancionados.materno', 'LIKE', '%' . $request->term . '%');
                }
            })
            ->orderBy('sancionados.id', 'DESC')
            ->get();

        return Excel::download(new TablaGenericaExport($datos, ['ID', 'DNI', 'Nombres', 'Paterno', 'Materno', 'Motivo', 'Proceso']), 'observados.xlsx');
    }


}
