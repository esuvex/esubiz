<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class ProductManifestService
{
    public function __construct(
        protected ProductStorageService $storage
    ) {
    }

    /**
     * Read one product manifest without scanning Central storage.
     */
    public function get(string $type, string $identifier): array
    {
        $path = $this->storage->path(
            'manifests',
            $type . '/' . $identifier . '.json'
        );

        if (!File::exists($path)) {
            throw new RuntimeException(
                "Product manifest not found: {$type}/{$identifier}"
            );
        }

        $data = json_decode(
            File::get($path),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($data)) {
            throw new RuntimeException('Invalid product manifest.');
        }

        return $data;
    }

    /**
     * Check whether one product manifest exists.
     */
    public function exists(string $type, string $identifier): bool
    {
        return $this->storage->exists(
            'manifests',
            $type . '/' . $identifier . '.json'
        );
    }
}
