<?php

namespace App\Services\Core\Installation;

use RuntimeException;

/**
 * ESUBIZ_CORE_ENVIRONMENT_WRITER_V1
 *
 * Safely writes the minimum environment configuration required
 * during the initial OFF-SERVER Core installation.
 *
 * Responsibilities:
 *
 * - preserve unrelated .env values;
 * - create an installation-time backup;
 * - safely encode values;
 * - configure the validated database;
 * - configure the website URL;
 * - explicitly declare off_server deployment;
 * - provide rollback support.
 *
 * This service MUST NOT store:
 *
 * - Esubiz licence keys;
 * - administrator passwords;
 * - other installer-only secrets.
 */
class CoreEnvironmentWriter
{
    protected ?string $backupPath = null;

    /**
     * @param array<string,mixed> $website
     * @param array<string,mixed> $database
     */
    public function write(
        array $website,
        array $database
    ): array {
        $envPath = base_path('.env');

        if (!is_file($envPath)) {
            throw new RuntimeException(
                'The Core .env file does not exist.'
            );
        }

        if (!is_readable($envPath)) {
            throw new RuntimeException(
                'The Core .env file is not readable.'
            );
        }

        if (!is_writable($envPath)) {
            throw new RuntimeException(
                'The Core .env file is not writable.'
            );
        }

        $original = file_get_contents(
            $envPath
        );

        if ($original === false) {
            throw new RuntimeException(
                'Unable to read the Core .env file.'
            );
        }

        $this->backupPath =
            storage_path(
                'app/core/installation-backups/'
                . '.env.'
                . date('Ymd-His')
                . '.bak'
            );

        $backupDirectory =
            dirname(
                $this->backupPath
            );

        if (
            !is_dir($backupDirectory)
            && !mkdir(
                $backupDirectory,
                0750,
                true
            )
            && !is_dir($backupDirectory)
        ) {
            throw new RuntimeException(
                'Unable to create the Core installation backup directory.'
            );
        }

        if (
            file_put_contents(
                $this->backupPath,
                $original,
                LOCK_EX
            ) === false
        ) {
            throw new RuntimeException(
                'Unable to back up the Core .env file.'
            );
        }

        @chmod(
            $this->backupPath,
            0640
        );

        $domain = $this->normaliseDomain(
            (string) (
                $website['domain']
                ?? ''
            )
        );

        if ($domain === '') {
            throw new RuntimeException(
                'A valid website domain is required.'
            );
        }

        $siteName = trim(
            (string) (
                $website['site_name']
                ?? 'Esubiz Website'
            )
        );

        if ($siteName === '') {
            $siteName =
                'Esubiz Website';
        }

        $values = [
            'APP_NAME' =>
                $siteName,

            'APP_URL' =>
                'https://' . $domain,

            'ESUBIZ_DEPLOYMENT' =>
                'off_server',

            'DB_CONNECTION' =>
                'mysql',

            'DB_HOST' =>
                (string) (
                    $database['host']
                    ?? '127.0.0.1'
                ),

            'DB_PORT' =>
                (string) (
                    $database['port']
                    ?? 3306
                ),

            'DB_DATABASE' =>
                (string) (
                    $database['database']
                    ?? ''
                ),

            'DB_USERNAME' =>
                (string) (
                    $database['username']
                    ?? ''
                ),

            'DB_PASSWORD' =>
                (string) (
                    $database['password']
                    ?? ''
                ),
        ];

        $updated = $original;

        foreach (
            $values as $key => $value
        ) {
            $updated =
                $this->setValue(
                    $updated,
                    $key,
                    $value
                );
        }

        $temporary =
            $envPath
            . '.esubiz-installing-'
            . bin2hex(
                random_bytes(6)
            );

        try {
            if (
                file_put_contents(
                    $temporary,
                    $updated,
                    LOCK_EX
                ) === false
            ) {
                throw new RuntimeException(
                    'Unable to prepare the Core environment configuration.'
                );
            }

            @chmod(
                $temporary,
                0640
            );

            /*
             * Rename keeps the final write as close to atomic as
             * the underlying filesystem permits.
             */
            if (
                !rename(
                    $temporary,
                    $envPath
                )
            ) {
                throw new RuntimeException(
                    'Unable to replace the Core environment configuration.'
                );
            }

            @chmod(
                $envPath,
                0640
            );
        } catch (\Throwable $e) {
            if (is_file($temporary)) {
                @unlink(
                    $temporary
                );
            }

            $this->rollback();

            throw $e;
        }

        return [
            'environment_written' =>
                true,

            'backup_path' =>
                $this->backupPath,

            'app_url' =>
                'https://' . $domain,

            'deployment' =>
                'off_server',

            'database' =>
                (string) (
                    $database['database']
                    ?? ''
                ),
        ];
    }

