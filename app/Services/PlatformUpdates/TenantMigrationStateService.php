<?php

namespace App\Services\PlatformUpdates;

use App\Models\Website;
use App\Services\Website\WebsiteTenantDatabaseService;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Strictly read-only tenant migration state inspector.
 *
 * This service uses the same authoritative tenant database connection
 * lifecycle as TenantCoreInstallationService, but it NEVER:
 *
 * - creates the migrations table;
 * - executes Artisan migrations;
 * - calls migrateExisting();
 * - inserts, updates or deletes tenant records;
 * - modifies tenant files.
 */
class TenantMigrationStateService
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    /**
     * Inspect one Core migration against one tenant.
     */
    public function inspect(Website $website, string $migrationPath): array
    {
        $migration = pathinfo($migrationPath, PATHINFO_FILENAME);

        if ($migration === '') {
            return $this->result(
                'unable_to_verify',
                $migration,
                'Migration filename could not be determined.'
            );
        }

        /*
         * databaseConnection is the same provisioning authority used by
         * TenantCoreInstallationService before it connects a tenant.
         */
        if (!$website->databaseConnection) {
            return $this->result(
                'skipped',
                $migration,
                'Tenant is not provisioned or has no tenant database connection.'
            );
        }

        $connected = false;

        try {
            /*
             * Authoritative Esubiz tenant connection setup.
             *
             * This configures/connects the existing website_tenant
             * connection. It does not run migrations.
             */
            $this->tenantDatabaseService->connect($website);
            $connected = true;

            $connection = DB::connection('website_tenant');
            $schema = $connection->getSchemaBuilder();

            /*
             * READ ONLY:
             * Do NOT reproduce TenantCoreInstallationService::
             * createMigrationTable().
             */
            if (!$schema->hasTable('migrations')) {
                return $this->result(
                    'unable_to_verify',
                    $migration,
                    'Tenant migration repository does not exist. Dry Run did not create it.'
                );
            }

            /*
             * SELECT/EXISTS only.
             */
            $applied = $connection
                ->table('migrations')
                ->where('migration', $migration)
                ->exists();

            if ($applied) {
                return $this->result(
                    'already_applied',
                    $migration,
                    'Migration is already recorded for this tenant.'
                );
            }

            return $this->result(
                'pending',
                $migration,
                'Migration is not recorded for this tenant.'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->result(
                'unable_to_verify',
                $migration,
                'Tenant migration state could not be verified safely.'
            );
        } finally {
            if ($connected) {
                /*
                 * Always restore the central application connection state.
                 */
                try {
                    $this->tenantDatabaseService->disconnect();
                } catch (Throwable $e) {
                    report($e);
                }
            }
        }
    }

    protected function result(
        string $status,
        string $migration,
        string $message
    ): array {
        return [
            'status' => $status,
            'migration' => $migration,
            'message' => $message,
            'read_only' => true,
        ];
    }
}
