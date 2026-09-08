<?php

namespace App\Services\Core\Installer\Handlers;

use App\Services\Core\Installer\Contracts\CoreProductInstallerHandler;
use App\Services\Core\Themes\InstalledThemeRegistry;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * ESUBIZ_CORE_THEME_INSTALLER_V1
 *
 * Universal Theme installer used by both:
 *
 * - Esubiz-hosted SaaS Core websites;
 * - off-server Esubiz Core websites.
 *
 * Package validation, entitlement/license validation, checksum validation,
 * extraction and staging MUST happen before this handler is called.
 */
class ThemeInstallerHandler implements CoreProductInstallerHandler
{
    public function __construct(
        protected InstalledThemeRegistry $themes
    ) {
    }

    /*
     * ESUBIZ_CORE_THEME_INSTALLER_CONTRACT_V2
     *
     * Complete the universal CoreProductInstallerHandler identity and
     * post-install navigation contract.
     */
    public function productType(): string
    {
        return 'theme';
    }

    public function startUrl(
        array $manifest,
        array $installation = [],
        array $context = []
    ): ?string {
        $candidates = [
            $context['start_url'] ?? null,
            $context['return_url'] ?? null,
            $installation['start_url'] ?? null,
            $manifest['start_url'] ?? null,
            $manifest['preview_url'] ?? null,
            $manifest['preview_path'] ?? null,
            $manifest['preview'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (
                is_string($candidate)
                && trim($candidate) !== ''
            ) {
                return trim($candidate);
            }
        }

        /*
         * A Theme does not require a fabricated route to be considered
         * installed/enabled. The caller may remain in Theme Hub when the
         * package does not declare a start/preview URL.
         */
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
                'Theme package requires a valid slug and version.'
            );
        }

        if (!is_dir($extractedPath)) {
            throw new RuntimeException(
                'Validated Theme staging directory does not exist.'
            );
        }
    }

    public function install(
        string $extractedPath,
        array $manifest,
        array $context = []
    ): array {
        $this->validate(
            $extractedPath,
            $manifest,
            $context
        );

        $identity = $this->identity($manifest);

        $destinationRoot = $this->themeRoot($context);
        $destination = $destinationRoot . DIRECTORY_SEPARATOR . $identity['slug'];

        File::ensureDirectoryExists($destinationRoot);

        /*
         * Keep a rollback snapshot when replacing an existing Theme.
         */
        $backup = null;

        if (is_dir($destination)) {
            $backup = $destination
                . '.esubiz-backup-'
                . date('YmdHis')
                . '-'
                . bin2hex(random_bytes(3));

            if (!@rename($destination, $backup)) {
                throw new RuntimeException(
                    "Existing Theme [{$identity['slug']}] could not be staged for replacement."
                );
            }
        }

        try {
            File::copyDirectory(
                $extractedPath,
                $destination
            );

            if (!is_dir($destination)) {
                throw new RuntimeException(
                    "Theme [{$identity['slug']}] could not be installed."
                );
            }

            $registered = $this->themes->registerInstalled(
                $identity['slug'],
                $identity['version'],
                [
                    'name' => $identity['name'],

                    'preview_path' =>
                        $manifest['preview_path']
                        ?? $manifest['preview']
                        ?? null,

                    'install_path' => $destination,

                    'marketplace_theme_package_id' =>
                        $manifest['marketplace_theme_package_id']
                        ?? $manifest['package_id']
                        ?? null,

                    'marketplace_theme_uuid' =>
                        $manifest['marketplace_theme_uuid']
                        ?? $manifest['uuid']
                        ?? null,

                    'checksum_sha256' =>
                        $manifest['checksum_sha256']
                        ?? $manifest['checksum']
                        ?? null,

                    'metadata' => [
                        'description' =>
                            $manifest['description'] ?? '',

                        'aliases' =>
                            $manifest['aliases']
                            ?? $manifest['legacy_aliases']
                            ?? [],

                        'product_type' => 'theme',

                        'source' =>
                            $context['source']
                            ?? 'core_installer',

                        'entitlement_id' =>
                            $context['entitlement_id']
                            ?? null,

                        'license_id' =>
                            $context['license_id']
                            ?? null,
                    ],
                ]
            );

            /*
             * Successful installation no longer needs the previous files.
             * Rollback during enablement can remove this new installation;
             * active-theme safety remains enforced by the registry.
             */
            if ($backup && is_dir($backup)) {
                File::deleteDirectory($backup);
                $backup = null;
            }

            return [
                'product_type' => 'theme',
                'slug' => $identity['slug'],
                'version' => $identity['version'],
                'name' => $identity['name'],
                'install_path' => $destination,
                'registry' => $registered,
                'previous_backup' => $backup,
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

        $active = $this->themes->activate(
            $identity['slug'],
            $identity['version']
        );

        return [
            'product_type' => 'theme',
            'slug' => $identity['slug'],
            'version' => $identity['version'],
            'enabled' => true,
            'theme' => $active,
        ];
    }

    public function update(
        string $extractedPath,
        array $manifest,
        array $context = []
    ): array {
        $identity = $this->identity($manifest);

        $currentlyInstalled = $this->themes->collection()
            ->first(
                fn (array $theme) =>
                    ($theme['slug'] ?? null) === $identity['slug']
                    && !empty($theme['active'])
            );

        $wasActive = (bool) $currentlyInstalled;

        $installation = $this->install(
            $extractedPath,
            $manifest,
            $context
        );

        $enablement = null;

        if ($wasActive) {
            $enablement = $this->enable(
                $manifest,
                $installation,
                $context
            );
        }

        return [
            'product_type' => 'theme',
            'updated' => true,
            'installation' => $installation,
            'enablement' => $enablement,
        ];
    }

    public function rollback(
        array $context = []
    ): void {
        $slug = strtolower(
            trim(
                (string) (
                    $context['product_slug']
                    ?? $context['slug']
                    ?? ''
                )
            )
        );

        $version = trim(
            (string) (
                $context['product_version']
                ?? $context['version']
                ?? ''
            )
        );

        if ($slug === '' || $version === '') {
            return;
        }

        $theme = $this->themes->find(
            $slug,
            $version
        );

        if (!$theme) {
            return;
        }

        /*
         * Never remove the currently active Theme during generic rollback.
         * An active Theme must first be replaced/enabled explicitly.
         */
        if (!empty($theme['active'])) {
            return;
        }

        $installPath = $theme['install_path'] ?? null;

        $this->themes->remove(
            $slug,
            $version
        );

        if (
            is_string($installPath)
            && $installPath !== ''
            && is_dir($installPath)
        ) {
            File::deleteDirectory($installPath);
        }
    }

    protected function identity(array $manifest): array
    {
        $slug = strtolower(
            trim(
                (string) (
                    $manifest['slug']
                    ?? $manifest['product_slug']
                    ?? $manifest['theme_slug']
                    ?? ''
                )
            )
        );

        $version = trim(
            (string) (
                $manifest['version']
                ?? $manifest['product_version']
                ?? $manifest['theme_version']
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

    protected function themeRoot(array $context): string
    {
        $root = $context['theme_root'] ?? null;

        if (is_string($root) && trim($root) !== '') {
            return rtrim(
                $root,
                DIRECTORY_SEPARATOR
            );
        }

        return resource_path('views/themes');
    }
}
