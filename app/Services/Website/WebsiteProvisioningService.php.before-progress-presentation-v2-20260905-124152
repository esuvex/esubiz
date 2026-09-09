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
             * ESUBIZ_REAL_DEPLOYMENT_PROGRESS_V2
             */
            $website->update([
                'status' => 'provisioning',
                'deployment_progress' => 10,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Dedicated Tenant Database
            |--------------------------------------------------------------------------
            */

            $this->databaseProvisioningService->provision($website);

            $website->update([
                'deployment_progress' => 45,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Install Esubiz Core
            |--------------------------------------------------------------------------
            */

            $website->update([
                'deployment_progress' => 60,
            ]);

            $this->tenantCoreInstallationService->install($website);

            $website->update([
                'deployment_progress' => 90,
            ]);

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
                'deployment_progress' => 98,
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
                'status' => 'failed',
                'deployment_progress' => 0,
            ]);

            throw $e;
        }
    }
}
