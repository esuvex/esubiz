<?php

namespace App\Services\Marketplace\Themes;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ThemeMarketplaceResolver
{
    /*
     * ESUBIZ_THEME_MARKETPLACE_RESOLVER_V1
     *
     * Universal Theme Marketplace eligibility resolver.
     *
     * This service is deliberately Theme-agnostic.
     *
     * It is the common source for:
     *
     * - Central Esubiz Theme Marketplace
     * - SaaS/Core Theme Marketplace
     * - off-server/Core Theme Marketplace
     * - User Website Wizard
     * - Developer Website Wizard
     *
     * Website Type assignment and wizard visibility are additional
     * filters layered on top of the base Marketplace eligibility.
     */

    public const DEPLOYMENT_SAAS = 'saas';

    public const DEPLOYMENT_OFF_SERVER = 'off_server';


    /**
     * Base public Marketplace eligibility.
     *
     * A Theme cannot be publicly purchased unless all three master
     * commerce conditions are satisfied.
     */
    public function eligibleQuery(): Builder
    {
        return DB::table('theme_packages')
            ->whereNull('theme_packages.deleted_at')
            ->where('theme_packages.is_active', true)
            ->where('theme_packages.marketplace_enabled', true)
            ->where('theme_packages.marketplace_ready', true);
    }


    /**
     * Public themes available for a particular deployment context.
     */
    public function forDeployment(
        string $deployment
    ): Collection {
        return $this->deploymentQuery(
            $deployment
        )
            ->orderByDesc(
                'theme_packages.marketplace_featured'
            )
            ->orderBy(
                'theme_packages.name'
            )
            ->get();
    }


    /**
     * Query for a particular deployment context.
     */
    public function deploymentQuery(
        string $deployment
    ): Builder {
        $query =
            $this->eligibleQuery();

        if (
            $deployment
            === self::DEPLOYMENT_SAAS
        ) {
            return $query
                ->where(
                    'theme_packages.saas_available',
                    true
                );
        }

        if (
            $deployment
            === self::DEPLOYMENT_OFF_SERVER
        ) {
            return $query
                ->where(
                    'theme_packages.off_server_available',
                    true
                );
        }

        throw new InvalidArgumentException(
            'Unsupported Theme deployment context: '
            . $deployment
        );
    }


    /**
     * Determine whether a specific Theme can currently be purchased
     * for the requested deployment.
     *
     * Checkout / fulfilment can call this again even if the Theme
     * previously appeared in a listing. This prevents stale/direct
     * checkout links from bypassing Admin controls.
     */
    public function canPurchase(
        int $themePackageId,
        string $deployment
    ): bool {
        return $this->deploymentQuery(
            $deployment
        )
            ->where(
                'theme_packages.id',
                $themePackageId
            )
            ->exists();
    }


    /**
     * Themes eligible for the User Website Wizard.
     *
     * Wizard visibility requires BOTH:
     *
     * 1. Theme is eligible for SaaS deployment.
     * 2. Admin enabled User Wizard visibility.
     * 3. Theme is assigned to the selected Website Type.
     */
    public function forUserWizard(
        int $websiteTypeId
    ): Collection {
        return $this->wizardQuery(
            $websiteTypeId,
            'show_in_user_wizard'
        )
            ->orderByDesc(
                'theme_packages.marketplace_featured'
            )
            ->orderBy(
                'theme_packages.name'
            )
            ->get();
    }


    /**
     * Themes eligible for the Developer Website Wizard.
     *
     * Developer visibility remains independent from User visibility.
     */
    public function forDeveloperWizard(
        int $websiteTypeId
    ): Collection {
        return $this->wizardQuery(
            $websiteTypeId,
            'show_in_developer_wizard'
        )
            ->orderByDesc(
                'theme_packages.marketplace_featured'
            )
            ->orderBy(
                'theme_packages.name'
            )
            ->get();
    }


    /**
     * Shared wizard query.
     *
     * Add-ons are intentionally NOT involved here.
     *
     * Website Type assignment applies to Themes (and separately
     * Modules), never Add-ons.
     */
    protected function wizardQuery(
        int $websiteTypeId,
        string $visibilityColumn
    ): Builder {
        if (
            !in_array(
                $visibilityColumn,
                [
                    'show_in_user_wizard',
                    'show_in_developer_wizard',
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported Theme wizard visibility column.'
            );
        }


        return $this->deploymentQuery(
            self::DEPLOYMENT_SAAS
        )
            ->join(
                'theme_package_website_type',
                'theme_package_website_type.theme_package_id',
                '=',
                'theme_packages.id'
            )
            ->where(
                'theme_package_website_type.website_type_id',
                $websiteTypeId
            )
            ->where(
                'theme_packages.'
                . $visibilityColumn,
                true
            )
            ->select(
                'theme_packages.*'
            )
            ->distinct();
    }
}
