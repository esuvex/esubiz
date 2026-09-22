<?php

namespace App\Services\Marketplace\Modules;

use App\Services\Marketplace\MarketplaceProductResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ESUBIZ_MODULE_MARKETPLACE_RESOLVER_V1
 *
 * Resolves authoritative Module Marketplace products from the
 * canonical catalog_products registry.
 *
 * Commercial truth remains Central-owned.
 *
 * Website Type compatibility remains connected through the existing
 * Website Type deployment profile/component architecture.
 */
class ModuleMarketplaceResolver
{
    public const DEPLOYMENT_SAAS = 'saas';
    public const DEPLOYMENT_OFF_SERVER = 'off_server';

    public function __construct(
        protected MarketplaceProductResolver $products
    ) {
    }

    /**
     * Return active Module products available to a deployment.
     */
    public function forDeployment(
        string $deployment
    ): Collection {
        return $this->deploymentQuery($deployment)
            ->get()
            ->filter(
                fn (object $module) =>
                    $this->products->available(
                        $module,
                        $deployment
                    )
            )
            ->values();
    }

    /**
     * Canonical Module Marketplace query.
     */
    public function deploymentQuery(
        string $deployment
    ) {
        if (
            !in_array(
                $deployment,
                [
                    self::DEPLOYMENT_SAAS,
                    self::DEPLOYMENT_OFF_SERVER,
                ],
                true
            )
        ) {
            throw new \InvalidArgumentException(
                'Unsupported Module Marketplace deployment: '
                . $deployment
            );
        }

        return DB::table('catalog_products')
            ->where('product_type', 'module')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderByDesc('id');
    }

    /**
     * Resolve deployment-specific commercial price.
     */
    public function price(
        object $module,
        string $deployment
    ): ?float {
        return $this->products->price(
            $module,
            $deployment
        );
    }

    /**
     * Resolve deployment-specific currency.
     */
    public function currency(
        object $module,
        string $deployment
    ): string {
        return $this->products->currency(
            $module,
            $deployment
        );
    }

    /**
     * Resolve deployment-specific billing duration.
     */
    public function billing(
        object $module,
        string $deployment
    ): ?array {
        return $this->products->billing(
            $module,
            $deployment
        );
    }

    /**
     * Resolve canonical Admin-managed Module Marketplace categories.
     */
    public function categories(
        object $module
    ): array {
        return $this->products->categories(
            $module,
            'module'
        );
    }

}