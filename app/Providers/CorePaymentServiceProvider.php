<?php

namespace App\Providers;

use App\Services\Payment\CorePaymentGatewayManager;
use Illuminate\Support\ServiceProvider;

class CorePaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            CorePaymentGatewayManager::class,
            function () {
                return new CorePaymentGatewayManager();
            }
        );
    }

    public function boot(): void
    {
        //
    }
}
