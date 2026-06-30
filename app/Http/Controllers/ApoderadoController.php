<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Apoderado;
use App\Models\Paso;
use Illuminate\Support\Str;

class ApoderadoController extends Controller {

  public function getApoderado(Request $request )
  {
    $res = Apoderado::select( 'id','tipo_doc', 'nro_documento', 'paterno', 'materno', 'nombres', 'tipo_apoderado' )
    ->where('apoderado.id_postulante','=', $request->id_postulante)
    ->where('apoderado.tipo_apoderado','=', $request->tipo)
    ->get();
    
    if(count($res) === 0) {
      $this->response['mensaje'] = "Apoderado no registrado";
      $this->response['estado'] = false;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);      
    }else {

      $this->response['mensaje'] = "tienes apoderado";
      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }
  }



  public function getApoderadoAdmin(Request $request){
    $query_where = [];
    // array_push($query_where, ['filial.cod_dep', '=', 'provincia.cod_dep']);
    $res = Apoderado::select(
      'apoderado.id', 'apoderado.nro_documento as dni', 'apoderado.paterno', 'apoderado.materno',
      'apoderado.nombres', 'apoderado.tipo_apoderado',
      'postulante.id AS id_postulante', 'postulante.nombres as postulante', 'postulante.nro_doc as dni_postulante',
      'postulante.primer_apellido', 'postulante.segundo_apellido'  
    )
    ->leftjoin('postulante','apoderado.id_postulante','postulante.id')
    ->where($query_where)
    ->where(function ($query) use ($request) {
        return $query
            ->orWhere('apoderado.nro_documento', 'LIKE', '%' . $request->term . '%')
            ->orWhere('apoderado.nombres', 'LIKE', '%' . $request->term . '%')
            ->orWhere('apoderado.paterno', 'LIKE', '%' . $request->term . '%')
            ->orWhere('apoderado.materno', 'LIKE', '%' . $request->term . '%')
            ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
            ->orWhere('postulante.nombres', 'LIKE', '%' . $request->term . '%')
            ->orWhere('postulante.primer_apellido', 'LIKE', '%' . $request->term . '%')
            ->orWhere('postulante.segundo_apellido', 'LIKE', '%' . $request->term . '%');
    })->orderBy('apoderado.id', 'DESC')
    ->paginate($request->pages);

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
    
  }
    public function getFiliales(Request $request)
    {
      $query_where = [];
     
     // array_push($query_where, ['filial.cod_dep', '=', 'provincia.cod_dep']);

      $res = Filial::select(
        'filial.id',
        'filial.codigo',
        'filial.nombre',
        'departamento.nombre AS departamento',
        'departamento.id AS id_dep',
        'provincia.nombre AS provincia',
        'provincia.id as id_prov',
        'filial.estado AS estado',
        'filial.efi'
      )
        ->join ('departamento', 'filial.id_dep', '=','departamento.id' )
        ->join ('provincia', 'filial.id_prov', '=','provincia.id')
        ->where($query_where)
        ->where(function ($query) use ($request) {
            return $query
                ->orWhere('filial.codigo', 'LIKE', '%' . $request->term . '%')
                ->orWhere('departamento.nombre', 'LIKE', '%' . $request->term . '%')
                ->orWhere('filial.nombre', 'LIKE', '%' . $request->term . '%');
        })->orderBy('filial.id', 'DESC')
        ->paginate(10);
  
      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }



  public function saveApoderado(Request $request ) {

    $tipo_doc = 1;
    if( Str::length($request->dni) == 12 ) { $tipo_doc = 2; }


    $apoderado = null;
    if (!$request->id) {
        $apoderado = Apoderado::create([
            'tipo_doc' => $tipo_doc,
            'nro_documento' => $request->dni,
            'paterno' => $request->paterno, 
            'materno' => $request->materno,
            'nombres' => $request->nombres,
            'id_postulante' => $request->id_postulante,
            'tipo_apoderado' => $request->tipo_apoderado,
        ]);

        $this->response['tipo'] = 'success';
        $this->response['titulo'] = 'REGISTRO NUEVO';
        $this->response['mensaje'] = 'Apoderado '.$apoderado->nombres.' creado con exito';
        $this->response['estado'] = true;
        $this->response['datos'] = $apoderado;
    } else {
        $apoderado = Apoderado::find($request->id);
        $apoderado->tipo_doc = $tipo_doc;
        $apoderado->nro_documento = $request->dni;
        $apoderado->paterno = $request->paterno; 
        $apoderado->materno = $request->materno;
        $apoderado->nombres = $request->nombres;
        $apoderado->tipo_apoderado = $request->tipo_apoderado;
        $apoderado->save();

        $this->response['tipo'] = 'info';
        $this->response['titulo'] = '!REGISTRO MODIFICADO!';
        $this->response['mensaje'] = 'Datos del '.$apoderado->nombres.' actualizados';
        $this->response['estado'] = true;
        $this->response['datos'] = $apoderado;
      }

      return response()->json($this->response, 200);
    }


    private function savePasos($nom, $num, $avan, $pos, $pro, $t) {
      $numero = 0;
      if($t == 3) { 
        $numero = 5;
      } else { 
        $numero = $num;
      } 

      $pasos = null;
      $pasos = Paso::create([
          'nombre' => $nom,
          'nro' => $numero,
          'avance' => $avan, 
          'postulante' => $pos,
          'proceso' => $pro
      ]);
      $this->response['tipo'] = 'success';
      $this->response['titulo'] = 'PASO REGISTRADO';
      $this->response['mensaje'] = 'Proceso '.$pasos->nombre.' creado con exito';
      $this->response['estado'] = true;
      $this->response['datos'] = $pasos;
      
      return response()->json($this->response, 200);
    }

    public function saveApoderadoAdmin(Request $request ) {
      $tipo_doc = 1;
  
      $apoderado = null;
      if (!$request->id) {
          $apoderado = Apoderado::create([
              'tipo_doc' => $tipo_doc,
              'nro_documento' => $request->dni,
              'paterno' => $request->paterno, 
              'materno' => $request->materno,
              'nombres' => $request->nombres,
              'observacion' => $request->observacion,
              'id_postulante' => $request->id_postulante,
              'tipo_apoderado' => $request->tipo_apoderado,
          ]);
  
          if($request->actualizar == 'si'){
            $this->savePasos($request->name, $request->nro, $request->avance, $request->id_postulante, $request->proceso);
          }
  
          $this->response['tipo'] = 'success';
          $this->response['titulo'] = 'REGISTRO NUEVO';
          $this->response['mensaje'] = 'Apoderado '.$apoderado->nombres.' creado con exito';
          $this->response['estado'] = true;
          $this->response['datos'] = $apoderado;
      } else {
          $temp = Apoderado::find($request->id);
          $apoderado = Apoderado::find($request->id);
          $apoderado->tipo_doc = $tipo_doc;
          $apoderado->nro_documento = $request->nro_documento;
          $apoderado->paterno = $request->paterno; 
          $apoderado->materno = $request->materno;
          $apoderado->observacion = $request->observacion;
          $apoderado->nombres = $request->nombres;
          $apoderado->tipo_apoderado = $request->tipo_apoderado;
          $apoderado->id_usuario = auth()->id();
          $apoderado->save();
  
          $this->response['tipo'] = 'info';
          $this->response['titulo'] = '!REGISTRO MODIFICADO!';
          $this->response['mensaje'] = 'Datos del '.$apoderado->nombres.' actualizados';
          $this->response['estado'] = true;
          $this->response['datos'] = $apoderado;  
        }

        return response()->json($this->response, 200);
    }

    public function getApoderadobyDni(Request $request) {

      $apoderado = Apoderado::select('id', 'nro_documento as dni', 'paterno', 'materno', 'nombres')
      ->where('nro_documento','=',$request->dni)
      ->first();
      if($apoderado){
        $this->response['estado'] = true;
        $this->response['datos'] = $apoderado;
      }else{
        $this->response['estado'] = false;
      }


      return response()->json($this->response, 200);
    }



}
