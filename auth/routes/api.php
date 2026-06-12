<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\ApiAuthenticate;
use App\Http\Middleware\ValidateSystemToken;
use App\Http\Controllers\Api\v1\SystemController;
use App\Http\Controllers\Api\v1\RoleController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\RolePermissionController;
use App\Http\Controllers\Api\v1\ActionController;
use App\Http\Controllers\Api\v1\ModuleController;
use App\Http\Controllers\Api\v1\ViewController;
use App\Http\Controllers\Api\v1\SelectController;
use App\Http\Controllers\Api\v1\RouteController;


//Route::post('register', [AuthController::class, 'register']);

Route::post('login', [AuthController::class, 'login'])->middleware(ValidateSystemToken::class);
Route::post('logout', [AuthController::class, 'logout']);
Route::get('me', [AuthController::class, 'me']);


Route::get('ver-black-list', [AuthController::class, 'verBlacklist']);


Route::middleware([ApiAuthenticate::class ])->group(function () {   


    Route::post('refresh', [AuthController::class, 'refresh']);
});

//Route::group(['prefix' => 'v1', 'middleware' => [ApiAuthenticate::class]], function () {

Route::group(['prefix' => 'v1'], function () {
    //SYSTEMS
    Route::post('/systems', [SystemController::class, 'store']);
    Route::get('/systems', [SystemController::class, 'index']);
    Route::put('/systems/{id}', [SystemController::class, 'update']);
    Route::delete('/systems/{id}', [SystemController::class, 'destroy']);

    //ROLES
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    //USUARIOS
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    //ACCIONES
    Route::post('/actions', [ActionController::class, 'store']);
    Route::get('/actions', [ActionController::class, 'index']);
    Route::put('/actions/{id}', [ActionController::class, 'update']);
    Route::delete('/actions/{id}', [ActionController::class, 'destroy']);

    //MODULOS
    Route::post('/modulos', [ModuleController::class, 'store']);
    Route::get('/modulos', [ModuleController::class, 'index']);
    Route::put('/modulos/{id}', [ModuleController::class, 'update']);
    Route::delete('/modulos/{id}', [ModuleController::class, 'destroy']);

    //VISTAS
    Route::post('/views', [ViewController::class, 'store']);
    Route::get('/views', [ViewController::class, 'index']);
    Route::put('/views/{id}', [ViewController::class, 'update']);
    Route::delete('/views/{id}', [ViewController::class, 'destroy']);

    //SELECTS
    Route::get('/select-systems', [SelectController::class, 'getSelectSystems']);
    Route::get('/select-permission-by-view/{id_action}', [SelectController::class, 'getSelectPermissionsByView']);



    //PERMISOS
    Route::get('/permissions_role', [RolePermissionController::class, 'index']);
    Route::get('/permissions', [RolePermissionController::class, 'getPermisos']);
    Route::get('/permissions_role/{rol}', [RolePermissionController::class, 'PermisosRol']);
    Route::post('/permissions_role', [RolePermissionController::class, 'store']);
    Route::post('/permissions_role_delete', [RolePermissionController::class, 'destroyPermisoRol']);
    Route::post('/permissions_role_add', [RolePermissionController::class, 'savePermisoRol']);
    Route::post('/permissions_role_remove', [RolePermissionController::class, 'removePermisoRol']);
    
    Route::get('/tree-routes', [RolePermissionController::class, 'treeDataRoutes']);
        
    //RUTAS
    Route::get('/routes', [RouteController::class, 'index']);
    Route::get('/routes/{id}', [RouteController::class, 'show']);
    Route::post('/routes', [RouteController::class, 'store']);
    Route::put('/routes/{id}', [RouteController::class, 'update']);
    Route::delete('/routes/{id}', [RouteController::class, 'destroy']);

    //API TOKENS
    Route::get('/routes-services/{system_id}', [RouteController::class, 'rutasServicio']);
    Route::post('/routes-generate-token', [RouteController::class, 'generarApiToken']);
    

    //REDIS
    Route::get('/sync-routes-redis', [RouteController::class, 'actualizarRutasRedis']);
    Route::get('/show-routes-redis', [RouteController::class, 'verRutasRedis']);
    
    Route::get('/sync-routes-token', [RouteController::class, 'sincronizarRutasConTokens']);
    Route::get('/show-routes-token', [RouteController::class, 'verDatosSincronizados']);

    

});
