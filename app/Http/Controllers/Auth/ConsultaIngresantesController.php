<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConsultaPorProcesoRequest;
use App\Services\Auth\IngresanteLookupService;
use Illuminate\Http\JsonResponse;

class ConsultaIngresantesController extends Controller
{
    public function __invoke(
        ConsultaPorProcesoRequest $request,
        IngresanteLookupService $ingresanteLookup
    ): JsonResponse {
        $datos = $request->validated();

        return response()->json([
            'data' => $ingresanteLookup->buscarIngresantes(
                (int) $datos['id_proceso'],
                $datos['nro_docs']
            ),
        ]);
    }
}
