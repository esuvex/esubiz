<?php

namespace App\Services\Marketplace;

use App\Services\Marketplace\Packages\EsubizProductPackageValidator;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class ThemePackageService
{
    public function __construct(
        protected EsubizProductPackageValidator $productPackageValidator
    ) {
    }

    public const SCHEMA =
        'esubiz-theme-package';

    public const SCHEMA_VERSION =
        '1.0';


    /**
     * Validate a theme package before Esubiz accepts it.
     *
     * Every Marketplace, Admin-uploaded and AI-built theme
     * will eventually pass through this same validator.
     */
    public function validatePackage(
        string $absolutePackagePath
    ): array {
        /*
         * Universal Esubiz package identity validation runs first.
         * A Theme upload must identify itself as product_type=theme.
         */
        $this->productPackageValidator->validate(
            $absolutePackagePath,
            'theme'
        );

        if (
            !is_file(
                $absolutePackagePath
            )
        ) {
            throw new RuntimeException(
                'Theme package does not exist.'
            );
        }


        $zip = new ZipArchive();

        $opened = $zip->open(
            $absolutePackagePath
        );

        if ($opened !== true) {
            throw new RuntimeException(
                'Theme package could not be opened.'
            );
        }


        try {

            $manifestJson =
                $zip->getFromName(
                    'theme.json'
                );

            if (
                $manifestJson === false
            ) {
                throw new RuntimeException(
                    'Theme package is missing theme.json.'
                );
            }


            $manifest = json_decode(
                $manifestJson,
                true
            );

            if (
                !is_array(
                    $manifest
                )
            ) {
                throw new RuntimeException(
                    'Theme manifest is invalid JSON.'
                );
            }


            if (
                (
                    $manifest['schema']
                    ?? null
                )
                !== static::SCHEMA
            ) {
                throw new RuntimeException(
                    'Unsupported theme package schema.'
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
                    'Unsupported theme schema version.'
                );
            }


            foreach (
                [
                    'theme.name',
                    'theme.slug',
                    'theme.version',
                    'publisher.display_name',
                    'compatibility.deployment',
                    'integration.inherits_site_settings',
                    'files.entry_layout',
                    'files.homepage',
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
                        "Theme manifest is missing [{$required}]."
                    );
                }
            }


            /*
             * Esubiz themes must inherit site settings.
             */
            if (
                data_get(
                    $manifest,
                    'integration.inherits_site_settings'
                )
                !== true
            ) {
                throw new RuntimeException(
                    'Theme must inherit Esubiz site settings.'
                );
            }


            /*
             * Required theme files.
             */
            foreach (
                [
                    'views/layout.blade.php',
                    'views/home.blade.php',
                    'views/page.blade.php',
                ]
                as $requiredFile
            ) {
                if (
                    $zip->locateName(
                        $requiredFile
                    )
                    === false
                ) {
                    throw new RuntimeException(
                        "Theme package is missing [{$requiredFile}]."
                    );
                }
            }


            return $manifest;

        } finally {

            $zip->close();
        }
    }


    /**
     * Resolve the protected root used by Esubiz theme packages.
     */
    public function protectedRoot(): string
    {
        return storage_path(
            'app/private/esubiz-products/themes'
        );
    }
}
