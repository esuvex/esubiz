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
