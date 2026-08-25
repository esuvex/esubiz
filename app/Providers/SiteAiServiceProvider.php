<?php

namespace App\Providers;

use App\Services\SiteAi\Capabilities\ThemeHomepageCapability;
use App\Services\SiteAi\Contracts\SiteAiProvider;
use App\Services\SiteAi\Providers\EsubizCentralSiteAiProvider;
use App\Services\SiteAi\SiteAiEngine;
use App\Services\SiteAi\SiteAiRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Register the central Site AI Engine once for every website
 * feature in Esubiz Core.
 */
class SiteAiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            SiteAiRegistry::class,
            function () {

                $registry =
                    new SiteAiRegistry();


                /*
                 * Generic Theme Homepage capability.
                 *
                 * Additional capabilities will register here:
                 *
                 * page-builder.section
                 * page-builder.widget
                 * ecommerce.product
                 * blog.post
                 * seo
                 * form
                 * etc.
                 */
                $registry->register(
                    new ThemeHomepageCapability()
                );


                return $registry;
            }
        );


        $this->app->singleton(
            SiteAiProvider::class,
            EsubizCentralSiteAiProvider::class
        );


        $this->app->singleton(
            SiteAiEngine::class,
            function ($app) {

                $engine =
                    new SiteAiEngine(
                        $app->make(
                            SiteAiRegistry::class
                        )
                    );


                return $engine
                    ->useProvider(
                        $app->make(
                            SiteAiProvider::class
                        )
                    );
            }
        );
    }
}
