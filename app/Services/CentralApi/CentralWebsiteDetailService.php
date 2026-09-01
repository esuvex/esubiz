<?php

namespace App\Services\CentralApi;

use App\Models\Website;
use Illuminate\Support\Facades\DB;

class CentralWebsiteDetailService
{
    /**
     * Build the canonical Central management snapshot for a website.
     *
     * IMPORTANT:
     *
     * This service is an aggregator only.
     *
     * It does not create or maintain separate credit, entitlement,
     * theme, module, licence, or Marketplace authorities.
     *
     * Each section reads from its existing canonical source.
     */
    public function get(Website $website): array
    {
        return [
            'website' => $this->website($website),

            'credits' => $this->credits($website),

            'entitlements' => $this->entitlements($website),

            'addons' => $this->addons($website),

            'owned_resources' => $this->ownedResources($website),

            'theme' => $this->theme($website),

            'modules' => $this->modules($website),

            'licence' => $this->licence($website),
        ];
    }


    /**
     * Canonical website registry identity.
     */
    protected function website(Website $website): array
    {
        return [
            'id' => $website->id,

            'website_uuid' =>
                $website->website_uuid,

            'identity' =>
                $website->centralWebsiteIdentity(),

            'name' =>
                $website->name,

            'type' =>
                $website->type,

            'edition' =>
                $website->edition,

            'deployment_type' =>
                $website->deployment_type,

            /*
             * For SaaS websites the deployed subdomain is the live
             * website address and therefore takes precedence over an
             * old wizard/draft registered_domain value.
             */
            'registered_domain' =>
                (
                    $website->isSaas()
                    && !empty($website->subdomain)
                )
                    ? strtolower(
                        trim(
                            (string) $website->subdomain
                        )
                    )
                        . '.esubiz.com'
                    : $website->registered_domain,

            'registered_host' =>
                (
                    $website->isSaas()
                    && !empty($website->subdomain)
                )
                    ? strtolower(
                        trim(
                            (string) $website->subdomain
                        )
                    )
                        . '.esubiz.com'
                    : $website->registeredHost(),

            'registry_status' =>
                $website->registry_status,

            'owner_id' =>
                $website->owner_id,

            'developer_id' =>
                $website->developer_id,

            'workspace_id' =>
                $website->workspace_id,

            'plan_id' =>
                $website->plan_id,

            'status' =>
                $website->status,

            'user_enabled' =>
                (bool) $website->user_enabled,

            'is_saas' =>
                $website->isSaas(),

            'is_off_server' =>
                $website->isOffServer(),

            'registry_active' =>
                $website->isRegistryActive(),
        ];
    }


    /**
     * Central service-credit authority.
     *
     * Never read legacy websites.ai_credits / sms_credits here.
     */
    protected function credits(Website $website): array
    {
        $services = [
            'ai',
            'sms',
            'email',
            'whatsapp',
        ];

        $rows =
            DB::table(
                'central_website_service_credits'
            )
                ->where(
                    'website_id',
                    $website->id
                )
                ->whereIn(
                    'service',
                    $services
                )
                ->get()
                ->keyBy('service');

        $result = [];

        foreach ($services as $service) {
            $row =
                $rows->get(
                    $service
                );

            $result[$service] = [
                'balance' =>
                    (float) (
                        $row->balance
                        ?? 0
                    ),

                'lifetime_credited' =>
                    (float) (
                        $row->lifetime_credited
                        ?? 0
                    ),

                'lifetime_consumed' =>
                    (float) (
                        $row->lifetime_consumed
                        ?? 0
                    ),
            ];
        }

        return $result;
    }


    /**
     * Canonical purchased-product entitlement records.
     */
    protected function entitlements(Website $website)
    {
        return DB::table(
            'product_entitlements'
        )
            ->where(
                'website_id',
                $website->id
            )
            ->whereNull(
                'deleted_at'
            )
            ->orderByDesc(
                'id'
            )
            ->get();
    }


    /**
     * Active Core add-on entitlements for this website.
     *
     * product_entitlements remains the authority.
     */
    protected function addons(Website $website)
    {
        return DB::table(
            'product_entitlements'
        )
            ->where(
                'website_id',
                $website->id
            )
            ->where(
                'product_type',
                'core_addon'
            )
            ->where(
                'status',
                'active'
            )
            ->whereNull(
                'deleted_at'
            )
            ->orderByDesc(
                'id'
            )
            ->get();
    }


