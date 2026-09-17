<?php

namespace App\Services\PlatformUpdates;

use App\Models\Website;
use App\Services\Website\TenantCoreInstallationService;
use App\Services\Website\WebsiteMailboxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class PlatformUpdateService
{
    public function __construct(
        protected TenantCoreInstallationService $tenantCore,
        protected TenantMigrationStateService $migrationState,
        protected WebsiteMailboxService $websiteMailboxService
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

        /*
         * ESUBIZ_PLATFORM_UPDATE_POST_ACTION_RETRY_V1
         *
         * A tenant migration may succeed before its explicitly allow-listed
         * Central post-migration action fails. In that case the migration is
         * already applied, but the failed Central action must remain safely
         * retryable without rerunning the tenant migration.
         */
        $postMigrationAction = $this->postMigrationAction(
            (object) $update
        );

        $retryPostMigrationAction = false;

        if (
            $status !== 'pending'
            && $postMigrationAction !== null
            && isset($update['id'])
        ) {
            $retryPostMigrationAction =
                DB::table('platform_update_runs')
                    ->where(
                        'platform_update_id',
                        $update['id']
                    )
                    ->where(
                        'website_id',
                        $website->getKey()
                    )
                    ->where('status', 'failed')
                    ->exists();
        }

        return $base + $state + [
            /*
             * Dry Run never executes anything.
             *
             * Normal execution requires a pending migration. A failed
             * allow-listed post-migration action may also be retried after
             * its migration checkpoint has already been applied.
             */
            'can_execute' =>
                $status === 'pending'
                || $retryPostMigrationAction,

            'retry_post_migration_action' =>
                $retryPostMigrationAction,
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
            $retryPostMigrationAction =
                (bool) (
                    $preview['retry_post_migration_action']
                    ?? false
                );

            if (!$retryPostMigrationAction) {
                $this->tenantCore->migrateExisting($website);
            }

            /*
             * ESUBIZ_PLATFORM_UPDATE_POST_MIGRATION_ACTIONS_V1
             *
             * Some existing-SaaS upgrades require a Central-owned action
             * after the tenant schema migration succeeds.
             *
             * If that external action previously failed after the migration
             * succeeded, retry only the action. Never rerun an already
             * applied tenant migration merely to retry an external side
             * effect.
             *
             * Never execute arbitrary classes, methods or shell commands
             * from update metadata.
             */
            $postMigrationAction =
                $this->postMigrationAction($update);

            if ($postMigrationAction !== null) {
                $this->executePostMigrationAction(
                    $website,
                    $postMigrationAction
                );
            }

            return $this->recordResult(
                $website,
                $update,
                'updated',
                $postMigrationAction === null
                    ? 'Core migrations completed successfully.'
                    : 'Core migration and post-migration action completed successfully.',
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

    /**
     * Resolve an optional, explicitly declared post-migration action.
     */
    protected function postMigrationAction(
        object $update
    ): ?string {
        $manifest = $update->manifest ?? null;

        if (is_string($manifest)) {
            $manifest = json_decode(
                $manifest,
                true
            );
        } elseif (is_object($manifest)) {
            $manifest = (array) $manifest;
        }

        if (!is_array($manifest)) {
            return null;
        }

        $action = trim(
            (string) (
                $manifest['post_migration_action']
                ?? ''
            )
        );

        return $action !== ''
            ? $action
            : null;
    }

    /**
     * ESUBIZ_PLATFORM_UPDATE_POST_MIGRATION_ACTION_ALLOWLIST_V1
     *
     * Central actions are deliberately allow-listed. Update metadata
     * cannot dynamically invoke arbitrary PHP code.
     */
    protected function executePostMigrationAction(
        Website $website,
        string $action
    ): void {
        match ($action) {
            'provision_included_saas_mailbox' =>
                $this->provisionIncludedSaasMailbox(
                    $website
                ),

            default => throw new RuntimeException(
                'Unsupported platform update post-migration action: '
                . $action
            ),
        };
    }

    /**
     * Backfill the included Esubiz-managed mailbox for an existing
     * SaaS website through the same idempotent authority used by
     * newly provisioned SaaS websites.
     */
    protected function provisionIncludedSaasMailbox(
        Website $website
    ): void {
        if (!$website->isSaas()) {
            return;
        }

        $this->websiteMailboxService->provisionSaas(
            $website
        );
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
