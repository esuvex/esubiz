<?php

namespace App\Services\Marketplace\Developer;

use App\Services\Marketplace\Settings\MarketplaceProductRegistry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class DeveloperProductTypePolicyService
{

    public function policy(string $productType): object
    {
        $productType = $this->normalizeType($productType);

        $policy = DB::table('developer_product_type_policies')
            ->where('product_type', $productType)
            ->first();

        /*
         * Secure default:
         * no Central policy means Developer Build/Publish is unavailable.
         */
        return $policy ?? (object) [
            'product_type' => $productType,
            'admin_build_enabled' => false,
            'developer_build_enabled' => false,
            'developer_publish_enabled' => false,
            'developer_resell_enabled' => false,
            'saas_max_price' => null,
            'off_server_max_price' => null,
        ];
    }

    public function canAdminBuild(string $productType): bool
    {
        return (bool) $this->policy($productType)
            ->admin_build_enabled;
    }

    public function canDeveloperResell(string $productType): bool
    {
        return (bool) $this->policy($productType)
            ->developer_resell_enabled;
    }

    public function canBuild(string $productType): bool
    {
        return (bool) $this->policy($productType)
            ->developer_build_enabled;
    }

    public function canPublish(string $productType): bool
    {
        return (bool) $this->policy($productType)
            ->developer_publish_enabled;
    }

    public function assertCanBuild(string $productType): void
    {
        if (!$this->canBuild($productType)) {
            throw new RuntimeException(
                "Developer building of [{$productType}] products is disabled by Esubiz Central."
            );
        }
    }

    public function assertCanPublish(string $productType): void
    {
        if (!$this->canPublish($productType)) {
            throw new RuntimeException(
                "Developer publishing of [{$productType}] products is disabled by Esubiz Central."
            );
        }
    }

    public function maximumPrice(
        string $productType,
        string $deployment
    ): ?float {
        $policy = $this->policy($productType);

        $column = match ($deployment) {
            'saas' => 'saas_max_price',
            'off_server' => 'off_server_max_price',
            default => throw new InvalidArgumentException(
                "Unsupported deployment [{$deployment}]."
            ),
        };

        $value = $policy->{$column} ?? null;

        return $value === null
            ? null
            : (float) $value;
    }

    public function assertPriceAllowed(
        string $productType,
        string $deployment,
        float $price
    ): void {
        if ($price < 0) {
            throw new InvalidArgumentException(
                'Developer product price cannot be negative.'
            );
        }

        $maximum = $this->maximumPrice(
            $productType,
            $deployment
        );

        /*
         * NULL means Central has not imposed a maximum benchmark.
         */
        if ($maximum === null) {
            return;
        }

        if ($price > $maximum) {
            throw new RuntimeException(
                "Developer price exceeds the Esubiz Central maximum "
                . "for [{$productType}] [{$deployment}]."
            );
        }
    }

    public function isInternalMarketplacePurchasable(
        string $productType
    ): bool {
        return $this->normalizeType($productType)
            !== 'website_type';
    }

    public function isWizardPurchasable(
        string $productType
    ): bool {
        return $this->normalizeType($productType)
            === 'website_type';
    }

    protected function normalizeType(
        string $productType
    ): string {
        $productType = strtolower(
            trim($productType)
        );

        if ($productType === '') {
            throw new InvalidArgumentException(
                'Esubiz product type cannot be empty.'
            );
        }

        /*
         * Marketplace product families are configuration-first.
         *
         * A product may be registered in Central before its Admin page,
         * builder, sales flow or other implementation exists.
         * Missing implementation must never create a 500 here.
         */
        $registeredTypes = app(
            MarketplaceProductRegistry::class
        )->types();

        if (!in_array($productType, $registeredTypes, true)) {
            throw new InvalidArgumentException(
                "Unsupported Esubiz product type [{$productType}]."
            );
        }

        return $productType;
    }

}
