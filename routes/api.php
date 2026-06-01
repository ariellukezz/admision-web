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
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReniecController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\App\AuthController;
use App\Http\Controllers\App\RegistroController;

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
Route::get('/biometrico/seguimiento/{periodo}/{dni}', [ApixController::class, 'getIngresantePeriodoDniSeguimiento']);

Route::post('/login', [LoginController::class, 'login']);
Route::get('/get-codigo-conexion/{codigoConexion}', [LoginController::class, 'getCodigoConexion']);

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


Route::get('/get-pago-caja/{dni}/{secuencia}', function ($dni, $secuencia) {
    try {
        $response = Http::get(
            'http://tesoreria.unap.edu.pe/services/document/',
            [
                'w' => $dni,
                'd' => '2025-12-01'
            ]
        );

        if (!$response->successful()) {
            return response()->json(['error' => 'La solicitud no fue exitosa'], $response->status());
        }

        $pagos = $response->json('data');

        if (!is_array($pagos) || empty($pagos)) {
            return response()->json(['error' => 'No se encontraron pagos'], 404);
        }

        $pago = collect($pagos)->first(function ($item) use ($secuencia) {
            return isset($item['paymentTitle'])
                && str_ends_with($item['paymentTitle'], $secuencia);
        });

        if (!$pago) {
            return response()->json([
                'error' => 'No se encontró el pago con la secuencia ' . $secuencia
            ], 404);
        }

        return response()->json($pago);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error interno: ' . $e->getMessage()
        ], 500);
    }
});


Route::get('/get-pago-caja/{dni}', function ($dni) {
    try {
        $response = Http::get('http://tesoreria.unap.edu.pe/services/document/?w=' . $dni . '&d=2025-12-01');
         if ($response->successful()) {
            $datosCaja = $response->json(['data']);
            return response()->json($datosCaja);
        } else {
            return response()->json(['error' => 'La solicitud no fue exitosa'], $response->status());
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Se produjo un error al procesar la solicitud: ' . $e->getMessage()], 500);
    }
});

Route::get('/get-avance-proceso-postulante/{proceso}/{dni}', [TestController::class, 'getAvancePostulanteProceso']);
Route::get('/carreras-previas/{dni}', [IngresoController::class, 'carrerasPrevias']);

Route::post('/actualizar-lista-reniec', [ReniecController::class, 'consultarLista']);


Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::post('/registro', [UserController::class, 'registro']);
Route::post('/login-app', [UserController::class, 'loginApp']);
Route::post('/recuperar-password', [UserController::class, 'recuperarPassword']);
Route::post('/restablecer-password', [UserController::class, 'restablecerPassword']);

// ─── API APP MOBILE (SANCTUM) ────────────────────────────────────
Route::prefix('app')->group(function () {

    // Auth público (sin middleware)
    Route::post('/registro', [AuthController::class, 'registro']);
    Route::post('/login', [AuthController::class, 'login']);

    // Rutas protegidas con Sanctum
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // PASO 1: Datos personales
        Route::get('/validar-dni/{dni}', [RegistroController::class, 'validarDni']);
        Route::get('/consultar-reniec/{dni}', [RegistroController::class, 'consultarReniec']);
        Route::post('/registro-datos-personales', [RegistroController::class, 'registroDatosPersonales']);

        // PASO 2: Datos de contacto
        Route::post('/validar-correo', [RegistroController::class, 'validarCorreo']);
        Route::post('/validar-celular', [RegistroController::class, 'validarCelular']);
        Route::post('/registro-datos-contacto', [RegistroController::class, 'registroDatosContacto']);

        // PASO 3: Datos del colegio
        Route::get('/ubigeo/departamentos', [RegistroController::class, 'getDepartamentos']);
        Route::get('/ubigeo/provincias/{departamento}', [RegistroController::class, 'getProvincias']);
        Route::get('/ubigeo/distritos/{departamento}/{provincia}', [RegistroController::class, 'getDistritos']);
        Route::get('/colegios/{ubigeo}', [RegistroController::class, 'getColegiosPorUbigeo']);
        Route::post('/registro-datos-colegio', [RegistroController::class, 'registroDatosColegio']);

        // PASO 4: Datos del apoderado
        Route::get('/consultar-apoderado-reniec/{dni}', [RegistroController::class, 'consultarApoderadoReniec']);
        Route::post('/registro-datos-apoderado', [RegistroController::class, 'registroDatosApoderado']);
        Route::get('/apoderados/{idPostulante}', [RegistroController::class, 'getApoderados']);

        // CONSULTAR DATOS REGISTRADOS
        Route::get('/consultar-datos/{dni}', [RegistroController::class, 'consultarDatos']);
    });
});