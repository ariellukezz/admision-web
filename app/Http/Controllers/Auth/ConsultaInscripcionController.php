<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConsultarInscripcionesRequest;
use App\Services\Auth\InscripcionLookupService;
use Illuminate\Http\JsonResponse;

class ConsultaInscripcionController extends Controller
{
    public function __invoke(
        ConsultarInscripcionesRequest $request,
        InscripcionLookupService $inscripcionLookup
    ): JsonResponse {
        return response()->json([
            'data' => $inscripcionLookup->buscarInscritos(
                (int) $request->user()->id_proceso,
                $request->validated('nro_docs')
            ),
        ]);
    }
}
