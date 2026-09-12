<?php

namespace App\Providers;


use App\Services\Platform\CentralAuthUiService;
use App\Services\Platform\CentralRegistrationAccessService;
use App\Services\Platform\CentralRoleAccessService;
use Illuminate\Support\Facades\View;

use App\Services\Platform\CentralVisitorCurrencyService;
use Illuminate\Support\Facades\Blade;

use App\Services\SiteAi\Proposals\AiProposalApplierRegistry;
use App\Services\SiteAi\Proposals\AiProposalDestinationRegistry;
use App\Services\SiteAi\Proposals\Appliers\ThemeHomepageProposalApplier;
use App\Services\SiteAi\Proposals\Destinations\SaasTenantProposalDestination;
use App\Services\Marketplace\MarketplaceFulfilmentManager;
use App\Services\Core\CoreAddonMarketplaceFulfilmentService;
use App\Services\Core\CoreAddonSalesTriggerRegistry;

use Illuminate\Support\ServiceProvider;
use App\Listeners\PersistCentralRegistrationCountry;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

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
         * ESUBIZ_CENTRAL_SETTINGS_PROPAGATION_V1
         *
         * Make Central Main Settings available consistently to all
         * Central Esubiz views. Tenant/Core views remain authoritative
         * to their own website-local site_settings and are excluded.
         */
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $viewName = (string) $view->getName();

            if (
                str_starts_with($viewName, 'tenant.')
                || str_starts_with($viewName, 'components.core.')
            ) {
                return;
            }

            try {
                $service = app(
                    \App\Services\Platform\CentralSiteSettingsService::class
                );

                $allCentralSettings = $service->all();

                $view->with(
                    'centralSiteSettings',
                    $allCentralSettings
                );

                /*
                 * ESUBIZ_CENTRAL_SYSTEM_SETTINGS_V3
                 *
                 * Expose the authoritative Central system/platform
                 * configuration separately from website-local settings.
                 * This activates saved System Settings for Central
                 * application surfaces without overwriting tenant Core
                 * site_settings.
                 */
                $view->with(
                    'centralSystemSettings',
                    collect($allCentralSettings)
                        ->filter(
                            static fn ($value, $key) =>
                                str_starts_with(
                                    (string) $key,
                                    'system.'
                                )
                                || str_starts_with(
                                    (string) $key,
                                    'platform.'
                                )
                        )
                        ->all()
                );

                $view->with(
                    'centralSiteName',
                    $service->siteName()
                );

                $view->with(
                    'centralTimezone',
                    $service->timezone()
                );

                /*
                 * ESUBIZ_CENTRAL_CURRENCY_DISPLAY_V4
                 *
                 * Expose the authoritative Central pricing/currency
                 * service to Central Esubiz views. Tenant/Core websites
                 * continue using their own local currency configuration.
                 */
                $currencyPricing = app(
                    \App\Services\Platform\EsubizCurrencyPricingService::class
                );

                $view->with(
                    'centralCurrencyPricing',
                    $currencyPricing
                );

                $view->with(
                    'centralCurrencyCode',
                    (string) (
                        $service->get('platform.default_currency')
                        ?: $service->get('currency.default')
                        ?: 'NGN'
                    )
                );

                /*
                 * ESUBIZ_CENTRAL_TIMEZONE_PROPAGATION_V2
                 *
                 * Backend timestamps remain UTC. Central views receive
                 * the configured display timezone plus safe conversion
                 * helpers for rendering user-facing dates consistently.
                 */
                $timezone = app(
                    \App\Services\Platform\CentralTimezoneService::class
                );

                $view->with(
                    'centralTimezoneService',
                    $timezone
                );

                $view->with(
                    'centralNow',
                    now('UTC')->setTimezone(
                        $timezone->timezone()
                    )
                );

                $view->with(
                    'centralToLocalTime',
                    static function ($value) use ($timezone) {
                        if ($value === null || $value === '') {
                            return null;
                        }

                        try {
                            return $timezone->fromUtc($value);
                        } catch (\Throwable $exception) {
                            return $value;
                        }
                    }
                );
            } catch (\Throwable $exception) {
                /*
                 * Keep CLI, migrations and early installation surfaces
                 * operational before site_settings is available.
                 */
            }
        });

        /*
         * ESUBIZ_CORE_FEATURE_BOOTSTRAP_V1
         *
         * Register only Core features actually present in this build.
         * Other products/features register themselves independently.
         */
        $this->app->make(
            \App\Services\Core\CoreFeatureBootstrapper::class
        )->boot();


        /*
         * ESUBIZ_CORE_BLADE_PERMISSION_DIRECTIVES_V1
         *
         * Universal Core authorization helpers for:
         * - dashboard widgets
         * - sidebar/menu items
         * - buttons/actions
         * - Core features
         * - add-ons
         * - bundles
         * - themes
         * - modules
         * - products
         * - site functions
         *
         * No SaaS/off-server branching.
         */

        Blade::if('coreCan', function (string $permission) {
            return app(
                \App\Services\Core\CorePermissionService::class
            )->can($permission);
        });

        Blade::if('coreCannot', function (string $permission) {
            return app(
                \App\Services\Core\CorePermissionService::class
            )->cannot($permission);
        });

        Blade::if('coreCanAny', function (...$permissions) {
            if (
                count($permissions) === 1 &&
                is_array($permissions[0])
            ) {
                $permissions = $permissions[0];
            }

            return app(
                \App\Services\Core\CorePermissionService::class
            )->canAny($permissions);
        });

        Blade::if('coreCanAll', function (...$permissions) {
            if (
                count($permissions) === 1 &&
                is_array($permissions[0])
            ) {
                $permissions = $permissions[0];
            }

            return app(
                \App\Services\Core\CorePermissionService::class
            )->canAll($permissions);
        });

        Blade::if('coreRole', function (string $role) {
            return app(
                \App\Services\Core\CorePermissionService::class
            )->hasRole($role);
        });

        Blade::if('coreAdministrator', function () {
            return app(
                \App\Services\Core\CorePermissionService::class
            )->isAdministrator();
        });

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


        /*
         * ESUBIZ_REGISTER_COUNTRY_EVENT_V7
         *
         * Persist the registration country selected by the
         * visitor after the account has been created.
         */
        Event::listen(
            Registered::class,
            PersistCentralRegistrationCountry::class
        );


        /*
         * ESUBIZ_CENTRAL_AUTH_PROFILE_VIEW_CONTEXT_V9
         *
         * Central auth/profile configuration authority.
         *
         * Central auth uses Central settings.
         * Tenant/Core auth remains independently configurable.
         */
        View::composer('*', function ($view) {
            try {
                $viewName = method_exists(
                    $view,
                    'getName'
                )
                    ? (string) $view->getName()
                    : '';

                /*
                 * Never inject Central auth configuration into
                 * tenant/Core presentation views.
                 */
                if (
                    str_starts_with(
                        $viewName,
                        'tenant.'
                    )
                    || str_starts_with(
                        $viewName,
                        'components.core.'
                    )
                ) {
                    return;
                }

                $authUi = app(
                    CentralAuthUiService::class
                );

                $registration = app(
                    CentralRegistrationAccessService::class
                );

                $roleAccess = app(
                    CentralRoleAccessService::class
                );

                $authUser = auth()->user();

                $view->with(
                    'centralAuthUi',
                    $authUi->all()
                );

                $view->with(
                    'centralAuthEnabledProviders',
                    $authUi->enabledProviders()
                );

                $view->with(
                    'centralPublicRegistrationRoles',
                    $registration->enabledPublicRoles()
                );

                $view->with(
                    'centralDefaultRegistrationRole',
                    $registration->defaultRole()
                );

                $view->with(
                    'centralRegistrationAccess',
                    $registration
                );

                $view->with(
                    'centralRoleAccess',
                    $roleAccess
                );

                $view->with(
                    'centralAccountFamily',
                    $authUser
                        ? $roleAccess->family(
                            $authUser
                        )
                        : null
                );

                $view->with(
                    'centralVisibleContexts',
                    $authUser
                        ? $roleAccess->visibleContexts(
                            $authUser
                        )
                        : []
                );

                $approvalStatus = $authUser
                    ? (
                        $authUser->approval_status
                        ?? null
                    )
                    : null;

                $view->with(
                    'centralAccountApprovalStatus',
                    $approvalStatus
                );

                $view->with(
                    'centralAccountCanUseFunctions',
                    $authUser
                        ? $registration->canUseFunctions(
                            $approvalStatus
                        )
                        : false
                );
            } catch (\Throwable $exception) {
                /*
                 * Presentation configuration must never make
                 * public/auth pages unavailable.
                 */
            }
        });

