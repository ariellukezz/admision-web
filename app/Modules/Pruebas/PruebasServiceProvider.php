<?php

namespace App\Modules\Pruebas;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class PruebasServiceProvider extends ServiceProvider
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
            'prefix' => 'api/pruebas',
            'middleware' => ['api', 'auth:sanctum', 'calificador'],
            'namespace' => 'App\Modules\Pruebas\Controllers',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        });
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Modules\Pruebas\Console\Commands\ImportarPuntajesCommand::class,
            ]);
        }
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'pruebas');
    }
}
