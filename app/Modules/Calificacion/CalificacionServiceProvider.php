<?php

namespace App\Modules\Calificacion;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CalificacionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerViews();
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api/calificacion',
            'middleware' => ['api', 'auth:sanctum', 'calificador'],
            'namespace' => 'App\Modules\Calificacion\Controllers',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        });
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'calificacion');
    }
}
