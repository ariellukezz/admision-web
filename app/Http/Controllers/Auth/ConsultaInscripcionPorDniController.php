<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConsultaInscripcionPorDniRequest;
use App\Services\Auth\InscripcionLookupService;
use Illuminate\Http\JsonResponse;

class ConsultaInscripcionPorDniController extends Controller
{
    public function __invoke(
        ConsultaInscripcionPorDniRequest $request,
        InscripcionLookupService $inscripcionLookup
    ): JsonResponse {
        $datos = $request->validated();

        return response()->json([
            'data' => $inscripcionLookup->buscarInscripcionPorDni(
                (int) $datos['id_proceso'],
                $datos['nro_doc']
            ),
        ]);
    }
}
