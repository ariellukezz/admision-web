<?php
namespace App\Http\Controllers\Segundas;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Modalidad;
use DB;

class ModalidadSegundaController extends Controller
{           
    public function getModalidadesActivas( ) {
        $res = Modalidad::where('estado', 1)
        ->select('id as value', 'nombre as label')
        ->get();
        
        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }
    
}

