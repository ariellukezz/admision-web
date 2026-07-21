<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Calificacion Module API Routes
|--------------------------------------------------------------------------
|
| Todas las rutas del módulo de Calificación.
| Prefijo: /api/calificacion
| Middleware: auth:sanctum, calificador (aplicados en CalificacionServiceProvider)
|
*/

// Tablas (introspección de tablas legacy)
Route::get('tables', 'TableController@index');
Route::get('tables/most-related', 'TableController@mostRelated');
Route::get('tables/procesos', 'TableController@procesos');
Route::get('tables/modalidades', 'TableController@modalidades');
Route::get('tables/areas', 'TableController@areas');
Route::get('tables/programas', 'TableController@programas');
Route::get('tables/{table}', 'TableController@show');
Route::get('tables/{table}/columns', 'TableController@columns');

// Selecciones de columnas
Route::post('column-selections', 'ColumnSelectionController@store');
Route::get('column-selections/last', 'ColumnSelectionController@last');

// Filtros y códigos
Route::get('filters/stats-by-modalities', 'FilterController@statsByModalities');
Route::post('filters/generate-and-save', 'FilterController@generateAndSave');
Route::get('filters/preview/{id}', 'FilterController@preview');
Route::delete('filters/{id}', 'FilterController@destroy');
Route::get('filters/available-postulantes', 'FilterController@availablePostulantes');
Route::post('filters/postulantes-for-codes', 'FilterController@postulantesForCodes');
Route::get('filters/processing-stats', 'FilterController@processingStats');
Route::post('filters', 'FilterController@store');
Route::get('filter-groups', 'FilterController@groups');

// Aulas - Distribución
Route::post('classrooms/generate', 'ClassroomController@generate');
Route::get('classrooms/{filterGroupId}', 'ClassroomController@show');
Route::delete('classrooms/{id}', 'ClassroomController@destroy');

// Aulas - Intercambios manuales
Route::post('classrooms/{filterGroupId}/swaps', 'SwapController@store');
Route::get('classrooms/{filterGroupId}/audit-log', 'SwapController@auditLog');

// Aulas - Detección de conflictos
Route::get('classrooms/{filterGroupId}/detect-conflicts', 'ConflictController@detect');

// Aulas - PDFs
Route::get('classrooms/{id}/export-pdf', 'DistributionPdfController@classroomPdf');
Route::get('classrooms/{id}/export-conflicts-pdf', 'DistributionPdfController@conflictsPdf');
Route::get('classrooms/{id}/export-parameters-pdf', 'DistributionPdfController@parametersPdf');
Route::get('classrooms/{id}/export-audit-log-pdf', 'DistributionPdfController@auditLogPdf');
Route::get('classrooms/{id}/export-test-types-pdf', 'DistributionPdfController@testTypesPdf');
Route::get('classrooms/{id}/export-padron-pdf', 'DistributionPdfController@padronPdf');
Route::get('classrooms/{id}/export-lista-pdf', 'DistributionPdfController@listaPdf');

// Asignaturas
Route::get('asignaturas', 'AsignaturaController@index');
Route::post('asignaturas', 'AsignaturaController@store');
Route::put('asignaturas/{id}', 'AsignaturaController@update');
Route::delete('asignaturas/{id}', 'AsignaturaController@destroy');

// Ponderaciones
Route::post('ponderaciones', 'PonderacionController@store');
Route::get('ponderaciones', 'PonderacionController@index');
Route::post('ponderaciones/detalle', 'PonderacionController@storeDetalle');
Route::get('ponderaciones/detalle/{id}', 'PonderacionController@showDetalle');
Route::delete('ponderaciones/{id}', 'PonderacionController@destroy');
Route::post('ponderaciones/{id}/duplicar', 'PonderacionController@duplicar');
Route::get('ponderaciones/select', 'PonderacionController@select');
Route::get('ponderaciones/areas', 'PonderacionController@areas');
Route::get('ponderaciones/pdf/{id}', 'PonderacionController@pdf');

// Multiplicadores
Route::get('multiplicadores', 'MultiplicadorController@index');
Route::post('multiplicadores', 'MultiplicadorController@store');
Route::put('multiplicadores/{id}', 'MultiplicadorController@update');
Route::delete('multiplicadores/{id}', 'MultiplicadorController@destroy');

// Exámenes
Route::get('examenes', 'ExamenController@index');
Route::post('examenes', 'ExamenController@store');
Route::put('examenes/{id}', 'ExamenController@update');
Route::delete('examenes/{id}', 'ExamenController@destroy');

// Exámenes - Tipos
Route::get('examenes/{idExamen}/tipos', 'ExamenController@tipos');
Route::post('examenes-tipos', 'ExamenController@storeTipo');
Route::put('examenes-tipos/{id}', 'ExamenController@updateTipo');
Route::delete('examenes-tipos/{id}', 'ExamenController@destroyTipo');
Route::post('examenes-tipos-archivo', 'ExamenController@uploadTiposArchivo');
Route::get('examenes-archivo/{idArchivo}', 'ExamenController@archivo');

