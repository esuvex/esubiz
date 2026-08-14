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
