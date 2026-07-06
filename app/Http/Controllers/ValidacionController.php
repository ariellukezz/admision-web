<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Postulante;

class ValidacionController extends Controller {

    public function existeCelular(Request $request) {
        $existeEnOtroDni = Postulante::where('celular', $request->celular)
            ->where('nro_doc', '!=', $request->dni)
            ->exists();

        return response()->json($existeEnOtroDni, 200);
    }

    public function existeCorreo(Request $request) {
        $existeEnOtroDni = Postulante::where('email', $request->email)
            ->where('nro_doc', '!=', $request->dni)
            ->exists();

        return response()->json($existeEnOtroDni, 200);
    }

    
}
