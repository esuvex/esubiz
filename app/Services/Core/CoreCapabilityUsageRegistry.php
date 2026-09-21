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
     * Resolve usage from a Central-configured Core limit.
     *
     * Explicit capability providers remain authoritative. When none exists,
     * metadata may declare a generic usage source so future SaaS limits can
     * be introduced without creating capability-specific entitlement code.
     *
     * Supported metadata:
     *
     * {
     *   "usage": {
     *     "driver": "database_count",
     *     "connection": "tenant",
     *     "table": "pages"
     *   }
     * }
     */
    /**
     * Resolve current usage for any measurable Core resource.
     *
     * Resolution order:
     *
     * 1. Explicit usage provider.
     * 2. Plug-and-play feature registry definition.
     * 3. Core feature resource metadata.
     *
     * Usage measurement belongs to the Core resource itself.
     * Central SaaS limits are independent and affect only the
     * right-hand allowance through CoreEntitlementService.
     */
    public function configuredUsage(
        string $capabilityKey,
        int $websiteId
    ): int|float {
        if ($this->has($capabilityKey)) {
            return $this->usage(
                $capabilityKey,
                $websiteId
            );
        }

        $website = Website::query()->find($websiteId);

        if (!$website) {
            throw new RuntimeException(
                "Website [{$websiteId}] could not be resolved."
            );
        }

        $usage = $this->configuredUsageDefinition(
            $capabilityKey
        );

        $driver = strtolower(
            trim((string) ($usage['driver'] ?? ''))
        );

        if ($driver === '') {
            throw new RuntimeException(
                "Core resource [{$capabilityKey}] has no configured usage source."
            );
        }

        if ($driver !== 'database_count') {
            throw new RuntimeException(
                "Unsupported usage driver [{$driver}] for "
                . "[{$capabilityKey}]."
            );
        }

        $connectionName = strtolower(
            trim((string) ($usage['connection'] ?? 'tenant'))
        );

        $table = trim(
            (string) ($usage['table'] ?? '')
        );

        if (
            $table === ''
            || !preg_match('/^[A-Za-z0-9_]+$/', $table)
        ) {
            throw new RuntimeException(
                "Core resource [{$capabilityKey}] has an invalid usage table."
            );
        }

        if (
            !in_array(
                $connectionName,
                ['tenant', 'website_tenant'],
                true
            )
        ) {
            throw new RuntimeException(
                "Unsupported usage connection [{$connectionName}] for "
                . "[{$capabilityKey}]."
            );
        }

        $tenantDatabase = app(
            \App\Services\Website\WebsiteTenantDatabaseService::class
        );

        $tenantDatabase->connect($website);

        try {
            /*
             * WebsiteTenantDatabaseService is authoritative for the
             * active website database. Historical logical labels
             * tenant / website_tenant both resolve through it.
             */
            $connection = $tenantDatabase->connection();

            if (!$connection->getSchemaBuilder()->hasTable($table)) {
                throw new RuntimeException(
                    "Configured usage table [{$table}] does not exist "
                    . "for [{$capabilityKey}]."
                );
            }

            return (int) $connection
                ->table($table)
                ->count();
        } finally {
            $tenantDatabase->disconnect();
        }
    }

    /**
     * Resolve the feature-owned usage definition for a Core resource.
     */
    protected function configuredUsageDefinition(
        string $capabilityKey
    ): array {
        /*
         * CRM functions are plug-and-play and own their measurement
         * definitions through CoreCrmFeatureRegistry.
         */
        try {
            $crmFeature = app(
                \App\Services\Core\CoreCrmFeatureRegistry::class
            )->get($capabilityKey);

            if (
                is_array($crmFeature)
                && is_array($crmFeature['usage'] ?? null)
            ) {
                return $crmFeature['usage'];
            }
        } catch (\Throwable $e) {
            /*
             * A registry failure must not prevent other Core resources
             * from resolving through their own metadata.
             */
        }

        /*
         * Direct feature:
         *
         * pages -> Pages
         * forms -> Forms
         * users -> Users
         */
        $feature = \Illuminate\Support\Facades\DB::table(
            'core_features'
        )
            ->where('key', $capabilityKey)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        /*
         * Direct feature resource.
         */
        if ($feature) {
            $metadata = $feature->metadata
                ? json_decode($feature->metadata, true)
                : [];

            $resources = is_array($metadata)
                ? ($metadata['resources'] ?? [])
                : [];

            $usage = is_array($resources)
                ? ($resources[$capabilityKey]['usage'] ?? [])
                : [];

            if (is_array($usage) && !empty($usage)) {
                return $usage;
            }
        }

        /*
         * Child resource discovery is independent of Central limits.
         *
         * Search active Core feature resource maps directly so a built
         * resource can resolve usage and display Unlimited before any
         * SaaS limit has ever been created for it.
         */
        $features = \Illuminate\Support\Facades\DB::table(
            'core_features'
        )
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->select(['key', 'metadata'])
            ->get();

        foreach ($features as $candidate) {
            $metadata = $candidate->metadata
                ? json_decode($candidate->metadata, true)
                : [];

            $resources = is_array($metadata)
                ? ($metadata['resources'] ?? [])
                : [];

            if (!is_array($resources)) {
                continue;
            }

            $usage = $resources[$capabilityKey]['usage'] ?? [];

            if (is_array($usage) && !empty($usage)) {
                return $usage;
            }
        }

        return [];
    }

    /**
     * Determine whether current usage can be resolved without depending
     * on the existence of a Central SaaS limit.
     */
    public function canResolve(
        string $capabilityKey
    ): bool {
        if ($this->has($capabilityKey)) {
            return true;
        }

        try {
            $usage = $this->configuredUsageDefinition(
                $capabilityKey
            );

            return !empty(
                trim((string) ($usage['driver'] ?? ''))
            );
        } catch (\Throwable $e) {
            return false;
        }
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
