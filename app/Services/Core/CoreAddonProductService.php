<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoreAddonProductService
{
    /**
     * Build the canonical metadata stored with an add-on or bundle purchase.
     *
     * The product itself remains owned by Esubiz Marketplace/Central Admin.
     * This metadata tells Core how the entitlement may be used.
     */
    public function entitlementMetadata(
        string $deploymentType,
        string $commercialModel,
        array $capabilities = [],
        ?int $bundleId = null
    ): array {
        if (!in_array($deploymentType, ['saas', 'off_server'], true)) {
            throw new RuntimeException(
                'Invalid Core add-on deployment type.'
            );
        }

        if (!in_array($commercialModel, ['rental', 'subscription', 'license'], true)) {
            throw new RuntimeException(
                'Invalid Core add-on commercial model.'
            );
        }

        if (
            $deploymentType === 'saas' &&
            !in_array($commercialModel, ['rental', 'subscription'], true)
        ) {
            throw new RuntimeException(
                'SaaS add-ons must use rental or subscription entitlement.'
            );
        }

        if (
            $deploymentType === 'off_server' &&
            $commercialModel !== 'license'
        ) {
            throw new RuntimeException(
                'Off-server add-ons must use one-off license entitlement.'
            );
        }

        return [
            'deployment_types' => [$deploymentType],
            'commercial_model' => $commercialModel,
            'capabilities' => array_values(array_unique($capabilities)),
            'bundle_id' => $bundleId,
            'entitlement_version' => 1,
        ];
    }

    /**
     * Find active add-on purchases for a website/workspace.
     */
    public function activeEntitlements(
        int $userId,
        ?int $websiteId = null,
        ?int $workspaceId = null
    ) {
        return DB::table('product_entitlements')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->when(
                $websiteId !== null,
                fn ($q) => $q->where('website_id', $websiteId)
            )
            ->when(
                $workspaceId !== null,
                fn ($q) => $q->where('workspace_id', $workspaceId)
            )
            ->orderByDesc('id')
            ->get();
    }
}