    /**
     * Current website theme state.
     *
     * The website.theme field is the current website-side theme
     * selection. If it resolves to a published theme package, include
     * the matching package as additional catalogue information.
     */
    protected function theme(Website $website): array
    {
        $selected =
            $website->theme;

        $package = null;

        if (
            is_string($selected)
            && trim($selected) !== ''
        ) {
            $package =
                DB::table(
                    'theme_packages'
                )
                    ->whereNull(
                        'deleted_at'
                    )
                    ->where(
                        function ($query) use ($selected) {
                            $query
                                ->where(
                                    'slug',
                                    $selected
                                )
                                ->orWhere(
                                    'uuid',
                                    $selected
                                );
                        }
                    )
                    ->orderByDesc(
                        'is_current'
                    )
                    ->orderByDesc(
                        'id'
                    )
                    ->first();
        }

        return [
            'selected' =>
                $selected,

            'package' =>
                $package,
        ];
    }


    /**
     * Module installations currently belong to a workspace.
     *
     * A website without a workspace therefore has no workspace module
     * installation context. We do not manufacture one here.
     */
    protected function modules(Website $website)
    {
        if (!$website->workspace_id) {
            return collect();
        }

        return DB::table(
            'module_installations'
        )
            ->leftJoin(
                'modules',
                'modules.id',
                '=',
                'module_installations.module_id'
            )
            ->where(
                'module_installations.workspace_id',
                $website->workspace_id
            )
            ->whereNull(
                'module_installations.deleted_at'
            )
            ->where(
                'module_installations.status',
                '!=',
                'removed'
            )
            ->select([
                'module_installations.*',
                'modules.id as module_record_id',
            ])
            ->orderByDesc(
                'module_installations.id'
            )
            ->get();
    }


    /**
     * Licence / deployment state.
     *
     * SaaS websites do not require an off-server licence record.
     * Off-server websites read the existing licence authority.
     */
    protected function licence(Website $website): array
    {
        if ($website->isSaas()) {
            return [
                'required' => false,

                'deployment_type' =>
                    Website::DEPLOYMENT_SAAS,

                'licence' => null,
            ];
        }

        $columns =
            collect(
                DB::select(
                    'SHOW COLUMNS FROM off_server_licenses'
                )
            )
                ->pluck('Field');

        $query =
            DB::table(
                'off_server_licenses'
            );

        if (
            $columns->contains(
                'website_id'
            )
        ) {
            $query->where(
                'website_id',
                $website->id
            );
        } elseif (
            $columns->contains(
                'website_uuid'
            )
        ) {
            $query->where(
                'website_uuid',
                $website->website_uuid
            );
        } else {
            return [
                'required' => true,

                'deployment_type' =>
                    Website::DEPLOYMENT_OFF_SERVER,

                'licence' => null,

                'resolution' =>
                    'unsupported_schema',
            ];
        }

        if (
            $columns->contains(
                'deleted_at'
            )
        ) {
            $query->whereNull(
                'deleted_at'
            );
        }

        return [
            'required' => true,

            'deployment_type' =>
                Website::DEPLOYMENT_OFF_SERVER,

            'licence' =>
                $query
                    ->orderByDesc('id')
                    ->first(),
        ];
    }


