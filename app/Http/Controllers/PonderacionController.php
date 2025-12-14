<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ponderacion;
use App\Models\PonderacionDetalle;

class PonderacionController extends Controller
{

    public function getPonderaciones(Request $request)
    {
      $res = Ponderacion::select(
        'ponderacion_simulacro.id',
        'ponderacion_simulacro.nombre',
        DB::raw("if(ponderacion_simulacro.area = 1, 'Biomedicas', if(ponderacion_simulacro.area = 2, 'Ingenierías', 'Sociales' )) as area")
      )
        ->where(function ($query) use ($request) {
            return $query
                ->orWhere('ponderacion_simulacro.nombre', 'LIKE', '%' . $request->term . '%');
        })->orderBy('ponderacion_simulacro.id', 'DESC')
        ->paginate($request->paginasize);
  
      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }


    public function save(Request $request ) {

        $filial = null;
        if (!$request->id) {
            $filial = Ponderacion::create([
                'nombre' => $request->nombre,
                'area' => $request->area
            ]);

            $this->response['titulo'] = 'CREADO CON EXITO0';
            $this->response['estado'] = true;
            $this->response['datos'] = $filial;
        } 
        // else {

        //     $filial = Filial::find($request->id);
        //     Dataversion::create([ 'registro_id' => $filial->id, 'tabla' => $filial->getTable(),  'usuario_id' => auth()->id(), 'fecha' => now(), 'datos' => $filial->toJson() ]);

        //     $filial->nombre = $request->nombre;
        //     $filial->codigo = $request->codigo;
        //     $filial->ubigeo = $request->lugar;
        //     $filial->estado = $request->estado;
        //     $filial->direccion = $request->direccion;
        //     $filial->id_usuario = auth()->id();
        //     $filial->save();  

        //     $this->response['titulo'] = '!REGISTRO MODIFICADO!';
        //     $this->response['mensaje'] = 'Filial '.$filial->nombre.' modificado con exito';
        //     $this->response['estado'] = true;
        //     $this->response['datos'] = $filial;
        //}

    return response()->json($this->response, 200);
  }


  public function deleteFilial($id){
    $filial = Filial::find($id);
    $p = $filial;
    $filial->delete();

    $this->response['titulo'] = '!REGISTRO ELIMINADO!';
    $this->response['mensaje'] = 'Filial '.$p->nombre.' eliminada con exito';
    $this->response['estado'] = true;
    $this->response['datos'] = $p;
    return response()->json($this->response, 200);
  }


  public function insertarPonderacion(Request $request)
  {
      $data = $request->input('pesos');

      // 👇 SOLO el ID, no el objeto
      $id_ponderacion = (int) $request->id_ponderacion['id'];

      $cont = 1;

      foreach ($data as $item) {

          for ($i = 0; $i < (int)$item['n_preguntas']; $i++) {
              PonderacionDetalle::create([
                  'asignatura' => $item['nombre'],
                  'ponderacion' => (float) $item['ponderacion'],
                  'numero' => $cont,
                  'id_ponderacion_simulacro' => $id_ponderacion
              ]);

              $cont++;
          }
      }

      return response()->json(['ok' => true]);
  }
 
  
  public function getPonderacionesSelect(Request $request){
    $res = Ponderacion::select(
      'ponderacion_simulacro.id as key',
      DB::raw("CONCAT('(', IF(AREA = 1, 'Biomédicas', IF(AREA = 2, 'Ingenierías', 'Sociales')), ') - ', nombre) AS 'value'")
    )
      ->where(function ($query) use ($request) {
          return $query
              ->orWhere('ponderacion_simulacro.nombre', 'LIKE', '%' . $request->term . '%');
      })->orderBy('ponderacion_simulacro.id', 'DESC')
      ->paginate($request->paginasize);

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  
  }


}
