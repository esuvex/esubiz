<?php

namespace App\Services\Marketplace;

use App\Services\Marketplace\Packages\EsubizProductPackageValidator;
use RuntimeException;
use ZipArchive;

class AddonPackageService
{
    public function __construct(
        protected EsubizProductPackageValidator $productPackageValidator
    ) {
    }
    public const SCHEMA =
        'esubiz-addon-package';

    public const SCHEMA_VERSION =
        '1.0';


    /**
     * Validate one Esubiz add-on ZIP.
     *
     * Expected structure:
     *
     * addon.json
     * app/
     * database/
     * resources/
     * routes/
     * public/
     *
     * Not every directory is mandatory. addon.json is mandatory.
     */
    public function validatePackage(
        string $absolutePackagePath
    ): array {
        /*
         * Universal package identity must confirm this ZIP is an Add-on.
         */
        $this->productPackageValidator->validate(
            $absolutePackagePath,
            'addon'
        );


        if (
            !is_file(
                $absolutePackagePath
            )
        ) {
            throw new RuntimeException(
                'Add-on package does not exist.'
            );
        }


        if (
            !class_exists(
                ZipArchive::class
            )
        ) {
            throw new RuntimeException(
                'ZIP support is unavailable on this server.'
            );
        }


        $zip =
            new ZipArchive();


        $opened =
            $zip->open(
                $absolutePackagePath
            );


        if ($opened !== true) {
            throw new RuntimeException(
                'Add-on package could not be opened.'
            );
        }


        try {

            /*
            |--------------------------------------------------------------------------
            | ZIP ENTRY SAFETY
            |--------------------------------------------------------------------------
            |
            | Reject:
            |
            | ../
            | absolute paths
            | Windows drive paths
            | NUL bytes
            | backslash traversal
            |
            */

            for (
                $index = 0;
                $index < $zip->numFiles;
                $index++
            ) {

                $entry =
                    $zip->getNameIndex(
                        $index
                    );


                if (
                    $entry === false
                ) {
                    throw new RuntimeException(
                        'Add-on package contains an unreadable ZIP entry.'
                    );
                }


                $this->assertSafeEntry(
                    $entry
                );
            }


            /*
            |--------------------------------------------------------------------------
            | MANIFEST
            |--------------------------------------------------------------------------
            */

            $manifestJson =
                $zip->getFromName(
                    'addon.json'
                );


            if (
                $manifestJson === false
            ) {
                throw new RuntimeException(
                    'Add-on package is missing addon.json.'
                );
            }


            $manifest =
                json_decode(
                    $manifestJson,
                    true
                );


            if (
                !is_array(
                    $manifest
                )
            ) {
                throw new RuntimeException(
                    'Add-on manifest is invalid JSON.'
                );
            }


            if (
                (
                    $manifest[
                        'schema'
                    ]
                    ?? null
                )
                !== static::SCHEMA
            ) {
                throw new RuntimeException(
                    'Unsupported add-on package schema.'
                );
            }


            if (
                (
                    $manifest[
                        'schema_version'
                    ]
                    ?? null
                )
                !== static::SCHEMA_VERSION
            ) {
                throw new RuntimeException(
                    'Unsupported add-on schema version.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | REQUIRED MANIFEST FIELDS
            |--------------------------------------------------------------------------
            */

            foreach (
                [
                    'addon.key',
                    'addon.name',
                    'addon.version',
                    'publisher.display_name',
                    'compatibility.deployment',
                    'integration.activation_class',
                ]
                as $required
            ) {

                if (
                    data_get(
                        $manifest,
                        $required
                    )
                    === null
                ) {
                    throw new RuntimeException(
                        "Add-on manifest is missing [{$required}]."
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | DEPLOYMENT COMPATIBILITY
            |--------------------------------------------------------------------------
            */

            $deployment =
                data_get(
                    $manifest,
                    'compatibility.deployment'
                );


            if (
                !is_array(
                    $deployment
                )
            ) {
                throw new RuntimeException(
                    'Add-on deployment compatibility must be an array.'
                );
            }


            $deployment =
                array_values(
                    array_unique(
                        array_map(
                            'strval',
                            $deployment
                        )
                    )
                );


            foreach (
                $deployment
                as $mode
            ) {

                if (
                    !in_array(
                        $mode,
                        [
                            'saas',
                            'off_server',
                        ],
                        true
                    )
                ) {
                    throw new RuntimeException(
                        "Unsupported add-on deployment mode [{$mode}]."
                    );
                }
            }


            if (
                empty(
                    $deployment
                )
            ) {
                throw new RuntimeException(
                    'Add-on package must support at least one deployment mode.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | VERSION FORMAT
            |--------------------------------------------------------------------------
            */

            $version =
                trim(
                    (string)
                    data_get(
                        $manifest,
                        'addon.version'
                    )
                );


            if (
                $version === ''
            ) {
                throw new RuntimeException(
                    'Add-on version is required.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | ADD-ON KEY
            |--------------------------------------------------------------------------
            */

            $key =
                trim(
                    (string)
                    data_get(
                        $manifest,
                        'addon.key'
                    )
                );


            if (
                !preg_match(
                    '/^[a-z0-9][a-z0-9._-]*$/',
                    $key
                )
            ) {
                throw new RuntimeException(
                    'Add-on key contains unsupported characters.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | ACTIVATION CLASS
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | App\\Addons\\CrmPro\\AddonServiceProvider
            |
            | This value is recorded now; activation will later verify
            | that the class exists after package staging/extraction.
            |
            */

            $activationClass =
                trim(
                    (string)
                    data_get(
                        $manifest,
                        'integration.activation_class'
                    )
                );


            if (
                $activationClass === ''
            ) {
                throw new RuntimeException(
                    'Add-on activation class is required.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | OPTIONAL FILE DECLARATIONS
            |--------------------------------------------------------------------------
            */

            foreach (
                [
                    'files.routes',
                    'files.migrations',
                    'files.assets',
                ]
                as $listField
            ) {

                $value =
                    data_get(
                        $manifest,
                        $listField
                    );


                if (
                    $value !== null
                    && !is_array(
                        $value
                    )
                ) {
                    throw new RuntimeException(
                        "Add-on manifest field [{$listField}] must be an array."
                    );
                }
            }


            return $manifest;

        } finally {

            $zip->close();
        }
    }


    /**
     * SHA-256 package checksum.
     */
    public function checksum(
        string $absolutePackagePath
    ): string {

        if (
            !is_file(
                $absolutePackagePath
            )
        ) {
            throw new RuntimeException(
                'Add-on package does not exist.'
            );
        }


        $checksum =
            hash_file(
                'sha256',
                $absolutePackagePath
            );


        if (
            $checksum === false
        ) {
            throw new RuntimeException(
                'Add-on package checksum could not be calculated.'
            );
        }


        return $checksum;
    }


    /**
     * Protected central storage root.
     */
    public function protectedRoot(): string
    {
        return storage_path(
            'app/private/esubiz-products/addons'
        );
    }


    /**
     * Reject ZIP-slip and unsafe archive paths.
     */
    protected function assertSafeEntry(
        string $entry
    ): void {

        if (
            str_contains(
                $entry,
                "\0"
            )
        ) {
            throw new RuntimeException(
                'Add-on package contains an unsafe ZIP entry.'
            );
        }


        $normalized =
            str_replace(
                '\\',
                '/',
                $entry
            );


        if (
            str_starts_with(
                $normalized,
                '/'
            )
        ) {
            throw new RuntimeException(
                'Add-on package contains an absolute ZIP path.'
            );
        }


        if (
            preg_match(
                '/^[A-Za-z]:\//',
                $normalized
            )
        ) {
            throw new RuntimeException(
                'Add-on package contains an absolute Windows ZIP path.'
            );
        }


        $segments =
            explode(
                '/',
                $normalized
            );


        foreach (
            $segments
            as $segment
        ) {

            if (
                $segment === '..'
            ) {
                throw new RuntimeException(
                    'Add-on package contains directory traversal.'
                );
            }
        }
    }
}
