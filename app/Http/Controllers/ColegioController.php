<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Colegio; 
use App\Models\Dataversion;

class ColegioController extends Controller
{

  
    public function save(Request $request){
      if (!$request->id) {
          $colegio = Colegio::create([
            'cod_modular' => $request->cod_modular, 
            'cod_local' => $request->cod_local,
            'nombre' => $request->nombre,
            'forma' => $request->forma,
            'nivel' => $request->nivel, 
            'sexo'=>'MIXTO',
            'gestion' => $request->gestion, 
            'ubigeo' => $request->ubigeo,
            'direccion' => $request-> direccion,
            'observacion' => $request->observacion
          ]);

          $this->response['titulo'] = '!NUEVO!';
          $this->response['mensaje'] = 'COLEGIO '.$request->nombre.' CREADO CON EXITO';
          $this->response['estado'] = true;
          $this->response['datos'] = $colegio;
      }else{

          $colegio = Colegio::find($request->id);
          Dataversion::create([ 'registro_id' => $colegio->id, 'tabla' => $colegio->getTable(),  'usuario_id' => auth()->id(), 'fecha' => now(), 'datos' => $colegio->toJson() ]);

          $colegio->cod_modular = $request->cod_modular;
          $colegio->cod_local = $request->cod_local;
          $colegio->nombre = $request->nombre;
          $colegio->nivel = $request->nivel;
          $colegio->forma = $request->forma;
          $colegio->gestion = $request->gestion;
          $colegio->ubigeo = $request->ubigeo;
          $colegio->direccion = $request->direccion;
          $colegio->observacion = $request->observacion;
          $colegio->save();  

          $this->response['titulo'] = '!REGISTRO MODIFICADO!';
          $this->response['mensaje'] = 'COLEGIO'.$colegio->nombre.' MODIFICADO CON EXITO';
          $this->response['estado'] = true;
          $this->response['datos'] = $colegio;


      } 

      return response()->json($this->response, 200);
    }

    public function getUbigeoColegio(Request $request)
    {
      $res = Colegio::select('colegios.id as value', 'colegios.nombre as label', 'colegios.direccion', 'colegios.ubigeo as ubigeo',
        'postulante.anio_egreso as egreso',
        'departamento.nombre as departamento', 'departamento.codigo as dep',
        'provincia.nombre as provincia', 'provincia.codigo as prov', 
        'distritos.nombre as distrito', 'distritos.codigo as dist'
      )
      ->join('postulante','postulante.id_colegio','colegios.id')
      ->leftjoin('ubigeo','colegios.ubigeo','ubigeo.ubigeo')
      ->leftjoin('departamento','ubigeo.id_departamento','departamento.id')
      ->leftjoin('provincia','ubigeo.id_provincia','provincia.id')
      ->leftjoin('distritos','distritos.id','ubigeo.id_distrito')
      ->where('postulante.id','=', $request->id_postulante)
      ->get(10);

      return $res;
      
      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }

    public function getColegiosDistrito(Request $request) {

      $res = Colegio::select( 'colegios.id as value', 'colegios.direccion',
        DB::raw('CONCAT(colegios.nombre, " (", colegios.gestion, ")", IF(colegios.direccion IS NOT NULL AND colegios.direccion != "", CONCAT(" - ", colegios.direccion), "")) as label') )
        ->where('colegios.ubigeo','=',$request->ubigeo_cole)
        ->orderBy('colegios.nombre', 'ASC')->get();
  
      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }

    public function getColegiosAdmin(Request $request)
    {
      $query_where = [];

      $res = Colegio::select( 'id','nombre','cod_modular','ubigeo' )
      ->where($query_where)
      ->where(function ($query) use ($request) {
          return $query
            ->orWhere('nombre', 'LIKE', '%' . $request->term . '%')
            ->orWhere('ubigeo', 'LIKE', '%' . $request->term . '%')
            ->orWhere('cod_modular', 'LIKE', '%' . $request->term . '%')
            ->orWhere('ubigeo', 'LIKE', '%' . $request->term . '%');
      })
      ->paginate(20);
  
      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }

    public function getColegios(Request $request){
      $query_where = [];
      if($request->dep && !$request->prov && !$request->dist){ array_push($query_where, [DB::raw('SUBSTRING(colegios.ubigeo,1,2)'), '=', $request->dep]);}
      if($request->prov && !$request->dist){ array_push($query_where, [DB::raw('SUBSTRING(colegios.ubigeo,1,4)'), '=', $request->dep.$request->prov]);}
      if($request->dist){ array_push($query_where, [DB::raw('SUBSTRING(colegios.ubigeo,1,6)'), '=', $request->dep.$request->prov.$request->dist]);}
      if($request->ges){ array_push($query_where, ['colegios.gestion', '=', $request->ges]);}

        $res = Colegio::select( 'colegios.id', 'distritos.nombre as distrito', 'colegios.cod_modular',  'colegios.cod_local',  'colegios.nombre',  'colegios.nivel',   'colegios.gestion', 'colegios.direccion',  'colegios.ubigeo', DB::raw("CONCAT(departamento.nombre, '/', provincia.nombre, '/', distritos.nombre) AS lugar"))
            ->join('ubigeo', 'ubigeo.ubigeo', '=', 'colegios.ubigeo')
            ->join('departamento', 'ubigeo.id_departamento', '=', 'departamento.id')
            ->join('provincia', 'ubigeo.id_provincia', '=', 'provincia.id')
            ->join('distritos', 'ubigeo.id_distrito', '=', 'distritos.id')
            ->where($query_where)
            ->where(function ($query) use ($request) {
                return $query
                ->orWhere('colegios.nombre', 'LIKE', '%' . $request->term . '%')
                ->orWhere('colegios.cod_modular', 'LIKE', '%' . $request->term . '%')
                ->orWhere('colegios.cod_modular', 'LIKE', '%' . $request->term . '%')
                ->orWhere(DB::raw('CONCAT(departamento.nombre, "/", provincia.nombre, "/", distritos.nombre)'),'LIKE','%'.$request->term . '%');
            })
            ->paginate($request->paginashoja);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);

    }
    
}
