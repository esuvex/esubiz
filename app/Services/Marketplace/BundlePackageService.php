<?php

namespace App\Services\Marketplace;

use RuntimeException;
use ZipArchive;

class BundlePackageService
{
    public const SCHEMA =
        'esubiz-addon-bundle-package';

    public const SCHEMA_VERSION =
        '1.0';


    public function __construct(
        protected AddonPackageService $addons
    ) {
    }


    /**
     * Validate one Esubiz add-on bundle ZIP.
     *
     * Expected structure:
     *
     * bundle.json
     * addons/
     *   addon-one.zip
     *   addon-two.zip
     *   ...
     */
    public function validatePackage(
        string $absolutePackagePath
    ): array {

        if (
            !is_file(
                $absolutePackagePath
            )
        ) {
            throw new RuntimeException(
                'Bundle package does not exist.'
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
                'Bundle package could not be opened.'
            );
        }


        $tempRoot =
            storage_path(
                'app/private/esubiz-products/tmp/bundles/'
                . bin2hex(
                    random_bytes(12)
                )
            );


        if (
            !is_dir(
                $tempRoot
            )
            && !mkdir(
                $tempRoot,
                0775,
                true
            )
        ) {
            $zip->close();

            throw new RuntimeException(
                'Bundle validation temporary directory could not be created.'
            );
        }


        try {

            /*
            |--------------------------------------------------------------------------
            | ZIP ENTRY SAFETY
            |--------------------------------------------------------------------------
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
                        'Bundle package contains an unreadable ZIP entry.'
                    );
                }


                $this->assertSafeEntry(
                    $entry
                );
            }


            /*
            |--------------------------------------------------------------------------
            | BUNDLE MANIFEST
            |--------------------------------------------------------------------------
            */

            $manifestJson =
                $zip->getFromName(
                    'bundle.json'
                );


            if (
                $manifestJson === false
            ) {
                throw new RuntimeException(
                    'Bundle package is missing bundle.json.'
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
                    'Bundle manifest is invalid JSON.'
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
                    'Unsupported bundle package schema.'
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
                    'Unsupported bundle schema version.'
                );
            }


            foreach (
                [
                    'bundle.key',
                    'bundle.name',
                    'bundle.version',
                    'publisher.display_name',
                    'compatibility.deployment',
                    'addons',
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
                        "Bundle manifest is missing [{$required}]."
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
                || empty(
                    $deployment
                )
            ) {
                throw new RuntimeException(
                    'Bundle deployment compatibility must be a non-empty array.'
                );
            }


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
                        "Unsupported bundle deployment mode [{$mode}]."
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | NESTED ADD-ON PACKAGES
            |--------------------------------------------------------------------------
            */

            $declaredAddons =
                data_get(
                    $manifest,
                    'addons'
                );


            if (
                !is_array(
                    $declaredAddons
                )
                || empty(
                    $declaredAddons
                )
            ) {
                throw new RuntimeException(
                    'Bundle must contain at least one declared add-on.'
                );
            }


            $validatedAddons =
                [];


            foreach (
                $declaredAddons
                as $position => $definition
            ) {

                if (
                    !is_array(
                        $definition
                    )
                ) {
                    throw new RuntimeException(
                        'Bundle add-on definition must be an object.'
                    );
                }


                $embeddedPath =
                    trim(
                        (string) (
                            $definition[
                                'package'
                            ]
                            ?? ''
                        )
                    );


                if (
                    $embeddedPath === ''
                ) {
                    throw new RuntimeException(
                        'Bundle add-on definition is missing package path.'
                    );
                }


                $normalizedEmbeddedPath =
                    str_replace(
                        '\\',
                        '/',
                        $embeddedPath
                    );


                if (
                    !str_starts_with(
                        $normalizedEmbeddedPath,
                        'addons/'
                    )
                    || !str_ends_with(
                        strtolower(
                            $normalizedEmbeddedPath
                        ),
                        '.zip'
                    )
                ) {
                    throw new RuntimeException(
                        'Nested add-on packages must be ZIP files inside the addons/ directory.'
                    );
                }


                $this->assertSafeEntry(
                    $normalizedEmbeddedPath
                );


                $binary =
                    $zip->getFromName(
                        $normalizedEmbeddedPath
                    );


                if (
                    $binary === false
                ) {
                    throw new RuntimeException(
                        "Bundle is missing nested add-on package [{$normalizedEmbeddedPath}]."
                    );
                }


                $tempPackage =
                    $tempRoot
                    . '/'
                    . sprintf(
                        '%03d-',
                        (int) $position
                    )
                    . basename(
                        $normalizedEmbeddedPath
                    );


                if (
                    file_put_contents(
                        $tempPackage,
                        $binary
                    )
                    === false
                ) {
                    throw new RuntimeException(
                        'Nested add-on package could not be prepared for validation.'
                    );
                }


                $addonManifest =
                    $this->addons
                        ->validatePackage(
                            $tempPackage
                        );


                $addonChecksum =
                    $this->addons
                        ->checksum(
                            $tempPackage
                        );


                $declaredKey =
                    trim(
                        (string) (
                            $definition[
                                'key'
                            ]
                            ?? ''
                        )
                    );


                $manifestKey =
                    trim(
                        (string)
                        data_get(
                            $addonManifest,
                            'addon.key'
                        )
                    );


                if (
                    $declaredKey !== ''
                    && $declaredKey !== $manifestKey
                ) {
                    throw new RuntimeException(
                        "Bundle add-on key mismatch for [{$normalizedEmbeddedPath}]."
                    );
                }


                $declaredVersion =
                    trim(
                        (string) (
                            $definition[
                                'version'
                            ]
                            ?? ''
                        )
                    );


                $manifestVersion =
                    trim(
                        (string)
                        data_get(
                            $addonManifest,
                            'addon.version'
                        )
                    );


                if (
                    $declaredVersion !== ''
                    && $declaredVersion !== $manifestVersion
                ) {
                    throw new RuntimeException(
                        "Bundle add-on version mismatch for [{$normalizedEmbeddedPath}]."
                    );
                }


                $validatedAddons[] = [
                    'key' =>
                        $manifestKey,

                    'name' =>
                        (string)
                        data_get(
                            $addonManifest,
                            'addon.name'
                        ),

                    'version' =>
                        $manifestVersion,

                    'embedded_path' =>
                        $normalizedEmbeddedPath,

                    'checksum_sha256' =>
                        $addonChecksum,

                    'manifest' =>
                        $addonManifest,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | DUPLICATE ADD-ON KEYS
            |--------------------------------------------------------------------------
            */

            $keys =
                array_map(
                    fn ($addon) =>
                        $addon['key'],
                    $validatedAddons
                );


            if (
                count(
                    $keys
                )
                !== count(
                    array_unique(
                        $keys
                    )
                )
            ) {
                throw new RuntimeException(
                    'Bundle contains duplicate add-on keys.'
                );
            }


            return [
                'manifest' =>
                    $manifest,

                'addons' =>
                    $validatedAddons,
            ];

        } finally {

            $zip->close();

            $this->deleteDirectory(
                $tempRoot
            );
        }
    }


    public function checksum(
        string $absolutePackagePath
    ): string {

        if (
            !is_file(
                $absolutePackagePath
            )
        ) {
            throw new RuntimeException(
                'Bundle package does not exist.'
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
                'Bundle package checksum could not be calculated.'
            );
        }


        return $checksum;
    }


    public function protectedRoot(): string
    {
        return storage_path(
            'app/private/esubiz-products/addon-bundles'
        );
    }


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
                'Bundle contains an unsafe ZIP entry.'
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
                'Bundle contains an absolute ZIP path.'
            );
        }


        if (
            preg_match(
                '/^[A-Za-z]:\//',
                $normalized
            )
        ) {
            throw new RuntimeException(
                'Bundle contains an absolute Windows ZIP path.'
            );
        }


        foreach (
            explode(
                '/',
                $normalized
            )
            as $segment
        ) {

            if (
                $segment === '..'
            ) {
                throw new RuntimeException(
                    'Bundle contains directory traversal.'
                );
            }
        }
    }


    protected function deleteDirectory(
        string $directory
    ): void {

        if (
            !is_dir(
                $directory
            )
        ) {
            return;
        }


        $items =
            scandir(
                $directory
            );


        if (
            $items === false
        ) {
            return;
        }


        foreach (
            $items
            as $item
        ) {

            if (
                $item === '.'
                || $item === '..'
            ) {
                continue;
            }


            $path =
                $directory
                . '/'
                . $item;


            if (
                is_dir(
                    $path
                )
            ) {
                $this->deleteDirectory(
                    $path
                );
            } else {
                @unlink(
                    $path
                );
            }
        }


        @rmdir(
            $directory
        );
    }
}
