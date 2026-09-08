<?php

namespace App\Services\Core\Installer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * ESUBIZ_CORE_INSTALLED_PRODUCT_REGISTRY_V1
 *
 * Persistent installed-product registry shared by Esubiz Core.
 *
 * Used for Core-installable product families such as:
 * - module
 * - addon
 *
 * Themes retain their dedicated InstalledThemeRegistry.
 *
 * This service is deployment-agnostic and therefore works for both
 * Esubiz-hosted SaaS tenant Core and off-server Esubiz Core.
 */
class CoreInstalledProductRegistry
{
    protected string $table = 'core_installed_products';

    public function all(
        ?string $productType = null
    ): array {
        $this->ensureTable();

        $query = DB::table($this->table);

        if (
            is_string($productType)
            && trim($productType) !== ''
        ) {
            $query->where(
                'product_type',
                $this->normalizeType($productType)
            );
        }

        return $query
            ->orderBy('product_type')
            ->orderBy('product_slug')
            ->orderByDesc('installed_at')
            ->get()
            ->map(fn ($row) => $this->normalizeRow($row))
            ->all();
    }

    public function find(
        string $productType,
        string $slug,
        ?string $version = null
    ): ?array {
        $this->ensureTable();

        $type = $this->normalizeType($productType);
        $slug = $this->normalizeSlug($slug);

        $query = DB::table($this->table)
            ->where('product_type', $type)
            ->where('product_slug', $slug);

        if (
            is_string($version)
            && trim($version) !== ''
        ) {
            $query->where(
                'product_version',
                trim($version)
            );
        } else {
            $query->orderByDesc('installed_at');
        }

        $row = $query->first();

        return $row
            ? $this->normalizeRow($row)
            : null;
    }

    public function isInstalled(
        string $productType,
        string $slug,
        ?string $version = null
    ): bool {
        return $this->find(
            $productType,
            $slug,
            $version
        ) !== null;
    }

    public function registerInstalled(
        string $productType,
        string $slug,
        string $version,
        array $data = []
    ): array {
        $this->ensureTable();

        $type = $this->normalizeType($productType);
        $slug = $this->normalizeSlug($slug);
        $version = trim($version);

        if ($version === '') {
            throw new RuntimeException(
                'Installed product version is required.'
            );
        }

        $now = now();

        $identity = [
            'product_type' => $type,
            'product_slug' => $slug,
            'product_version' => $version,
        ];

        $existing = DB::table($this->table)
            ->where($identity)
            ->first();

        $metadata = $data['metadata'] ?? null;

        if (
            $metadata !== null
            && !is_array($metadata)
        ) {
            throw new RuntimeException(
                'Installed product metadata must be an array.'
            );
        }

        $values = [
            'name' =>
                $this->nullableString(
                    $data['name'] ?? null
                ),

            'install_path' =>
                $this->nullableString(
                    $data['install_path'] ?? null
                ),

            'entitlement_id' =>
                $this->nullableInteger(
                    $data['entitlement_id'] ?? null
                ),

            'license_id' =>
                $this->nullableInteger(
                    $data['license_id'] ?? null
                ),

            'catalog_product_id' =>
                $this->nullableInteger(
                    $data['catalog_product_id'] ?? null
                ),

            'marketplace_listing_id' =>
                $this->nullableInteger(
                    $data['marketplace_listing_id'] ?? null
                ),

            'checksum_sha256' =>
                $this->nullableString(
                    $data['checksum_sha256'] ?? null
                ),

            'metadata' =>
                $metadata !== null
                    ? json_encode(
                        $metadata,
                        JSON_UNESCAPED_SLASHES
                        | JSON_UNESCAPED_UNICODE
                    )
                    : null,

            'installed_at' => $now,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table($this->table)
                ->where('id', $existing->id)
                ->update($values);
        } else {
            DB::table($this->table)->insert(
                array_merge(
                    $identity,
                    $values,
                    [
                        'is_enabled' => false,
                        'enabled_at' => null,
                        'created_at' => $now,
                    ]
                )
            );
        }

        $installed = $this->find(
            $type,
            $slug,
            $version
        );

        if (!$installed) {
            throw new RuntimeException(
                "Installed product [{$type}:{$slug}@{$version}] could not be registered."
            );
        }

        return $installed;
    }

