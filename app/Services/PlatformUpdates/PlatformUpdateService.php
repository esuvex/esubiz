<?php

namespace App\Services\PlatformUpdates;

use App\Models\Website;
use App\Services\Website\TenantCoreInstallationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class PlatformUpdateService
{
    public function __construct(
        protected TenantCoreInstallationService $tenantCore,
        protected TenantMigrationStateService $migrationState
    ) {
    }

    /**
     * Preview whether an existing website can receive an update.
     *
     * IMPORTANT:
     * This method MUST NOT modify the tenant.
     */
    /**
     * Read-only Dry Run preview.
     *
     * This method MUST NOT:
     * - execute migrations;
     * - call migrateExisting();
     * - write tenant database records;
     * - modify tenant files.
     */
    public function preview(Website $website, object|array $update): array
    {
        $update = (array) $update;

        $base = [
            'website_id' => $website->getKey(),
            'website_name' => $website->name
                ?? $website->site_name
                ?? $website->domain
                ?? ('Website #' . $website->getKey()),
            'update_id' => $update['id'] ?? null,
            'update_name' => $update['name'] ?? null,
            'version' => $update['version'] ?? null,
            'product_type' => $update['product_type'] ?? 'core',
            'dry_run' => true,
            'read_only' => true,
        ];

        if (($update['product_type'] ?? 'core') !== 'core') {
            return $base + [
                'status' => 'unable_to_verify',
                'can_execute' => false,
                'message' => 'Read-only migration inspection currently supports Core updates only.',
            ];
        }

        $migrationPath = trim((string) ($update['migration_path'] ?? ''));

        if ($migrationPath === '') {
            return $base + [
                'status' => 'unable_to_verify',
                'can_execute' => false,
                'message' => 'This Core update has no migration path to inspect.',
            ];
        }

        $state = $this->migrationState->inspect(
            $website,
            $migrationPath
        );

        $status = $state['status'] ?? 'unable_to_verify';

        return $base + $state + [
            /*
             * Dry Run never executes anything.
             *
             * can_execute only describes whether the migration appears
             * pending from the read-only state check. The real execution
             * path must still independently enforce publication, backup,
             * destructive-update and other safety guards.
             */
            'can_execute' => $status === 'pending',
        ];
    }

    /**
     * Execute one approved Core update against one existing tenant.
     *
     * Existing-tenant rule:
     * migrateExisting() only.
     * Never install(), seed(), initialize(), or recreate tenant data.
     */
    public function execute(
        Website $website,
        object $update,
        ?int $executedBy = null
    ): array {
        $preview = $this->preview($website, $update);

        /*
         * ESUBIZ_PLATFORM_UPDATE_EXECUTE_PREVIEW_CONTRACT_V17
         *
         * preview() exposes the read-only migration eligibility state through
         * can_execute. Execution must consume that same contract rather than
         * the removed legacy eligible key.
         *
         * This does not bypass any execution safety rule. The controller still
         * requires a published, non-destructive update, while preview() only
         * permits execution when the tenant migration is actually pending.
         */
        if (!(bool) ($preview['can_execute'] ?? false)) {
            return $this->recordResult(
                $website,
                $update,
                'skipped',
                $preview['message']
                    ?? 'This update is not pending for this website.',
                $executedBy
            );
        }

        $run = DB::table('platform_update_runs')
            ->where('platform_update_id', $update->id)
            ->where('website_id', $website->id)
            ->first();

        if ($run && $run->status === 'updated') {
            return [
                'status' => 'skipped',
                'website_id' => $website->id,
                'update_id' => $update->id,
                'message' => 'This website has already received this update.',
            ];
        }

        $checkpoint = [
            'website_id' => $website->id,
            'update_id' => $update->id,
            'captured_at' => now()->toIso8601String(),
            'mode' => 'existing_tenant_migration',
        ];

        $this->upsertRun(
            $website,
            $update,
            'running',
            'Core update started.',
            $executedBy,
            [
                'checkpoint' => json_encode($checkpoint),
                'started_at' => now(),
                'finished_at' => null,
            ]
        );

        try {
            /*
             * This is the established safe existing-tenant path.
             * It runs pending tenant Core migrations only.
             */
            $this->tenantCore->migrateExisting($website);

            return $this->recordResult(
                $website,
                $update,
                'updated',
                'Core migrations completed successfully.',
                $executedBy
            );
        } catch (Throwable $e) {
            $message = $e->getMessage();

            /*
             * Non-provisioned Website records are classified as skipped,
             * not as ordinary migration failures.
             */
            $status = str_contains(
                strtolower($message),
                'no tenant database connection'
            ) ? 'skipped' : 'failed';

            return $this->recordResult(
                $website,
                $update,
                $status,
                $message,
                $executedBy
            );
        }
    }

    /**
     * Execute an update across selected website IDs.
     */
    public function executeMany(
        object $update,
        array $websiteIds,
        ?int $executedBy = null
    ): array {
        $results = [];

        $websites = Website::query()
            ->whereIn('id', array_values(array_unique($websiteIds)))
            ->get();

        foreach ($websites as $website) {
            $results[] = $this->execute(
                $website,
                $update,
                $executedBy
            );
        }

        return $results;
    }

    protected function recordResult(
        Website $website,
        object $update,
        string $status,
        ?string $message,
        ?int $executedBy
    ): array {
        $this->upsertRun(
            $website,
            $update,
            $status,
            $message,
            $executedBy,
            [
                'finished_at' => now(),
            ]
        );

        return [
            'status' => $status,
            'website_id' => $website->id,
            'update_id' => $update->id,
            'message' => $message,
        ];
    }

    protected function upsertRun(
        Website $website,
        object $update,
        string $status,
        ?string $message,
        ?int $executedBy,
        array $extra = []
    ): void {
        if (!Schema::hasTable('platform_update_runs')) {
            throw new RuntimeException(
                'Platform update run table is not installed.'
            );
        }

        $values = array_merge([
            'status' => $status,
            'to_version' => $update->version ?? null,
            'dry_run' => false,
            'message' => $message,
            'executed_by' => $executedBy,
            'updated_at' => now(),
        ], $extra);

        DB::table('platform_update_runs')->updateOrInsert(
            [
                'platform_update_id' => $update->id,
                'website_id' => $website->id,
            ],
            array_merge(
                [
                    'created_at' => now(),
                ],
                $values
            )
        );
    }
}
