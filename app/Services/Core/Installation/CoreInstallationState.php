<?php

namespace App\Services\Core\Installation;

use RuntimeException;

/**
 * ESUBIZ_CORE_ROOT_FIRST_RUN_STATE_V1
 *
 * Authoritative first-install state for an OFF-SERVER Core.
 *
 * There is deliberately NO permanent /install URL requirement.
 *
 * FIRST RUN
 * ---------
 *
 * https://example.com/
 *          |
 *          v
 * Core not installed
 *          |
 *          v
 * Root request presents/runs the built-in first-time setup
 *          |
 *          v
 * License + database + website/admin configuration
 *          |
 *          v
 * Installation completes
 *          |
 *          v
 * Installed lock is written
 *          |
 *          v
 * https://example.com/ becomes the normal website
 *
 * AFTER INSTALLATION
 * ------------------
 *
 * First-run setup must never become reachable merely by knowing
 * a special URL. The installed lock is authoritative.
 *
 * This state service is ONLY for the initial Core/website setup.
 *
 * It is NOT the permanent Marketplace installer/updater.
 */
class CoreInstallationState
{
    /**
     * Relative location inside storage/app.
     *
     * It deliberately lives outside public/.
     */
    private const LOCK_FILE =
        'core/esubiz-installed.lock';

    /**
     * Determine whether initial Core installation has completed.
     */
    public function isInstalled(): bool
    {
        return is_file(
            $this->lockPath()
        );
    }

    /**
     * Determine whether the root request should enter
     * first-run setup.
     */
    public function requiresInstallation(): bool
    {
        return !$this->isInstalled();
    }

    /**
     * Mark Core as successfully installed.
     *
     * IMPORTANT:
     * Call this ONLY after every required installation stage
     * succeeds, including license validation and database/setup.
     *
     * @param array<string,mixed> $metadata
     */
    public function markInstalled(
        array $metadata = []
    ): void {
        $path = $this->lockPath();

        $directory = dirname(
            $path
        );

        if (
            !is_dir($directory)
            && !mkdir(
                $directory,
                0755,
                true
            )
            && !is_dir($directory)
        ) {
            throw new RuntimeException(
                'Unable to create Core installation-state directory.'
            );
        }

        $payload = [
            'schema' =>
                'esubiz-core-installation-state',

            'schema_version' =>
                '1.0',

            'installed' =>
                true,

            'installed_at' =>
                now()->toIso8601String(),

            /*
             * Keep this metadata non-sensitive.
             *
             * Never store DB passwords, license keys,
             * API secrets or APP_KEY here.
             */
            'metadata' =>
                $metadata,
        ];

        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
        );

        if (
            file_put_contents(
                $path,
                $json . PHP_EOL,
                LOCK_EX
            ) === false
        ) {
            throw new RuntimeException(
                'Unable to write Core installation state.'
            );
        }

        @chmod(
            $path,
            0640
        );
    }

    /**
     * Return the absolute private lock-file path.
     */
    public function lockPath(): string
    {
        return storage_path(
            'app/' . self::LOCK_FILE
        );
    }
}