    /**
     * Restore the .env file created immediately before write().
     */
    public function rollback(): void
    {
        if (
            !$this->backupPath
            || !is_file(
                $this->backupPath
            )
        ) {
            return;
        }

        $envPath =
            base_path('.env');

        $backup =
            file_get_contents(
                $this->backupPath
            );

        if ($backup === false) {
            throw new RuntimeException(
                'Unable to read the Core environment backup during rollback.'
            );
        }

        $temporary =
            $envPath
            . '.esubiz-rollback-'
            . bin2hex(
                random_bytes(6)
            );

        if (
            file_put_contents(
                $temporary,
                $backup,
                LOCK_EX
            ) === false
        ) {
            throw new RuntimeException(
                'Unable to prepare the Core environment rollback.'
            );
        }

        @chmod(
            $temporary,
            0640
        );

        if (
            !rename(
                $temporary,
                $envPath
            )
        ) {
            @unlink(
                $temporary
            );

            throw new RuntimeException(
                'Unable to restore the Core environment configuration.'
            );
        }

        @chmod(
            $envPath,
            0640
        );
    }

    public function backupPath(): ?string
    {
        return $this->backupPath;
    }

    protected function setValue(
        string $contents,
        string $key,
        string $value
    ): string {
        $encoded =
            $this->encodeValue(
                $value
            );

        $line =
            $key
            . '='
            . $encoded;

        $pattern =
            '/^(?:\s*#\s*)?'
            . preg_quote(
                $key,
                '/'
            )
            . '\s*=.*$/m';

        if (
            preg_match(
                $pattern,
                $contents
            ) === 1
        ) {
            return preg_replace(
                $pattern,
                $line,
                $contents,
                1
            ) ?? $contents;
        }

        $contents =
            rtrim(
                $contents
            );

        return $contents
            . PHP_EOL
            . $line
            . PHP_EOL;
    }

    protected function encodeValue(
        string $value
    ): string {
        /*
         * Always quote installer-written strings so spaces,
         * # characters and other dotenv-sensitive values do not
         * corrupt the resulting environment file.
         */
        $value = str_replace(
            [
                '\\',
                '"',
                "\r",
                "\n",
            ],
            [
                '\\\\',
                '\\"',
                '',
                '\\n',
            ],
            $value
        );

        return '"'
            . $value
            . '"';
    }

    protected function normaliseDomain(
        string $domain
    ): string {
        $domain =
            strtolower(
                trim(
                    $domain
                )
            );

        $domain =
            preg_replace(
                '#^https?://#i',
                '',
                $domain
            ) ?? $domain;

        $domain =
            trim(
                $domain,
                '/'
            );

        /*
         * Do not allow paths, query strings, fragments or whitespace
         * to become APP_URL.
         */
        if (
            $domain === ''
            || str_contains(
                $domain,
                '/'
            )
            || str_contains(
                $domain,
                '?'
            )
            || str_contains(
                $domain,
                '#'
            )
            || preg_match(
                '/\s/',
                $domain
            )
        ) {
            return '';
        }

        return $domain;
    }
}
