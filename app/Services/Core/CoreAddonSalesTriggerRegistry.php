<?php

namespace App\Services\Core;

use InvalidArgumentException;

/*
 * ESUBIZ_ADDON_SALES_TRIGGER_DYNAMIC_LOCATION_REGISTRY_V2
 *
 * Plug-and-play sales-trigger location registry.
 *
 * IMPORTANT:
 * Locations are NOT centrally hardcoded here.
 *
 * Each Core feature / Website Type / Module that supports Add-on sales
 * submits the locations it owns.
 *
 * Examples:
 *
 * Page Builder Pro feature may submit:
 *   pages.basic_builder
 *
 * 360 Panorama may submit:
 *   page_builder.widgets
 *
 * Email Accounts may submit:
 *   email.accounts
 *
 * Future modules may submit their own locations without changing this
 * registry or the generic Add-on sales-trigger engine.
 */
class CoreAddonSalesTriggerRegistry
{
    /*
     * ESUBIZ_UNIVERSAL_ADDON_PLACEMENTS_V1
     *
     * Central plug-and-play placements available to every Add-on.
     *
     * Add-ons do not create their own placement definitions.
     * Admin chooses one or more placements on the Add/Edit page.
     *
     * Dashboard:
     *   May display feature recommendations and resource sales triggers.
     *
     * Settings:
     *   Places the Add-on in the appropriate settings/overview area.
     *
     * Page Builder:
     *   Makes the Add-on available to the Page Builder integration layer.
     *
     * Widgets:
     *   Makes widget-capable Add-ons available to the widget layer.
     *
     * Resource threshold/limit sales behaviour is a Dashboard concern.
     */
    private const UNIVERSAL_PLACEMENTS = [
        'dashboard' => [
            'label' => 'Dashboard',
            'feature_key' => 'core',
            'group' => 'Universal Placements',
            'supports_resource_condition' => true,
            'resource_key' => null,
            'metadata' => [
                'placement' => 'dashboard',
                'supports_feature_locked' => true,
                'supports_sales_trigger' => true,
            ],
        ],

        'settings' => [
            'label' => 'Settings',
            'feature_key' => 'core',
            'group' => 'Universal Placements',
            'supports_resource_condition' => false,
            'resource_key' => null,
            'metadata' => [
                'placement' => 'settings',
                'supports_feature_locked' => true,
                'supports_sales_trigger' => false,
            ],
        ],

        'page_builder' => [
            'label' => 'Page Builder',
            'feature_key' => 'core',
            'group' => 'Universal Placements',
            'supports_resource_condition' => false,
            'resource_key' => null,
            'metadata' => [
                'placement' => 'page_builder',
                'supports_feature_locked' => true,
                'supports_sales_trigger' => false,
            ],
        ],

        'widgets' => [
            'label' => 'Widgets',
            'feature_key' => 'core',
            'group' => 'Universal Placements',
            'supports_resource_condition' => false,
            'resource_key' => null,
            'metadata' => [
                'placement' => 'widgets',
                'supports_feature_locked' => true,
                'supports_sales_trigger' => false,
            ],
        ],
    ];

    public function __construct()
    {
        /*
         * Register the four central placements first.
         *
         * Existing feature-submitted locations are temporarily retained
         * for backward compatibility while their working render hooks are
         * migrated to these universal placements.
         */
        $this->submit(
            'core_addon_placements',
            self::UNIVERSAL_PLACEMENTS
        );
    }

    /**
     * Request-lifetime submitted trigger locations.
     *
     * [
     *   'page_builder.widgets' => [
     *       'key' => 'page_builder.widgets',
     *       'label' => 'Page Builder Widgets',
     *       'feature_key' => '360_panorama',
     *       'group' => 'Page Builder',
     *       'supports_resource_condition' => false,
     *       'metadata' => [],
     *   ],
     * ]
     */
    protected array $locations = [];

    /**
     * A Core feature/module submits one or more locations that it owns.
     *
     * Example:
     *
     * $registry->submit('360_panorama', [
     *     'page_builder.widgets' => [
     *         'label' => 'Page Builder Widgets',
     *         'group' => 'Page Builder',
     *     ],
     * ]);
     */
    public function submit(
        string $featureKey,
        array $locations
    ): void {
        $featureKey = trim($featureKey);

        if ($featureKey === '') {
            throw new InvalidArgumentException(
                'Sales trigger feature key cannot be empty.'
            );
        }

        foreach ($locations as $locationKey => $definition) {

            if (is_int($locationKey)) {
                $locationKey = is_string($definition)
                    ? $definition
                    : '';
                $definition = [];
            }

            $locationKey = trim((string) $locationKey);

            if ($locationKey === '') {
                throw new InvalidArgumentException(
                    'Sales trigger location key cannot be empty.'
                );
            }

            if (!is_array($definition)) {
                $definition = [];
            }

            /*
             * A location key represents one actual UI/application slot.
             * Multiple Add-ons may later use the same submitted location.
             *
             * If another feature submits the same key, preserve the first
             * owner instead of silently changing ownership.
             */
            if (isset($this->locations[$locationKey])) {
                continue;
            }

            $this->locations[$locationKey] = [
                'key' => $locationKey,

                'label' =>
                    $definition['label']
                    ?? ucwords(
                        str_replace(
                            ['.', '_', '-'],
                            ' ',
                            $locationKey
                        )
                    ),

                'feature_key' => $featureKey,

                'group' =>
                    $definition['group']
                    ?? ucwords(
                        str_replace(
                            ['.', '_', '-'],
                            ' ',
                            $featureKey
                        )
                    ),

                /*
                 * True for measurable limits such as Bandwidth,
                 * Storage, Email Accounts, CRM Clients, etc.
                 *
                 * False for feature-upgrade placements such as
                 * Page Builder widgets or 360 Panorama.
                 */
                'supports_resource_condition' => (bool) (
                    $definition['supports_resource_condition']
                    ?? false
                ),

                /*
                 * Optional capability/resource that naturally belongs
                 * to this location. Admin can still configure the
                 * actual Add-on trigger independently.
                 */
                'resource_key' =>
                    $definition['resource_key']
                    ?? null,

                'metadata' =>
                    is_array($definition['metadata'] ?? null)
                        ? $definition['metadata']
                        : [],
            ];
        }
    }

    /**
     * Return every location submitted by installed/active features.
     */
    public function locations(): array
    {
        return $this->locations;
    }

    /**
     * Return only locations submitted by one feature/module.
     */
    public function locationsForFeature(
        string $featureKey
    ): array {
        return array_filter(
            $this->locations,
            fn (array $location) =>
                ($location['feature_key'] ?? null) === $featureKey
        );
    }

    public function hasLocation(string $key): bool
    {
        return isset($this->locations[$key]);
    }

    public function location(string $key): ?array
    {
        return $this->locations[$key] ?? null;
    }

    public function supportsResourceCondition(
        string $key
    ): bool {
        return (bool) (
            $this->locations[$key]['supports_resource_condition']
            ?? false
        );
    }

    /**
     * Generic conditions available to every submitted location.
     *
     * Individual locations may later restrict these if necessary
     * without changing the database structure.
     */
    public function conditions(): array
    {
        return [
            'always' => 'Always Show',
            'resource_threshold' => 'Resource Usage Threshold',
            'limit_reached' => 'Limit Reached',
            'feature_locked' => 'Feature Locked',
        ];
    }
}
