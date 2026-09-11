<?php

namespace App\Services\PlatformUpdates;

use App\Models\Website;
use RuntimeException;
use Throwable;

/**
 * Managed database checkpoint service for tenant updates.
 *
 * A successful checkpoint must exist before a backup-required
 * tenant update is allowed to execute.
 *
 * This service does not modify tenant schema, data or files.
 */
class TenantUpdateBackupService
{
    /**
     * Create a tenant database checkpoint.
     */
    public function create(
        Website $website,
        int $updateId
    ): array {
        $databaseConnection = $website->databaseConnection;

        if (!$databaseConnection) {
            throw new RuntimeException(
                'Cannot create checkpoint: tenant is not provisioned.'
            );
        }

        $database = (string) ($databaseConnection->database ?? '');

        $host = (string) (
            $databaseConnection->host
            ?? config('database.connections.mysql.host')
            ?? '127.0.0.1'
        );

        $port = (string) (
            $databaseConnection->port
            ?? config('database.connections.mysql.port')
            ?? '3306'
        );

        /*
         * WebsiteTenantDatabaseService is the authority for how Esubiz
         * connects to tenant databases. Tenant records identify the
         * database, while credentials come from the central mysql
         * connection configuration.
         */
        $username = (string) config(
            'database.connections.mysql.username',
            ''
        );

        $password = (string) config(
            'database.connections.mysql.password',
            ''
        );

        if ($database === '' || $username === '') {
            throw new RuntimeException(
                'Cannot create checkpoint: tenant database configuration is incomplete.'
            );
        }

        $directory = storage_path(
            'app/platform-update-checkpoints/' .
            $website->getKey()
        );

        if (!is_dir($directory) &&
            !mkdir($directory, 0700, true) &&
            !is_dir($directory)) {
            throw new RuntimeException(
                'Unable to create checkpoint directory.'
            );
        }

        $stamp = now()->format('Ymd_His');

        $filename = sprintf(
            'update_%d_%s.sql',
            $updateId,
            $stamp
        );

        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        /*
         * MYSQL_PWD avoids exposing the password directly in the
         * command argument list.
         */
        $command = sprintf(
            'MYSQL_PWD=%s mysqldump --single-transaction --quick ' .
            '--skip-lock-tables --host=%s --port=%s --user=%s %s > %s 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($path)
        );

        $exitCode = 1;

        try {
            exec($command, $output, $exitCode);
        } catch (Throwable $e) {
            @unlink($path);
            throw new RuntimeException(
                'Tenant database checkpoint command failed.',
                0,
                $e
            );
        }

        if ($exitCode !== 0 ||
            !is_file($path) ||
            filesize($path) === 0) {
            @unlink($path);

            throw new RuntimeException(
                'Tenant database checkpoint was not created successfully.'
            );
        }

        @chmod($path, 0600);

        return [
            'created' => true,
            'website_id' => $website->getKey(),
            'update_id' => $updateId,
            'type' => 'database',
            'path' => $path,
            'filename' => $filename,
            'size' => filesize($path),
            'sha256' => hash_file('sha256', $path),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
