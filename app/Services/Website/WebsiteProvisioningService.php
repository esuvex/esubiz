<?php

namespace App\Services\Website;

use App\Models\Website;
use App\Services\Website\Recipes\RecipeService;

class WebsiteProvisioningService
{
    protected RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
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
        | Future Provisioning Pipeline
        |--------------------------------------------------------------------------
        |
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
