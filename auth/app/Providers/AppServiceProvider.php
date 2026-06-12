<?php

namespace App\Providers;
//use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use League\OAuth2\Server\Grant\PasswordGrant;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckPermission;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use DateInterval;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        Route::aliasMiddleware('check.permission', CheckPermission::class);

        $this->registerPolicies();
        Passport::personalAccessTokensExpireIn(now()->addDays(15));
        Passport::tokensExpireIn(now()->addMinutes(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));

        app(AuthorizationServer::class)->enableGrantType(
            new PasswordGrant(
                app(UserRepository::class),
                app(RefreshTokenRepository::class)
            ),
            new DateInterval('PT1H')
        );

        Scramble::configure()
        ->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });

    }

}
