<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoreAddonEntitlementService
{
    /**
     * Determine whether an add-on is available to a deployment type.
     *
     * Supported deployment types:
     * - saas
     * - off_server
     */
    public function availableForDeployment(
        int $productEntitlementId,
        string $deploymentType
    ): bool {
        $entitlement = DB::table('product_entitlements')
            ->where('id', $productEntitlementId)
            ->where('status', 'active')
            ->first();

        if (!$entitlement) {
            return false;
        }

        $metadata = $this->metadata($entitlement);

        if (
            isset($metadata['deployment_types']) &&
            is_array($metadata['deployment_types']) &&
            !in_array($deploymentType, $metadata['deployment_types'], true)
        ) {
            return false;
        }

        if ($deploymentType === 'saas') {
            return $this->saasEntitlementActive($entitlement);
        }

        if ($deploymentType === 'off_server') {
            return $this->offServerLicenseActive($entitlement);
        }

        throw new RuntimeException(
            "Unsupported Core add-on deployment type [{$deploymentType}]."
        );
    }

    /**
     * SaaS add-ons are rented/subscribed for a period.
     */
    public function saasEntitlementActive(object $entitlement): bool
    {
        if (
            $entitlement->starts_at &&
            now()->lt($entitlement->starts_at)
        ) {
            return false;
        }

        if (
            $entitlement->expires_at &&
            now()->gte($entitlement->expires_at)
        ) {
            return false;
        }

        return in_array(
            $entitlement->fulfilment_type,
            ['subscription', 'rental'],
            true
        );
    }

    /**
     * Off-server add-ons are one-off licensed purchases.
     */
    public function offServerLicenseActive(object $entitlement): bool
    {
        if ($entitlement->fulfilment_type !== 'license') {
            return false;
        }

        if (
            $entitlement->starts_at &&
            now()->lt($entitlement->starts_at)
        ) {
            return false;
        }

        if (
            $entitlement->expires_at &&
            now()->gte($entitlement->expires_at)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check whether the purchased add-on is currently active.
     */
    public function active(int $productEntitlementId): bool
    {
        $entitlement = DB::table('product_entitlements')
            ->where('id', $productEntitlementId)
            ->where('status', 'active')
            ->first();

        if (!$entitlement) {
            return false;
        }

        if (
            $entitlement->starts_at &&
            now()->lt($entitlement->starts_at)
        ) {
            return false;
        }

        if (
            $entitlement->expires_at &&
            now()->gte($entitlement->expires_at)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check an allocated workspace add-on.
     */
    public function workspaceAddonActive(int $workspaceAddonId): bool
    {
        $addon = DB::table('workspace_addons')
            ->where('id', $workspaceAddonId)
            ->where('is_active', true)
            ->first();

        if (!$addon) {
            return false;
        }

        if (
            $addon->expires_at &&
            now()->gte($addon->expires_at)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Available allocation for a workspace add-on.
     */
    public function availableAllocation(int $workspaceAddonId): ?int
    {
        $addon = DB::table('workspace_addons')
            ->where('id', $workspaceAddonId)
            ->where('is_active', true)
            ->first();

        if (!$addon) {
            return null;
        }

        if ($addon->is_unlimited) {
            return null;
        }

        return max(
            0,
            (int) $addon->allocated
            - (int) $addon->used
            - (int) $addon->reserved
        );
    }

    /**
     * Determine whether requested allocation can be consumed.
     */
    public function canConsume(
        int $workspaceAddonId,
        int $quantity = 1
    ): bool {
        if ($quantity < 1) {
            throw new RuntimeException(
                'Addon consumption quantity must be greater than zero.'
            );
        }

        if (!$this->workspaceAddonActive($workspaceAddonId)) {
            return false;
        }

        $available = $this->availableAllocation($workspaceAddonId);

        if ($available === null) {
            return true;
        }

        return $available >= $quantity;
    }

    /**
     * Consume allocated add-on capacity.
     */
    public function consume(
        int $workspaceAddonId,
        int $quantity = 1
    ): void {
        if (!$this->canConsume($workspaceAddonId, $quantity)) {
            throw new RuntimeException(
                'Core add-on entitlement limit exceeded.'
            );
        }

        $addon = DB::table('workspace_addons')
            ->where('id', $workspaceAddonId)
            ->first();

        if ($addon->is_unlimited) {
            return;
        }

        DB::table('workspace_addons')
            ->where('id', $workspaceAddonId)
            ->update([
                'used' => DB::raw(
                    'used + ' . (int) $quantity
                ),
                'updated_at' => now(),
            ]);
    }

    /**
     * Return the effective allocation for a Core add-on capability.
     *
     * Core default + all active purchased add-on allocations.
     * Unlimited on any active allocation makes the capability unlimited.
     */
    public function effectiveCapabilityAllocation(
        int $websiteId,
        string $capabilityKey,
        ?int $coreDefault = null
    ): ?int {
        $base = $coreDefault;

        $addonIds = DB::table('product_entitlements')
            ->where('website_id', $websiteId)
            ->where('product_type', 'core_addon')
            ->where('status', 'active')
            ->pluck('product_id')
            ->unique()
            ->values();

        if ($addonIds->isEmpty()) {
            return $base;
        }

        $rows = DB::table('core_addon_capability_allocations')
            ->whereIn('addon_id', $addonIds)
            ->where('capability_key', $capabilityKey)
            ->get(['allocation', 'is_unlimited']);

        if ($rows->contains(
            fn ($row) => (bool) $row->is_unlimited
        )) {
            return null;
        }

        $additional = (int) $rows->sum(
            fn ($row) => (int) ($row->allocation ?? 0)
        );

        return $base === null
            ? $additional
            : $base + $additional;
    }

    /**
     * Determine whether a website has an active Core add-on capability.
     *
     * Returns true when the capability is supplied by at least one
     * active Core add-on entitlement for the website.
     */
    public function capabilityEnabledForWebsite(
        int $websiteId,
        string $capabilityKey
    ): bool {
        $entitlements = DB::table('product_entitlements')
            ->where('website_id', $websiteId)
            ->where('product_type', 'core_addon')
            ->where('status', 'active')
            ->get(['product_id', 'metadata']);

        foreach ($entitlements as $entitlement) {
            $metadata = $this->metadata($entitlement);

            $capabilities = $metadata['capabilities'] ?? [];

            if (
                is_array($capabilities) &&
                in_array($capabilityKey, $capabilities, true)
            ) {
                return true;
            }

            $allocation = DB::table('core_addon_capability_allocations')
                ->where('addon_id', $entitlement->product_id)
                ->where('capability_key', $capabilityKey)
                ->first();

            if ($allocation) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the active add-on allocation for a website capability.
     *
     * null means unlimited when an active entitlement explicitly grants
     * unlimited access. Zero means the capability is not allocated.
     */
    public function capabilityAllocationForWebsite(
        int $websiteId,
        string $capabilityKey
    ): ?int {
        $entitlements = DB::table('product_entitlements')
            ->where('website_id', $websiteId)
            ->where('product_type', 'core_addon')
            ->where('status', 'active')
            ->get(['product_id']);

        $total = 0;
        $found = false;

        foreach ($entitlements as $entitlement) {
            $allocation = DB::table('core_addon_capability_allocations')
                ->where('addon_id', $entitlement->product_id)
                ->where('capability_key', $capabilityKey)
                ->first();

            if (!$allocation) {
                continue;
            }

            $found = true;

            if ((bool) $allocation->is_unlimited) {
                return null;
            }

            $total += (int) ($allocation->allocation ?? 0);
        }

        return $found ? $total : 0;
    }

    /**
     * Read entitlement metadata safely.
     */
    protected function metadata(object $entitlement): array
    {
        if (!$entitlement->metadata) {
            return [];
        }

        $metadata = json_decode(
            $entitlement->metadata,
            true
        );

        return is_array($metadata) ? $metadata : [];
    }
}
