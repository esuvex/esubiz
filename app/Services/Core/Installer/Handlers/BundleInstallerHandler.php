<?php

namespace App\Services\Core\Installer\Handlers;

use App\Services\Core\Installer\Contracts\CoreProductInstallerHandler;
use App\Services\Core\Installer\CoreProductInstallerManager;
use RuntimeException;

/**
 * ESUBIZ_CORE_BUNDLE_INSTALLER_V1
 *
 * A Bundle is an orchestration product, not a standalone folder product.
 *
 * Each contained product is installed by its own registered Core installer,
 * preserving the universal Theme / Module / Add-on / App architecture.
 *
 * Bundle package validation and child-package staging must happen before
 * this handler is invoked.
 */
class BundleInstallerHandler implements CoreProductInstallerHandler
{
    public function productType(): string
    {
        return 'bundle';
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
        ] as $candidate) {
            if (
                is_string($candidate)
                && trim($candidate) !== ''
            ) {
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
        if (!is_dir($extractedPath)) {
            throw new RuntimeException(
                'Validated Bundle staging directory does not exist.'
            );
        }

        $items = $this->items($manifest);

        if ($items === []) {
            throw new RuntimeException(
                'Bundle manifest does not contain any installable products.'
            );
        }

        foreach ($items as $index => $item) {
            $type = $this->itemType($item);

            if ($type === '' || $type === 'bundle') {
                throw new RuntimeException(
                    "Bundle item [{$index}] has an invalid product type."
                );
            }

            $path = $this->itemPath(
                $extractedPath,
                $item
            );

            if (!is_dir($path)) {
                throw new RuntimeException(
                    "Bundle item [{$index}] staging directory could not be resolved."
                );
            }
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

        $manager = app(
            CoreProductInstallerManager::class
        );

        $installed = [];

        try {
            foreach ($this->items($manifest) as $index => $item) {
                $type = $this->itemType($item);

                if (!$manager->supports($type)) {
                    throw new RuntimeException(
                        "Bundle contains unsupported product type [{$type}]."
                    );
                }

                $childManifest =
                    $item['manifest']
                    ?? $item;

                if (!is_array($childManifest)) {
                    throw new RuntimeException(
                        "Bundle item [{$index}] manifest is invalid."
                    );
                }

                $childPath = $this->itemPath(
                    $extractedPath,
                    $item
                );

                $childContext = array_merge(
                    $context,
                    [
                        'source' => 'bundle',

                        'bundle_slug' =>
                            $manifest['slug']
                            ?? $manifest['product_slug']
                            ?? null,

                        'bundle_version' =>
                            $manifest['version']
                            ?? $manifest['product_version']
                            ?? null,

                        'product_slug' =>
                            $childManifest['slug']
                            ?? $childManifest['product_slug']
                            ?? null,

                        'product_version' =>
                            $childManifest['version']
                            ?? $childManifest['product_version']
                            ?? null,
                    ]
                );

                /*
                 * Install only here.
                 *
                 * Bundle enablement is deliberately performed separately by
                 * enable() so the Core installer contract remains intact.
                 */
                $installation = $manager->install(
                    $type,
                    $childPath,
                    $childManifest,
                    $childContext
                );

                $installed[] = [
                    'index' => $index,
                    'product_type' => $type,
                    'manifest' => $childManifest,
                    'installation' => $installation,
                    'context' => $childContext,
                ];
            }

            return [
                'product_type' => 'bundle',
                'slug' =>
                    $manifest['slug']
                    ?? $manifest['product_slug']
                    ?? null,

                'version' =>
                    $manifest['version']
                    ?? $manifest['product_version']
                    ?? null,

                'installed' => true,
                'enabled' => false,
                'items' => $installed,
            ];
        } catch (\Throwable $e) {
            $this->rollbackInstalledItems(
                $installed,
                $manager
            );

            throw $e;
        }
    }

    /**
     * ESUBIZ_CORE_BUNDLE_ENABLE_ROLLBACK_V2
     *
     * Enable every installed child in order.
     *
     * If any child fails to enable, immediately roll back the full set of
     * children installed for this bundle in reverse order. This keeps bundle
     * installation atomic even when the outer installer manager only has the
     * original bundle context.
     */
    public function enable(
        array $manifest,
        array $installation,
        array $context = []
    ): array {
        $items = $installation['items'] ?? [];

        if (!is_array($items)) {
            throw new RuntimeException(
                'Bundle installation result does not contain a valid item list.'
            );
        }

        $enabled = [];

        try {
            foreach ($items as $index => $item) {
                if (!is_array($item)) {
                    throw new RuntimeException(
                        "Bundle installation item [{$index}] is invalid."
                    );
                }

                $productType = strtolower(
                    trim((string) ($item['product_type'] ?? ''))
                );

                $childManifest =
                    $item['manifest']
                    ?? [];

                $childInstallation =
                    $item['installation']
                    ?? [];

                $childContext =
                    $item['context']
                    ?? $context;

                if ($productType === '') {
                    throw new RuntimeException(
                        "Bundle installation item [{$index}] has no product type."
                    );
                }

                $result = $this->manager->enable(
                    $productType,
                    $childManifest,
                    $childInstallation,
                    $childContext
                );

                $enabled[] = [
                    'product_type' => $productType,
                    'manifest' => $childManifest,
                    'installation' => $childInstallation,
                    'context' => $childContext,
                    'result' => $result,
                ];
            }
        } catch (\Throwable $e) {
            $this->rollbackInstalledItems(
                $items,
                $this->manager
            );

            throw $e;
        }

        return [
            'enabled' => true,
            'items' => $enabled,
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
            'product_type' => 'bundle',
            'updated' => true,
            'installation' => $installation,
            'enablement' => $enablement,
        ];
    }

    public function rollback(
        array $context = []
    ): void {
        $items =
            $context['bundle_installed_items']
            ?? [];

        if (!is_array($items) || $items === []) {
            return;
        }

        $this->rollbackInstalledItems(
            $items,
            app(CoreProductInstallerManager::class)
        );
    }

    protected function rollbackInstalledItems(
        array $items,
        CoreProductInstallerManager $manager
    ): void {
        foreach (array_reverse($items) as $item) {
            $type =
                $item['product_type']
                ?? '';

            if (
                $type === ''
                || !$manager->supports($type)
            ) {
                continue;
            }

            try {
                $manager->rollback(
                    $type,
                    $item['context']
                    ?? []
                );
            } catch (\Throwable) {
                /*
                 * Continue attempting rollback for the remaining Bundle
                 * products. The original failure remains authoritative.
                 */
            }
        }
    }

    protected function items(
        array $manifest
    ): array {
        $items =
            $manifest['products']
            ?? $manifest['items']
            ?? $manifest['bundle_items']
            ?? [];

        return is_array($items)
            ? array_values($items)
            : [];
    }

    protected function itemType(
        array $item
    ): string {
        return strtolower(
            trim(
                str_replace(
                    '-',
                    '_',
                    (string) (
                        $item['product_type']
                        ?? $item['type']
                        ?? (
                            is_array($item['manifest'] ?? null)
                                ? (
                                    $item['manifest']['product_type']
                                    ?? $item['manifest']['type']
                                    ?? ''
                                )
                                : ''
                        )
                    )
                )
            )
        );
    }

    protected function itemPath(
        string $bundleRoot,
        array $item
    ): string {
        $relative =
            $item['path']
            ?? $item['package_path']
            ?? $item['staged_path']
            ?? null;

        if (
            !is_string($relative)
            || trim($relative) === ''
        ) {
            throw new RuntimeException(
                'Bundle child product path is missing.'
            );
        }

        $relative = trim(
            str_replace(
                ['\\', '/'],
                DIRECTORY_SEPARATOR,
                $relative
            ),
            DIRECTORY_SEPARATOR
        );

        if (
            $relative === ''
            || str_contains(
                $relative,
                '..' . DIRECTORY_SEPARATOR
            )
            || $relative === '..'
        ) {
            throw new RuntimeException(
                'Bundle child product path is invalid.'
            );
        }

        return rtrim(
            $bundleRoot,
            DIRECTORY_SEPARATOR
        )
            . DIRECTORY_SEPARATOR
            . $relative;
    }
}