    public function enable(
        string $productType,
        string $slug,
        string $version
    ): array {
        $this->ensureTable();

        $type = $this->normalizeType($productType);
        $slug = $this->normalizeSlug($slug);
        $version = trim($version);

        $installed = $this->find(
            $type,
            $slug,
            $version
        );

        if (!$installed) {
            throw new RuntimeException(
                "Product [{$type}:{$slug}@{$version}] is not installed."
            );
        }

        DB::transaction(function () use (
            $type,
            $slug,
            $version
        ) {
            /*
             * Only one version of the same product slug should be enabled
             * at a time. Other products of the same type remain untouched.
             */
            DB::table($this->table)
                ->where('product_type', $type)
                ->where('product_slug', $slug)
                ->update([
                    'is_enabled' => false,
                    'enabled_at' => null,
                    'updated_at' => now(),
                ]);

            DB::table($this->table)
                ->where('product_type', $type)
                ->where('product_slug', $slug)
                ->where('product_version', $version)
                ->update([
                    'is_enabled' => true,
                    'enabled_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        $enabled = $this->find(
            $type,
            $slug,
            $version
        );

        if (!$enabled || !$enabled['is_enabled']) {
            throw new RuntimeException(
                "Product [{$type}:{$slug}@{$version}] could not be enabled."
            );
        }

        return $enabled;
    }

    public function disable(
        string $productType,
        string $slug,
        string $version
    ): array {
        $this->ensureTable();

        $type = $this->normalizeType($productType);
        $slug = $this->normalizeSlug($slug);
        $version = trim($version);

        DB::table($this->table)
            ->where('product_type', $type)
            ->where('product_slug', $slug)
            ->where('product_version', $version)
            ->update([
                'is_enabled' => false,
                'enabled_at' => null,
                'updated_at' => now(),
            ]);

        $product = $this->find(
            $type,
            $slug,
            $version
        );

        if (!$product) {
            throw new RuntimeException(
                "Product [{$type}:{$slug}@{$version}] is not installed."
            );
        }

        return $product;
    }

    public function remove(
        string $productType,
        string $slug,
        string $version
    ): bool {
        $this->ensureTable();

        $type = $this->normalizeType($productType);
        $slug = $this->normalizeSlug($slug);
        $version = trim($version);

        $installed = $this->find(
            $type,
            $slug,
            $version
        );

        if (!$installed) {
            return false;
        }

        if ($installed['is_enabled']) {
            throw new RuntimeException(
                "Enabled product [{$type}:{$slug}@{$version}] cannot be removed."
            );
        }

        return DB::table($this->table)
            ->where('id', $installed['id'])
            ->delete() > 0;
    }

    protected function ensureTable(): void
    {
        if (!Schema::hasTable($this->table)) {
            throw new RuntimeException(
                'Core installed-product registry table is unavailable.'
            );
        }
    }

    protected function normalizeType(
        string $type
    ): string {
        $type = strtolower(
            trim(
                str_replace('-', '_', $type)
            )
        );

        if ($type === '') {
            throw new RuntimeException(
                'Installed product type is required.'
            );
        }

        return $type;
    }

    protected function normalizeSlug(
        string $slug
    ): string {
        $slug = strtolower(trim($slug));

        if ($slug === '') {
            throw new RuntimeException(
                'Installed product slug is required.'
            );
        }

        return $slug;
    }

    protected function nullableString(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }

    protected function nullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return (int) $value;
    }

    protected function normalizeRow(
        object $row
    ): array {
        $data = (array) $row;

        $data['is_enabled'] =
            (bool) ($data['is_enabled'] ?? false);

        $metadata = $data['metadata'] ?? null;

        if (is_string($metadata) && $metadata !== '') {
            $decoded = json_decode(
                $metadata,
                true
            );

            $data['metadata'] =
                is_array($decoded)
                    ? $decoded
                    : [];
        } elseif (!is_array($metadata)) {
            $data['metadata'] = [];
        }

        return $data;
    }
}
