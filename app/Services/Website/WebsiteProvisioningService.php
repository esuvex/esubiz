<?php

namespace App\Services\Website;

use App\Models\Website;
use RuntimeException;
use App\Services\Website\Recipes\RecipeService;

class WebsiteProvisioningService
{
    protected RecipeService $recipeService;

    protected WebsiteDatabaseProvisioningService $databaseProvisioningService;

    protected TenantCoreInstallationService $tenantCoreInstallationService;

    protected WebsiteDatabaseCleanupService $databaseCleanupService;

    public function __construct(
        RecipeService $recipeService,
        WebsiteDatabaseProvisioningService $databaseProvisioningService,
        TenantCoreInstallationService $tenantCoreInstallationService,
        WebsiteDatabaseCleanupService $databaseCleanupService
    ) {
        $this->recipeService = $recipeService;
        $this->databaseProvisioningService = $databaseProvisioningService;
        $this->tenantCoreInstallationService = $tenantCoreInstallationService;
        $this->databaseCleanupService = $databaseCleanupService;
    }

    /**
     * Provision a newly created website.
     */
    public function provision(Website $website): void
    {
        /*
        |--------------------------------------------------------------------------
        | Website Recipe
        |--------------------------------------------------------------------------
        */

        $recipe = $this->recipeService->get(
            $website->type ?? 'business'
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | Dedicated Tenant Database
            |--------------------------------------------------------------------------
            */

            $this->databaseProvisioningService->provision($website);

            /*
            |--------------------------------------------------------------------------
            | Install Esubiz Core
            |--------------------------------------------------------------------------
            */

            $this->tenantCoreInstallationService->install($website);

            /*
            |--------------------------------------------------------------------------
            | Future Provisioning Pipeline
            |--------------------------------------------------------------------------
            |
            | ✓ Prepared website type
            | ✓ Dedicated tenant database
            | ✓ Install selected theme
            | ✓ Generate default pages
            | ✓ Install default modules
            | ✓ Configure CRM
            | ✓ Configure HR
            | ✓ Configure Finance
            | ✓ Configure AI
            | ✓ Configure Wallet
            | ✓ Configure Email
            | ✓ Configure Storage
            | ✓ Configure Payment Gateway
            | ✓ Queue deployment jobs
            |
            */

            $website->update([
                'status' => 'active',
            ]);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Provisioning Failure Cleanup
            |--------------------------------------------------------------------------
            */

            try {
                $this->databaseCleanupService->cleanup($website);
            } catch (\Throwable $cleanupException) {
                report($cleanupException);
            }

            $website->update([
                'status' => 'provisioning',
            ]);

            throw $e;
        }
    }
}
