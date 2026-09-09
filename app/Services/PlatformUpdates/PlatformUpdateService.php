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
        protected TenantCoreInstallationService $tenantCore
    ) {
    }

    /**
     * Preview whether an existing website can receive an update.
     *
     * IMPORTANT:
     * This method MUST NOT modify the tenant.
     */
    public function preview(Website $website, object $update): array
    {
        $result = [
            'eligible' => false,
            'status' => 'skipped',
            'website_id' => $website->id,
            'update_id' => $update->id,
            'message' => null,
        ];

        if (($update->status ?? 'draft') !== 'published') {
            $result['message'] = 'Update is not published.';
            return $result;
        }

        if ((bool) ($update->is_destructive ?? false)) {
            $result['message'] =
                'Destructive updates cannot run through the standard updater.';
            return $result;
        }

        if (($update->product_type ?? 'core') !== 'core') {
            $result['message'] =
                'This update type is not yet supported by the Core migration runner.';
            return $result;
        }

        /*
         * Backup enforcement:
         *
         * A registered update may require a real tenant backup before
         * execution. The backup engine has not yet been connected, so
         * such an update must never be reported as execution-ready.
         *
         * Dry Run remains available conceptually, but execution-ready
         * eligibility is withheld until the backup service exists.
         */
        if ((bool) ($update->requires_backup ?? true)) {
            $result['message'] =
                'Backup is required for this update. The managed backup engine must complete before execution.';
            return $result;
        }

        /*
         * Dry-run intentionally does not guess tenant database column
         * names and does not connect to or mutate the tenant.
         *
         * TenantCoreInstallationService remains authoritative for the
         * actual existing-tenant connection. If execution discovers
         * that the Website has no provisioned tenant database, the run
         * is recorded as skipped rather than failed.
         */

        $result['eligible'] = true;
        $result['status'] = 'eligible';
        $result['message'] = 'Website is eligible for this Core update.';

        return $result;
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

        if (!$preview['eligible']) {
            return $this->recordResult(
                $website,
                $update,
                'skipped',
                $preview['message'],
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
