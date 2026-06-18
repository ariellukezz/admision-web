<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use App\Http\Controllers\RevisorDashboardController;
use App\Http\Controllers\RevisorPersonalController;
use App\Http\Controllers\RevisorNotificationController;
use App\Http\Controllers\RevisorDocumentoController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\SeleccionDataController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\PagoBancoController;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\PostulanteDocumentoController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\HuellaController;
use App\Http\Controllers\PreguntaController;

/*
|--------------------------------------------------------------------------
| Rutas de Revisor
|--------------------------------------------------------------------------
| Todas las rutas del módulo revisor. Middleware: auth + revisor.
|
| Para migrar a RBAC, reemplazar 'revisor' por 'rbac:revisor.access'
| en el grupo. Mantener 'auth' siempre.
*/

Route::prefix('revisor')->middleware('auth', 'revisor')->group(function () {

    Route::get('/', fn () => Inertia::render('Revisor/revisor'))->name('revisor');

    // ── Notificaciones del revisor ──────────────────────────
    Route::get('/notificaciones', [RevisorNotificationController::class, 'index']);
    Route::get('/notificaciones/no-leidas', [RevisorNotificationController::class, 'noLeidas']);
    Route::post('/notificaciones/{id}/leer', [RevisorNotificationController::class, 'marcarLeida']);
    Route::post('/notificaciones/leer-todas', [RevisorNotificationController::class, 'marcarTodasLeidas']);
    Route::get('/solicitudes-revision', [RevisorNotificationController::class, 'solicitudesRevision'])->name('revisor.solicitudes-revision')->middleware('rbac:revisor-solicitudes.read');

    // ── Mi Actividad (dashboard personal) ───────────────────
    Route::get('/mi-actividad', fn () => Inertia::render('Revisor/miActividad'))->name('revisor-mi-actividad')->middleware('rbac:revisor-actividad.read');
    Route::get('/mi-actividad/resumen', [RevisorPersonalController::class, 'resumen']);
    Route::get('/mi-actividad/timeline', [RevisorPersonalController::class, 'timeline']);
    Route::get('/mi-actividad/acciones-recientes', [RevisorPersonalController::class, 'accionesRecientes']);
    Route::get('/mi-actividad/distribucion-actividad', [RevisorPersonalController::class, 'distribucionActividad']);
    Route::get('/mi-actividad/ranking', [RevisorPersonalController::class, 'ranking']);
    Route::get('/mi-actividad/pendientes', [RevisorPersonalController::class, 'pendientes']);

    // ── Dashboard API ────────────────────────────────────────
    Route::get('/dashboard/resumen', [RevisorDashboardController::class, 'resumen']);
    Route::get('/dashboard/biometrico-resumen', [RevisorDashboardController::class, 'biometricoResumen']);
    Route::get('/dashboard/inscripciones-por-area', [RevisorDashboardController::class, 'inscripcionesPorArea']);
    Route::get('/dashboard/genero-por-area', [RevisorDashboardController::class, 'generoPorArea']);
    Route::get('/dashboard/inscritos-por-programa', [RevisorDashboardController::class, 'inscripcionesPorPrograma']);
    Route::get('/dashboard/timeline-inscripciones', [RevisorDashboardController::class, 'timelineInscripciones']);
    Route::get('/dashboard/modalidad-distribucion', [RevisorDashboardController::class, 'modalidadDistribucion']);
    Route::get('/dashboard/verificaciones-pendientes', [RevisorDashboardController::class, 'verificacionesPendientes']);

    // ── Páginas principales del revisor ──────────────────────
    Route::get('/validacion', fn () => Inertia::render('Revisor/validacion'))->name('revisor-validacion')->middleware('rbac:revisor-validacion.read');
    Route::get('/documentos', fn () => Inertia::render('Revisor/documentos'))->name('revisor-documentos')->middleware('rbac:revisor-documentos.read');
    Route::get('/imprimir', fn () => Inertia::render('Revisor/imprimir', ['id_proceso' => auth()->user()->id_proceso]))->name('revisor-imprimir')->middleware('rbac:revisor-biometrico.read');
    Route::get('/postulantes', fn () => Inertia::render('Revisor/postulantes'))->name('revisor-postulantes')->middleware('rbac:revisor-postulantes.read');
    Route::get('/postulante/{dni}', function ($dni) {
        return Inertia::render('Revisor/postulantes', ['dni' => $dni, 'solicitudId' => request()->query('solicitud')]);
    })->name('revisor.postulante-perfil')->middleware('rbac:revisor-postulantes.read');
    Route::get('/comprobantes-xd', fn () => Inertia::render('Revisor/components/voucher'));

    // ── Pagos ────────────────────────────────────────────────
    Route::post('/get-pagos-banco', [PagoBancoController::class, 'getPagosBanco']);
    Route::post('/get-comprobantes', [SeleccionDataController::class, 'getComprobantesDNI']);
    Route::post('/get-comprobantes-banco', [PagoBancoController::class, 'getComprobantesDNI']);
    Route::post('/verificar-comprobante', [SeleccionDataController::class, 'verificarComprobante']);
    Route::post('/verificar-comprobante-proceso', [PagoBancoController::class, 'verificarComprobanteProceso']);
    Route::get('/api-pagos/{parametro}', function ($parametro) {
        try {
            $response = Http::get('http://unap.scielodigital.net.pe/caja/pago_admision/server/CHECK_PAYMENT/?w=' . $parametro);
            return response($response->body(), $response->status())->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en la solicitud'], 500);
        }
    });

    // ── Revisión de documentos ───────────────────────────────
    Route::post('/get-certificados-revision', [DocumentoController::class, 'getCertificadosRevision']);
    Route::post('/cambiar-estado', [DocumentoController::class, 'cambiarEstado']);
    Route::post('/cambiar-estado-documento', [RevisorDocumentoController::class, 'cambiarEstadoDocumento'])->name('revisor.cambiar-estado-documento');
    Route::post('/observar-documento', [RevisorDocumentoController::class, 'observarDocumento'])->name('revisor.observar-documento');
    Route::post('/revision-rapida/{dni}', [RevisorDocumentoController::class, 'revisionRapida'])->name('revisor.revision-rapida');
    Route::post('/iniciar-revision/{dni}', [RevisorDocumentoController::class, 'iniciarRevision'])->name('revisor.iniciar-revision');
    Route::post('/finalizar-revision/{dni}', [RevisorDocumentoController::class, 'finalizarRevision'])->name('revisor.finalizar-revision');
    Route::post('/renotificar-postulante/{dni}', [RevisorDocumentoController::class, 'renotificarPostulante'])->name('revisor.renotificar-postulante');
    Route::post('/marcar-apto/{dni}', [RevisorDocumentoController::class, 'marcarApto'])->name('revisor.marcar-apto');
    Route::get('/citacion-sugerida/{dni}', [RevisorDocumentoController::class, 'citacionSugerida'])->name('revisor.citacion-sugerida');
    Route::get('/documentos-requisitos/{dni}', [RevisorDocumentoController::class, 'documentosPorRequisitos'])->name('revisor.documentos-requisitos');

    // ── Requisitos ───────────────────────────────────────────
    Route::get('/get-requisitos', [SeleccionDataController::class, 'getRequisitos']);
    Route::post('/save-requisito', [SeleccionDataController::class, 'saveReq']);

    // ── Postulantes ──────────────────────────────────────────
    Route::post('/get-postulantes', [SeleccionDataController::class, 'getPostulantes']);
    Route::post('/get-postulantes-biometrico', [PostulanteController::class, 'getPostulantesBiometrico']);
    Route::post('/get-postulante-dni', [SeleccionDataController::class, 'getPostulanteDNI']);
    Route::post('/get-postulante-requisitos', [SeleccionDataController::class, 'getPostulanteRequisitos']);
    Route::post('/get-postulantes-requisitos', [SeleccionDataController::class, 'getRequisitoPostulantes']);
    Route::post('/actualizar-postulante', [PostulanteController::class, 'actualizarDatos']);
    Route::post('/actualizar-ingresante', [PostulanteController::class, 'actualizarDatosIngresante']);
    Route::get('/get-codigos-postulante/{dni}', [DocumentoController::class, 'getCodigoDNI']);
    Route::post('/cambiar-codigo', [DocumentoController::class, 'cambiarCodigo']);

    // ── Avance ───────────────────────────────────────────────
    Route::get('/avance', [TestController::class, 'saveAvance']);

    // ── Fotos y biométrico ───────────────────────────────────
    Route::get('/foto-inscripcion', fn () => Inertia::render('Foto/foto'))->name('foto-inscripcion')->middleware('rbac:revisor-inscripcion.read');
    Route::get('/foto-biometrico', fn () => Inertia::render('Foto/fotobiometrico'))->name('foto-biometrico')->middleware('rbac:revisor-biometrico.read');
    Route::post('/guardar-foto-inscripcion', [FotoController::class, 'guardarFotoInscripcion']);
    Route::post('/guardar-foto-biometrico', [FotoController::class, 'guardarFotoBiometrico']);
    Route::post('/control-biometrico', [IngresoController::class, 'biometrico']);
    Route::post('/crear_correo_institucional', [IngresoController::class, 'crearCorreo']);
    Route::get('/fotos-admision', fn () => Inertia::render('Revisor/fotos/fotos'))->name('revisor-fotos-admision')->middleware('rbac:revisor-biometrico.read');
    Route::get('/get-codigo-conexion', [FotoController::class, 'getCodigoConexion']);
    Route::post('/guardar-huella', [HuellaController::class, 'guardar']);
    Route::post('/verificar-huella-1-1', [HuellaController::class, 'verificar1a1']);
    Route::post('/verificar-huella-1-n', [HuellaController::class, 'verificar1aN']);

    // ── Inscripción ──────────────────────────────────────────
    Route::get('/impresion', fn () => Inertia::render('Revisor/impresion'))->name('revisor-impresion-inscripcion')->middleware('rbac:revisor-inscripcion.read');
    Route::get('/get-postulante-dni/{dni}', [InscripcionController::class, 'getPostulanteByDni']);
    Route::get('/get-apoderados-postulante/{dni}', [InscripcionController::class, 'getApoderados']);
    Route::get('/get-vouchers-postulante/{dni}', [InscripcionController::class, 'getVouchers']);
    Route::get('/get-documentos-postulante/{dni}', [InscripcionController::class, 'getDocumentos']);
    Route::get('/preview-documento-revisor/{id}', [PostulanteDocumentoController::class, 'previewDocumentoRevisor']);
    Route::get('/descargar-documento-revisor/{id}', [PostulanteDocumentoController::class, 'descargarDocumentoRevisor']);
    Route::get('/get-preinscripciones-postulante/{dni}', [InscripcionController::class, 'getPreinscipciones']);
    Route::get('/get-inscripciones-postulante/{dni}', [InscripcionController::class, 'getInscripciones']);
    Route::get('/pdf-inscripción/{dni}', [InscripcionController::class, 'pdfInscripcion']);
    Route::post('/inscribir', [InscripcionController::class, 'Inscribir']);
    Route::get('/nuevo-pdf-inscripcion/{dni}', [InscripcionController::class, 'pdfInscripcion']);

    // ── Seguimiento ──────────────────────────────────────────
    Route::get('/seguimiento', fn () => Inertia::render('Revisor/seguimiento'))->name('revisor-seguimiento')->middleware('rbac:revisor-postulantes.read');

    // ── Ingresantes ──────────────────────────────────────────
    Route::get('/get-ingresante/{dni}', [IngresoController::class, 'getDatosIngreso']);
    Route::get('/get-ingresante-general/{dni}', [IngresoController::class, 'getDatosIngresoGeneral']);
    Route::get('/get-codigo/{dni}', [IngresoController::class, 'getCodigo']);

    // ── Cambiar proceso ──────────────────────────────────────
    Route::post('/cambiar_proceso', [ProcesoController::class, 'cambiarProceso']);
});

// PDF biométrico (fuera del grupo pero con middleware revisor)
Route::get('/pdf-datos-biometrico/{dni}', [IngresoController::class, 'pdfbiometrico2'])->middleware('auth', 'revisor', 'rbac:revisor-biometrico.read');
