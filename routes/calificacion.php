<?php

use App\Http\Controllers\ResultadosController;
use App\Http\Controllers\PonderacionController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\ExcepcionesController;
use App\Http\Controllers\MultiplicadorController;
use App\Http\Controllers\ExamenController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('calificacion')->group(function () {

    Route::get('/', fn () => Inertia::render('Simulacro/Calificacion/calificacion'))->name('calificar-cal');

    Route::get('/subir-resultado', fn () => Inertia::render('Simulacro/SubirResultado'));
    Route::get('/resultado-simulacro', fn () => Inertia::render('Simulacro/resultados'));
    Route::post('/subir-excel-simulacro', [ResultadosController::class, 'SubirResultado']);

    //CALIFICACIÓN
    Route::get('/calificacion', fn () => Inertia::render('Simulacro/Calificacion/lecturas'))->name('simulacro-calificacion');
    Route::post('/carga-ide', [ResultadosController::class, 'cargaArchivoIde']);
    Route::post('/actualizar-ide', [ResultadosController::class, 'actualizarIde']);

    //TEMP
    Route::post('/carga-ide/{proceso}/{area}', [ResultadosController::class, 'cargaArchivoIde'])->withoutMiddleware(['web']);
    Route::post('/carga-res/{proceso}', [ResultadosController::class, 'cargaArchivoRes'])->withoutMiddleware(['web']);
    Route::post('/carga-pat/{proceso}', [ResultadosController::class, 'cargaArchivoPat'])->withoutMiddleware(['web']);

    Route::get('/get-select-puestos/{id_proceso}', [ResultadosController::class, 'selectPuestos']);
    Route::get('/descargar-excel', [ResultadosController::class, 'descargarExcel']);

    // PONDERACIONES
    Route::get('/ponderacion', fn () => Inertia::render('Simulacro/Calificacion/ponderacion'))->name('calificar-ponderacion');
    Route::post('/save-ponderacion', [PonderacionController::class, 'save']);
    Route::post('/get-ponderaciones', [PonderacionController::class, 'getPonderaciones']);
    Route::post('/save-ponderacion-detalle', [PonderacionController::class, 'saveDetalle']);
    Route::get('/get-ponderacion-detalle/{id}', [PonderacionController::class, 'getDetalle']);
    Route::delete('/delete-ponderacion/{id}', [PonderacionController::class, 'destroy']);
    Route::post('/duplicar-ponderacion/{id}', [PonderacionController::class, 'duplicar']);
    Route::post('/get-ponderaciones-select', [PonderacionController::class, 'getPonderacionesSelect']);
    Route::get('/get-areas', [PonderacionController::class, 'getAreas']);

    // ASIGNATURAS
    Route::get('/asignaturas', fn () => Inertia::render('Simulacro/Calificacion/asignaturas'))->name('calificar-asignaturas');
    Route::get('/asignaturas-list', [AsignaturaController::class, 'index']);
    Route::post('/asignaturas', [AsignaturaController::class, 'store']);
    Route::put('/asignaturas/{id}', [AsignaturaController::class, 'update']);
    Route::delete('/asignaturas/{id}', [AsignaturaController::class, 'destroy']);

    // EXCEPCIONES
    Route::get('/excepciones', [ExcepcionesController::class, 'index']);
    Route::post('/excepciones', [ExcepcionesController::class, 'store']);
    Route::put('/excepciones/{id}', [ExcepcionesController::class, 'update']);
    Route::delete('/excepciones/{id}', [ExcepcionesController::class, 'destroy']);
    Route::get('/excepciones/proceso/{id_proceso}', [ExcepcionesController::class, 'getByProceso']);
    Route::post('/excepciones/search', [ExcepcionesController::class, 'search']);

    // MULTIPLICADORES
    Route::get('/multiplicadores', fn () => Inertia::render('Simulacro/Calificacion/multiplicadores'))->name('calificar-multiplicadores');
    Route::get('/multiplicadores-list', [MultiplicadorController::class, 'index']);
    Route::post('/multiplicadores', [MultiplicadorController::class, 'store']);
    Route::put('/multiplicadores/{id}', [MultiplicadorController::class, 'update']);
    Route::delete('/multiplicadores/{id}', [MultiplicadorController::class, 'destroy']);

    // EXÁMENES (examen_simulacro con tipos y excepciones)
    Route::get('/examenes', fn () => Inertia::render('Simulacro/Calificacion/examenes'))->name('calificar-examenes');
    Route::get('/examenes/{id_examen}/tipos', [ExamenController::class, 'tiposPage'])->name('calificar-examenes-tipos');
    Route::get('/examenes-list', [ExamenController::class, 'index']);
    Route::post('/examenes', [ExamenController::class, 'store']);
    Route::put('/examenes/{id}', [ExamenController::class, 'update']);
    Route::delete('/examenes/{id}', [ExamenController::class, 'destroy']);
    Route::get('/examenes-tipos/{id_examen}', [ExamenController::class, 'getTipos']);
    Route::post('/examenes-tipos', [ExamenController::class, 'saveTipo']);
    Route::delete('/examenes-tipos/{id}', [ExamenController::class, 'deleteTipo']);
    Route::post('/examenes-tipos-archivo/{id_examen}', [ExamenController::class, 'subirTiposArchivo'])->withoutMiddleware(['web']);
    Route::get('/examenes-archivo/{id_archivo}', [ExamenController::class, 'verArchivo']);
    Route::post('/examenes-res/{id_tipo}', [ExamenController::class, 'subirRes'])->withoutMiddleware(['web']);
    Route::get('/examenes-res/{id_tipo}', [ExamenController::class, 'getRes']);
    Route::delete('/examenes-res/{id_tipo}', [ExamenController::class, 'deleteRes']);
    Route::get('/examenes-excepciones/{id_tipo}', [ExamenController::class, 'getExcepciones']);
    Route::post('/examenes-excepciones', [ExamenController::class, 'saveExcepcion']);
    Route::delete('/examenes-excepciones/{id}', [ExamenController::class, 'deleteExcepcion']);
});
