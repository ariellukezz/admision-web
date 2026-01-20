<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Modalidad;

class ModalidadController extends Controller
{
    public function index()
    {
        return Inertia::render('Modalidad/index');

    }

    public function getModalidades(Request $request)
    {
      $query_where = [];

      $res = Modalidad::select(
        'modalidad.id',
        'modalidad.codigo',
        'modalidad.nombre',
        'modalidad.estado'
      )
      ->where($query_where)
      ->where(function ($query) use ($request) {
          return $query
              ->orWhere('modalidad.codigo', 'LIKE', '%' . $request->term . '%')
              ->orWhere('modalidad.nombre', 'LIKE', '%' . $request->term . '%');
      })->orderBy('modalidad.estado', 'DESC')->orderBy('modalidad.id', 'asc')
      ->paginate(10);

      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }


    public function saveModalidad(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'estado' => 'nullable|boolean',
            'id' => 'nullable|integer'
        ]);

        try {
            if (!$request->id) {
                $modalidad = Modalidad::create([
                    'codigo' => $request->codigo,
                    'nombre' => $request->nombre,
                    'estado' => 1,
                    'id_usuario' => auth()->id()
                ]);

                return response()->json([
                    'estado' => true,
                    'titulo' => 'REGISTRO NUEVO',
                    'mensaje' => "MODALIDAD {$modalidad->nombre} CREADA CON ÉXITO",
                    'datos' => $modalidad
                ], 200);

            } else {
                $modalidad = Modalidad::findOrFail($request->id);
                $modalidad->update([
                    'codigo' => $request->codigo,
                    'nombre' => $request->nombre,
                    'estado' => $request->estado ? 1 : 0,
                    'id_usuario' => auth()->id()
                ]);

                return response()->json([
                    'estado' => true,
                    'titulo' => 'REGISTRO MODIFICADO',
                    'mensaje' => "MODALIDAD {$modalidad->nombre} MODIFICADA CON ÉXITO",
                    'datos' => $modalidad
                ], 200);
            }

        } catch (\Throwable $e) {

            return response()->json([
                'estado' => false,
                'titulo' => 'ERROR',
                'mensaje' => 'Ocurrió un error al guardar la modalidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }


  public function deleteModalidad($id){
    $modalidad = Modalidad::find($id);
    $p = $modalidad;
    $modalidad->delete();

    $this->response['titulo'] = '!REGISTRO ELIMINADO!';
    $this->response['mensaje'] = 'MODALIDAD '.$p->nombre.' ELIMINADA CON EXITO';
    $this->response['estado'] = true;
    $this->response['datos'] = $p;
    return response()->json($this->response, 200);
  }


  public function getSelectModalidades(Request $request)
  {
    $res = Modalidad::select('id as dataIndex', 'nombre as title')
        ->where('estado', '=', 1)
        ->where(function ($query) use ($request) {
            return $query->orWhere('nombre', 'LIKE', '%' . $request->term . '%');
        })
        ->orderBy('nombre', 'ASC')
        ->paginate(20);
    $data = $res->toArray();

    array_unshift($data['data'], ['dataIndex' => 0, 'title' => 'Programa', 'width' => '300px']);

    $res->setCollection(collect($data['data']));

    $this->response['estado'] = true;
    $this->response['datos'] = $res;

    return response()->json($this->response, 200);

  }


  public function getModalidadesActivas( ) {
    $res = Modalidad::where('estado', 1)
    ->select('id as value', 'nombre as label')
    ->get();

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  }


}
