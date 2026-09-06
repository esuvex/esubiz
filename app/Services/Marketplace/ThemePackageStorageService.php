<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

class ThemePackageStorageService
{
    /*
    |--------------------------------------------------------------------------
    | ESUBIZ THEME PACKAGE STORAGE V1
    |--------------------------------------------------------------------------
    |
    | Central discovery layer for Theme ZIP products already present in
    | Esubiz protected product storage.
    |
    | Admin may:
    | - upload a new Theme ZIP; or
    | - select an existing Theme ZIP from this storage.
    |
    | Both sources ultimately use the same ThemePackageService validator
    | and ingestion contract.
    |
    | Developers will later use the same package format/validator. Their
    | difference is ownership, permissions and approval workflow — not ZIP
    | structure.
    |
    */

    public function root(): string
    {
        return storage_path(
            'app/private/esubiz-products/themes'
        );
    }


    /**
     * Theme ZIPs available for Admin selection.
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
     * Resolve an Admin-selected storage package safely.
     *
     * Only a ZIP directly inside the protected Theme product root
     * can be selected. Arbitrary filesystem paths are rejected.
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
                'Invalid Theme package selection.'
            );
        }

        $root = realpath(
            $this->root()
        );

        if (!$root) {
            throw new RuntimeException(
                'Theme package storage is unavailable.'
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
                'Selected Theme package was not found.'
            );
        }

        return $candidate;
    }


    /**
     * Read safe display information from theme.json.
     *
     * This is discovery only.
     * ThemePackageService remains the authoritative validator.
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

            'slug' =>
                null,

            'version' =>
                null,

            'publisher' =>
                null,

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
                    'theme.json'
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


            $theme =
                $manifest['theme']
                ?? [];

            $publisher =
                $manifest['publisher']
                ?? [];


            $result['name'] =
                $theme['name']
                ?? $result['name'];

            $result['slug'] =
                $theme['slug']
                ?? null;

            $result['version'] =
                $theme['version']
                ?? null;

            $result['publisher'] =
                $publisher['display_name']
                ?? null;

            $result['valid_manifest'] =
                isset(
                    $theme['name'],
                    $theme['slug'],
                    $theme['version']
                );

            return $result;

        } finally {

            $zip->close();
        }
    }
}
