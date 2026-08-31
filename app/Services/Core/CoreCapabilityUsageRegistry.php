<?php

namespace App\Services\Core;

use App\Models\Website;
use App\Services\Website\TenantBandwidthUsageService;
use App\Services\Website\WebsiteResourceUsageService;
use Closure;
use RuntimeException;

class CoreCapabilityUsageRegistry
{
    /*
     * ESUBIZ_UNIVERSAL_CAPABILITY_USAGE_REGISTRY_V1
     *
     * Central usage authority for allocation-backed Core capabilities.
     *
     * Core capability/resource owners register how their current usage
     * is measured exactly once.
     *
     * Add-ons NEVER register usage providers.
     *
     * Add-ons only grant additional/unlimited allocations through the
     * existing Function Allocation / entitlement architecture.
     *
     * The same contract is used by SaaS and off-server websites.
     */

    protected array $providers = [];

    /*
     * ESUBIZ_UNIVERSAL_USAGE_FAMILY_PROVIDERS_V1
     *
     * Family providers allow a Core implementation to own usage
     * measurement for an entire entitlement family without requiring
     * Add-on-specific providers.
     *
     * Example:
     * accounts -> future central account authority
     *
     * Exact capability providers remain authoritative when registered.
     */
    protected array $familyProviders = [];

    public function __construct()
    {
        /*
         * Existing Core resource meters.
         *
         * These are capability implementations, not Add-on-specific
         * enforcement rules.
         */
        $this->register(
            'storage',
            function (Website $website): int|float {
                $usage =
                    app(
                        WebsiteResourceUsageService::class
                    )->storage(
                        $website
                    );

                /*
                 * ESUBIZ_CAPABILITY_USAGE_UNIT_NORMALIZATION_V1
                 *
                 * Function Allocation stores Storage in GB.
                 * The existing storage meter reports MB.
                 *
                 * Normalize only at the capability-provider boundary;
                 * the entitlement engine itself remains unit-agnostic.
                 */
                $usedMb =
                    (float) (
                        $usage['used_mb']
                        ?? 0
                    );

                return $usedMb / 1024;
            }
        );

        $this->register(
            'bandwidth',
            function (Website $website): int|float {
                $usage =
                    app(
                        TenantBandwidthUsageService::class
                    )->currentMonth(
                        $website
                    );

                /*
                 * The existing bandwidth meter reports MB while
                 * Function Allocation stores Bandwidth in GB.
                 *
                 * Prefer raw usage rather than percentage so purchased
                 * Add-on allocations extend the actual effective limit.
                 */
                foreach (
                    [
                        'used_mb',
                        'usage_mb',
                        'transferred_mb',
                    ]
                    as $key
                ) {
                    if (
                        array_key_exists($key, $usage)
                        && is_numeric($usage[$key])
                    ) {
                        /*
                         * Function Allocation stores Bandwidth in GB.
                         * Existing bandwidth meter reports MB.
                         */
                        return (
                            (float) $usage[$key]
                        ) / 1024;
                    }
                }

                /*
                 * Do not silently convert percentage into allocation units.
                 */
                throw new RuntimeException(
                    'Bandwidth meter did not provide usage in MB.'
                );
            }
        );
    }

    /**
     * Register or replace the usage authority for a capability.
     *
     * Future Core modules may call this from their service provider.
     */
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
                'Capability usage provider requires a capability key.'
            );
        }

        $this->providers[$capabilityKey] =
            $provider;
    }

    /**
     * Register or replace one trusted usage authority for an entire
     * entitlement family.
     *
     * Provider signature:
     *   function (Website $website, string $capabilityKey): int|float
     *
     * Core implementations register here. Add-ons never do.
     */
    public function registerFamily(
        string $family,
        Closure $provider
    ): void {
        $family =
            strtolower(
                trim(
                    $family
                )
            );

        if ($family === '') {
            throw new RuntimeException(
                'Capability usage family provider requires a family.'
            );
        }

        $this->familyProviders[$family] =
            $provider;
    }

    public function hasFamily(
        string $family
    ): bool {
        return isset(
            $this->familyProviders[
                strtolower(
                    trim(
                        $family
                    )
                )
            ]
        );
    }

    public function has(
        string $capabilityKey
    ): bool {
        return isset(
            $this->providers[$capabilityKey]
        );
    }

    /**
     * Resolve current usage for one website/capability.
     */
    public function usage(
        string $capabilityKey,
        int $websiteId
    ): int|float {
        if (!$this->has($capabilityKey)) {
            throw new RuntimeException(
                "No usage provider is registered for "
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

        $usage =
            ($this->providers[$capabilityKey])(
                $website
            );

        if (!is_numeric($usage)) {
            throw new RuntimeException(
                "Usage provider [{$capabilityKey}] "
                . 'returned a non-numeric value.'
            );
        }

        return $usage + 0;
    }

    /**
     * Resolve usage through a family authority.
     *
     * This remains fail-closed when the Core implementation has not
     * registered a trusted provider.
     */
    public function familyUsage(
        string $family,
        string $capabilityKey,
        int $websiteId
    ): int|float {
        $family =
            strtolower(
                trim(
                    $family
                )
            );

        if (!$this->hasFamily($family)) {
            throw new RuntimeException(
                "No usage provider is registered for family "
                . "[{$family}]."
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

        $usage =
            ($this->familyProviders[$family])(
                $website,
                $capabilityKey
            );

        if (!is_numeric($usage)) {
            throw new RuntimeException(
                "Usage family provider [{$family}] "
                . 'returned a non-numeric value.'
            );
        }

        return $usage + 0;
    }

    /**
     * Return registered usage families.
     */
    public function families(): array
    {
        return array_keys(
            $this->familyProviders
        );
    }

    /**
     * Return registered capability keys.
     */
    public function keys(): array
    {
        return array_keys(
            $this->providers
        );
    }
}
