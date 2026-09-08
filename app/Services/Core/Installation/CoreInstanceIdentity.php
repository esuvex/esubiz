<?php

namespace App\Services\Core\Installation;

use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

/**
 * ESUBIZ_CORE_INSTANCE_IDENTITY_V1
 *
 * Owns the persistent identity of one physical/logical Core
 * installation.
 *
 * Unlike a browser session UUID, this identity survives:
 *
 * - browser/session restarts;
 * - future Marketplace purchases;
 * - licence revalidation;
 * - product updates;
 * - Core updates.
 *
 * It contains no human-readable licence key or user secret.
 */
class CoreInstanceIdentity
{
    protected function path(): string
    {
        return storage_path(
            'app/core/instance.json'
        );
    }

    /**
     * Return the existing UUID or create one exactly once.
     */
    public function uuid(): string
    {
        $existing =
            $this->read();

        if (
            is_array($existing)
            && !empty(
                $existing['instance_uuid']
            )
        ) {
            $uuid =
                (string) $existing[
                    'instance_uuid'
                ];

            if (!Str::isUuid($uuid)) {
                throw new RuntimeException(
                    'The stored Esubiz Core instance UUID is invalid.'
                );
            }

            return $uuid;
        }

        $uuid =
            (string) Str::uuid();

        $this->write([
            'schema' =>
                'esubiz-core-instance',

            'schema_version' =>
                '1.0',

            'instance_uuid' =>
                $uuid,

            'deployment' =>
                'off_server',

            'created_at' =>
                now()->toIso8601String(),
        ]);

        return $uuid;
    }

    /**
     * Update non-secret installation metadata while preserving UUID.
     *
     * @param array<string,mixed> $metadata
     */
    public function finalize(
        array $metadata
    ): array {
        $current =
            $this->read()
            ?? [];

        $uuid =
            (string) (
                $current['instance_uuid']
                ?? $this->uuid()
            );

        if (!Str::isUuid($uuid)) {
            throw new RuntimeException(
                'The Esubiz Core instance UUID is invalid.'
            );
        }

        /*
         * Explicitly exclude secrets even if a caller accidentally
         * supplies them.
         */
        foreach (
            [
                'license_key',
                'password',
                'db_password',
                'database_password',
                'admin_password',
            ] as $secretKey
        ) {
            unset(
                $metadata[$secretKey]
            );
        }

        $payload =
            array_merge(
                $current,
                $metadata,
                [
                    'schema' =>
                        'esubiz-core-instance',

                    'schema_version' =>
                        '1.0',

                    'instance_uuid' =>
                        $uuid,

                    'deployment' =>
                        'off_server',

                    'updated_at' =>
                        now()->toIso8601String(),
                ]
            );

        $this->write(
            $payload
        );

        return $payload;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function read(): ?array
    {
        $path =
            $this->path();

        if (!is_file($path)) {
            return null;
        }

        if (!is_readable($path)) {
            throw new RuntimeException(
                'The private Esubiz Core instance identity is not readable.'
            );
        }

        $raw =
            file_get_contents(
                $path
            );

        if ($raw === false) {
            throw new RuntimeException(
                'Unable to read the private Esubiz Core instance identity.'
            );
        }

        try {
            $payload =
                json_decode(
                    $raw,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'The private Esubiz Core instance identity contains invalid JSON.',
                previous: $e
            );
        }

        if (!is_array($payload)) {
            throw new RuntimeException(
                'The private Esubiz Core instance identity is invalid.'
            );
        }

        return $payload;
    }

    /**
     * @param array<string,mixed> $payload
     */
    protected function write(
        array $payload
    ): void {
        $path =
            $this->path();

        $directory =
            dirname(
                $path
            );

        if (
            !is_dir($directory)
            && !mkdir(
                $directory,
                0750,
                true
            )
            && !is_dir($directory)
        ) {
            throw new RuntimeException(
                'Unable to create the private Esubiz Core identity directory.'
            );
        }

        try {
            $json =
                json_encode(
                    $payload,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'Unable to encode the Esubiz Core instance identity.',
                previous: $e
            );
        }

        $temporary =
            $path
            . '.tmp-'
            . bin2hex(
                random_bytes(6)
            );

        if (
            file_put_contents(
                $temporary,
                $json . PHP_EOL,
                LOCK_EX
            ) === false
        ) {
            throw new RuntimeException(
                'Unable to write the private Esubiz Core instance identity.'
            );
        }

        @chmod(
            $temporary,
            0640
        );

        if (
            !rename(
                $temporary,
                $path
            )
        ) {
            @unlink(
                $temporary
            );

            throw new RuntimeException(
                'Unable to finalize the private Esubiz Core instance identity.'
            );
        }

        @chmod(
            $path,
            0640
        );
    }
}
