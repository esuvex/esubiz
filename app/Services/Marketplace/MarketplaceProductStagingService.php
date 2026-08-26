<?php

namespace App\Services\Marketplace;

use App\Models\MarketplaceProductStaging;
use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class MarketplaceProductStagingService
{
    /**
     * Record a paid/fulfilled product as prepared for activation
     * against one authoritative Central Esubiz website record.
     *
     * This service does NOT decide how ZIP extraction happens.
     *
     * SaaS:
     *   Esubiz can later extract/install directly.
     *
     * Off-server:
     *   the external Core will later fetch the authorized package
     *   and perform extraction locally.
     *
     * Add-ons/bundles:
     *   may use staging_type=capability with no ZIP path.
     */
    public function stage(
        Website|int $website,
        string $productType,
        int $productId,
        string $deploymentType,
        string $stagingKey,
        array $context = []
    ): MarketplaceProductStaging {

        if (
            !in_array(
                $deploymentType,
                [
                    'saas',
                    'off_server',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Invalid Marketplace product deployment type.'
            );
        }


        $websiteId =
            $website instanceof Website
                ? $website->id
                : $website;


        return DB::transaction(
            function () use (
                $websiteId,
                $productType,
                $productId,
                $deploymentType,
                $stagingKey,
                $context
            ) {

                /*
                 * Fulfilment/payment callbacks may retry.
                 * One staging key must therefore be idempotent.
                 */
                $existing =
                    MarketplaceProductStaging::query()
                        ->where(
                            'staging_key',
                            $stagingKey
                        )
                        ->first();


                if ($existing) {
                    return $existing;
                }


                $website =
                    Website::query()
                        ->findOrFail(
                            $websiteId
                        );


                return MarketplaceProductStaging::query()
                    ->create([
                        'uuid' =>
                            (string)
                            Str::uuid(),

                        'staging_key' =>
                            $stagingKey,

                        'website_id' =>
                            $website->id,

                        'workspace_id' =>
                            $context['workspace_id']
                            ?? $website->workspace_id
                            ?? null,

                        'user_id' =>
                            $context['user_id']
                            ?? $website->owner_id
                            ?? $website->developer_id
                            ?? null,

                        'marketplace_order_id' =>
                            $context[
                                'marketplace_order_id'
                            ]
                            ?? null,

                        'marketplace_listing_id' =>
                            $context[
                                'marketplace_listing_id'
                            ]
                            ?? null,

                        'product_type' =>
                            $productType,

                        'product_id' =>
                            $productId,

                        'product_name' =>
                            $context[
                                'product_name'
                            ]
                            ?? null,

                        'product_version' =>
                            $context[
                                'product_version'
                            ]
                            ?? null,

                        'deployment_type' =>
                            $deploymentType,

                        'staging_type' =>
                            $context[
                                'staging_type'
                            ]
                            ?? 'package',

                        'status' =>
                            $context[
                                'status'
                            ]
                            ?? 'staged',

                        /*
                         * Internal protected reference only.
                         */
                        'package_path' =>
                            $context[
                                'package_path'
                            ]
                            ?? null,

                        'checksum_sha256' =>
                            $context[
                                'checksum_sha256'
                            ]
                            ?? null,

                        'activation_area' =>
                            $context[
                                'activation_area'
                            ]
                            ?? $this->defaultActivationArea(
                                $productType
                            ),

                        'staged_at' =>
                            now(),

                        'metadata' =>
                            $context[
                                'metadata'
                            ]
                            ?? [],
                    ]);
            },
            5
        );
    }


    public function markActivated(
        MarketplaceProductStaging|int $staging
    ): MarketplaceProductStaging {

        $record =
            $staging instanceof MarketplaceProductStaging
                ? $staging
                : MarketplaceProductStaging::query()
                    ->findOrFail(
                        $staging
                    );


        $record->update([
            'status' =>
                'activated',

            'activated_at' =>
                now(),

            'error_message' =>
                null,
        ]);


        return $record->fresh();
    }


    public function markFailed(
        MarketplaceProductStaging|int $staging,
        string $message
    ): MarketplaceProductStaging {

        $record =
            $staging instanceof MarketplaceProductStaging
                ? $staging
                : MarketplaceProductStaging::query()
                    ->findOrFail(
                        $staging
                    );


        $record->update([
            'status' =>
                'failed',

            'error_message' =>
                $message,
        ]);


        return $record->fresh();
    }


    protected function defaultActivationArea(
        string $productType
    ): string {

        return match (
            $productType
        ) {
            'addon',
            'core_addon',
            'bundle',
            'core_bundle'
                => 'addons',

            'theme'
                => 'themes',

            'module'
                => 'modules',

            default
                => 'marketplace',
        };
    }
}
