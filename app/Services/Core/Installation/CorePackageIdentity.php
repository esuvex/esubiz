<?php

namespace App\Services\Core\Installation;

use JsonException;
use RuntimeException;

/**
 * ESUBIZ_CORE_PACKAGE_IDENTITY_V1
 *
 * Reads the immutable identity embedded in the distributed
 * Esubiz Core package.
 *
 * Central Marketplace database IDs must NOT be embedded into
 * a portable Core package because those IDs belong to Central
 * commerce storage, not to the package itself.
 *
 * Portable identity is:
 *
 * product type + slug + version
 */
class CorePackageIdentity
{
    /**
     * @return array<string,mixed>
     */
    public function get(): array
    {
        $path =
            base_path(
                'core.json'
            );

        if (!is_file($path)) {
            throw new RuntimeException(
                'The Esubiz Core package identity manifest is missing.'
            );
        }

        if (!is_readable($path)) {
            throw new RuntimeException(
                'The Esubiz Core package identity manifest is not readable.'
            );
        }

        $raw =
            file_get_contents(
                $path
            );

        if ($raw === false) {
            throw new RuntimeException(
                'Unable to read the Esubiz Core package identity manifest.'
            );
        }

        try {
            $manifest =
                json_decode(
                    $raw,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'The Esubiz Core package identity manifest contains invalid JSON.',
                previous: $e
            );
        }

        if (
            !is_array($manifest)
            || (
                $manifest['schema']
                ?? null
            ) !== 'esubiz-core-package'
            || (
                $manifest['schema_version']
                ?? null
            ) !== '1.0'
        ) {
            throw new RuntimeException(
                'The Esubiz Core package identity manifest is invalid.'
            );
        }

        $product =
            $manifest['product']
            ?? null;

        if (!is_array($product)) {
            throw new RuntimeException(
                'The Esubiz Core package product identity is missing.'
            );
        }

        $type =
            trim(
                (string) (
                    $product['type']
                    ?? ''
                )
            );

        $slug =
            trim(
                (string) (
                    $product['slug']
                    ?? ''
                )
            );

        $name =
            trim(
                (string) (
                    $product['name']
                    ?? ''
                )
            );

        $version =
            trim(
                (string) (
                    $product['version']
                    ?? ''
                )
            );

        if (
            $type !== 'core'
            || $slug === ''
            || $name === ''
            || $version === ''
        ) {
            throw new RuntimeException(
                'The Esubiz Core package product identity is incomplete.'
            );
        }

        $deployments =
            $manifest['compatibility']['deployment']
            ?? [];

        if (
            !is_array($deployments)
            || !in_array(
                'off_server',
                $deployments,
                true
            )
        ) {
            throw new RuntimeException(
                'This Esubiz Core package does not support off-server installation.'
            );
        }

        return [
            'schema' =>
                'esubiz-core-package',

            'schema_version' =>
                '1.0',

            'product_type' =>
                $type,

            'product_slug' =>
                $slug,

            'product_name' =>
                $name,

            'product_version' =>
                $version,

            'publisher_slug' =>
                trim(
                    (string) (
                        $manifest['publisher']['slug']
                        ?? ''
                    )
                ),

            'publisher_name' =>
                trim(
                    (string) (
                        $manifest['publisher']['display_name']
                        ?? ''
                    )
                ),

            'deployments' =>
                array_values(
                    $deployments
                ),
        ];
    }

    public function type(): string
    {
        return (string)
            $this->get()[
                'product_type'
            ];
    }

    public function slug(): string
    {
        return (string)
            $this->get()[
                'product_slug'
            ];
    }

    public function name(): string
    {
        return (string)
            $this->get()[
                'product_name'
            ];
    }

    public function version(): string
    {
        return (string)
            $this->get()[
                'product_version'
            ];
    }
}
