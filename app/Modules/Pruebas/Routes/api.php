<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pruebas Module API Routes
|--------------------------------------------------------------------------
| Prefijo: /api/pruebas
| Middleware: auth:sanctum, calificador
|
*/

// CRUD Pruebas
Route::get('/', 'PruebaController@index');
Route::post('/', 'PruebaController@store');
Route::get('/{idPrueba}', 'PruebaController@show');
Route::put('/{idPrueba}', 'PruebaController@update');
Route::delete('/{idPrueba}', 'PruebaController@destroy');

// Archivos (IDE / RES / Tipos)
Route::post('/{idPrueba}/archivos', 'ArchivoController@cargar');
Route::get('/{idPrueba}/archivos', 'ArchivoController@index');
Route::get('/archivos/{idArchivo}/registros', 'ArchivoController@registros');
Route::post('/actualizar-ide', 'ArchivoController@actualizarIde');
Route::delete('/archivos/{idArchivo}', 'ArchivoController@destroy');

// Tipos de una prueba (con sus respuestas clave)
Route::get('/{idPrueba}/tipos', 'ArchivoController@tipos');

// Excepciones por tipo
Route::get('/{idPrueba}/tipos/{idTipo}/excepciones', 'ExcepcionPruebaController@index');
Route::post('/{idPrueba}/tipos/{idTipo}/excepciones', 'ExcepcionPruebaController@store');
Route::put('/excepciones/{id}', 'ExcepcionPruebaController@update');
Route::delete('/excepciones/{id}', 'ExcepcionPruebaController@destroy');

// Calificación de prueba
Route::post('/{idPrueba}/calificar', 'CalificacionPruebaController@calificar');
Route::get('/{idPrueba}/resultados', 'CalificacionPruebaController@resultados');
Route::get('/{idPrueba}/excel', 'CalificacionPruebaController@excel');

// Fichas PDF
Route::get('/{idPrueba}/ficha-pdf', 'FichaPruebaController@individual');
Route::post('/{idPrueba}/ficha-pdf-masivo', 'FichaPruebaController@masivo');
Route::get('/{idPrueba}/ranking-pdf', 'FichaPruebaController@ranking');
