<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

class AddonPackageStorageService
{
    /*
    |--------------------------------------------------------------------------
    | ESUBIZ ADD-ON PACKAGE STORAGE V1
    |--------------------------------------------------------------------------
    |
    | Central discovery layer for genuine Add-on ZIP products already present
    | in protected Esubiz Add-on product storage.
    |
    | Discovery is intentionally separate from authoritative validation.
    | AddonPackageService remains the source of truth for package validity.
    |
    */

    public function root(): string
    {
        return storage_path(
            'app/private/esubiz-products/addons'
        );
    }


    /**
     * Add-on ZIPs available for Admin selection.
     */
    public function packages(): Collection
    {
        $root = $this->root();

        if (!is_dir($root)) {
            return collect();
        }

        $files = glob(
            $root . DIRECTORY_SEPARATOR . '*.zip'
        ) ?: [];

        return collect($files)
            ->filter(
                fn (string $path) =>
                    is_file($path)
            )
            ->map(
                fn (string $path) =>
                    $this->describe($path)
            )
            ->filter()
            ->sortBy(
                fn (array $package) =>
                    strtolower(
                        $package['name']
                        ?? $package['filename']
                    )
            )
            ->values();
    }


    /**
     * Resolve an Admin-selected Add-on package safely.
     *
     * Arbitrary filesystem paths are never accepted.
     */
    public function resolve(
        string $filename
    ): string {
        $filename = basename(
            trim($filename)
        );

        if (
            $filename === ''
            || !str_ends_with(
                strtolower($filename),
                '.zip'
            )
        ) {
            throw new RuntimeException(
                'Invalid Add-on package selection.'
            );
        }

        $root = realpath(
            $this->root()
        );

        if (!$root) {
            throw new RuntimeException(
                'Add-on package storage is unavailable.'
            );
        }

        $candidate = realpath(
            $root
            . DIRECTORY_SEPARATOR
            . $filename
        );

        if (
            !$candidate
            || !is_file($candidate)
            || dirname($candidate) !== $root
        ) {
            throw new RuntimeException(
                'Selected Add-on package was not found.'
            );
        }

        return $candidate;
    }


    /**
     * Read safe discovery information from addon.json.
     *
     * This does NOT establish package validity.
     * AddonPackageService performs authoritative validation.
     */
    protected function describe(
        string $path
    ): ?array {
        $filename = basename($path);

        $result = [
            'filename' =>
                $filename,

            'path' =>
                $path,

            'bytes' =>
                filesize($path) ?: 0,

            'name' =>
                pathinfo(
                    $filename,
                    PATHINFO_FILENAME
                ),

            'key' =>
                null,

            'version' =>
                null,

            'publisher' =>
                null,

            'deployment' =>
                [],

            'valid_manifest' =>
                false,
        ];

        if (
            !class_exists(
                ZipArchive::class
            )
        ) {
            return $result;
        }

        $zip = new ZipArchive();

        if (
            $zip->open($path)
            !== true
        ) {
            return $result;
        }

        try {
            $manifestJson =
                $zip->getFromName(
                    'addon.json'
                );

            if (
                $manifestJson === false
            ) {
                return $result;
            }

            $manifest = json_decode(
                $manifestJson,
                true
            );

            if (
                !is_array($manifest)
            ) {
                return $result;
            }

            $addon =
                $manifest['addon']
                ?? [];

            $publisher =
                $manifest['publisher']
                ?? [];

            $compatibility =
                $manifest['compatibility']
                ?? [];

            $result['name'] =
                $addon['name']
                ?? $result['name'];

            $result['key'] =
                $addon['key']
                ?? null;

            $result['version'] =
                $addon['version']
                ?? null;

            $result['publisher'] =
                $publisher['display_name']
                ?? null;

            $result['deployment'] =
                is_array(
                    $compatibility['deployment']
                    ?? null
                )
                    ? array_values(
                        $compatibility['deployment']
                    )
                    : [];

            $result['valid_manifest'] =
                isset(
                    $addon['key'],
                    $addon['name'],
                    $addon['version']
                );

            return $result;

        } finally {
            $zip->close();
        }
    }
}
