<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Filial;
use App\Models\Carpeta;
use Illuminate\Support\Facades\Storage;
use App\Models\Dataversion;
use App\Exports\TablaGenericaExport;
use Excel;

class FilialController extends Controller
{
    public function index()
    {
        return Inertia::render('Filial/filial'); 
    }  

    public function getFiliales(Request $request) {
      $res = Filial::select( 'filial.id', 'filial.codigo','filial.nombre', 'filial.ubigeo', 
        'filial.estado AS estado','filial.efi', 'filial.direccion',
        DB::raw("CONCAT(departamento.nombre,'/',provincia.nombre,'/',distritos.nombre) AS lugar")
      )
        ->leftjoin('ubigeo','ubigeo.ubigeo','filial.ubigeo')
        ->leftjoin('departamento','departamento.id','ubigeo.id_departamento')
        ->leftjoin('provincia','provincia.id','ubigeo.id_provincia')
        ->leftjoin('distritos','distritos.id','ubigeo.id_distrito')
        ->where(function ($query) use ($request) {
            return $query
                ->orWhere('filial.codigo', 'LIKE', '%' . $request->term . '%')
                ->orWhere(DB::raw("CONCAT(departamento.nombre,'/',provincia.nombre,'/',distritos.nombre)"),'LIKE', '%' . $request->term . '%')
                ->orWhere('filial.nombre', 'LIKE', '%' . $request->term . '%')
                ->orWhere('departamento.nombre', 'LIKE', '%' . $request->term . '%')
                ->orWhere('provincia.nombre', 'LIKE', '%' . $request->term . '%')
                ->orWhere('distritos.nombre', 'LIKE', '%' . $request->term . '%');
        })->orderBy('filial.id', 'DESC')
        ->paginate($request->paginasize);
  
      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
    }


    public function saveFilial(Request $request ) {

        $filial = null;
        if (!$request->id) {
            // $carpeta = Carpeta::create([
            //     'nombre' => $request->nombre,
            //     'carpeta_padre_id' => 1,
            // ]);

            $carpeta = 1;
            $filial = Filial::create([
                'nombre' => $request->nombre,
                'codigo' => $request->codigo,
                'ubigeo' => $request->lugar,
                'estado' => $request->estado,
                'carpeta' => $carpeta,
                'direccion' => $request->direccion,
                'id_usuario' => auth()->id()
            ]);

            // $carpeta->url = "/1/$carpeta->id";
            // $carpeta->save();
        
            // $directorio = 'raiz/' . $carpeta->id;
            // Storage::disk('local')->makeDirectory($directorio);

            $this->response['titulo'] = 'REGISTRO NUEVO';
            $this->response['mensaje'] = 'Filial '.$filial->nombre.' creada con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $filial;
        } else {

            $filial = Filial::find($request->id);
            Dataversion::create([ 'registro_id' => $filial->id, 'tabla' => $filial->getTable(),  'usuario_id' => auth()->id(), 'fecha' => now(), 'datos' => $filial->toJson() ]);

            $filial->nombre = $request->nombre;
            $filial->codigo = $request->codigo;
            $filial->ubigeo = $request->lugar;
            $filial->estado = $request->estado;
            $filial->direccion = $request->direccion;
            $filial->id_usuario = auth()->id();
            $filial->save();  

            $this->response['titulo'] = '!REGISTRO MODIFICADO!';
            $this->response['mensaje'] = 'Filial '.$filial->nombre.' modificado con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $filial;
        }

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

    public function exportarExcel(Request $request)
    {
        $datos = Filial::select('filial.id', 'filial.codigo','filial.nombre', 'filial.ubigeo',
            'filial.estado AS estado','filial.efi', 'filial.direccion',
            DB::raw("CONCAT(departamento.nombre,'/',provincia.nombre,'/',distritos.nombre) AS lugar")
        )
        ->leftjoin('ubigeo','ubigeo.ubigeo','filial.ubigeo')
        ->leftjoin('departamento','departamento.id','ubigeo.id_departamento')
        ->leftjoin('provincia','provincia.id','ubigeo.id_provincia')
        ->leftjoin('distritos','distritos.id','ubigeo.id_distrito')
        ->where(function ($query) use ($request) {
            if ($request->filled('term')) {
                $query->orWhere('filial.codigo', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('filial.nombre', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('departamento.nombre', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('provincia.nombre', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('distritos.nombre', 'LIKE', '%' . $request->term . '%');
            }
        })
        ->orderBy('filial.id', 'DESC')
        ->get();

        return Excel::download(new TablaGenericaExport($datos, ['ID', 'Código', 'Nombre', 'Ubigeo', 'Estado', 'EFI', 'Dirección', 'Lugar']), 'filiales.xlsx');
    }

}
