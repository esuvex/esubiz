<?php

namespace App\Services\Website;

use App\Models\Website;
use Illuminate\Support\Facades\DB;

class TenantBandwidthUsageService
{
    /*
     * ESUBIZ_TENANT_BANDWIDTH_CANONICAL_DB_CONNECTION_V2
     *
     * Always use Esubiz's existing website tenant database lifecycle.
     */
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    /*
     * ESUBIZ_TENANT_BANDWIDTH_USAGE_V1
     *
     * Canonical tenant bandwidth accounting.
     *
     * Core resource:
     * bandwidth = 40 GB
     *
     * Usage is stored in the tenant database so one website can
     * never consume or modify another website's bandwidth records.
     */

    public function recordBytes(
        Website $website,
        int $bytes,
        ?string $sourceType = null,
        ?int $sourceId = null,
        array $metadata = []
    ): void {
        if ($bytes <= 0) {
            return;
        }

        $this->tenantDatabaseService->connect(
            $website
        );

        $db =
            $this->tenantDatabaseService->connection();

        /*
         * resource_usage is defined in GB, so preserve sufficient
         * decimal precision when converting transferred bytes.
         */
        $gigabytes =
            $bytes / 1024 / 1024 / 1024;

        $db->transaction(
                function () use (
                    $db,
                    $gigabytes,
                    $bytes,
                    $sourceType,
                    $sourceId,
                    $metadata
                ) {
                    $connection =
                        $db;

                    $usage =
                        $connection
                            ->table('resource_usage')
                            ->where(
                                'resource',
                                'bandwidth'
                            )
                            ->lockForUpdate()
                            ->first();

                    if (!$usage) {
                        $connection
                            ->table('resource_usage')
                            ->insert([
                                'resource' => 'bandwidth',
                                'used' => 0,
                                'limit' =>
                                    config(
                                        'esubiz_core.resources.bandwidth.limit',
                                        40
                                    ),
                                'is_unlimited' => false,
                                'unit' => 'GB',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                    }

                    $connection
                        ->table('resource_usage')
                        ->where(
                            'resource',
                            'bandwidth'
                        )
                        ->increment(
                            'used',
                            $gigabytes,
                            [
                                'updated_at' => now(),
                            ]
                        );

                    $connection
                        ->table('resource_usage_events')
                        ->insert([
                            'resource' => 'bandwidth',
                            'action' => 'transfer',
                            'quantity' => $gigabytes,
                            'source_type' => $sourceType,
                            'source_id' => $sourceId,
                            'metadata' =>
                                json_encode(
                                    array_merge(
                                        $metadata,
                                        [
                                            'bytes' => $bytes,
                                            'period' =>
                                                now()->format('Y-m'),
                                        ]
                                    )
                                ),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
            );
    }

    /*
     * Return the current calendar month's actual transfer.
     *
     * We calculate monthly usage from events rather than relying on
     * the lifetime aggregate in resource_usage. This gives the
     * dashboard a true monthly bandwidth meter without destroying
     * historical transfer records.
     */
    public function currentMonth(Website $website): array
    {
        $limitGb =
            (float) (
                (int) $website->bandwidth_mb > 0
                    ? $website->bandwidth_mb / 1024
                    : config(
                        'esubiz_core.resources.bandwidth.limit',
                        40
                    )
            );

        try {
            $this->tenantDatabaseService->connect(
                $website
            );

            $db =
                $this->tenantDatabaseService->connection();

            $usedGb =
                (float) $db
                    ->table('resource_usage_events')
                    ->where(
                        'resource',
                        'bandwidth'
                    )
                    ->where(
                        'action',
                        'transfer'
                    )
                    ->where(
                        'created_at',
                        '>=',
                        now()->startOfMonth()
                    )
                    ->where(
                        'created_at',
                        '<',
                        now()
                            ->copy()
                            ->startOfMonth()
                            ->addMonth()
                    )
                    ->sum('quantity');
        } catch (\Throwable $e) {
            return $this->emptyUsage(
                $limitGb
            );
        }

        /*
         * ESUBIZ_BANDWIDTH_VISIBLE_USAGE_CAP_V1
         *
         * Preserve raw transfer events for internal accounting,
         * but a capped tenant resource must never visually exceed
         * its entitlement.
         */
        $visibleUsedGb =
            $limitGb > 0
                ? min(
                    $usedGb,
                    $limitGb
                )
                : $usedGb;

        $percentage =
            $limitGb > 0
                ? min(
                    100,
                    ($visibleUsedGb / $limitGb) * 100
                )
                : 0;

        return [
            'used_gb' =>
                round(
                    $visibleUsedGb,
                    4
                ),

            'used_mb' =>
                round(
                    $visibleUsedGb * 1024,
                    2
                ),

            'limit_gb' =>
                round(
                    $limitGb,
                    2
                ),

            'limit_mb' =>
                round(
                    $limitGb * 1024,
                    2
                ),

            'percentage' =>
                round(
                    $percentage,
                    2
                ),

            'tracking' => true,
            'period' =>
                now()->format('Y-m'),
        ];
    }

    protected function emptyUsage(
        float $limitGb
    ): array {
        return [
            'used_gb' => 0,
            'used_mb' => 0,
            'limit_gb' =>
                round(
                    $limitGb,
                    2
                ),
            'limit_mb' =>
                round(
                    $limitGb * 1024,
                    2
                ),
            'percentage' => 0,
            'tracking' => true,
            'period' =>
                now()->format('Y-m'),
        ];
    }
}
