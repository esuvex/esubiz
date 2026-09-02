<?php

namespace App\Services\Core;

/**
 * ESUBIZ_CORE_USER_NAVIGATION_REGISTRY_V1
 *
 * Universal Core frontend/user navigation registry.
 *
 * This is intentionally separate from the technical/admin sidebar.
 *
 * Core features, CRM components, Add-ons, Bundles, Modules,
 * Themes, Products and other site functions may register
 * USER-FACING navigation here.
 *
 * Registration does NOT mean availability.
 *
 * A non-Core feature must explicitly declare available=true
 * before it can appear.
 *
 * Feature owners must provide their real Core-local URL.
 * This registry does not invent routes.
 */
class CoreUserNavigationRegistry
{
    /**
     * Request-lifetime registry.
     *
     * Static storage lets independently booted Core products/modules
     * register items without requiring a deployment-specific branch.
     */
    protected static array $items = [];

    protected static bool $defaultsBooted = false;

    public function __construct()
    {
        $this->bootDefaults();
    }

    protected function bootDefaults(): void
    {
        if (static::$defaultsBooted) {
            return;
        }

        static::$defaultsBooted = true;

        $this->register([
            'key' => 'core.user.dashboard',
            'label' => 'Dashboard',
            'url' => '/user/dashboard',
            'section' => 'Account',
            'source_type' => 'core',
            'source_key' => 'user_dashboard',
            'available' => true,
            'order' => 10,
            'icon' => 'D',
        ]);

        $this->register([
            'key' => 'core.user.website',
            'label' => 'Visit Website',
            'url' => '/',
            'section' => 'Account',
            'source_type' => 'core',
            'source_key' => 'website',
            'available' => true,
            'order' => 900,
            'icon' => 'W',
        ]);
    }

    public function register(array $item): static
    {
        $key = trim((string) ($item['key'] ?? ''));

        if ($key === '') {
            return $this;
        }

        static::$items[$key] = array_merge([
            'key' => $key,
            'label' => $key,
            'url' => null,
            'section' => 'Workspace',
            'source_type' => 'site_function',
            'source_key' => null,

            // IMPORTANT:
            // Registered != installed/available.
            'available' => false,

            // Optional Core RBAC gate.
            'permission' => null,

            'order' => 500,
            'icon' => null,
        ], $item, [
            'key' => $key,
        ]);

        return $this;
    }

    public function registerMany(array $items): static
    {
        foreach ($items as $item) {
            if (is_array($item)) {
                $this->register($item);
            }
        }

        return $this;
    }

    /**
     * Register a user-facing CRM feature.
     *
     * Examples of feature owners that may use this:
     * invoices, contracts, tasks, receipts, projects,
     * estimates, quotations and future CRM user functions.
     *
     * Exact URLs remain owned by the actual CRM feature.
     */
    public function registerCrm(
        string $key,
        string $label,
        string $url,
        bool $available,
        ?string $permission = null,
        string $section = 'My Business',
        int $order = 500,
        ?string $icon = null
    ): static {
        return $this->register([
            'key' => $key,
            'label' => $label,
            'url' => $url,
            'section' => $section,
            'source_type' => 'crm',
            'source_key' => $key,
            'available' => $available,
            'permission' => $permission,
            'order' => $order,
            'icon' => $icon,
        ]);
    }

    /**
     * Register a user-facing activated Module feature.
     *
     * Modules supply their OWN real name and route.
     * There is no generic "Module Manager" user entry.
     */
    public function registerModule(
        string $moduleKey,
        string $key,
        string $label,
        string $url,
        bool $available,
        ?string $permission = null,
        string $section = 'Services',
        int $order = 500,
        ?string $icon = null
    ): static {
        return $this->register([
            'key' => $key,
            'label' => $label,
            'url' => $url,
            'section' => $section,
            'source_type' => 'module',
            'source_key' => $moduleKey,
            'available' => $available,
            'permission' => $permission,
            'order' => $order,
            'icon' => $icon,
        ]);
    }

    /**
     * Generic hook for future Add-ons/Bundles/Themes/Products/
     * site functions that expose something to the Core user.
     */
    public function registerFeature(
        string $sourceType,
        string $sourceKey,
        array $item
    ): static {
        $allowedSources = [
            'core',
            'crm',
            'addon',
            'bundle',
            'theme',
            'module',
            'product',
            'site_function',
        ];

        if (!in_array($sourceType, $allowedSources, true)) {
            $sourceType = 'site_function';
        }

        $item['source_type'] = $sourceType;
        $item['source_key'] = $sourceKey;

        return $this->register($item);
    }

    public function all(): array
    {
        return array_values(static::$items);
    }

    public function available(): array
    {
        $permissionService = null;

        try {
            $permissionService = app(
                \App\Services\Core\CorePermissionService::class
            );
        } catch (\Throwable $e) {
            $permissionService = null;
        }

        $items = array_filter(
            static::$items,
            function (array $item) use ($permissionService): bool {
                if (($item['available'] ?? false) !== true) {
                    return false;
                }

                $url = trim((string) ($item['url'] ?? ''));

                if ($url === '' || $url === '#') {
                    return false;
                }

                $permission = trim(
                    (string) ($item['permission'] ?? '')
                );

                if (
                    $permission !== ''
                    && $permissionService
                    && method_exists($permissionService, 'can')
                    && !$permissionService->can($permission)
                ) {
                    return false;
                }

                return true;
            }
        );

        usort(
            $items,
            static function (array $a, array $b): int {
                $sectionCompare = strcmp(
                    (string) ($a['section'] ?? ''),
                    (string) ($b['section'] ?? '')
                );

                if ($sectionCompare !== 0) {
                    return $sectionCompare;
                }

                $orderCompare =
                    ((int) ($a['order'] ?? 500))
                    <=>
                    ((int) ($b['order'] ?? 500));

                if ($orderCompare !== 0) {
                    return $orderCompare;
                }

                return strcmp(
                    (string) ($a['label'] ?? ''),
                    (string) ($b['label'] ?? '')
                );
            }
        );

        return array_values($items);
    }

    public function grouped(): array
    {
        $groups = [];

        foreach ($this->available() as $item) {
            $section = trim(
                (string) ($item['section'] ?? 'Workspace')
            ) ?: 'Workspace';

            $groups[$section][] = $item;
        }

        return $groups;
    }
}
