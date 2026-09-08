<?php

namespace App\Services\Core\Installer\Handlers;

use App\Services\Core\Installer\Contracts\CoreProductInstallerHandler;
use App\Services\Core\Installer\CoreInstalledProductRegistry;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * ESUBIZ_CORE_MODULE_INSTALLER_V1
 *
 * Universal Module installer for SaaS-hosted and off-server Esubiz Core.
 *
 * This handler receives only packages that have already passed entitlement,
 * license, identity, checksum and staging validation.
 */
/**
 * ESUBIZ_CORE_MODULE_PERSISTENT_REGISTRY_V3
 *
 * Module installation and enablement are persisted through
 * CoreInstalledProductRegistry.
 */
class ModuleInstallerHandler implements CoreProductInstallerHandler
{
    public function __construct(
        protected CoreInstalledProductRegistry $registry
    ) {
    }

    public function productType(): string
    {
        return 'module';
    }

    public function startUrl(
        array $manifest,
        array $installation = [],
        array $context = []
    ): ?string {
        foreach ([
            $context['start_url'] ?? null,
            $context['return_url'] ?? null,
            $installation['start_url'] ?? null,
            $manifest['start_url'] ?? null,
            $manifest['admin_url'] ?? null,
        ] as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    public function validate(
        string $extractedPath,
        array $manifest,
        array $context = []
    ): void {
        $identity = $this->identity($manifest);

        if ($identity['slug'] === '' || $identity['version'] === '') {
            throw new RuntimeException(
                'Module package requires a valid slug and version.'
            );
        }

        if (!is_dir($extractedPath)) {
            throw new RuntimeException(
                'Validated Module staging directory does not exist.'
            );
        }
    }

    public function install(
        string $extractedPath,
        array $manifest,
        array $context = []
    ): array {
        $this->validate($extractedPath, $manifest, $context);

        $identity = $this->identity($manifest);
        $root = $this->moduleRoot($context);
        $destination = $root . DIRECTORY_SEPARATOR . $identity['slug'];

        File::ensureDirectoryExists($root);

        $backup = null;

        if (is_dir($destination)) {
            $backup = $destination
                . '.esubiz-backup-'
                . date('YmdHis')
                . '-'
                . bin2hex(random_bytes(3));

            if (!@rename($destination, $backup)) {
                throw new RuntimeException(
                    "Existing Module [{$identity['slug']}] could not be staged for replacement."
                );
            }
        }

        try {
            File::copyDirectory($extractedPath, $destination);

            if (!is_dir($destination)) {
                throw new RuntimeException(
                    "Module [{$identity['slug']}] could not be installed."
                );
            }

            if ($backup && is_dir($backup)) {
                File::deleteDirectory($backup);
            }

            $registered = $this->registry->registerInstalled(
                'module',
                $identity['slug'],
                $identity['version'],
                [
                    'name' => $identity['name'],
                    'install_path' => $destination,
                    'entitlement_id' => $context['entitlement_id'] ?? null,
                    'license_id' => $context['license_id'] ?? null,
                    'catalog_product_id' => $context['catalog_product_id'] ?? null,
                    'marketplace_listing_id' => $context['marketplace_listing_id'] ?? null,
                    'checksum_sha256' =>
                        $context['checksum_sha256']
                        ?? $manifest['checksum_sha256']
                        ?? null,
                    'metadata' => [
                        'source' => $context['source'] ?? null,
                        'deployment' =>
                            $context['deployment']
                            ?? $context['deployment_type']
                            ?? null,
                        'bundle_slug' => $context['bundle_slug'] ?? null,
                        'bundle_version' => $context['bundle_version'] ?? null,
                    ],
                ]
            );

            return [
                'product_type' => 'module',
                'slug' => $identity['slug'],
                'version' => $identity['version'],
                'name' => $identity['name'],
                'install_path' => $destination,
                'enabled' => false,
                'registry' => $registered,
                'entitlement_id' => $context['entitlement_id'] ?? null,
                'license_id' => $context['license_id'] ?? null,
            ];
        } catch (\Throwable $e) {
            if (is_dir($destination)) {
                File::deleteDirectory($destination);
            }

            if ($backup && is_dir($backup)) {
                @rename($backup, $destination);
            }

            throw $e;
        }
    }

    public function enable(
        array $manifest,
        array $installation,
        array $context = []
    ): array {
        $identity = $this->identity($manifest);

        $registered = $this->registry->enable(
            'module',
            $identity['slug'],
            $identity['version']
        );

        return [
            'product_type' => 'module',
            'slug' => $identity['slug'],
            'version' => $identity['version'],
            'enabled' => true,
            'install_path' =>
                $installation['install_path']
                ?? $registered['install_path']
                ?? null,
            'registry' => $registered,
            'start_url' => $this->startUrl(
                $manifest,
                $installation,
                $context
            ),
        ];
    }

    public function update(
        string $extractedPath,
        array $manifest,
        array $context = []
    ): array {
        $installation = $this->install(
            $extractedPath,
            $manifest,
            $context
        );

        $enablement = $this->enable(
            $manifest,
            $installation,
            $context
        );

        return [
            'product_type' => 'module',
            'updated' => true,
            'installation' => $installation,
            'enablement' => $enablement,
        ];
    }

    /**
     * ESUBIZ_CORE_MODULE_REGISTRY_ROLLBACK_V4
     *
     * Roll back both filesystem state and persistent installed-product state.
     */
    public function rollback(array $context = []): void
    {
        $slug = trim((string) ($context['product_slug'] ?? ''));
        $version = trim((string) ($context['product_version'] ?? ''));

        if ($slug === '') {
            return;
        }

        if ($version !== '') {
            $record = $this->registry->find(
                'module',
                $slug,
                $version
            );

            if ($record) {
                if ((bool) ($record->is_enabled ?? false)) {
                    $this->registry->disable(
                        'module',
                        $slug,
                        $version
                    );
                }

                $this->registry->remove(
                    'module',
                    $slug,
                    $version
                );
            }
        }

        $root = $context['module_root']
            ?? base_path('modules');

        $target = rtrim((string) $root, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $slug;

        if (is_dir($target)) {
            $this->deleteDirectory($target);
        }
    }

    protected function identity(array $manifest): array
    {
        $slug = strtolower(
            trim(
                (string) (
                    $manifest['slug']
                    ?? $manifest['product_slug']
                    ?? $manifest['module_slug']
                    ?? ''
                )
            )
        );

        $version = trim(
            (string) (
                $manifest['version']
                    ?? $manifest['product_version']
                    ?? $manifest['module_version']
                    ?? ''
            )
        );

        $name = trim(
            (string) (
                $manifest['name']
                    ?? $manifest['product_name']
                    ?? $slug
            )
        );

        return [
            'slug' => $slug,
            'version' => $version,
            'name' => $name !== '' ? $name : $slug,
        ];
    }

    protected function moduleRoot(array $context): string
    {
        $root = $context['module_root'] ?? null;

        if (is_string($root) && trim($root) !== '') {
            return rtrim($root, DIRECTORY_SEPARATOR);
        }

        return base_path('modules');
    }
}
