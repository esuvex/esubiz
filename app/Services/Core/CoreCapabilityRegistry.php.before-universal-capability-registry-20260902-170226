<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoreCapabilityRegistry
{
    /**
     * Canonical Core capability map.
     *
     * This describes ownership/implementation only.
     * It does NOT invent or alter limits.
     */
    protected array $capabilities = [

        'crm' => [
            'feature_key' => 'crm',
            'owner' => 'crm',
            'entitlement' => 'feature',
        ],

        'hr' => [
            'feature_key' => 'hr',
            'owner' => 'hr',
            'entitlement' => 'feature',
            'staff_resource' => 'site_users_with_staff_roles',
        ],

        'pos' => [
            'feature_key' => 'pos',
            'owner' => 'pos',
            'entitlement' => 'feature',
        ],

        'payment_gateways' => [
            'feature_key' => 'payment_gateways',
            'owner' => 'payments',
            'entitlement' => 'feature',
        ],

        'referrals' => [
            'feature_key' => 'referrals',
            'owner' => 'growth',
            'entitlement' => 'service',
        ],

        'marketing' => [
            'feature_key' => 'marketing',
            'owner' => 'marketing',
            'entitlement' => 'service',
        ],

        'sms' => [
            'feature_key' => 'sms',
            'owner' => 'communication',
            'entitlement' => 'credits',
        ],

        'email' => [
            'feature_key' => 'email',
            'owner' => 'communication',
            'entitlement' => 'credits/accounts',
        ],

        'whatsapp' => [
            'feature_key' => 'whatsapp',
            'owner' => 'communication',
            'entitlement' => 'credits/accounts',
        ],

        'ai' => [
            'feature_key' => 'ai',
            'owner' => 'ai',
            'entitlement' => 'credits',
        ],

        'site_settings' => [
            'feature_key' => 'site_settings',
            'owner' => 'cms',
            'entitlement' => 'feature',
        ],

        'pages' => [
            'feature_key' => 'pages',
            'owner' => 'cms',
            'entitlement' => 'quantity/unlimited',
        ],

        'form_builder' => [
            'feature_key' => 'form_builder',
            'owner' => 'cms',
            'entitlement' => 'feature',

    /*
     * ESUBIZ_FORM_BUILDER_SALES_TRIGGER_LOCATIONS_V1
     *
     * Forms owns these locations.
     * Page Builder only consumes the Forms widget.
     */
    'sales_trigger_locations' => [
        'forms.builder' => [
            'label' => 'Form Builder',
            'group' => 'Forms',
            'supports_resource_condition' => false,
        ],
        'page_builder.widgets.forms' => [
            'label' => 'Form Widget',
            'group' => 'Page Builder Widgets',
            'supports_resource_condition' => false,
        ],
    ],
],

        'page_builder' => [
            'feature_key' => 'page_builder',
            'owner' => 'cms',
            'entitlement' => 'feature',

            /*
             * ESUBIZ_PAGE_BUILDER_SALES_TRIGGER_LOCATIONS_V1
             *
             * Page Builder owns this location.
             */
            'sales_trigger_locations' => [
                'pages.basic_builder' => [
                    'label' => 'Basic Page Builder',
                    'group' => 'Pages',
                    'supports_resource_condition' => false,
                ],
            ],
        ],

        'media' => [
            'feature_key' => 'media',
            'owner' => 'cms',
            'entitlement' => 'quantity/unlimited',
        ],

        'menus' => [
            'feature_key' => 'menus',
            'owner' => 'cms',
            'entitlement' => 'quantity/unlimited',
        ],

        'widgets' => [
            'feature_key' => 'widgets',
            'owner' => 'cms',
            'entitlement' => 'feature',
        ],

        'cms' => [
            'feature_key' => 'cms',
            'owner' => 'cms',
            'entitlement' => 'feature',
        ],

        'knowledgebase' => [
            'feature_key' => 'knowledgebase',
            'owner' => 'support',
            'entitlement' => 'quantity',
        ],

        'ticketing' => [
            'feature_key' => null,
            'owner' => 'central_support',
            'entitlement' => 'feature',
            'implementation' => 'central_ticketing',
        ],

        'live_chat' => [
            'feature_key' => 'live_chat',
            'owner' => 'communication',
            'entitlement' => 'feature',
        ],

        'panorama_360' => [
            'feature_key' => 'panorama_360',
            'owner' => 'media',
            'entitlement' => 'feature',

            /*
             * ESUBIZ_360_PANORAMA_SALES_TRIGGER_LOCATIONS_V1
             *
             * 360 Panorama owns this sales location even though
             * it is rendered inside Page Builder.
             */
            /*
             * ESUBIZ_360_PANORAMA_STANDALONE_TRIGGER_LOCATIONS_V2
             *
             * 360 Panorama is a standalone Core feature.
             * It owns its management area and publishes a widget into
             * Page Builder without Page Builder owning the feature.
             */
            'sales_trigger_locations' => [
                'panorama.settings' => [
                    'label' => '360 Panorama Settings',
                    'group' => '360 Panorama',
                    'supports_resource_condition' => false,
                ],
                'page_builder.widgets.panorama' => [
                    'label' => '360 Panorama Widget',
                    'group' => 'Page Builder Widgets',
                    'supports_resource_condition' => false,
                ],
            ],
        ],

        'qr_generator' => [
            'feature_key' => 'qr_generator',
            'owner' => 'tools',
            'entitlement' => 'quantity',
        ],

        'security' => [
            'feature_key' => 'security',
            'owner' => 'security',
            'entitlement' => 'feature',
        ],

        'resource_monitor' => [
            'feature_key' => 'resource_monitor',
            'owner' => 'resources',
            'entitlement' => 'feature',

                /*
                 * ESUBIZ_RESOURCE_MONITOR_SALES_TRIGGER_LOCATIONS_V1
                 *
                 * Resource Monitor owns the generic dashboard resource
                 * recommendation location. Individual resources/Add-ons
                 * select their own resource_key and trigger condition.
                 */
                'sales_trigger_locations' => [
                    'dashboard.resources' => [
                        'label' => 'Dashboard Resources',
                        'group' => 'Dashboard',
                        'supports_resource_condition' => true,
                    ],
                ],
        ],

        'storage' => [
            'feature_key' => 'storage',
            'owner' => 'resources',
            'entitlement' => 'storage',
        ],

        'bandwidth' => [
            'feature_key' => 'bandwidth',
            'owner' => 'resources',
            'entitlement' => 'bandwidth',
        ],

        'branches' => [
            'feature_key' => 'branches',
            'owner' => 'business',
            'entitlement' => 'quantity/unlimited',
        ],

        'api' => [
            'feature_key' => null,
            'owner' => 'platform',
            'entitlement' => 'service',
            'implementation' => 'api_sso',
        ],

        'notifications' => [
            'feature_key' => null,
            'owner' => 'platform',
            'entitlement' => 'service',
            'implementation' => 'notifications',
        ],

        'workflows' => [
            'feature_key' => null,
            'owner' => 'automation',
            'entitlement' => 'service',
            'implementation' => 'workflows',
        ],

        'wallet' => [
            'feature_key' => null,
            'owner' => 'finance',
            'entitlement' => 'service',
            'implementation' => 'wallet',
        ],

        'currency' => [
            'feature_key' => null,
            'owner' => 'finance',
            'entitlement' => 'service',
            'implementation' => 'currency',
        ],

        'marketplace' => [
            'feature_key' => null,
            'owner' => 'platform',
            'entitlement' => 'service',
            'implementation' => 'marketplace',
        ],

        'domains' => [
            'feature_key' => null,
            'owner' => 'website',
            'entitlement' => 'service',
            'implementation' => 'domains',
        ],
    ];

    public function all(): array
    {
        return $this->capabilities;
    }

    public function get(string $key): array
    {
        if (!isset($this->capabilities[$key])) {
            throw new RuntimeException(
                "Unknown Core capability [{$key}]."
            );
        }

        return $this->capabilities[$key];
    }

    public function featureKey(string $key): ?string
    {
        return $this->get($key)['feature_key'];
    }

    /*
     * ESUBIZ_CAPABILITY_AUTOMATIC_ENFORCEMENT_MODE_V1
     *
     * Enforcement is derived from the capability definition rather than
     * from an Add-on, route or product.
     *
     * Feature/service capabilities are access gates.
     *
     * Quantities, resources, accounts and credits are allocation-backed
     * capabilities whose usage/balance authority is supplied separately
     * by the capability owner.
     *
     * This contract is shared by SaaS and off-server Core installations.
     */
    /*
     * ESUBIZ_UNIVERSAL_ENTITLEMENT_CLASSIFICATION_V2
     *
     * Central plug-and-play entitlement classification for both SaaS
     * and off-server Core installations.
     *
     * Classification comes from the existing Core feature/limit model.
     * Add-ons only extend/unlock capabilities through Function Allocation.
     *
     * Families:
     * - unlimited : permanent unlimited Core entitlement
     * - feature   : boolean Core/Add-on feature entitlement
     * - service   : centrally controlled service
     * - quantity  : finite Core + Add-on quantity allocation
     * - resource  : metered Core + Add-on resource allocation
     * - credits   : centrally consumed credit balance
     * - accounts  : account allocation, optionally paired with credits
     */
    public function enforcementFamily(string $key): string
    {
        $definition = $this->get($key);

        if (!$definition) {
            throw new RuntimeException(
                "Unknown Core capability [{$key}]."
            );
        }

        $entitlement =
            strtolower(
                trim(
                    (string) ($definition['entitlement'] ?? 'feature')
                )
            );

        /*
         * Credits and account-backed products have their own central
         * authorities and must not be mistaken for ordinary quantities.
         */
        if ($entitlement === 'credits') {
            return 'credits';
        }

        if ($entitlement === 'credits/accounts') {
            return 'accounts';
        }

        /*
         * Metered resources use the universal usage/base-allocation
         * registries. Add-ons simply extend their allocations.
         */
        if (
            $entitlement === 'storage'
            || $entitlement === 'bandwidth'
        ) {
            return 'resource';
        }

        $featureKey =
            $definition['feature_key']
            ?? $key;

        $feature = null;

        if ($featureKey) {
            $feature =
                DB::table('core_features')
                    ->where('key', $featureKey)
                    ->whereNull('deleted_at')
                    ->first();
        }

        /*
         * Registry-only platform services intentionally do not require
         * a core_features row. Their implementation/service authority
         * controls availability.
         */
        if (!$feature) {
            return $entitlement === 'service'
                ? 'service'
                : 'feature';
        }

        $limits =
            DB::table('core_feature_limits')
                ->where('core_feature_id', $feature->id)
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->get();

        /*
         * Any active unlimited Core limit means the capability itself is
         * permanently unlimited. No quantity enforcement is required.
         *
         * Examples currently include pages/media/menus/branches and any
         * future Core capability configured the same way.
         */
        /*
         * ESUBIZ_PERMANENT_CORE_UNLIMITED_CLASSIFICATION_V1
         *
         * Unlimited Core limits make quantity/service capabilities
         * permanently unlimited.
         *
         * Feature parents are deliberately excluded: an unlimited child
         * limit must never make the whole parent feature unlimited.
         * Function Allocation continues to control Pro/child capabilities.
         */
        if (
            in_array(
                $entitlement,
                [
                    'quantity',
                    'quantity/unlimited',
                    'service',
                ],
                true
            )
            && $limits->contains(
                fn ($limit) =>
                    (bool) $limit->is_unlimited
            )
        ) {
            return 'unlimited';
        }

        /*
         * Finite quantity remains enforceable even when no Add-on currently
         * upgrades it. If an Add-on is allocated later, the existing
         * entitlement service automatically adds that allocation.
         */
        if (
            $entitlement === 'quantity'
            || $entitlement === 'quantity/unlimited'
        ) {
            return 'quantity';
        }

        if ($entitlement === 'service') {
            return 'service';
        }

        return 'feature';
    }

    /*
     * Compatibility bridge for existing middleware while the richer
     * family contract remains the central source of truth.
     */
    /*
     * ESUBIZ_UNIVERSAL_ACCOUNT_CAPABILITY_RESOLVER_V1
     *
     * Resolve the account-allocation child of any accounts-family
     * capability from Core configuration.
     *
     * No Email/WhatsApp/product/Add-on names are hardcoded.
     * Present and future account-backed capabilities only need an active
     * Core feature limit whose unit is "accounts".
     */
    public function accountAllocationCapability(
        string $key
    ): ?array {
        if ($this->enforcementFamily($key) !== 'accounts') {
            return null;
        }

        $definition = $this->get($key);

        if (!$definition) {
            return null;
        }

        $featureKey =
            $definition['feature_key']
            ?? $key;

        $feature =
            DB::table('core_features')
                ->where('key', $featureKey)
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->first();

        if (!$feature) {
            return null;
        }

        $limit =
            DB::table('core_feature_limits')
                ->where('core_feature_id', $feature->id)
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->whereRaw(
                    'LOWER(COALESCE(unit, "")) = ?',
                    ['accounts']
                )
                ->orderBy('id')
                ->first();

        if (!$limit) {
            return null;
        }

        return [
            'capability_key' => (string) $limit->limit_key,
            'default' =>
                (bool) $limit->is_unlimited
                    ? null
                    : (float) ($limit->default_value ?? 0),
            'unlimited' => (bool) $limit->is_unlimited,
            'unit' => (string) ($limit->unit ?? 'accounts'),
        ];
    }

    public function enforcementMode(string $key): string
    {
        return match ($this->enforcementFamily($key)) {
            'quantity',
            'resource',
            'credits',
            'accounts' => 'allocation',

            'unlimited' => 'unlimited',

            default => 'feature',
        };
    }


    public function hasRegistryFeature(string $key): bool
    {
        $featureKey = $this->featureKey($key);

        return $featureKey !== null
            && DB::table('core_features')
                ->where('key', $featureKey)
                ->exists();
    }

    public function limits(string $key)
    {
        $featureKey = $this->featureKey($key);

        if ($featureKey === null) {
            return collect();
        }

        $feature = DB::table('core_features')
            ->where('key', $featureKey)
            ->first();

        if (!$feature) {
            return collect();
        }

        return DB::table('core_feature_limits')
            ->where('core_feature_id', $feature->id)
            ->where('is_active', true)
            ->get();
    }

    /*
     * ESUBIZ_CORE_CAPABILITY_SALES_TRIGGER_LOCATIONS_V1
     *
     * Core capabilities may optionally declare application locations
     * where Add-on recommendations belonging to that capability can
     * appear.
     *
     * Example capability definition:
     *
     * 'page_builder' => [
     *     ...
     *     'sales_trigger_locations' => [
     *         'pages.basic_builder' => [
     *             'label' => 'Basic Page Builder',
     *             'group' => 'Pages',
     *         ],
     *     ],
     * ],
     *
     * This method contains NO product-specific locations itself.
     * It only forwards locations declared by capability owners to the
     * generic Add-on sales-trigger registry.
     */
    public function submitSalesTriggerLocations(
        CoreAddonSalesTriggerRegistry $salesRegistry
    ): void {
        foreach ($this->all() as $capabilityKey => $definition) {

            if (!is_array($definition)) {
                continue;
            }

            $locations =
                $definition['sales_trigger_locations']
                ?? [];

            if (!is_array($locations) || !$locations) {
                continue;
            }

            $featureKey =
                $definition['feature_key']
                ?? $capabilityKey;

            if (!$featureKey) {
                continue;
            }

            $salesRegistry->submit(
                (string) $featureKey,
                $locations
            );
        }
    }

}
