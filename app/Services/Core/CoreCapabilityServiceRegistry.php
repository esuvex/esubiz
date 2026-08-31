<?php

namespace App\Services\Core;

use App\Models\Website;
use Closure;
use RuntimeException;

class CoreCapabilityServiceRegistry
{
    /*
     * ESUBIZ_UNIVERSAL_SERVICE_AUTHORITY_REGISTRY_V1
     *
     * Central authority for service-family capabilities.
     *
     * Core/platform service owners register their availability authority
     * exactly once. Add-ons never register service providers.
     *
     * The same contract is used by SaaS and authenticated off-server
     * websites after central website identity has been resolved.
     */

    protected array $providers = [];

    protected array $familyProviders = [];

    /**
     * Register or replace an exact service capability authority.
     *
     * Provider signature:
     *   function (Website $website): bool
     */
    public function register(
        string $capabilityKey,
        Closure $provider
    ): void {
        $capabilityKey = trim($capabilityKey);

        if ($capabilityKey === '') {
            throw new RuntimeException(
                'Service capability provider requires a capability key.'
            );
        }

        $this->providers[$capabilityKey] = $provider;
    }

    /**
     * Register or replace a family-wide service authority.
     *
     * Provider signature:
     *   function (Website $website, string $capabilityKey): bool
     */
    public function registerFamily(
        string $family,
        Closure $provider
    ): void {
        $family = strtolower(trim($family));

        if ($family === '') {
            throw new RuntimeException(
                'Service family provider requires a family.'
            );
        }

        $this->familyProviders[$family] = $provider;
    }

    public function has(
        string $capabilityKey
    ): bool {
        return isset(
            $this->providers[$capabilityKey]
        );
    }

    public function hasFamily(
        string $family
    ): bool {
        return isset(
            $this->familyProviders[
                strtolower(trim($family))
            ]
        );
    }

    /**
     * Resolve whether one service capability is available.
     *
     * Exact capability authority wins over family authority.
     * Missing authority fails closed.
     */
    public function allows(
        string $capabilityKey,
        int $websiteId,
        string $family = 'service'
    ): bool {
        $website =
            Website::query()
                ->find($websiteId);

        if (!$website) {
            throw new RuntimeException(
                "Website [{$websiteId}] could not be resolved."
            );
        }

        if ($this->has($capabilityKey)) {
            $allowed =
                ($this->providers[$capabilityKey])(
                    $website
                );

            if (!is_bool($allowed)) {
                throw new RuntimeException(
                    "Service provider [{$capabilityKey}] "
                    . 'returned a non-boolean value.'
                );
            }

            return $allowed;
        }

        $family = strtolower(trim($family));

        if ($this->hasFamily($family)) {
            $allowed =
                ($this->familyProviders[$family])(
                    $website,
                    $capabilityKey
                );

            if (!is_bool($allowed)) {
                throw new RuntimeException(
                    "Service family provider [{$family}] "
                    . 'returned a non-boolean value.'
                );
            }

            return $allowed;
        }

        throw new RuntimeException(
            "No service authority is registered for "
            . "[{$capabilityKey}]."
        );
    }

    public function keys(): array
    {
        return array_keys(
            $this->providers
        );
    }

    public function families(): array
    {
        return array_keys(
            $this->familyProviders
        );
    }
}