// Exámenes - Respuestas (RES)
Route::get('examenes-res/{idTipo}', 'ExamenController@res');
Route::post('examenes-res/{idTipo}', 'ExamenController@storeRes');
Route::delete('examenes-res/{idTipo}', 'ExamenController@destroyRes');

// Exámenes - Excepciones
Route::get('examenes-excepciones/{idTipo}', 'ExamenController@excepciones');
Route::post('examenes-excepciones', 'ExamenController@storeExcepcion');
Route::delete('examenes-excepciones/{id}', 'ExamenController@destroyExcepcion');

// Excepciones
Route::get('excepciones', 'ExcepcionController@index');
Route::get('excepciones/proceso/{idProceso}', 'ExcepcionController@byProceso');
Route::get('excepciones/pdf', 'ExcepcionController@pdf');
Route::post('excepciones', 'ExcepcionController@store');
Route::put('excepciones/{id}', 'ExcepcionController@update');
Route::delete('excepciones/{id}', 'ExcepcionController@destroy');
Route::get('excepciones/search', 'ExcepcionController@search');

// Lecturas (carga de archivos IDE/RES/PAT)
Route::post('lecturas/carga-ide', 'LecturaController@cargaIde');
Route::post('lecturas/carga-res', 'LecturaController@cargaRes');
Route::post('lecturas/carga-pat', 'LecturaController@cargaPat');
Route::post('lecturas/actualizar-ide', 'LecturaController@actualizarIde');
Route::get('lecturas/get-select-puestos/{idProceso}', 'LecturaController@getSelectPuestos');
Route::get('lecturas/descargar-excel', 'LecturaController@descargarExcel');

// Archivos de lectura (archivo_lectura)
Route::get('lecturas/archivos', 'LecturaController@archivos');
Route::get('lecturas/archivos/{idArchivo}/ides', 'LecturaController@ides');
Route::get('lecturas/archivos/{idArchivo}/respuestas', 'LecturaController@respuestas');
Route::delete('lecturas/archivos/{id}', 'LecturaController@destroyArchivo');
Route::get('lecturas/pdf-errores', 'LecturaController@pdfErrores');
Route::get('lecturas/ficha-pdf', 'LecturaController@fichaPdf');
Route::post('lecturas/ficha-pdf-masivo', 'LecturaController@fichaPdfMasivo');

// Calificación
Route::post('calificar', 'CalificacionController@calificar');
Route::get('postulantes', 'CalificacionController@postulantes');
Route::get('postulantes/excel', 'CalificacionController@descargarExcelPostulantes');
Route::get('resultados', 'CalificacionController@resultados');
Route::post('asignar', 'CalificacionController@asignar');
Route::get('resultados/pdf-errores', 'CalificacionController@pdfErrores');
Route::get('resultados/pdf', 'CalificacionController@resultadosPdf');
Route::get('vacantes/pdf', 'CalificacionController@pdfVacantes');
Route::get('ingresantes/txt', 'CalificacionController@generarIngresantesTxt');
Route::get('clasificados/txt', 'CalificacionController@generarClasificadosTxt');
Route::get('ranking/pdf', 'CalificacionController@generarRankingPdf');
Route::get('ficha-pdf', 'CalificacionController@generarFichaPdf');
Route::post('ficha-pdf-masivo', 'CalificacionController@generarFichaPdfMasivo');

// Configuración de Calificaciones (CRUD)
Route::get('calificaciones', 'CalificacionConfigController@index');
Route::post('calificaciones', 'CalificacionConfigController@store');
Route::get('calificaciones/{id}', 'CalificacionConfigController@show');
Route::put('calificaciones/{id}', 'CalificacionConfigController@update');
Route::delete('calificaciones/{id}', 'CalificacionConfigController@destroy');

// Participantes
Route::get('participantes', 'ParticipanteController@index');
Route::post('participantes', 'ParticipanteController@store');
Route::put('participantes/{id}', 'ParticipanteController@update');
Route::delete('participantes/{id}', 'ParticipanteController@destroy');

// Pabellones y Aulas
Route::get('pabellones', 'PabellonController@index');
Route::post('pabellones', 'PabellonController@store');
Route::get('pabellones/{id}', 'PabellonController@show');
Route::put('pabellones/{id}', 'PabellonController@update');
Route::delete('pabellones/{id}', 'PabellonController@destroy');
Route::get('pabellones/{id}/aulas', 'PabellonController@aulas');
Route::get('aulas-gestion', 'AulaGestionController@index');
Route::post('aulas-gestion', 'AulaGestionController@store');
Route::put('aulas-gestion/{id}', 'AulaGestionController@update');
Route::delete('aulas-gestion/{id}', 'AulaGestionController@destroy');

// Ubicación de Aulas (tabla temporal para asignación física)
Route::get('ubicacion-aula', 'UbicacionAulaController@index');
Route::post('ubicacion-aula', 'UbicacionAulaController@store');
Route::put('ubicacion-aula/{id}', 'UbicacionAulaController@update');
Route::delete('ubicacion-aula/{id}', 'UbicacionAulaController@destroy');
Route::post('ubicacion-aula/import', 'UbicacionAulaController@import');
Route::delete('ubicacion-aula/all', 'UbicacionAulaController@destroyAll');
Route::get('ubicacion-aula/areas', 'UbicacionAulaController@areas');
