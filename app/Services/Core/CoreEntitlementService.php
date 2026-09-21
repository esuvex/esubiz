<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoreEntitlementService
{
    /**
     * Determine whether a Core feature is active.
     */
    public function featureEnabled(string $featureKey): bool
    {
        $feature = DB::table('core_features')
            ->where('key', $featureKey)
            ->where('is_core', true)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        return $feature !== null;
    }

    /**
     * Return a configured limit for a Core feature.
     */
    public function limit(string $featureKey, string $limitKey): ?object
    {
        return DB::table('core_features as f')
            ->join('core_feature_limits as l', 'l.core_feature_id', '=', 'f.id')
            ->where('f.key', $featureKey)
            ->where('f.is_core', true)
            ->where('f.is_active', true)
            ->where('l.limit_key', $limitKey)
            ->where('l.is_active', true)
            ->whereNull('f.deleted_at')
            ->whereNull('l.deleted_at')
            ->select(
                'l.id',
                'l.limit_key',
                'l.name',
                'l.value_type',
                'l.default_value',
                'l.unit',
                'l.is_unlimited',
                'l.metadata'
            )
            ->first();
    }

    /**
     * Get the effective numeric limit.
     */
    public function value(
        string $featureKey,
        string $limitKey,
        ?int $websiteId = null
    ): ?int
    {
        $limit = $this->limit($featureKey, $limitKey);

        if (!$limit) {
            return null;
        }

        if ($limit->is_unlimited || $limit->value_type === 'unlimited') {
            return null;
        }

        $base = $limit->default_value === null
            ? null
            : (int) $limit->default_value;

        if ($websiteId === null) {
            return $base;
        }

        return app(CoreAddonEntitlementService::class)
            ->effectiveCapabilityAllocation(
                $websiteId,
                $limitKey,
                $base
            );
    }

    /**
     * Check a quantity against a configured Core limit.
     *
     * null means unlimited / no numeric restriction.
     */
    public function allows(string $featureKey, string $limitKey, int $currentUsage): bool
    {
        if (!$this->featureEnabled($featureKey)) {
            return false;
        }

        $limit = $this->limit($featureKey, $limitKey);

        if (!$limit) {
            return true;
        }

        if ($limit->is_unlimited || $limit->value_type === 'unlimited') {
            return true;
        }

        if ($limit->default_value === null) {
            return true;
        }

        return $currentUsage < $this->value(
            $featureKey,
            $limitKey,
            request()->integer('website_id') ?: null
        );
    }

    /**
     * Enforce a CRM limit using the current record count.
     */
    public function enforceCrmLimit(string $limitKey, int $currentUsage): void
    {
        $this->enforce('crm', $limitKey, $currentUsage);
    }

    /**
     * Check a CRM limit without throwing.
     */
    public function allowsCrm(string $limitKey, int $currentUsage): bool
    {
        return $this->allows('crm', $limitKey, $currentUsage);
    }

    /**
     * Enforce a Core feature and throw a consistent exception when blocked.
     */
    public function enforce(
        string $featureKey,
        string $limitKey,
        int $currentUsage
    ): void {
        if (!$this->featureEnabled($featureKey)) {
            throw new RuntimeException(
                "Core feature [{$featureKey}] is currently unavailable."
            );
        }

        if (!$this->allows($featureKey, $limitKey, $currentUsage)) {
            $limit = $this->limit($featureKey, $limitKey);

            throw new RuntimeException(
                "The {$limit->name} limit has been reached. "
                . "Configured limit: {$limit->default_value} {$limit->unit}."
            );
        }
    }

    /*
     * ESUBIZ_UNIVERSAL_ENTITLEMENT_ENFORCEMENT_V1
     *
     * Central plug-and-play entitlement enforcement for both SaaS and
     * off-server websites.
     *
     * Add-ons declare capabilities/allocations. Feature implementations
     * ask this service whether the website may use the capability.
     * No Add-on-specific enforcement code is required.
     */

    /**
     * Resolve a website ID safely from an explicit value or the current
     * tenant route/request.
     */
    public function resolveWebsiteId(?int $websiteId = null): ?int
    {
        if ($websiteId !== null && $websiteId > 0) {
            return $websiteId;
        }

        if (function_exists('request') && app()->bound('request')) {
            $request = request();

            $routeWebsite = $request->route('website');

            if (is_object($routeWebsite) && isset($routeWebsite->id)) {
                return (int) $routeWebsite->id;
            }

            if (is_numeric($routeWebsite) && (int) $routeWebsite > 0) {
                return (int) $routeWebsite;
            }

            $requestWebsiteId = $request->integer('website_id');

            if ($requestWebsiteId > 0) {
                return $requestWebsiteId;
            }
        }

        return null;
    }

    /**
     * Determine whether a website owns a capability supplied by an
     * active purchased Add-on.
     */
    public function addonCapabilityEnabled(
        string $capabilityKey,
        ?int $websiteId = null
    ): bool {
        $websiteId = $this->resolveWebsiteId($websiteId);

        if ($websiteId === null) {
            return false;
        }

        return app(CoreAddonEntitlementService::class)
            ->capabilityEnabledForWebsite(
                $websiteId,
                $capabilityKey
            );
    }

    /**
     * Return the effective allocation for any registered capability.
     *
     * null = unlimited
     * 0    = no allocation
     * >0   = effective finite allocation
     */
    /**
     * ESUBIZ_ACTIVE_ADDON_CAPABILITY_IDENTITY_BRIDGE_V1
     *
     * Universal active purchased Add-on identity for any capability.
     */
    public function activeAddonForCapability(
        string $capabilityKey,
        ?int $websiteId = null
    ): ?object {
        $websiteId = $this->resolveWebsiteId($websiteId);

        if ($websiteId === null) {
            return null;
        }

        return app(CoreAddonEntitlementService::class)
            ->activeAddonForCapability(
                $websiteId,
                $capabilityKey
            );
    }

    /**
     * Resolve current usage through the universal Core usage registry.
     *
     * Explicit providers remain authoritative for special meters such as
     * Storage/Bandwidth. Ordinary resources can be configured centrally
     * through core_feature_limits.metadata without capability-specific PHP.
     */
    public function currentUsage(
        string $capabilityKey,
        ?int $websiteId = null
    ): int|float {
        $websiteId = $this->resolveWebsiteId($websiteId);

        if ($websiteId === null) {
            throw new RuntimeException(
                "A website is required to resolve usage for [{$capabilityKey}]."
            );
        }

        return app(CoreCapabilityUsageRegistry::class)
            ->configuredUsage($capabilityKey, $websiteId);
    }

    /**
     * Determine whether another quantity can be consumed under the live
     * universal SaaS allocation.
     *
     * Off-server Core is unlimited and therefore always passes.
     */
    public function allowsConfiguredAllocation(
        string $capabilityKey,
        int|float $additional = 1,
        ?int $websiteId = null
    ): bool {
        $websiteId = $this->resolveWebsiteId($websiteId);

        if ($websiteId === null) {
            return false;
        }

        $limit = $this->effectiveAllocation(
            $capabilityKey,
            $websiteId
        );

        if ($limit === null) {
            return true;
        }

        $usage = $this->currentUsage(
            $capabilityKey,
            $websiteId
        );

        return ($usage + max(0, $additional)) <= $limit;
    }

    public function effectiveAllocation(
        string $capabilityKey,
        ?int $websiteId = null,
        ?int $coreDefault = 0
    ): ?int {
        $websiteId = $this->resolveWebsiteId($websiteId);

        if ($websiteId === null) {
            throw new RuntimeException(
                'A website context is required to resolve an effective allocation.'
            );
        }

        $website = \App\Models\Website::query()->find($websiteId);

        if (!$website) {
            throw new RuntimeException(
                "Website [{$websiteId}] could not be resolved."
            );
        }

        /*
         * ESUBIZ_UNIVERSAL_SAAS_LIMIT_AUTHORITY_V1
         *
         * Native off-server Core is unlimited for allocation-backed Core
         * capabilities. Central Core Limits and Allocation Add-ons are a
         * SaaS enforcement layer only.
         */
        if ($website->isOffServer()) {
            return null;
        }

        /*
         * Resolve the SaaS base allocation LIVE from Central configuration.
         *
         * Nothing is copied into the website. Changing a Core limit in
         * Central therefore affects existing and future SaaS websites on
         * their next request.
         */
        $limit = DB::table('core_feature_limits')
            ->where('limit_key', $capabilityKey)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        /*
         * No Central limit means this native Core resource is unlimited.
         *
         * A Central limit must explicitly exist before SaaS enforcement
         * applies. Off-server Core remains unlimited independently.
         */
        if (!$limit) {
            return null;
        }

        if (
            (bool) $limit->is_unlimited
            || (string) $limit->value_type === 'unlimited'
        ) {
            return null;
        }

        $coreDefault = $limit->default_value === null
            ? $coreDefault
            : (int) $limit->default_value;

        /*
         * Allocation Add-ons extend the current live SaaS base allocation.
         */
        return app(CoreAddonEntitlementService::class)
            ->effectiveCapabilityAllocation(
                $websiteId,
                $capabilityKey,
                $coreDefault
            );
    }

    /**
     * Universal feature-lock check.
     *
     * A capability is allowed when it is part of active Core access or
     * an active Add-on entitlement explicitly grants that capability.
     */
    /*
     * ESUBIZ_UNIVERSAL_CHILD_CAPABILITY_ENFORCEMENT_V1
     *
     * Registered Core capabilities may be available directly from Core.
     *
     * Pro/child capabilities stored as Core feature limits are different:
     * the parent Core feature being active must NOT automatically unlock
     * the child capability. The website must own an entitlement that
     * explicitly allocates that child capability.
     */
    public function allowsCapability(
        string $capabilityKey,
        ?int $websiteId = null
    ): bool {
        $websiteId = $this->resolveWebsiteId($websiteId);

        $childCapability = DB::table(
            'core_feature_limits as l'
        )
            ->join(
                'core_features as f',
                'f.id',
                '=',
                'l.core_feature_id'
            )
            ->where(
                'l.limit_key',
                $capabilityKey
            )
            ->where('l.is_active', true)
            ->whereNull('l.deleted_at')
            ->where('f.is_active', true)
            ->whereNull('f.deleted_at')
            ->exists();

        if ($childCapability) {
            return $this->addonCapabilityEnabled(
                $capabilityKey,
                $websiteId
            );
        }

        try {
            $definition = $this->capability(
                $capabilityKey
            );
        } catch (\Throwable $e) {
            return $this->addonCapabilityEnabled(
                $capabilityKey,
                $websiteId
            );
        }

        $featureKey =
            $definition['feature_key']
            ?? null;

        if (
            $featureKey !== null
            && $this->featureEnabled($featureKey)
        ) {
            return true;
        }

        if (
            $featureKey === null
            && !empty($definition)
        ) {
            return true;
        }

        return $this->addonCapabilityEnabled(
            $capabilityKey,
            $websiteId
        );
    }

    /**
     * Universal finite/unlimited resource or quantity check.
     *
     * Usage is resolved automatically through CoreCapabilityUsageRegistry
     * unless an explicit usage value is supplied for compatibility.
     */
    public function allowsAllocation(
        string $capabilityKey,
        int|float|null $currentUsage = null,
        ?int $websiteId = null,
        ?int $coreDefault = 0
    ): bool {
        $websiteId = $this->resolveWebsiteId($websiteId);

        if ($websiteId === null) {
            return false;
        }

        $allocation = $this->effectiveAllocation(
            $capabilityKey,
            $websiteId,
            $coreDefault
        );

        // Off-server Core and centrally unlimited SaaS resources.
        if ($allocation === null) {
            return true;
        }

        if ($currentUsage === null) {
            $currentUsage = $this->currentUsage(
                $capabilityKey,
                $websiteId
            );
        }

        return $currentUsage < $allocation;
    }

    /**
     * Enforce a feature-locked capability.
     */
    public function enforceCapability(
        string $capabilityKey,
        ?int $websiteId = null
    ): void {
        if (
            !$this->allowsCapability(
                $capabilityKey,
                $websiteId
            )
        ) {
            throw new RuntimeException(
                "Capability [{$capabilityKey}] is not available "
                . "for this website."
            );
        }
    }

    /**
     * Universal allocation enforcement.
     *
     * Usage belongs to the Core resource itself and is resolved through
     * the universal usage registry or a registered special meter.
     * No resource-specific limit engine is required.
     */
    public function enforceAllocation(
        string $capabilityKey,
        int|float|null $currentUsage = null,
        ?int $websiteId = null,
        ?int $coreDefault = 0
    ): void {
        if (
            !$this->allowsAllocation(
                $capabilityKey,
                $currentUsage,
                $websiteId,
                $coreDefault
            )
        ) {
            $allocation = $this->effectiveAllocation(
                $capabilityKey,
                $websiteId,
                $coreDefault
            );

            throw new RuntimeException(
                "The [{$capabilityKey}] allocation has been reached. "
                . "Effective allocation: {$allocation}."
            );
        }
    }

    /**
     * Universal entitlement enforcement entry point.
     *
     * Feature mode controls capability availability.
     * Allocation mode uses the live Central SaaS limit plus active Allocation
     * Add-ons and resolves usage through the universal usage registry.
     *
     * Off-server Core bypasses Central allocation limits.
     */
    public function enforceEntitlement(
        string $capabilityKey,
        string $mode = 'feature',
        int|float|null $currentUsage = null,
        ?int $websiteId = null,
        ?int $coreDefault = 0
    ): void {
        if ($mode === 'feature') {
            $this->enforceCapability(
                $capabilityKey,
                $websiteId
            );

            return;
        }

        if ($mode === 'allocation') {
            $this->enforceAllocation(
                $capabilityKey,
                $currentUsage,
                $websiteId,
                $coreDefault
            );

            return;
        }

        throw new RuntimeException(
            "Unsupported entitlement enforcement mode [{$mode}]."
        );
    }


    /**
     * Return the complete Core registry.
     */
    public function registry(): array
    {
        $features = DB::table('core_features')
            ->where('is_core', true)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return $features->map(function ($feature) {
            $limits = DB::table('core_feature_limits')
                ->where('core_feature_id', $feature->id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->get();

            return [
                'key' => $feature->key,
                'name' => $feature->name,
                'category' => $feature->category,
                'type' => $feature->type,
                'description' => $feature->description,
                'metadata' => $feature->metadata
                    ? json_decode($feature->metadata, true)
                    : null,
                'limits' => $limits->map(fn ($limit) => [
                    'key' => $limit->limit_key,
                    'name' => $limit->name,
                    'type' => $limit->value_type,
                    'value' => $limit->default_value,
                    'unit' => $limit->unit,
                    'unlimited' => (bool) $limit->is_unlimited,
                    'metadata' => $limit->metadata
                        ? json_decode($limit->metadata, true)
                        : null,
                ])->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Resolve whether a Core capability is registered and available.
     *
     * The same method is used by SaaS and off-server Core installations.
     * It does not create or alter limits.
     */
    public function capabilityAvailable(string $capability): bool
    {
        $registry = app(CoreCapabilityRegistry::class);
        $definition = $registry->get($capability);

        if (($definition['feature_key'] ?? null) === null) {
            // Centrally implemented Core capability.
            return true;
        }

        $feature = \Illuminate\Support\Facades\DB::table('core_features')
            ->where('key', $definition['feature_key'])
            ->where('is_core', true)
            ->where('is_active', true)
            ->first();

        return (bool) $feature;
    }

    /**
     * Return the canonical entitlement definition for a capability.
     */
    public function capability(string $capability): array
    {
        return app(CoreCapabilityRegistry::class)->get($capability);
    }

}
