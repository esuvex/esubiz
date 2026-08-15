<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

class ProductStorageService
{
    /**
     * Resolve the absolute storage path for one Central product.
     */
    public function path(string $type, string $identifier = ''): string
    {
        $directory = $this->directory($type);

        $path = $this->root() . '/' . $directory;

        if ($identifier !== '') {
            $path .= '/' . trim($identifier, '/');
        }

        return $path;
    }

    /**
     * Discover prepared website-type packages.
     */
    public function websiteTypePackages(): array
    {
        $root = $this->path('packages', 'website-types');

        if (!File::isDirectory($root)) {
            return [];
        }

        $packages = [];

        foreach (File::directories($root) as $packageDirectory) {
            $packageKey = basename($packageDirectory);

            foreach (File::directories($packageDirectory) as $versionDirectory) {
                $packageVersion = basename($versionDirectory);
                $manifestPath = $versionDirectory . '/manifest.json';

                if (!File::exists($manifestPath)) {
                    continue;
                }

                $packages[] = [
                    'key' => $packageKey,
                    'version' => $packageVersion,
                ];
            }
        }

        usort($packages, function (array $a, array $b) {
            return [$a['key'], $a['version']] <=> [$b['key'], $b['version']];
        });

        return $packages;
    }

    /**
     * Check whether a specific Central product exists.
     */
    public function exists(string $type, string $identifier): bool
    {
        return File::exists($this->path($type, $identifier));
    }

    /**
     * Return the configured Central product storage root.
     */
    public function root(): string
    {
        return rtrim(config('esubiz_products.storage_root'), '/');
    }

    /**
     * Resolve one approved Central product directory.
     */
    protected function directory(string $type): string
    {
        $directories = config('esubiz_products.directories', []);

        if (!isset($directories[$type])) {
            throw new InvalidArgumentException(
                "Unsupported Esubiz product storage type: {$type}"
            );
        }

        return trim($directories[$type], '/');
    }
}
