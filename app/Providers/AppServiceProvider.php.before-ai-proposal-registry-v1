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
        /*
         * ESUBIZ_GENERIC_SITE_AI_CAPABILITY
         *
         * One generic Site AI capability handles dynamic website
         * areas such as features, testimonials, hero sections,
         * services and future module/theme supplied AI functions.
         *
         * Specialised capabilities can still override their own
         * keys later.
         */
        $this->app->afterResolving(
            \App\Services\SiteAi\SiteAiRegistry::class,
            function (
                \App\Services\SiteAi\SiteAiRegistry $registry
            ): void {
                if (!$registry->has('site')) {
                    $registry->register(
                        new \App\Services\SiteAi\Capabilities\GenericSiteCapability(
                            'site'
                        )
                    );
                }
            }
        );

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
