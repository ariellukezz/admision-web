<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResumenIngresantesRequest;
use App\Services\Admin\ResumenIngresantesService;
use Illuminate\Http\JsonResponse;
use Inertia\Response;
use Inertia\Inertia;

class ResumenIngresantesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Resumenes/ingresantes');
    }

    public function resumen(
        ResumenIngresantesRequest $request,
        ResumenIngresantesService $resumenIngresantes
    ): JsonResponse {
        $idProceso = (int) $request->user()->id_proceso;

        return response()->json([
            'success' => true,
            'data' => $resumenIngresantes->porDimensiones(
                $idProceso,
                $request->validated('group_by')
            ),
            'total_general' => $resumenIngresantes->total($idProceso),
        ]);
    }
}
