<?php

use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'auth_service/api'
    )
    ->withMiddleware(function ($middleware) {

    })
    ->withExceptions(function ($handler) {

    })
    ->create();
