<?php

namespace App\Services\Core\Modules;

use App\Services\Core\Installer\CoreInstalledProductRegistry;
use App\Services\Marketplace\Modules\ModuleMarketplaceResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * ESUBIZ_CORE_MODULE_MARKETPLACE_CATALOG_SERVICE_V1
 *
 * Universal Core Module Marketplace catalog adapter.
 *
 * SaaS:
 *   Reads the authoritative Central Esubiz Module catalog locally.
 *
 * Off-server:
 *   Reads the authoritative Central Module catalog through its API.
 *
 * Installation state always belongs to the local Core registry.
 */
class ModuleMarketplaceCatalogService
{
    public function __construct(
        protected ModuleMarketplaceResolver $resolver,
        protected CoreInstalledProductRegistry $installedProducts
    ) {
    }

    public function modules(
        ?string $deployment = null,
        ?int $websiteId = null
    ): Collection {
        $deployment = $deployment ?: $this->deployment();

        if (
            $deployment
            === ModuleMarketplaceResolver::DEPLOYMENT_SAAS
        ) {
            return $this->withInstallationState(
                $this->saasModules(),
                $websiteId
            );
        }

        if (
            $deployment
            === ModuleMarketplaceResolver::DEPLOYMENT_OFF_SERVER
        ) {
            return $this->withInstallationState(
                $this->offServerModules(),
                $websiteId
            );
        }

        throw new RuntimeException(
            'Unsupported Core Module Marketplace deployment: '
            . $deployment
        );
    }

    protected function saasModules(): Collection
    {
        return $this->resolver
            ->forDeployment(
                ModuleMarketplaceResolver::DEPLOYMENT_SAAS
            )
            ->map(
                fn (object $module) =>
                    $this->normalizeDatabaseModule(
                        $module,
                        ModuleMarketplaceResolver::DEPLOYMENT_SAAS
                    )
            )
            ->values();
    }

    protected function offServerModules(): Collection
    {
        $url =
            rtrim(
                (string) config(
                    'services.esubiz.marketplace_url',
                    config('app.url')
                ),
                '/'
            )
            . '/marketplace/modules/catalog';

        $response = Http::timeout(15)
            ->acceptJson()
            ->get(
                $url,
                [
                    'deployment' =>
                        ModuleMarketplaceResolver::DEPLOYMENT_OFF_SERVER,
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Unable to load the Esubiz Module Marketplace catalog.'
            );
        }

        $payload = $response->json();

        if (
            !is_array($payload)
            || !($payload['ok'] ?? false)
            || !is_array($payload['modules'] ?? null)
        ) {
            throw new RuntimeException(
                'Invalid Esubiz Module Marketplace catalog response.'
            );
        }

        return collect($payload['modules'])
            ->map(
                fn (array $module) =>
                    $this->normalizeApiModule($module)
            )
            ->values();
    }

    protected function normalizeDatabaseModule(
        object $module,
        string $deployment
    ): array {
        return [
            'id' => (int) $module->id,
            'uuid' => $module->uuid ?? null,
            'name' => $module->name ?? null,
            'slug' => $module->slug ?? null,
            'version' => $module->version ?? null,
            'description' => $module->description ?? null,

            'deployment' => $deployment,

            'price' => $this->resolver->price(
                $module,
                $deployment
            ),

            'currency' => $this->resolver->currency(
                $module,
                $deployment
            ),

            'billing' => $this->resolver->billing(
                $module,
                $deployment
            ),

            'marketplace' => [
                'featured' => (bool) (
                    $module->marketplace_featured
                    ?? false
                ),
                'categories' =>
                    $this->resolver->categories(
                        $module
                    ),
            ],

            /*
             * Preserve Central-owned Module configuration so the
             * installed runtime can later receive compatible website
             * types, sidebar parent menu name and Core integrations.
             */
            'metadata' =>
                $this->decodeJson(
                    $module->metadata
                    ?? null
                ),
        ];
    }

    protected function normalizeApiModule(
        array $module
    ): array {
        return [
            'id' => (int) ($module['id'] ?? 0),
            'uuid' => $module['uuid'] ?? null,
            'name' => $module['name'] ?? null,
            'slug' => $module['slug'] ?? null,
            'version' => $module['version'] ?? null,
            'description' =>
                $module['description'] ?? null,

            'deployment' =>
                $module['deployment']
                ?? ModuleMarketplaceResolver::DEPLOYMENT_OFF_SERVER,

            'price' => $module['price'] ?? null,
            'currency' => $module['currency'] ?? config(
                'platform.currency.primary',
                ''
            ),
            'billing' =>
                is_array($module['billing'] ?? null)
                    ? $module['billing']
                    : null,

            'marketplace' =>
                is_array($module['marketplace'] ?? null)
                    ? $module['marketplace']
                    : [],

            'metadata' =>
                is_array($module['metadata'] ?? null)
                    ? $module['metadata']
                    : [],
        ];
    }

    public function deployment(): string
    {
        $deployment = strtolower(
            trim(
                (string) config(
                    'services.esubiz.deployment',
                    env(
                        'ESUBIZ_DEPLOYMENT',
                        ModuleMarketplaceResolver::DEPLOYMENT_SAAS
                    )
                )
            )
        );

        return $deployment
            === ModuleMarketplaceResolver::DEPLOYMENT_OFF_SERVER
                ? ModuleMarketplaceResolver::DEPLOYMENT_OFF_SERVER
                : ModuleMarketplaceResolver::DEPLOYMENT_SAAS;
    }

    protected function withInstallationState(
        Collection $modules,
        ?int $websiteId
    ): Collection {
        return $modules
            ->map(
                function (array $module) use ($websiteId) {
                    $installed =
                        $this->installedProducts->isInstalled(
                            'module',
                            (string) ($module['slug'] ?? ''),
                            $module['version'] ?? null,
                            $websiteId
                        );

                    $module['installed'] = $installed;
                    $module['can_purchase'] = !$installed;
                    $module['purchase_status'] =
                        $installed
                            ? 'installed'
                            : 'available';

                    return $module;
                }
            )
            ->values();
    }

    protected function decodeJson(
        mixed $value
    ): array {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded)
            ? $decoded
            : [];
    }
}
