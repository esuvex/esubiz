<?php

namespace App\Services\Core;

use Illuminate\Support\Collection;

/**
 * ESUBIZ_CORE_ADMIN_NAVIGATION_REGISTRY_V1
 *
 * Shared registry describing Core Admin sidebar capabilities.
 *
 * IMPORTANT:
 * - This does NOT create another sidebar.
 * - The existing Core sidebar remains the renderer.
 * - Site Management -> Modules remains unchanged.
 *
 * This registry gives Modules and other integrations a structured,
 * dynamic representation of Core admin menus and their submenu items.
 *
 * Modules may select which available Core functions they integrate with,
 * but this registry never grants permissions, increases allowances or
 * bypasses Core/Add-on entitlement rules.
 */
class CoreAdminNavigationRegistry
{
    protected static array $menus = [];

    public function register(
        string $key,
        array $definition
    ): void {
        $key = trim($key);

        if ($key === '') {
            return;
        }

        $children = collect(
            $definition['children'] ?? []
        )
            ->map(
                function ($child, $childKey) {
                    if (!is_array($child)) {
                        return null;
                    }

                    $key = is_string($childKey)
                        ? trim($childKey)
                        : trim(
                            (string) (
                                $child['key']
                                ?? ''
                            )
                        );

                    if ($key === '') {
                        return null;
                    }

                    return array_merge(
                        [
                            'key' => $key,
                            'label' => $key,
                            'permission' => null,
                            'feature' => null,
                            'available' => true,
                            'order' => 100,
                        ],
                        $child,
                        [
                            'key' => $key,
                        ]
                    );
                }
            )
            ->filter()
            ->sortBy(
                fn (array $child) => [
                    (int) ($child['order'] ?? 100),
                    strtolower(
                        (string) ($child['label'] ?? '')
                    ),
                ]
            )
            ->values()
            ->all();

        static::$menus[$key] = array_merge(
            [
                'key' => $key,
                'label' => $key,
                'permission' => null,
                'feature' => null,
                'available' => true,
                'order' => 100,
                'children' => [],
            ],
            $definition,
            [
                'key' => $key,
                'children' => $children,
            ]
        );
    }

    public function all(): Collection
    {
        return collect(static::$menus)
            ->sortBy(
                fn (array $menu) => [
                    (int) ($menu['order'] ?? 100),
                    strtolower(
                        (string) ($menu['label'] ?? '')
                    ),
                ]
            )
            ->values();
    }

    public function available(): Collection
    {
        return $this->all()
            ->filter(
                fn (array $menu) =>
                    $this->resolveAvailability(
                        $menu['available'] ?? true
                    )
            )
            ->map(
                function (array $menu) {
                    $menu['children'] = collect(
                        $menu['children'] ?? []
                    )
                        ->filter(
                            fn (array $child) =>
                                $this->resolveAvailability(
                                    $child['available'] ?? true
                                )
                        )
                        ->values()
                        ->all();

                    return $menu;
                }
            )
            ->values();
    }

    public function get(
        string $key
    ): ?array {
        return static::$menus[$key] ?? null;
    }

    public function has(
        string $key
    ): bool {
        return isset(static::$menus[$key]);
    }

    protected function resolveAvailability(
        mixed $availability
    ): bool {
        if (is_callable($availability)) {
            return (bool) $availability();
        }

        return (bool) $availability;
    }
}
