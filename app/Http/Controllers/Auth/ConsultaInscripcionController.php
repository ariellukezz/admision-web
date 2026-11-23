<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConsultaPorProcesoRequest;
use App\Services\Auth\InscripcionLookupService;
use Illuminate\Http\JsonResponse;

class ConsultaInscripcionController extends Controller
{
    public function __invoke(
        ConsultaPorProcesoRequest $request,
        InscripcionLookupService $inscripcionLookup
    ): JsonResponse {
        $datos = $request->validated();

        return response()->json([
            'data' => $inscripcionLookup->buscarInscritos(
                (int) $datos['id_proceso'],
                $datos['nro_docs']
            ),
        ]);
    }
}
