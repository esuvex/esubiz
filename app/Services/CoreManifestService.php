<?php

namespace App\Services;

class CoreManifestService
{
    /**
     * Return the complete Esubiz Core manifest.
     */
    public function manifest(): array
    {
        return [
            'type' => config('esubiz_core.type'),
            'name' => config('esubiz_core.name'),
            'version' => config('esubiz_core.version'),

            'capabilities' => config(
                'esubiz_core.capabilities',
                []
            ),

            'resources' => config(
                'esubiz_core.resources',
                []
            ),

            'unlimited' => config(
                'esubiz_core.unlimited',
                []
            ),

            'defaults' => config(
                'esubiz_core.defaults',
                []
            ),
        ];
    }

    /**
     * Get a specific Core capability.
     */
    public function capability(string $key): ?array
    {
        return config(
            'esubiz_core.capabilities.' . $key
        );
    }

    /**
     * Get a specific Core resource entitlement.
     */
    public function resource(string $key): ?array
    {
        return config(
            'esubiz_core.resources.' . $key
        );
    }

    /**
     * Determine whether a resource is unlimited in Core.
     */
    public function isUnlimited(string $resource): bool
    {
        return in_array(
            $resource,
            config('esubiz_core.unlimited', []),
            true
        );
    }

    /**
     * Get Core defaults.
     */
    public function defaults(): array
    {
        return config(
            'esubiz_core.defaults',
            []
        );
    }
}
