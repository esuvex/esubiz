<?php

namespace App\Services\Website\Recipes;

class RecipeService
{
    /**
     * Get the provisioning recipe for a website type.
     */
    public function get(string $websiteType): array
    {
        return config("website_recipes.{$websiteType}", []);
    }
}
