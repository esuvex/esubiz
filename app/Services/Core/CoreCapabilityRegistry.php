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
