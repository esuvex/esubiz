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
    public function effectiveAllocation(
        string $capabilityKey,
        ?int $websiteId = null,
        ?int $coreDefault = 0
    ): ?int {
        $websiteId = $this->resolveWebsiteId($websiteId);

        if ($websiteId === null) {
            return $coreDefault;
        }

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
     */
    public function allowsAllocation(
        string $capabilityKey,
        int|float $currentUsage,
        ?int $websiteId = null,
        ?int $coreDefault = 0
    ): bool {
        $allocation = $this->effectiveAllocation(
            $capabilityKey,
            $websiteId,
            $coreDefault
        );

        if ($allocation === null) {
            return true;
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
     * Enforce a finite/unlimited resource or quantity capability.
     */
    public function enforceAllocation(
        string $capabilityKey,
        int|float $currentUsage,
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
     * Universal enforcement entry point.
     *
     * mode=feature:
     *   enforce locked/unlocked capability access.
     *
     * mode=allocation:
     *   enforce quantity/resource allocation.
     *
     * Used identically by SaaS and off-server Core installations.
     */
    public function enforceEntitlement(
        string $capabilityKey,
        string $mode = 'feature',
        int|float $currentUsage = 0,
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
