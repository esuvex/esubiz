<?php

namespace App\Services\Marketplace\Packages;

use RuntimeException;
use ZipArchive;

class EsubizProductPackageValidator
{
    public const MANIFEST = 'esubiz-package.json';

    public const SCHEMA = 'esubiz-product-package';

    public const SCHEMA_VERSION = '1.0';

    /**
     * Product types that may be distributed as Esubiz ZIP products.
     */
    public const PRODUCT_TYPES = [
        'theme',
        'module',
        'addon',
        'bundle',
        'website_type',
    ];

    /**
     * Validate the universal identity of an Esubiz product ZIP.
     *
     * Product-specific validators run after this validator.
     */
    public function validate(
        string $absolutePackagePath,
        string $expectedProductType
    ): array {
        $expectedProductType = strtolower(
            trim($expectedProductType)
        );

        if (
            !in_array(
                $expectedProductType,
                static::PRODUCT_TYPES,
                true
            )
        ) {
            throw new RuntimeException(
                "Unsupported Esubiz product type [{$expectedProductType}]."
            );
        }

        if (!is_file($absolutePackagePath)) {
            throw new RuntimeException(
                'Esubiz product package does not exist.'
            );
        }

        $zip = new ZipArchive();

        $opened = $zip->open(
            $absolutePackagePath
        );

        if ($opened !== true) {
            throw new RuntimeException(
                'Esubiz product package could not be opened.'
            );
        }

        try {
            $manifestJson = $zip->getFromName(
                static::MANIFEST
            );

            if ($manifestJson === false) {
                throw new RuntimeException(
                    'Package rejected: missing root-level '
                    . static::MANIFEST
                    . '.'
                );
            }

            $manifest = json_decode(
                $manifestJson,
                true
            );

            if (!is_array($manifest)) {
                throw new RuntimeException(
                    'Package rejected: esubiz-package.json contains invalid JSON.'
                );
            }

            if (
                ($manifest['schema'] ?? null)
                !== static::SCHEMA
            ) {
                throw new RuntimeException(
                    'Package rejected: unsupported Esubiz package schema.'
                );
            }

            if (
                ($manifest['schema_version'] ?? null)
                !== static::SCHEMA_VERSION
            ) {
                throw new RuntimeException(
                    'Package rejected: unsupported Esubiz package schema version.'
                );
            }

            $actualProductType = strtolower(
                trim(
                    (string) (
                        $manifest['product_type']
                        ?? ''
                    )
                )
            );

            if ($actualProductType === '') {
                throw new RuntimeException(
                    'Package rejected: product_type is missing from esubiz-package.json.'
                );
            }

            if (
                !in_array(
                    $actualProductType,
                    static::PRODUCT_TYPES,
                    true
                )
            ) {
                throw new RuntimeException(
                    "Package rejected: unknown product type [{$actualProductType}]."
                );
            }

            if (
                $actualProductType
                !== $expectedProductType
            ) {
                throw new RuntimeException(
                    "Wrong product package. This upload accepts [{$expectedProductType}] packages, "
                    . "but the ZIP identifies itself as [{$actualProductType}]."
                );
            }

            foreach (
                [
                    'name',
                    'slug',
                    'version',
                ]
                as $required
            ) {
                if (
                    trim(
                        (string) (
                            $manifest[$required]
                            ?? ''
                        )
                    )
                    === ''
                ) {
                    throw new RuntimeException(
                        "Package rejected: [{$required}] is missing from esubiz-package.json."
                    );
                }
            }

            return $manifest;

        } finally {
            $zip->close();
        }
    }
}
