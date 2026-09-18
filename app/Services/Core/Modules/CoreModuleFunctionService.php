<?php

namespace App\Services\Core\Modules;

use App\Services\Core\CoreAdminNavigationRegistry;
use App\Services\Core\CoreFeatureRegistry;

/**
 * ESUBIZ_CORE_MODULE_FUNCTION_SERVICE_V1
 *
 * Supplies existing Core-owned functions for Module configuration.
 *
 * Central Admin can independently toggle which Core functions a module
 * integrates with, without duplicating or deleting the Core feature.
 *
 * UI convention:
 * - left / grey  = inactive
 * - right / blue = active
 *
 * The catalogue is registry-driven so future Core features automatically
 * become available for module configuration.
 */
class CoreModuleFunctionService
{
    public function __construct(
        protected CoreFeatureRegistry $features,
        protected CoreAdminNavigationRegistry $adminNavigation
    ) {
    }

    /**
     * Core-owned functions available for Module configuration.
     */
    public function availableFunctions(): array
    {
        $functions = [];

        foreach ($this->features->features() as $key => $feature) {
            if (($feature['source_type'] ?? 'core') !== 'core') {
                continue;
            }

            $functions[$key] = [
                'key' => $key,
                'label' => (string) ($feature['label'] ?? $key),
                'available' => $this->features->isAvailable($key),
                'has_navigation' => !empty($feature['navigation']),
                'navigation' => $feature['navigation'] ?? null,
                'permissions' => $feature['permissions'] ?? [],
            ];
        }

        return $functions;
    }

    /**
     * Normalize the Core functions selected for a module.
     *
     * Unknown/non-Core feature keys are discarded.
     */
    /**
     * Dynamic Core Admin menu hierarchy available to Module Settings.
     *
     * Each parent menu becomes an Esubiz active/inactive toggle.
     * Its children become individual checkbox selections.
     *
     * This controls integration only. Core permissions, entitlements,
     * Add-on expansions and usage limits remain authoritative.
     */
    public function adminMenus(): array
    {
        return $this->adminNavigation
            ->available()
            ->map(
                function (array $menu) {
                    return [
                        'key' => $menu['key'],
                        'label' => $menu['label'],
                        'feature' => $menu['feature'] ?? null,
                        'permission' => $menu['permission'] ?? null,
                        'children' => collect(
                            $menu['children'] ?? []
                        )
                            /*
                             * Module Management is Core infrastructure.
                             *
                             * A Module must never be allowed to integrate
                             * with, enable, disable or control the Modules
                             * manager itself.
                             */
                            ->reject(
                                fn (array $child) =>
                                    ($child['key'] ?? null)
                                    === 'modules'
                            )
                            ->map(
                                fn (array $child) => [
                                    'key' => $child['key'],
                                    'label' => $child['label'],
                                    'feature' =>
                                        $child['feature'] ?? null,
                                    'permission' =>
                                        $child['permission'] ?? null,
                                ]
                            )
                            ->values()
                            ->all(),
                    ];
                }
            )
            ->values()
            ->all();
    }


    public function normalizeSelection(array $selected): array
    {
        $available = $this->availableFunctions();
        $normalized = [];

        foreach ($selected as $key => $value) {
            if (is_int($key)) {
                $key = (string) $value;
                $enabled = true;
            } else {
                $key = (string) $key;
                $enabled = filter_var(
                    $value,
                    FILTER_VALIDATE_BOOLEAN
                );
            }

            $key = trim($key);

            if (
                $key === ''
                || !isset($available[$key])
            ) {
                continue;
            }

            $normalized[$key] = $enabled;
        }

        return $normalized;
    }

    /**
     * Whether a module configuration has enabled a Core function.
     */
    public function enabled(
        array $configuration,
        string $featureKey
    ): bool {
        $featureKey = trim($featureKey);

        if ($featureKey === '') {
            return false;
        }

        $selected = $this->normalizeSelection(
            $configuration['core_functions'] ?? []
        );

        return ($selected[$featureKey] ?? false) === true;
    }
}
