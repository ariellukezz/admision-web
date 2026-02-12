<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Segundas\ProgramaSegundaController;
use App\Http\Controllers\Segundas\PreinscripcionSegundasController;
use App\Http\Controllers\Segundas\PostulanteSegundaController;
use App\Http\Controllers\Segundas\VacantesSegundaController;
use App\Http\Controllers\Segundas\ModalidadSegundaController;
use App\Http\Controllers\Segundas\ObservadosSegundaController;
use App\Http\Controllers\Segundas\IdentidadSegundaController;
use App\Http\Controllers\Segundas\ResumenesSegundaController;


Route::get('/segundas', fn () => Inertia::render('Segundas/Admin/Preinscripciones/index'))->name('segundas-index');


// Route::middleware('auth')->group(function () { });

Route::prefix('segundas')->middleware('segundas','auth')->group(function () {

    //PROGRAMAS
    Route::post('select-programas-segundas', [ProgramaSegundaController::class, 'getSelectProgramas']);
    Route::post('select-programas-segundas-autorizados', [ProgramaSegundaController::class, 'getSelectProgramasAutorizados']);

    //PREINSCRIPCION
    Route::get('/preinscripciones', fn () => Inertia::render('Segundas/Admin/Preinscripciones/index'))->name('segundas-preinscripciones-admin');
    Route::post('get-postulantes-segundas', [PostulanteSegundaController::class, 'getPostulantes']);
    Route::get('postulante-perfil/{dni}', [PostulanteSegundaController::class, 'showPostulante']);
    Route::get('get-postulante-datos/{dni}', [PostulanteSegundaController::class, 'getDatosPostulante']);



    Route::post('actualizar-preinscripciones-segundas', [PreinscripcionSegundasController::class, 'Actualizar']);
    Route::post('guardar-inscripcion-segundas', [PreinscripcionSegundasController::class, 'Inscribir']);


    //POSTULANTES
    Route::get('/postulantes', fn () => Inertia::render('Segundas/Admin/Postulantes/index'))->name('segundas-postulantes-admin');
    Route::post('get-preinscripciones-segundas', [PreinscripcionSegundasController::class, 'getPreinscripciones']);
    Route::post('/save-postulante-admin', [PostulanteSegundaController::class, 'savePostulanteAdmin']);

    //VACANTES
    Route::get('/vacantes', fn () => Inertia::render('Segundas/Admin/Vacantes/index'))->name('segundas-vacantes-admin');
    Route::post('/get-vacantes-segundas-admin', [VacantesSegundaController::class, 'getVacantes']);
    Route::post('/save-numero-vacantes-segundas', [VacantesSegundaController::class, 'saveNumeroVacantes']);
    Route::post('/actualizar-vacantes-segundas', [VacantesSegundaController::class, 'actualizar']);
    Route::post('/delete-vacante-segundas', [VacantesSegundaController::class, 'eliminar']);

    //MODALIDADES
    Route::get('/get-modalidades-segundas-activas', [ModalidadSegundaController::class, 'getModalidadesActivas']);

    //OBSERVADOS
    Route::get('/observados', fn () => Inertia::render('Segundas/Admin/Observados/index'))->name('segundas-observados-admin');
    Route::post('/get-observados-segundas', [ObservadosSegundaController::class, 'getObservados']);
    Route::post('/save-observado-segundas', [ObservadosSegundaController::class, 'save']);


    //REPORTES
    Route::post('/get-resumen-preinscripcion-segundas', [ResumenesSegundaController::class, 'getResumenPreinscripcion']);
    Route::post('/get-detalle-preinscripcion-segundas', [ResumenesSegundaController::class, 'getPreinscripciones']);





});


//IDENTIDAD
Route::get('/get-condiciones-lengua-segundas', [IdentidadSegundaController::class, 'getCondicionesLengua']);
Route::get('/get-pertenencia-cultural-segundas', [IdentidadSegundaController::class, 'getPertenenciaCultural']);
Route::get('/get-lengua-segundas', [IdentidadSegundaController::class, 'getLenguaIndigena']);
Route::get('/get-pueblos-indigenes-segundas', [IdentidadSegundaController::class, 'getPueblosIndigenas']);
Route::get('/get-identidad-cultural/{id_postulante}/{id_proceso}', [IdentidadSegundaController::class, 'getIdentidadCulturalByPostulanteProceso']);



Route::post('/save-postulante-adicional', [PostulanteSegundaController::class, 'saveDataAdicional']);







