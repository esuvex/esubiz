<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CoreAddonMarketplaceFulfilmentService
{
    public function fulfil(
        int $userId,
        int $productId,
        string $productType,
        string $deploymentType,
        ?int $websiteId = null,
        ?int $workspaceId = null,
        ?int $orderId = null,
        ?string $orderReference = null,
        ?string $startsAt = null,
        ?string $expiresAt = null
    ): array {
        if (!in_array($deploymentType, ['saas', 'off_server'], true)) {
            throw new RuntimeException(
                'Invalid Core add-on deployment type.'
            );
        }

        return DB::transaction(function () use (
            $userId,
            $productId,
            $productType,
            $deploymentType,
            $websiteId,
            $workspaceId,
            $orderId,
            $orderReference,
            $startsAt,
            $expiresAt
        ) {
            $items = $this->resolveProduct(
                $productId,
                $productType
            );

            if (!$items) {
                throw new RuntimeException(
                    'Marketplace product is not a registered Core add-on or bundle.'
                );
            }

            $created = [];

            foreach ($items as $item) {

                $addon = $item['addon'];

                /*
                 * Direct add-on purchases must respect the add-on's own
                 * deployment availability.
                 *
                 * Bundle purchases are different: the bundle is the
                 * commercial product being purchased. If the bundle itself
                 * is available for the requested deployment, its included
                 * add-ons are fulfilled as part of that bundle even when an
                 * individual add-on is not independently available for that
                 * deployment.
                 */
                if (
                    $productType === 'core_addon'
                    && !$this->availableForDeployment(
                        $addon,
                        $deploymentType
                    )
                ) {
                    throw new RuntimeException(
                        "Core add-on [{$addon->key}] is not available for {$deploymentType}."
                    );
                }

                $commercialModel =
                    $deploymentType === 'saas'
                        ? ($addon->saas_billing_interval
                            ? 'subscription'
                            : 'rental')
                        : 'license';

                $metadata = [
                    'core_addon_id' => $addon->id,
                    'core_addon_key' => $addon->key,
                    'core_addon_name' => $addon->name,
                    'deployment_type' => $deploymentType,
                    'commercial_model' => $commercialModel,
                    'source_product_type' => $productType,
                    'source_product_id' => $productId,
                    'bundle_id' => $item['bundle_id'],
                    'allocation' => $item['allocation'],
                    'is_unlimited' => $item['is_unlimited'],
                    'entitlement_version' => 1,
                ];

                $existing = DB::table('product_entitlements')
                    ->where('user_id', $userId)
                    ->where('product_type', 'core_addon')
                    ->where('product_id', $addon->id)
                    ->where('status', 'active')
                    ->when(
                        $websiteId !== null,
                        fn ($q) => $q->where('website_id', $websiteId)
                    )
                    ->when(
                        $workspaceId !== null,
                        fn ($q) => $q->where('workspace_id', $workspaceId)
                    )
                    ->first();

                if ($existing) {
                    $created[] = $existing->id;
                    continue;
                }

                $entitlementId = DB::table('product_entitlements')
                    ->insertGetId([
                        'user_id' => $userId,
                        'website_id' => $websiteId,
                        'workspace_id' => $workspaceId,
                        'product_type' => 'core_addon',
                        'product_id' => $addon->id,
                        'product_name' => $addon->name,
                        'order_id' => $orderId,
                        'order_reference' => $orderReference,
                        'status' => 'active',
                        'fulfilment_type' =>
                            $deploymentType === 'off_server'
                                ? 'license'
                                : $commercialModel,
                        'starts_at' => $startsAt ?: now(),
                        'expires_at' => $expiresAt,
                        'metadata' => json_encode($metadata),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                $created[] = $entitlementId;

                /*
                 * Workspace allocation is created only when a concrete
                 * workspace exists. The Core add-on ID is deliberately
                 * preserved in metadata rather than pretending that the
                 * marketplace product ID is an addon_type_id.
                 *
                 * A future/additional addon-type resolver can populate
                 * workspace_addons without changing this entitlement.
                 */
                /*
                 * Provision the purchased add-on allocation whenever a
                 * concrete workspace is available. SaaS and off-server
                 * entitlements both retain the same Core capability data.
                 */
                if ($workspaceId !== null) {
                    $this->provisionWorkspaceAllocation(
                        $workspaceId,
                        $addon,
                        $item
                    );
                }

                /*
                 * A SaaS website may not have a workspace record yet.
                 * Preserve the entitlement against the website so the
                 * runtime can resolve its purchased Core capabilities
                 * directly from product_entitlements.
                 */
                if ($websiteId !== null) {
                    DB::table('product_entitlements')
                        ->where('id', $entitlementId)
                        ->update([
                            'metadata' => json_encode(array_merge(
                                $metadata,
                                [
                                    'capabilities' => json_decode(
                                        $addon->capabilities ?? '[]',
                                        true
                                    ) ?: [],
                                ]
                            )),
                            'updated_at' => now(),
                        ]);
                }
            }

            return [
                'product_type' => $productType,
                'product_id' => $productId,
                'deployment_type' => $deploymentType,
                'entitlements' => $created,
                'count' => count($created),
            ];
        });
    }

    public function isCoreProduct(
        int $productId,
        string $productType
    ): bool {
        return !empty(
            $this->resolveProduct($productId, $productType)
        );
    }

    protected function resolveProduct(
        int $productId,
        string $productType
    ): array {
        if ($productType === 'addon') {
            $addon = DB::table('core_addons')
                ->where('id', $productId)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            return $addon
                ? [[
                    'addon' => $addon,
                    'bundle_id' => null,
                    'allocation' => $addon->default_allocation,
                    'is_unlimited' => (bool) $addon->is_unlimited,
                ]]
                : [];
        }

        if ($productType === 'bundle') {
            $rows = DB::table('core_addon_bundle_items as items')
                ->join(
                    'core_addons as addons',
                    'addons.id',
                    '=',
                    'items.addon_id'
                )
                ->where('items.bundle_id', $productId)
                ->where('addons.is_active', true)
                ->whereNull('addons.deleted_at')
                ->select(
                    'addons.*',
                    'items.bundle_id',
                    'items.allocation',
                    'items.is_unlimited'
                )
                ->get();

            return $rows->map(fn ($row) => [
                'addon' => $row,
                'bundle_id' => $row->bundle_id,
                'allocation' => $row->allocation,
                'is_unlimited' => (bool) $row->is_unlimited,
            ])->all();
        }

        return [];
    }

    protected function availableForDeployment(
        object $addon,
        string $deploymentType
    ): bool {
        return $deploymentType === 'saas'
            ? (bool) $addon->saas_available
            : (bool) $addon->off_server_available;
    }

    protected function provisionWorkspaceAllocation(
        int $workspaceId,
        object $addon,
        array $item
    ): void {
        /*
         * Existing workspace_addons uses addon_type_id.
         * We only provision when an existing addon-type mapping can be
         * resolved safely. We never assume marketplace product IDs are
         * addon_type IDs.
         */
        $addonTypeId = DB::table('addon_types')
            ->where('key', $addon->key)
            ->value('id');

        if (!$addonTypeId) {
            return;
        }

        $allocation = $item['allocation'];

        $existing = DB::table('workspace_addons')
            ->where('workspace_id', $workspaceId)
            ->where('addon_type_id', $addonTypeId)
            ->first();

        if ($existing) {
            DB::table('workspace_addons')
                ->where('id', $existing->id)
                ->update([
                    'allocated' => DB::raw(
                        'allocated + ' . (int) ($allocation ?? 0)
                    ),
                    'is_unlimited' =>
                        $item['is_unlimited'] || $existing->is_unlimited,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('workspace_addons')->insert([
            'workspace_id' => $workspaceId,
            'addon_type_id' => $addonTypeId,
            'allocated' => (int) ($allocation ?? 0),
            'used' => 0,
            'reserved' => 0,
            'is_unlimited' => $item['is_unlimited'],
            'expires_at' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
