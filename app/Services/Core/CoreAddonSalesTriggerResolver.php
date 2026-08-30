<?php

namespace App\Services\Core;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Generic runtime resolver for Core Add-on sales triggers.
 *
 * ESUBIZ_GENERIC_ADDON_SALES_TRIGGER_RESOLVER_V1
 *
 * This service contains no product-specific or UI-location-specific logic.
 *
 * Registered Core features/modules own location registration.
 * Central Admin attaches Add-ons to those locations.
 * Runtime consumers only provide:
 *   - location key
 *   - website
 *   - optional resource usage/context
 */
class CoreAddonSalesTriggerResolver
{
    public function __construct(
        protected CoreAddonSalesTriggerRegistry $registry
    ) {
    }

    /**
     * Resolve all eligible Add-on recommendations for a registered location.
     */
    public function resolve(
        string $locationKey,
        object $website,
        array $context = []
    ): Collection {
        if (!$this->registry->hasLocation($locationKey)) {
            return collect();
        }

        $deploymentType = $this->deploymentType($website, $context);

        if (!in_array($deploymentType, ['saas', 'off_server'], true)) {
            return collect();
        }

        $visibilityColumn = $deploymentType === 'saas'
            ? 'saas_visible'
            : 'off_server_visible';

        $triggers = DB::table('core_addon_sales_triggers as trigger')
            ->join('core_addons as addon', 'addon.id', '=', 'trigger.addon_id')
            ->where('trigger.location_key', $locationKey)
            ->where('trigger.is_active', 1)
            ->where("trigger.{$visibilityColumn}", 1)
            ->where('addon.is_active', 1)
            ->select([
                'trigger.*',
                'addon.id as addon_id',
                'addon.uuid as addon_uuid',
                'addon.key as addon_key',
                'addon.name as addon_name',
                'addon.description as addon_description',
                'addon.capabilities as addon_capabilities',
                'addon.saas_available as addon_saas_available',
                'addon.off_server_available as addon_off_server_available',
            ])
            ->orderBy('trigger.priority')
            ->orderBy('trigger.id')
            ->get();

        return $triggers
            ->filter(function ($trigger) use ($website, $context, $deploymentType) {
                if (!$this->addonAvailableForDeployment($trigger, $deploymentType)) {
                    return false;
                }

                return $this->conditionMatches($trigger, $website, $context);
            })
            ->values()
            ->map(function ($trigger) use ($locationKey, $deploymentType) {
                return [
                    'trigger_id' => (int) $trigger->id,
                    'location_key' => $locationKey,
                    'condition_type' => (string) $trigger->condition_type,
                    'priority' => (int) $trigger->priority,

                    'addon_id' => (int) $trigger->addon_id,
                    'addon_uuid' => $trigger->addon_uuid,
                    'addon_key' => $trigger->addon_key,
                    'addon_name' => $trigger->addon_name,
                    'addon_description' => $trigger->addon_description,

                    'title' => $trigger->title ?: $trigger->addon_name,
                    'message' => $trigger->message ?: $trigger->addon_description,
                    'cta_text' => $trigger->cta_text ?: 'View Add-on',

                    'resource_key' => $trigger->resource_key,
                    'threshold_percentage' => $trigger->threshold_percentage !== null
                        ? (float) $trigger->threshold_percentage
                        : null,

                    'deployment_type' => $deploymentType,
                ];
            });
    }

    protected function deploymentType(object $website, array $context): ?string
    {
        $explicit = $context['deployment_type'] ?? null;

        if (in_array($explicit, ['saas', 'off_server'], true)) {
            return $explicit;
        }

        foreach (['deployment_type', 'deployment', 'hosting_type'] as $field) {
            $value = $website->{$field} ?? null;

            if (in_array($value, ['saas', 'off_server'], true)) {
                return $value;
            }
        }

        /*
         * Tenant websites running inside Esubiz are SaaS unless the caller
         * explicitly supplies an off-server deployment context.
         */
        return 'saas';
    }

