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

            $coreAddonFulfilment = $app->make(
                CoreAddonMarketplaceFulfilmentService::class
            );

            $handler = new class($coreAddonFulfilment) {
                public function __construct(
                    protected CoreAddonMarketplaceFulfilmentService $service
                ) {}

                public function fulfil(object $order, object $listing): array
                {
                    $productType = match ($listing->product_type) {
                        'core_addon' => 'addon',
                        'core_bundle' => 'bundle',
                        default => $listing->product_type,
                    };

                    return $this->service->fulfil(
                        (int) $order->buyer_id,
                        (int) $listing->product_id,
                        $productType,
                        $order->deployment_type ?? 'off_server',
                        $order->website_id ?? null,
                        $order->workspace_id ?? null,
                        $order->id ?? null,
                        $order->reference ?? null
                    );
                }
            };

            $manager->register('core_addon', $handler);
            $manager->register('core_bundle', $handler);

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
