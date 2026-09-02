<?php

namespace App\Providers;

use App\Services\SiteAi\Proposals\AiProposalApplierRegistry;
use App\Services\SiteAi\Proposals\AiProposalDestinationRegistry;
use App\Services\SiteAi\Proposals\Appliers\ThemeHomepageProposalApplier;
use App\Services\SiteAi\Proposals\Destinations\SaasTenantProposalDestination;
use App\Services\Marketplace\MarketplaceFulfilmentManager;
use App\Services\Core\CoreAddonMarketplaceFulfilmentService;
use App\Services\Core\CoreAddonSalesTriggerRegistry;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ESUBIZ_REGISTRATION_PLUGIN_REGISTRIES_V1
        //
        // One registry per request/application lifecycle.
        // Modules can resolve these registries and register their
        // registration offers/extensions without Core knowing them.
        $this->app->singleton(
            \App\Services\Auth\RegistrationOfferRegistry::class,
            fn () => new \App\Services\Auth\RegistrationOfferRegistry()
        );

        $this->app->singleton(
            \App\Services\Auth\RegistrationExtensionRegistry::class,
            fn () => new \App\Services\Auth\RegistrationExtensionRegistry()
        );

        /*
         * ESUBIZ_ADDON_SALES_TRIGGER_REGISTRY_SINGLETON_V1
         *
         * Shared plug-and-play Add-on sales-trigger location registry.
         *
         * This provider does NOT define feature locations.
         *
         * Core features, Website Types and Modules submit the
         * locations they own when they boot.
         *
         * Central Admin later reads this same registry and decides:
         * - which Add-on uses a submitted location;
         * - trigger condition;
         * - SaaS/off-server visibility;
         * - threshold where applicable;
         * - recommendation message / CTA.
         */
        $this->app->singleton(
            CoreAddonSalesTriggerRegistry::class,
            fn ($app) => new CoreAddonSalesTriggerRegistry()
        );


        /*
         * ESUBIZ_GLOBAL_AI_PROPOSAL_REGISTRY_V1
         *
         * Central proposal coordination only.
         *
         * Website-owned content remains in the tenant or
         * authenticated off-server installation storage.
         */
        $this->app->singleton(
            AiProposalDestinationRegistry::class,
            function ($app) {
                $registry =
                    new AiProposalDestinationRegistry();

                $registry->register(
                    $app->make(
                        SaasTenantProposalDestination::class
                    )
                );

                return $registry;
            }
        );

        $this->app->singleton(
            AiProposalApplierRegistry::class,
            function ($app) {
                $registry =
                    new AiProposalApplierRegistry();

                $registry->register(
                    $app->make(
                        ThemeHomepageProposalApplier::class
                    )
                );

                return $registry;
            }
        );

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
        /*
         * ESUBIZ_CORE_SALES_TRIGGER_LOCATION_BOOT_V1
         *
         * Core capabilities submit only the sales-trigger locations
         * they explicitly own.
         *
         * The central Add-on system consumes those submitted locations;
         * it does not define or guess feature locations itself.
         *
         * Future modules/providers can submit their own locations to the
         * same singleton registry independently.
         */
        $this->app->make(
            \App\Services\Core\CoreCapabilityRegistry::class
        )->submitSalesTriggerLocations(
            $this->app->make(
                CoreAddonSalesTriggerRegistry::class
            )
        );
    }
}