    /**
     * ESUBIZ_CENTRAL_WEBSITE_OWNED_RESOURCES_V2
     *
     * Effective Core resources/features owned by this website.
     *
     * Base allocation comes from CoreCapabilityBaseAllocationRegistry.
     * Purchased extensions come from active product_entitlements and
     * Function Allocation.
     * Current usage comes only from CoreCapabilityUsageRegistry.
     *
     * No usage is fabricated when a capability has no trusted provider.
     */
    protected function ownedResources(Website $website): array
    {
        $baseRegistry = app(
            \App\Services\Core\CoreCapabilityBaseAllocationRegistry::class
        );

        $usageRegistry = app(
            \App\Services\Core\CoreCapabilityUsageRegistry::class
        );

        $entitlementService = app(
            \App\Services\Core\CoreEntitlementService::class
        );

        /*
         * ESUBIZ_CENTRAL_RESOURCE_DEFINITION_DISCOVERY_V1
         *
         * Discover active Core resource/limit definitions dynamically.
         * This makes Website Info plug-and-play for future resources.
         *
         * Exact PHP base providers remain authoritative where registered
         * because some resources (Storage/Bandwidth) can have a
         * website-specific base allocation.
         */
        $coreResourceDefinitions = DB::table(
            'core_feature_limits as l'
        )
            ->join(
                'core_features as f',
                'f.id',
                '=',
                'l.core_feature_id'
            )
            ->where(
                'l.is_active',
                1
            )
            ->whereNull(
                'l.deleted_at'
            )
            ->where(
                'f.is_active',
                1
            )
            ->whereNull(
                'f.deleted_at'
            )
            ->get([
                'l.limit_key as capability_key',
                'l.name as resource_name',
                'l.value_type',
                'l.default_value',
                'l.unit',
                'l.is_unlimited',
                'f.key as feature_key',
                'f.name as feature_name',
            ])
            ->keyBy(
                'capability_key'
            );

        $resourceKeys = collect(
            $baseRegistry->keys()
        )
            ->merge(
                $coreResourceDefinitions->keys()
            )
            ->filter()
            ->unique()
            ->values();

        $addonEntitlements = DB::table(
            'product_entitlements as e'
        )
            ->join(
                'core_addons as a',
                'a.id',
                '=',
                'e.product_id'
            )
            ->where(
                'e.website_id',
                $website->id
            )
            ->where(
                'e.product_type',
                'core_addon'
            )
            ->where(
                'e.status',
                'active'
            )
            ->whereNull(
                'e.deleted_at'
            )
            ->where(
                'a.is_active',
                1
            )
            ->whereNull(
                'a.deleted_at'
            )
            ->orderByDesc(
                'e.id'
            )
            ->get([
                'e.id as entitlement_id',
                'a.id as addon_id',
                'a.key as addon_key',
                'a.name as addon_name',
                'a.description as addon_description',
                'a.entitlement_type',
                'a.parent_capability',
                'a.capabilities',
            ]);

        $allocations = DB::table(
            'core_addon_capability_allocations as ca'
        )
            ->join(
                'product_entitlements as e',
                function ($join) use ($website) {
                    $join
                        ->on(
                            'e.product_id',
                            '=',
                            'ca.addon_id'
                        )
                        ->where(
                            'e.website_id',
                            '=',
                            $website->id
                        )
                        ->where(
                            'e.product_type',
                            '=',
                            'core_addon'
                        )
                        ->where(
                            'e.status',
                            '=',
                            'active'
                        )
                        ->whereNull(
                            'e.deleted_at'
                        );
                }
            )
            ->join(
                'core_addons as a',
                'a.id',
                '=',
                'ca.addon_id'
            )
            ->where(
                'a.is_active',
                1
            )
            ->whereNull(
                'a.deleted_at'
            )
            ->get([
                'ca.addon_id',
                'ca.capability_key',
                'ca.allocation',
                'ca.is_unlimited',
                'a.name as addon_name',
            ]);

        $resourceKeys = $resourceKeys
            ->merge(
                $allocations->pluck(
                    'capability_key'
                )
            )
            ->filter()
            ->unique()
            ->values();

        /*
         * Feature-only Add-ons may not have a numeric allocation row.
         * Preserve their declared capabilities in the website snapshot.
         */
        foreach ($addonEntitlements as $addon) {
            if (!empty($addon->parent_capability)) {
                $resourceKeys->push(
                    (string) $addon->parent_capability
                );
            }

            if (!empty($addon->capabilities)) {
                $decoded = json_decode(
                    (string) $addon->capabilities,
                    true
                );

                if (is_array($decoded)) {
                    foreach ($decoded as $capability) {
                        if (is_string($capability) && $capability !== '') {
                            $resourceKeys->push($capability);
                        }
                    }
                }
            }
        }

        $resourceKeys = $resourceKeys
            ->filter()
            ->unique()
            ->values();

        $result = [];

        foreach ($resourceKeys as $capabilityKey) {
            $capabilityKey =
                trim(
                    (string) $capabilityKey
                );

            if ($capabilityKey === '') {
                continue;
            }

            $definition =
                $coreResourceDefinitions->get(
                    $capabilityKey
                );

            $hasBaseProvider =
                $baseRegistry->has(
                    $capabilityKey
                );

            $hasConfiguredBase =
                $definition !== null;

            $hasBase =
                $hasBaseProvider
                || $hasConfiguredBase;

            $baseAllocation = null;

            /*
             * Exact capability provider wins when present.
             */
            if ($hasBaseProvider) {
                $baseAllocation =
                    $baseRegistry->allocation(
                        $capabilityKey,
                        (int) $website->id
                    );
            } elseif (
                $definition
                && !(bool) $definition->is_unlimited
                && $definition->default_value !== null
                && is_numeric($definition->default_value)
            ) {
                $baseAllocation =
                    (float) $definition->default_value;
            }

            $unit =
                $definition?->unit;

            $coreUnlimited =
                (bool) (
                    $definition?->is_unlimited
                    ?? false
                );

            $upgradeRows =
                $allocations
                    ->where(
                        'capability_key',
                        $capabilityKey
                    )
                    ->values();

            $upgradeAllocation = 0;

            $isUnlimited =
                $coreUnlimited
                || $upgradeRows->contains(
                    fn ($row) =>
                        (bool) $row->is_unlimited
                );

            foreach ($upgradeRows as $row) {
                if (
                    !$row->is_unlimited
                    && $row->allocation !== null
                ) {
                    $upgradeAllocation +=
                        (float) $row->allocation;
                }
            }

            $totalAllocation = null;

            if ($isUnlimited) {
                $totalAllocation = null;
            } elseif ($hasBase || $upgradeRows->isNotEmpty()) {
                $totalAllocation =
                    (float) ($baseAllocation ?? 0)
                    + $upgradeAllocation;
            }

            $used = null;
            $remaining = null;
            $percentage = null;
            $hasUsageMeter =
                $usageRegistry->has(
                    $capabilityKey
                );

            if ($hasUsageMeter) {
                try {
                    $used =
                        (float) $usageRegistry->usage(
                            $capabilityKey,
                            (int) $website->id
                        );

                    if (!$isUnlimited && $totalAllocation !== null) {
                        $remaining =
                            max(
                                0,
                                $totalAllocation - $used
                            );

                        $percentage =
                            $totalAllocation > 0
                                ? min(
                                    100,
                                    max(
                                        0,
                                        ($used / $totalAllocation) * 100
                                    )
                                )
                                : 0;
                    }
                } catch (\Throwable $e) {
                    /*
                     * Website Info is observational.
                     * A meter failure must not break Central management.
                     */
                    $used = null;
                    $remaining = null;
                    $percentage = null;
                }
            }

            $upgradeSources = [];

            foreach ($upgradeRows as $row) {
                $upgradeSources[] = [
                    'addon_id' =>
                        (int) $row->addon_id,

                    'name' =>
                        $row->addon_name,

                    'allocation' =>
                        $row->allocation === null
                            ? null
                            : (float) $row->allocation,

                    'is_unlimited' =>
                        (bool) $row->is_unlimited,
                ];
            }

            /*
             * Feature-only identity fallback.
             */
            if (empty($upgradeSources)) {
                foreach ($addonEntitlements as $addon) {
                    $matches = false;

                    if (
                        (string) ($addon->parent_capability ?? '')
                        === $capabilityKey
                    ) {
                        $matches = true;
                    }

                    if (!$matches && !empty($addon->capabilities)) {
                        $decoded = json_decode(
                            (string) $addon->capabilities,
                            true
                        );

                        $matches =
                            is_array($decoded)
                            && in_array(
                                $capabilityKey,
                                $decoded,
                                true
                            );
                    }

                    if ($matches) {
                        $upgradeSources[] = [
                            'addon_id' =>
                                (int) $addon->addon_id,

                            'name' =>
                                $addon->addon_name,

                            'allocation' =>
                                null,

                            'is_unlimited' =>
                                false,
                        ];
                    }
                }
            }

            $result[] = [
                'capability_key' =>
                    $capabilityKey,

                'name' =>
                    $definition?->resource_name
                    ?: ucwords(
                        str_replace(
                            ['_', '-'],
                            ' ',
                            $capabilityKey
                        )
                    ),

                'unit' =>
                    $unit,

                'value_type' =>
                    $definition?->value_type,

                'base_allocation' =>
                    $baseAllocation,

                'upgrade_allocation' =>
                    $upgradeAllocation,

                'total_allocation' =>
                    $totalAllocation,

                'is_unlimited' =>
                    $isUnlimited,

                'used' =>
                    $used,

                'remaining' =>
                    $remaining,

                'percentage' =>
                    $percentage,

                'has_usage_meter' =>
                    $hasUsageMeter,

                'upgrades' =>
                    $upgradeSources,

                'enabled' =>
                    $hasBase
                    || !empty($upgradeSources)
                    || $entitlementService->allowsCapability(
                        $capabilityKey,
                        (int) $website->id
                    ),
            ];
        }

        return collect($result)
            ->filter(
                fn (array $resource) =>
                    $resource['enabled']
            )
            ->sortBy(
                fn (array $resource) =>
                    strtolower(
                        (string) ($resource['name'] ?? '')
                    ),
                SORT_NATURAL
            )
            ->values()
            ->all();
    }

}
