<?php

namespace App\Services\Core;

use App\Models\Website;
use Closure;
use RuntimeException;

class CoreCapabilityBaseAllocationRegistry
{
    /*
     * ESUBIZ_UNIVERSAL_BASE_ALLOCATION_REGISTRY_V1
     *
     * Central authority for a Core capability's base allocation.
     *
     * Add-ons do NOT define base limits here.
     * They only add finite/unlimited allocations through Function
     * Allocation and product entitlements.
     *
     * Capability owners register their base allocation once.
     * The same contract applies to SaaS and off-server websites.
     */

    protected array $providers = [];

    public function __construct()
    {
        /*
         * Storage
         *
         * Website storage_mb uses 0 as "use Core default".
         * Core default = 1024 MB = 1 GB.
         *
         * Function Allocation uses GB, therefore this provider also
         * returns GB.
         */
        $this->register(
            'storage',
            function (Website $website): int|float|null {
                $storageMb =
                    (int) (
                        $website->storage_mb
                        ?? 0
                    );

                if ($storageMb <= 0) {
                    $storageMb = 1024;
                }

                return $storageMb / 1024;
            }
        );

        /*
         * Bandwidth
         *
         * Website bandwidth_mb uses 0 as "use Core default".
         * Core default = 40960 MB = 40 GB per month.
         *
         * Function Allocation uses GB.
         */
        $this->register(
            'bandwidth',
            function (Website $website): int|float|null {
                $bandwidthMb =
                    (int) (
                        $website->bandwidth_mb
                        ?? 0
                    );

                if ($bandwidthMb <= 0) {
                    $bandwidthMb = 40960;
                }

                return $bandwidthMb / 1024;
            }
        );
    }

    public function register(
        string $capabilityKey,
        Closure $provider
    ): void {
        $capabilityKey =
            trim(
                $capabilityKey
            );

        if ($capabilityKey === '') {
            throw new RuntimeException(
                'Base allocation provider requires a capability key.'
            );
        }

        $this->providers[$capabilityKey] =
            $provider;
    }

    public function has(
        string $capabilityKey
    ): bool {
        return isset(
            $this->providers[$capabilityKey]
        );
    }

    public function allocation(
        string $capabilityKey,
        int $websiteId
    ): int|float|null {
        if (!$this->has($capabilityKey)) {
            throw new RuntimeException(
                "No base allocation provider is registered for "
                . "[{$capabilityKey}]."
            );
        }

        $website =
            Website::query()
                ->find(
                    $websiteId
                );

        if (!$website) {
            throw new RuntimeException(
                "Website [{$websiteId}] could not be resolved."
            );
        }

        $allocation =
            ($this->providers[$capabilityKey])(
                $website
            );

        if ($allocation === null) {
            return null;
        }

        if (!is_numeric($allocation)) {
            throw new RuntimeException(
                "Base allocation provider [{$capabilityKey}] "
                . 'returned an invalid value.'
            );
        }

        return $allocation + 0;
    }

    public function keys(): array
    {
        return array_keys(
            $this->providers
        );
    }
}
