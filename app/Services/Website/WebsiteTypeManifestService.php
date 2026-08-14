<?php

namespace App\Services\Website;

use App\Models\WebsiteType;
use App\Services\ProductStorageService;
use Illuminate\Support\Facades\File;
use RuntimeException;

class WebsiteTypeManifestService
{
    public function __construct(
        protected ProductStorageService $storage
    ) {
    }

    /**
     * Load the prepared package manifest for a website type.
     */
    public function get(WebsiteType $websiteType): array
    {
        $packageKey = $websiteType->package_key;
        $packageVersion = $websiteType->package_version;

        if (!$packageKey || !$packageVersion) {
            throw new RuntimeException(
                "Website type package is not configured: {$websiteType->slug}"
            );
        }

        $path = $this->storage->path(
            'packages',
            'website-types/' .
            $packageKey . '/' .
            $packageVersion .
            '/manifest.json'
        );

        if (!File::exists($path)) {
            throw new RuntimeException(
                "Website type package not found: {$packageKey}/{$packageVersion}"
            );
        }

        $manifest = json_decode(
            File::get($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($manifest)) {
            throw new RuntimeException(
                "Invalid website type package manifest: {$packageKey}/{$packageVersion}"
            );
        }

        return $manifest;
    }

    /**
     * Determine whether the prepared package exists.
     */
    public function exists(WebsiteType $websiteType): bool
    {
        if (!$websiteType->package_key || !$websiteType->package_version) {
            return false;
        }

        return $this->storage->exists(
            'packages',
            'website-types/' .
            $websiteType->package_key . '/' .
            $websiteType->package_version .
            '/manifest.json'
        );
    }
}
