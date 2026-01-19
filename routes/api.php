<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ApixController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ResCepreController;
use App\Http\Controllers\SancionadoController;
use App\Http\Controllers\CepreController;
use App\Http\Controllers\HuellaController;
use App\Http\Controllers\PagoBancoController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\IngresoController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get-ingresante/{dni}/{anio}/{ciclo}', [ApixController::class, 'getIngresante']);
    Route::get('/get-postulante-pago/{dni}/{proceso}', [ApixController::class, 'getPostulantePago']);
    Route::get('/v1/get-postulante-inscrito/{dni}', [ApixController::class, 'getPostulanteProcesos']);
    Route::post('/get-procesos', [ProcesoController::class, 'getProcesos']);
});
Route::middleware('throttle:50,1')->post('/v1/postulante-cepre-inscrito', [CepreController::class, 'getVerInscripcion']);

//Route::get('/get-ingresantes/{dni}/{anio}/{ciclo}', [ApixController::class, 'getIngresante']);

Route::get('/get-ingresante-pago/{dni}/{anio}/{ciclo}', [ApixController::class, 'getIngresantePago']);
Route::get('/get-postulante-biometrico/{codigo}', [ApixController::class, 'getBiometrico']);
Route::get('/verificar-ingreso/{periodo}/{dni}', [ApixController::class, 'esIngresante']);

Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get-puntaje/{dni}', [BlogController::class, 'getPuntajes']);

Route::get('/v1/resultados_simulacro/{dni}', [ResCepreController::class, 'obtenerInformacionEstudiante']);
//Route::get('/v1/get-observados/{p}/{dni}', [SancionadoController::class, 'getObservados']);

Route::get('/v1/observados-cepre/{dni}', [SancionadoController::class, 'getSancionadoCepre'])->middleware('cepre');
//Route::get('/v1/observados-cepre-libre/{dni}', [SancionadoController::class, 'getSancionadoCepre']);

Route::get('/obtener-origin', function (Request $request) {
    $respuesta = Http::get('https://inscripciones.admision.unap.edu.pe/api/v1/observados-cepre/70757838');
    $contenido = $respuesta->getBody()->getContents();
    return response()->json($contenido);
});

Route::get('/obtener-origin2', function (Request $request) {
    $origin = $request->header('Origin');
    return response()->json(['origin' => $origin]);
});

Route::post('/cargar-imagen', [HuellaController::class, 'upload']);
Route::post('/cargar-imagen-cepre', [HuellaController::class, 'uploadcepre']);

Route::post('/cargar-imagen-juli', [HuellaController::class, 'uploadJuli']);
Route::post('/cargar-imagen-azangaro', [HuellaController::class, 'uploadAzangaro']);


Route::post('/get-pagos-banco', [PagoBancoController::class, 'getComprobantesDNI']);
Route::post('/get-pagos-banco-secuencia', [PagoBancoController::class, 'getComprobantesSecuencia']);

Route::get('/get-select-procesos', [ProcesoController::class, 'getSelectProcesoHuellas']);
Route::post('/helper-photos', [HuellaController::class, 'uploadFotos']);
Route::post('/test-correo', [IngresoController::class, 'crearCorreo']);

Route::get('/get-procesos', [ProcesoController::class, 'getProcesoResultados']);

Route::get('/v1/get-foto-ingresante/{dni}', [ApixController::class, 'ingresanteBase64']);


Route::get('/api-pagos/{dni}/{secuencia}', function ($dni, $secuencia) {
    try {
        $response = Http::get('http://unap.scielodigital.net.pe/caja/pago_admision/server/CHECK_PAYMENT/?w=' . $dni);

        if (!$response->successful()) {
            return response()->json(['error' => 'Error al consultar el servicio externo'], 500);
        }

        $pagos = $response->json();

        if (!is_array($pagos) || empty($pagos)) {
            return response()->json(['error' => 'No se encontraron pagos'], 404);
        }

        $pagoFiltrado = collect($pagos)->first(function ($pago) use ($secuencia) {
            return isset($pago['paymentTitle']) && $pago['paymentTitle'] === $secuencia;
        });

        if (!$pagoFiltrado) {
            return response()->json(['error' => 'No se encontró el pago con la secuencia: ' . $secuencia], 404);
        }

        return response()->json($pagoFiltrado);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Error en la solicitud: ' . $e->getMessage()], 500);
    }
});



//id_proceso, id_etapa, foto
