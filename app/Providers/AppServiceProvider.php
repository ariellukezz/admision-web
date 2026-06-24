<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use App\Observers\GlobalObserver;
use App\Observers\AuditObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        foreach (config('global_observers.models') as $model) {
            $model::observe(GlobalObserver::class);
        }

        // Audit observers — se registran solo si la auditoría está activada
        if (config('audit.enabled', false)) {
            foreach (config('audit.watched_models', []) as $model) {
                $model::observe(AuditObserver::class);
            }
        }
    }
}
