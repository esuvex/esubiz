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

        /*
         * ESUBIZ_UNIVERSAL_ADDON_RUNTIME_ROUTING_V1
         *
         * Admin stores only universal placements.
         *
         * Capability-owned runtime locations consume the appropriate
         * universal placement automatically:
         *
         * settings     -> capability Settings/Overview
         * page_builder -> capability Page Builder location
         * widgets      -> capability Widget location
         *
         * Exact registered locations remain supported.
         */
        $locationDefinition = $this->registry->location($locationKey);

        $universalPlacementKey = null;

        /*
         * ESUBIZ_DIRECT_UNIVERSAL_PLACEMENT_RESOLUTION_V1
         *
         * Universal hosts resolve themselves directly.
         * Capability-owned child locations inherit their matching
         * universal placement and are capability-filtered below.
         */
        if (
            in_array(
                $locationKey,
                [
                    'settings',
                    'page_builder',
                    'widgets',
                ],
                true
            )
        ) {
            $universalPlacementKey = $locationKey;
        } elseif (
            str_starts_with(
                $locationKey,
                'page_builder.widgets.'
            )
        ) {
            $universalPlacementKey = 'widgets';
        } elseif (
            str_ends_with($locationKey, '.settings')
            || str_starts_with($locationKey, 'settings.')
        ) {
            $universalPlacementKey = 'settings';
        } elseif (
            str_starts_with($locationKey, 'page_builder.')
            || str_starts_with($locationKey, 'pages.')
        ) {
            $universalPlacementKey = 'page_builder';
        }

        $isDirectUniversalLocation =
            $universalPlacementKey === $locationKey
            && in_array(
                $locationKey,
                [
                    'settings',
                    'page_builder',
                    'widgets',
                ],
                true
            );

        $isCapabilityOwnedUniversalLocation =
            !$isDirectUniversalLocation
            && !empty($universalPlacementKey)
            && !empty(
                $locationDefinition['feature_key']
                ?? null
            );

        $triggers = DB::table('core_addon_sales_triggers as trigger')
            ->join('core_addons as addon', 'addon.id', '=', 'trigger.addon_id')
            ->where(function ($query) use (
                $locationKey,
                $universalPlacementKey,
                $isCapabilityOwnedUniversalLocation
            ) {
                $query->where(
                    'trigger.location_key',
                    $locationKey
                );

                if (
                    $isCapabilityOwnedUniversalLocation
                    && !empty($universalPlacementKey)
                ) {
                    $query->orWhere(
                        'trigger.location_key',
                        $universalPlacementKey
                    );
                }
            })
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

        /*
         * ESUBIZ_DASHBOARD_RESOURCE_TRIGGER_DEDUP_APPLIED_V1
         *
         * Dashboard renders one recommendation per resource while
         * retaining every eligible Add-on in purchase_options.
         */
        return $this->collapseDashboardResourceTriggers(
            $triggers
                        ->filter(function ($trigger) use (
                            $website,
                            $context,
                            $deploymentType,
                            $locationKey,
                            $locationDefinition,
                            $universalPlacementKey,
                            $isCapabilityOwnedUniversalLocation
                        ) {
                            if (!$this->addonAvailableForDeployment($trigger, $deploymentType)) {
                                return false;
                            }

                            /*
                             * ESUBIZ_UNIVERSAL_PLACEMENT_CAPABILITY_MATCH_V1
                             *
                             * Universal placements are routed only into locations
                             * owned by a capability allocated by the Add-on.
                             *
                             * This keeps placement plug-and-play without Add-on
                             * IDs, names or individual placement code.
                             */
                            if (
                                $isCapabilityOwnedUniversalLocation
                                && (string) $trigger->location_key
                                    === (string) $universalPlacementKey
                                && !$this->addonBelongsToPlacementCapability(
                                    (int) $trigger->addon_id,
                                    (string) (
                                        $locationDefinition['feature_key']
                                        ?? ''
                                    )
                                )
                            ) {
                                return false;
                            }

                            /*
                             * ESUBIZ_GENERIC_TRIGGER_PURCHASE_POLICY_V1
                             *
                             * Admin is_active is already the master gate in the query.
                             *
                             * Normal/feature Add-ons stop being recommended once owned.
                             * Resource Add-ons may explicitly retrigger after purchase;
                             * their threshold/limit condition still has to match below.
                             *
                             * SaaS renewal notices are intentionally outside this engine
                             * and remain with the existing invoice/reminder lifecycle.
                             */
                            if (
                                $this->addonAlreadyPurchased($trigger, $website)
                                && (string) ($trigger->repeat_policy ?? 'once_until_purchased')
                                    !== 'resource_retrigger'
                            ) {
                                return false;
                            }

                            return $this->conditionMatches($trigger, $website, $context);
                        })
                        ->values()
                        ->map(function ($trigger) use ($locationKey, $deploymentType) {
                            /*
                             * ESUBIZ_TRIGGER_ALTERNATIVE_PRODUCTS_PAYLOAD_V1
                             *
                             * The frontend can now decide:
                             * one product = direct checkout,
                             * multiple products = selection popup.
                             */
                            /*
                             * ESUBIZ_UNIVERSAL_ALTERNATIVE_PRODUCTS_ALL_PLACEMENTS_V1
                             *
                             * Multi-product discovery belongs to Dashboard
                             * resource sales triggers only.
                             *
                             * Other placements keep their original single
                             * Add-on recommendation and purchase identity.
                             */
                            $originalProduct = (object) [
                                'id' => (int) $trigger->addon_id,
                                'uuid' => $trigger->addon_uuid,
                                'key' => $trigger->addon_key,
                                'name' => $trigger->addon_name,
                                'description' =>
                                    $trigger->addon_description,
                                'capability_key' =>
                                    $trigger->resource_key ?? null,
                                'allocation' => null,
                                'is_unlimited' => false,
                                'unit' => null,
                            ];

                            /*
                             * ESUBIZ_DASHBOARD_PURCHASE_OPTIONS_FALLBACK_V1
                             *
                             * The configured Dashboard Add-on is always a
                             * valid purchase option. Matching Add-ons are
                             * then merged into the same resource selector.
                             *
                             * This prevents a failed/empty alternative
                             * lookup from suppressing the Dashboard CTA.
                             */
                            if ($locationKey === 'dashboard') {
                                $alternativeProducts =
                                    collect([$originalProduct])
                                        ->merge(
                                            $this->alternativeProducts(
                                                $trigger,
                                                $deploymentType
                                            )
                                        )
                                        ->unique('id')
                                        ->values();
                            } else {
                                $alternativeProducts =
                                    collect([$originalProduct]);
                            }

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

                                'purchase_options' =>
                                    $alternativeProducts
                                        ->map(function ($product) {
                                            return [
            
                                                'addon_id' =>
                                                    (int) $product->id,

                                                'addon_uuid' =>
                                                    $product->uuid,

                                                'addon_key' =>
                                                    $product->key,

                                                'name' =>
                                                    $product->name,

                                                'description' =>
                                                    $product->description,

                                                'capability_key' =>
                                                    $product->capability_key,

                                                'allocation' =>
                                                    (float) (
                                                        $product->allocation
                                                        ?? 0
                                                    ),

                                                'is_unlimited' =>
                                                    (bool) (
                                                        $product->is_unlimited
                                                        ?? false
                                                    ),

                                                /*
                                                 * ESUBIZ_PURCHASE_OPTION_ALLOCATION_UNIT_V1
                                                 *
                                                 * Allows the selector to display:
                                                 * 40 GB, 1 GB, 5 mailboxes, etc.
                                                 */
                                                'unit' =>
                                                    $product->unit
                                                    ?? null,
                                            ];
                                        })
                                        ->values()
                                        ->all(),

                                'purchase_option_count' =>
                                    $alternativeProducts->count(),

                                /*
                                 * ESUBIZ_ADMIN_CONTROLLED_TRIGGER_CONTENT_V1
                                 *
                                 * Premium placement content belongs to Admin.
                                 * Empty title/message remain empty instead of falling
                                 * back to the Add-on name/description.
                                 */
                                'title' => $trigger->title,
                                'message' => $trigger->message,
                                'cta_text' => $trigger->cta_text,


                                'resource_key' => $trigger->resource_key,
                                'threshold_percentage' => $trigger->threshold_percentage !== null
                                    ? (float) $trigger->threshold_percentage
                                    : null,

                                'deployment_type' => $deploymentType,
                            ];
                        }),
            $locationKey
        );
    }

    /*
     * ESUBIZ_GENERIC_TRIGGER_ADDON_OWNERSHIP_V1
     *
     * Uses the existing Marketplace/Admin entitlement authority.
     * No Add-on-specific purchase checks are introduced.
     */
    protected function addonAlreadyPurchased(
        object $trigger,
        object $website
    ): bool {
        $websiteId = $website->id ?? null;
        $addonId = $trigger->addon_id ?? null;

        if (!$websiteId || !$addonId) {
            return false;
        }

        return DB::table('product_entitlements')
            ->where('website_id', $websiteId)
            ->where('product_type', 'core_addon')
            ->where('product_id', $addonId)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereIn('status', ['active', 'granted']);
            })
            ->exists();
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

    /*
     * ESUBIZ_UNIVERSAL_WIDGET_CAPABILITY_OWNER_MATCH_V1
     *
     * Match an Add-on's Function Allocations to the capability that
     * owns the current widget location.
     *
     * Direct matches are supported:
     *   form_builder -> form_builder
     *
     * Pro/child capability matches are also supported through the
     * existing core_feature_limits parent capability relationship:
     *   panorama_360_pro -> panorama_360
     *
     * No Add-on IDs or widget product names are hardcoded.
     */
    protected function addonBelongsToPlacementCapability(
        int $addonId,
        string $widgetCapability
    ): bool {
        $widgetCapability = trim($widgetCapability);

        if ($addonId <= 0 || $widgetCapability === '') {
            return false;
        }

        $allocatedCapabilities = DB::table(
            'core_addon_capability_allocations'
        )
            ->where('addon_id', $addonId)
            ->where(function ($query) {
                $query
                    ->where('is_unlimited', 1)
                    ->orWhere('allocation', '>', 0);
            })
            ->pluck('capability_key')
            ->filter()
            ->map(
                fn ($key) => trim((string) $key)
            )
            ->filter()
            ->unique()
            ->values();

        if ($allocatedCapabilities->isEmpty()) {
            return false;
        }

        if ($allocatedCapabilities->contains($widgetCapability)) {
            return true;
        }

        /*
         * Function Allocations may point to a Pro/child limit while
         * the widget is owned by its base Core capability.
         *
         * Resolve that relationship from the existing Core feature
         * limit records rather than product-specific code.
         */
        $featureId = DB::table('core_features')
            ->where('key', $widgetCapability)
            ->value('id');

        if (!$featureId) {
            return false;
        }

        return DB::table('core_feature_limits')
            ->where('core_feature_id', $featureId)
            ->whereIn(
                'limit_key',
                $allocatedCapabilities->all()
            )
            ->exists();
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

        /*
         * ESUBIZ_GENERIC_TRIGGER_ALLOCATION_CAPABILITY_FALLBACK_V1
         *
         * Function Allocations are the generic capability authority for
         * Add-ons that do not store a legacy capabilities JSON array.
         *
         * This allows universal Settings, Page Builder and Widgets
         * placements to work for every Add-on without product-specific
         * placement code.
         */
        if (!is_array($capabilities) || !$capabilities) {
            $capabilities = DB::table(
                'core_addon_capability_allocations'
            )
                ->where(
                    'addon_id',
                    (int) ($trigger->addon_id ?? 0)
                )
                ->where(function ($query) {
                    $query
                        ->where('is_unlimited', 1)
                        ->orWhere('allocation', '>', 0);
                })
                ->pluck('capability_key')
                ->filter()
                ->map(
                    fn ($capabilityKey) =>
                        trim((string) $capabilityKey)
                )
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (!$capabilities) {
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

    /**
     * ESUBIZ_ADDON_ALTERNATIVE_PRODUCTS_V1
     *
     * Find all active Add-ons that can satisfy the same resource or
     * capability as a sales-trigger recommendation.
     *
     * This allows:
     *   1 eligible product  -> direct checkout
     *   2+ eligible products -> product-selection popup
     *
     * Function Allocations are the authoritative product relationship.
     * No Add-on names, IDs or resource names are hardcoded.
     */
    protected function alternativeProducts(
        object $trigger,
        string $deploymentType
    ): Collection {
        $resourceKey = trim(
            (string) ($trigger->resource_key ?? '')
        );

        /*
         * Resource triggers use resource_key directly.
         * Feature placements fall back to the Add-on's allocations.
         */
        $capabilityKeys = collect();

        if ($resourceKey !== '') {
            $capabilityKeys->push($resourceKey);
        }

        if ($capabilityKeys->isEmpty()) {
            $capabilityKeys = DB::table(
                'core_addon_capability_allocations'
            )
                ->where(
                    'addon_id',
                    (int) $trigger->addon_id
                )
                ->where(function ($query) {
                    $query
                        ->where('is_unlimited', 1)
                        ->orWhere('allocation', '>', 0);
                })
                ->pluck('capability_key')
                ->filter()
                ->values();
        }

        if ($capabilityKeys->isEmpty()) {
            
        }

        $availabilityColumn =
            $deploymentType === 'saas'
                ? 'addon.saas_available'
                : 'addon.off_server_available';

        $triggerVisibilityColumn =
            $deploymentType === 'saas'
                ? 'candidate_trigger.saas_visible'
                : 'candidate_trigger.off_server_visible';

        /*
         * ESUBIZ_DASHBOARD_SELECTOR_ADMIN_TRIGGER_ONLY_V1
         *
         * A same-resource Add-on is a checkout alternative only when
         * Admin explicitly configured an active Dashboard sales trigger
         * for that Add-on and the same resource.
         *
         * Function Allocation alone must never make an Add-on appear
         * in the selector.
         */
        return DB::table(
            'core_addon_capability_allocations as allocation'
        )
            ->join(
                'core_addons as addon',
                'addon.id',
                '=',
                'allocation.addon_id'
            )
            ->join(
                'core_addon_sales_triggers as candidate_trigger',
                'candidate_trigger.addon_id',
                '=',
                'addon.id'
            )
            ->whereIn(
                'allocation.capability_key',
                $capabilityKeys->all()
            )
            ->where('candidate_trigger.location_key', 'dashboard')
            ->where('candidate_trigger.is_active', 1)
            ->where($triggerVisibilityColumn, 1)
            ->whereIn(
                'candidate_trigger.condition_type',
                [
                    'resource_threshold',
                    'limit_reached',
                ]
            )
            ->when(
                $resourceKey !== '',
                function ($query) use ($resourceKey) {
                    $query->where(
                        'candidate_trigger.resource_key',
                        $resourceKey
                    );
                }
            )
            ->where('addon.is_active', 1)
            ->where($availabilityColumn, 1)
            ->where(function ($query) {
                $query
                    ->where('allocation.is_unlimited', 1)
                    ->orWhere('allocation.allocation', '>', 0);
            })
            ->select([
                'addon.id',
                'addon.uuid',
                'addon.key',
                'addon.name',
                'addon.description',
                'allocation.capability_key',
                'allocation.allocation',
                'allocation.is_unlimited',
            ])
            ->orderBy('addon.name')
            ->get()
            ->unique('id')
            ->values();
    }


    /**
     * ESUBIZ_DASHBOARD_RESOURCE_TRIGGER_DEDUP_V1
     *
     * Dashboard resource recommendations are resource-centric,
     * not Add-on-centric.
     *
     * Two or more Add-ons serving the same resource therefore
     * render as one recommendation containing all purchase options.
     */
    protected function collapseDashboardResourceTriggers(
        Collection $recommendations,
        string $locationKey
    ): Collection {
        if ($locationKey !== 'dashboard') {
            return $recommendations;
        }

        return $recommendations
            ->groupBy(function ($recommendation) {
                $resourceKey =
                    trim(
                        (string) (
                            $recommendation['resource_key']
                            ?? ''
                        )
                    );

                /*
                 * Non-resource Dashboard recommendations remain
                 * independently addressable.
                 */
                if ($resourceKey === '') {
                    return 'trigger:'
                        . (
                            $recommendation['trigger_id']
                            ?? $recommendation['addon_id']
                            ?? uniqid('', true)
                        );
                }

                return 'resource:' . $resourceKey;
            })
            ->map(function ($group) {
                $primary = $group->first();

                if ($group->count() === 1) {
                    return $primary;
                }

                $purchaseOptions =
                    $group
                        ->flatMap(function ($recommendation) {
                            return collect(
                                $recommendation['purchase_options']
                                ?? []
                            );
                        })
                        ->unique('addon_id')
                        ->values();

                $primary['purchase_options'] =
                    $purchaseOptions->all();

                $primary['purchase_option_count'] =
                    $purchaseOptions->count();

                return $primary;
            })
            ->values();
    }

}
