<?php

namespace App\Services\Core;

/**
 * ESUBIZ_CORE_CRM_FEATURE_REGISTRY_V1
 *
 * Canonical plug-and-play registry for built-in Core CRM functions.
 *
 * Each CRM function owns:
 * - its stable resource key;
 * - its display label;
 * - its tenant usage source;
 * - its User navigation metadata when a real route exists;
 * - its availability state.
 *
 * Core Limits do NOT define CRM functions or usage sources.
 * SaaS limits are applied independently by CoreEntitlementService.
 *
 * Future CRM functions can register here without changing the
 * universal measurement or entitlement engines.
 */
class CoreCrmFeatureRegistry
{
    protected array $features = [];

    public function __construct(
        protected CoreUserNavigationRegistry $navigation,
        protected CoreAddonSalesTriggerRegistry $salesTriggers
    ) {
        $this->bootDefaults();
    }

    protected function bootDefaults(): void
    {
        $this->registerDatabaseCount(
            key: 'clients',
            label: 'Clients',
            table: 'crm_contacts',
            userUrl: '/crm',
            order: 10
        );

        $this->registerDatabaseCount(
            key: 'companies',
            label: 'Companies',
            table: 'crm_companies',
            order: 20
        );

        $this->registerDatabaseCount(
            key: 'leads',
            label: 'Leads',
            table: 'crm_leads',
            userUrl: '/crm',
            order: 30
        );

        $this->registerDatabaseCount(
            key: 'deals',
            label: 'Deals',
            table: 'crm_deals',
            order: 40
        );

        $this->registerDatabaseCount(
            key: 'tasks',
            label: 'Tasks',
            table: 'crm_tasks',
            userUrl: '/crm',
            order: 50
        );

        $this->registerDatabaseCount(
            key: 'notes',
            label: 'Notes',
            table: 'crm_notes',
            order: 60
        );

        $this->registerDatabaseCount(
            key: 'activities',
            label: 'Activities',
            table: 'crm_activities',
            order: 70
        );

        $this->registerDatabaseCount(
            key: 'quotations',
            label: 'Quotations',
            table: 'crm_quotations',
            order: 80
        );
    }

    public function registerDatabaseCount(
        string $key,
        string $label,
        string $table,
        ?string $userUrl = null,
        int $order = 500,
        ?string $permission = null,
        bool $available = true
    ): static {
        $salesTriggerLocation = 'crm.' . $key;

        $this->features[$key] = [
            'key' => $key,
            'label' => $label,
            'source_type' => 'crm',
            'source_key' => 'crm',
            'available' => $available,
            'permission' => $permission,
            'order' => $order,
            'navigation' => [
                'user_url' => $userUrl,
                'order' => $order,
            ],
            'usage' => [
                'driver' => 'database_count',
                'connection' => 'tenant',
                'table' => $table,
            ],
            'sales_trigger_location' => $salesTriggerLocation,
        ];

        /*
         * Each measurable CRM function owns its sales-trigger location.
         * The generic resolver remains product/UI agnostic.
         */
        $this->salesTriggers->submit(
            'crm',
            [
                $salesTriggerLocation => [
                    'label' => $label . ' Usage',
                    'group' => 'CRM',
                    'supports_resource_condition' => true,
                    'resource_key' => $key,
                    'metadata' => [
                        'placement' => 'resource_measurement',
                        'supports_sales_trigger' => true,
                    ],
                ],
            ]
        );

        if ($userUrl !== null && trim($userUrl) !== '') {
            $this->navigation->registerCrm(
                key: $key,
                label: $label,
                url: $userUrl,
                available: $available,
                permission: $permission,
                section: 'CRM',
                order: $order
            );
        }

        return $this;
    }

    public function all(): array
    {
        return array_values($this->features);
    }

    public function available(): array
    {
        return array_values(
            array_filter(
                $this->features,
                static fn (array $feature): bool =>
                    ($feature['available'] ?? false) === true
            )
        );
    }

    public function get(string $key): ?array
    {
        return $this->features[$key] ?? null;
    }

    public function resources(): array
    {
        $resources = [];

        foreach ($this->available() as $feature) {
            $key = (string) ($feature['key'] ?? '');

            if ($key === '' || !is_array($feature['usage'] ?? null)) {
                continue;
            }

            $resources[$key] = [
                'usage' => $feature['usage'],
            ];
        }

        return $resources;
    }
}