/*
         * ESUBIZ_GLOBAL_VISITOR_CURRENCY_CONTEXT_V4
         *
         * Global Esubiz commercial currency context.
         *
         * Available to Central, Core and tenant views without
         * replacing any tenant website's own local currency.
         *
         * Esubiz-controlled product surfaces should use:
         *
         * $esubizVisitorCountry
         * $esubizVisitorCurrency
         * $esubizVisitorCurrencyContext
         * $esubizProductMoney($baseAmount)
         */
        View::composer('*', function ($view) {
            try {
                $request = request();

                $visitorCurrency =
                    app(
                        CentralVisitorCurrencyService::class
                    );

                $context =
                    $visitorCurrency->resolve(
                        $request
                    );

                $view->with(
                    'esubizVisitorCurrencyContext',
                    $context
                );

                $view->with(
                    'esubizVisitorCountry',
                    $context['country']
                        ?? null
                );

                $view->with(
                    'esubizVisitorCurrency',
                    $context['currency']
                        ?? null
                );

                $view->with(
                    'esubizVisitorIsDefaultCountry',
                    (bool) (
                        $context[
                            'is_default_country'
                        ]
                        ?? false
                    )
                );

                $view->with(
                    'esubizProductMoney',
                    static function (
                        $baseAmount
                    ) use (
                        $visitorCurrency,
                        $request
                    ) {
                        return $visitorCurrency
                            ->productMoney(
                                $request,
                                $baseAmount
                            );
                    }
                );
            } catch (\Throwable $exception) {
                /*
                 * Never break Central/Core rendering because
                 * geolocation, session or FX context is
                 * temporarily unavailable.
                 */
            }
        });

    }
}
