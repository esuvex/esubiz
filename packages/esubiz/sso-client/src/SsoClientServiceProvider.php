<?php

namespace Esubiz\SsoClient;

use Illuminate\Support\ServiceProvider;

class SsoClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/esubiz-sso.php',
            'esubiz-sso'
        );

        $this->app->singleton(SsoClient::class, function ($app) {
            return new SsoClient(
                config('esubiz-sso')
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/esubiz-sso.php' =>
                config_path('esubiz-sso.php'),
        ], 'esubiz-sso-config');

        if (config('esubiz-sso.routes_enabled', false)) {
            $this->loadRoutesFrom(
                __DIR__ . '/../routes/web.php'
            );
        }
    }
}