<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use App\Services\Core\Installer\CoreInstalledProductRegistry;
use App\Services\Core\Modules\ModuleMarketplaceCatalogService;
use Illuminate\View\View;
use Spatie\Multitenancy\Models\Tenant;

/**
 * ESUBIZ_CORE_MODULE_HUB_CONTROLLER_V1
 *
 * Core-owned Module Hub.
 *
 * Mirrors the established Theme Hub architecture:
 *
 * - Installed Modules = local Core state.
 * - Module Marketplace = authoritative Esubiz catalog.
 * - SaaS and off-server Core use the same interface.
 *
 * Website Type / Wizard provisioning remains independent and may
 * automatically install modules assigned to a Website Type.
 */
class TenantModuleController extends Controller
{
    public function __construct(
        protected CoreInstalledProductRegistry $installedProducts,
        protected ModuleMarketplaceCatalogService $marketplaceCatalog
    ) {
    }

    protected function website(): Website
    {
        /** @var WebsiteTenant|null $tenant */
        $tenant = Tenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        return Website::query()
            ->where('id', $tenant->website_id)
            ->where('status', 'active')
            ->firstOrFail();
    }

    /**
     * Installed Modules belonging to this Core installation.
     */
    protected function installedModules(): array
    {
        return $this->installedProducts->all(
            'module',
            (int) $this->website()->id
        );
    }

    /**
     * Installed Modules Hub.
     */
    public function index(): View
    {
        $website = $this->website();

        $modules = collect(
            $this->installedModules()
        );

        return view(
            'tenant.admin.modules.index',
            compact(
                'website',
                'modules'
            )
        );
    }

    /**
     * Dedicated Module Marketplace page.
     *
     * Marketplace catalog data will be supplied through the universal
     * Module Marketplace catalog adapter, matching the Theme flow.
     */
    public function marketplace(): View
    {
        $website = $this->website();

        $modules = $this->marketplaceCatalog
            ->modules(
                null,
                (int) $website->id
            )
            ->values();

        $categories = $modules
            ->pluck('marketplace.category')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $featuredModules = $modules
            ->filter(
                fn (array $module) =>
                    (bool) data_get(
                        $module,
                        'marketplace.featured',
                        false
                    )
            )
            ->values();

        /*
         * Keep purchase ranking empty until authoritative Marketplace
         * purchase statistics are connected.
         */
        $mostPurchasedModules = collect();

        /*
         * Resolver order is authoritative catalog order for now.
         */
        $newestModules = $modules;

        return view(
            'tenant.admin.modules.marketplace',
            compact(
                'website',
                'modules',
                'categories',
                'featuredModules',
                'mostPurchasedModules',
                'newestModules'
            )
        );
    }


    /**
     * Enable an installed Module for this Core website.
     *
     * Business data remains untouched. Enabling only restores the
     * Module's runtime integrations.
     */
    public function enable(
        string $module
    ) {
        $website = $this->website();
        $websiteId = (int) $website->id;

        $installed = $this->installedProducts->find(
            'module',
            $module,
            null,
            $websiteId
        );

        abort_unless(
            $installed,
            404,
            'Installed module not found.'
        );

        $this->installedProducts->enable(
            'module',
            $module,
            $installed['product_version'] ?? null,
            $websiteId
        );

        return redirect()
            ->route(
                'tenant.cms.modules.index',
                [
                    'subdomain' =>
                        request()->route('subdomain'),
                ]
            )
            ->with(
                'success',
                'Module enabled successfully.'
            );
    }

    /**
     * Disable a Module without deleting its files or business data.
     *
     * Disabled Modules are ignored by CoreModuleLoader, so their
     * pages, menus, widgets and runtime integrations disappear while
     * remaining available for later re-enablement.
     */
    public function disable(
        string $module
    ) {
        $website = $this->website();
        $websiteId = (int) $website->id;

        $installed = $this->installedProducts->find(
            'module',
            $module,
            null,
            $websiteId
        );

        abort_unless(
            $installed,
            404,
            'Installed module not found.'
        );

        $this->installedProducts->disable(
            'module',
            $module,
            $installed['product_version'] ?? null,
            $websiteId
        );

        return redirect()
            ->route(
                'tenant.cms.modules.index',
                [
                    'subdomain' =>
                        request()->route('subdomain'),
                ]
            )
            ->with(
                'success',
                'Module disabled successfully.'
            );
    }

}
