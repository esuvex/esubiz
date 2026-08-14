<?php

namespace App\Services\Website;

use App\Models\Website;
use App\Services\Website\Recipes\RecipeService;

class WebsiteProvisioningService
{
    protected RecipeService $recipeService;

    protected WebsiteDatabaseProvisioningService $databaseProvisioningService;

    public function __construct(
        RecipeService $recipeService,
        WebsiteDatabaseProvisioningService $databaseProvisioningService
    ) {
        $this->recipeService = $recipeService;
        $this->databaseProvisioningService = $databaseProvisioningService;
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

        /*
        |--------------------------------------------------------------------------
        | Dedicated Tenant Database
        |--------------------------------------------------------------------------
        */

        $this->databaseProvisioningService->provision($website);

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
    }
}