    protected function addonAvailableForDeployment(
        object $trigger,
        string $deploymentType
    ): bool {
        if ($deploymentType === 'saas') {
            return (bool) $trigger->addon_saas_available;
        }

        return (bool) $trigger->addon_off_server_available;
    }

    protected function conditionMatches(
        object $trigger,
        object $website,
        array $context
    ): bool {
        return match ((string) $trigger->condition_type) {
            'always' => true,

            'feature_locked' => $this->featureLocked(
                $trigger,
                $website,
                $context
            ),

            'resource_threshold' => $this->resourceThresholdReached(
                $trigger,
                $context
            ),

            'limit_reached' => $this->resourceLimitReached(
                $trigger,
                $context
            ),

            default => false,
        };
    }

    protected function featureLocked(
        object $trigger,
        object $website,
        array $context
    ): bool {
        $capabilities = json_decode(
            $trigger->addon_capabilities ?? '[]',
            true
        );

        if (!is_array($capabilities) || !$capabilities) {
            return false;
        }

        foreach ($capabilities as $capabilityKey) {
            if (!$this->websiteHasCapability(
                $website,
                (string) $capabilityKey,
                $context
            )) {
                return true;
            }
        }

        return false;
    }

    protected function websiteHasCapability(
        object $website,
        string $capabilityKey,
        array $context
    ): bool {
        /*
         * Callers may supply already-resolved entitlement state.
         * This keeps the resolver reusable for SaaS and off-server runtimes.
         */
        $resolved = $context['capabilities'] ?? [];

        if (is_array($resolved) && array_key_exists($capabilityKey, $resolved)) {
            $value = $resolved[$capabilityKey];

            if (is_bool($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return (float) $value > 0;
            }

            return !empty($value);
        }

        /*
         * Existing Add-on allocation grants are the central fallback.
         * We intentionally use the existing entitlement/allocation records
         * rather than creating another grant mechanism.
         */
        $websiteId = $website->id ?? null;

        if (!$websiteId) {
            return false;
        }

        $addonIds = DB::table('core_addon_capability_allocations')
            ->where('capability_key', $capabilityKey)
            ->where(function ($query) {
                $query->where('is_unlimited', 1)
                    ->orWhere('allocation', '>', 0);
            })
            ->pluck('addon_id');

        if ($addonIds->isEmpty()) {
            return false;
        }

        /*
         * product_entitlements is the existing marketplace/admin grant
         * authority. No new fulfilment path is introduced here.
         */
        return DB::table('product_entitlements')
            ->where('website_id', $websiteId)
            ->where('product_type', 'core_addon')
            ->whereIn('product_id', $addonIds)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereIn('status', ['active', 'granted']);
            })
            ->exists();
    }

    protected function resourceThresholdReached(
        object $trigger,
        array $context
    ): bool {
        $resourceKey = $trigger->resource_key;

        if (!$resourceKey) {
            return false;
        }

        $usage = $this->resourceUsage($resourceKey, $context);

        if ($usage === null) {
            return false;
        }

        $threshold = $trigger->threshold_percentage;

        if ($threshold === null) {
            return false;
        }

        return $usage >= (float) $threshold;
    }

    protected function resourceLimitReached(
        object $trigger,
        array $context
    ): bool {
        $resourceKey = $trigger->resource_key;

        if (!$resourceKey) {
            return false;
        }

        $usage = $this->resourceUsage($resourceKey, $context);

        return $usage !== null && $usage >= 100;
    }

    protected function resourceUsage(
        string $resourceKey,
        array $context
    ): ?float {
        $resources = $context['resources'] ?? [];

        if (!is_array($resources) || !array_key_exists($resourceKey, $resources)) {
            return null;
        }

        $resource = $resources[$resourceKey];

        if (is_numeric($resource)) {
            return (float) $resource;
        }

        if (!is_array($resource)) {
            return null;
        }

        foreach (['percentage', 'percent', 'usage_percentage'] as $key) {
            if (isset($resource[$key]) && is_numeric($resource[$key])) {
                return (float) $resource[$key];
            }
        }

        return null;
    }
}
