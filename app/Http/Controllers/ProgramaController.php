<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Filial;
use App\Models\Dataversion;
use App\Models\Programa;

class ProgramaController extends Controller
{
    public function index()
    {
        return Inertia::render('Programas/programas');
        
    }  



    public function getProgramas(Request $request)
    {
      $query_where = [];
     
     // array_push($query_where, ['filial.cod_dep', '=', 'provincia.cod_dep']);

      $res = Programa::select(
        'programa.id',
        'programa.codigo',
        'programa.nombre',
        'programa.estado AS estado',
        'programa.area AS area',
        'programa.tipo_autorizacion',
        'programa.nivel_academico',
        'facultad.id AS id_fac',
        'facultad.facultad AS facultad'
      )
        ->join ('facultad', 'facultad.id', '=','programa.id_facultad')
        ->where($query_where)
        ->where(function ($query) use ($request) {
            return $query
                ->orWhere('programa.codigo', 'LIKE', '%' . $request->term . '%')
                ->orWhere('programa.nombre', 'LIKE', '%' . $request->term . '%')
                ->orWhere('facultad.facultad', 'LIKE', '%' . $request->term . '%')
                ->orWhere('programa.area', 'LIKE', '%' . $request->term . '%');
        })->orderBy('programa.id', 'DESC')
        ->paginate(10);
  
      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }


    public function savePrograma(Request $request ) {

        $programa = null;
        if (!$request->id) {
            $programa = Programa::create([
                'nombre' => $request->nombre,
                'codigo' => $request->codigo,
                'nivel_academico' => $request->nivel_academico,
                'tipo_autorizacion' => $request->tipo_autorizacion,
                'efi' => 'NO',
                'estado' => $request->estado,
                'id_facultad' => $request->id_facultad,
                'area' => $request->area,
                'id_usuario' => auth()->id()
            ]);
            $this->response['titulo'] = 'REGISTRO NUEVO';
            $this->response['mensaje'] = 'Programa '.$programa->nombre.' creada con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $programa;
        } else {

            $programa = Programa::find($request->id);
            Dataversion::create([ 'registro_id' => $programa->id, 'tabla' => $programa->getTable(),  'usuario_id' => auth()->id(), 'fecha' => now(), 'datos' => $programa->toJson() ]);
            $programa->nombre = $request->nombre;
            $programa->codigo = $request->codigo;
            $programa->nivel_academico = $request->nivel_academico;
            $programa->tipo_autorizacion = $request->tipo_autorizacion;
            $programa->efi = "NO";
            $programa->estado = $request->estado;
            $programa->id_facultad = $request->id_facultad;
            $programa->area = $request->area;
            $programa->estado = $request->estado;
            $programa->id_usuario = auth()->id();
            $programa->save();

            $this->response['titulo'] = '!REGISTRO MODIFICADO!';
            $this->response['mensaje'] = 'Filial '.$programa->nombre.' modificado con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $programa;
        }

    return response()->json($this->response, 200);
  }


  public function deletePrograma($id){
    $programa = Programa::find($id);
    $p = $programa;
    $programa->delete();

    $this->response['titulo'] = '!REGISTRO ELIMINADO!';
    $this->response['mensaje'] = 'Programa '.$p->nombre.' eliminado con exito';
    $this->response['estado'] = true;
    $this->response['datos'] = $p;
    return response()->json($this->response, 200);
  }

    


}
