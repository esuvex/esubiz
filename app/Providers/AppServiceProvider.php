<?php

namespace App\Providers;

use App\Services\Marketplace\MarketplaceFulfilmentManager;
use App\Services\Core\CoreAddonMarketplaceFulfilmentService;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MarketplaceFulfilmentManager::class, function ($app) {
            $manager = new MarketplaceFulfilmentManager();

            $manager->register(
                'core_addon',
                $app->make(CoreAddonMarketplaceFulfilmentService::class)
            );

            $manager->register(
                'core_bundle',
                $app->make(CoreAddonMarketplaceFulfilmentService::class)
            );

            return $manager;
        });
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
