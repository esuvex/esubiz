<?php

namespace App\Services\Website;

use App\Models\Website;
use Illuminate\Support\Facades\Storage;

class WebsiteResourceUsageService
{
    /*
     * ESUBIZ_WEBSITE_RESOURCE_USAGE_V1
     *
     * Resource accounting is always scoped to one Website.
     * Never scan another tenant or the landlord storage area.
     */

    public function storage(Website $website): array
    {
        $disk = Storage::disk('local');

        $directory =
            'tenant-websites/'
            . $website->id;

        $bytes = 0;
        $files = 0;

        if ($disk->exists($directory)) {
            foreach ($disk->allFiles($directory) as $file) {
                try {
                    $bytes += (int) $disk->size($file);
                    $files++;
                } catch (\Throwable $e) {
                    // Ignore an individual unreadable file without
                    // invalidating the website's complete meter.
                }
            }
        }

        /*
         * ESUBIZ_CORE_SHARED_STORAGE_MAILBOX_USAGE_V2
         *
         * Core has ONE storage pool.
         *
         * Existing filesystem usage plus live managed-mailbox usage
         * are combined here. Physical deletion therefore naturally
         * frees storage:
         *
         * - deleted media/files disappear from the filesystem scan;
         * - permanently purged email disappears from mailbox usage.
         *
         * Moving an email to Trash does not free storage because the
         * message still physically exists.
         */
        $mailboxBytes =
            app(
                WebsiteMailboxService::class
            )->storageBytes(
                $website
            );

        $bytes +=
            max(
                0,
                $mailboxBytes
            );

        $usedMb =
            $bytes > 0
                ? $bytes / 1024 / 1024
                : 0;

        /*
         * ESUBIZ_CORE_RESOURCE_LIMIT_FALLBACK_V1
         *
         * Legacy Core websites may still contain zero because they
         * were provisioned before resource defaults were introduced.
         *
         * Zero therefore falls back to the Core base allocation.
         * Any explicit plan/add-on allocation remains authoritative.
         */
        $limitMb =
            (int) $website->storage_mb > 0
                ? (int) $website->storage_mb
                : 1024;

        $percentage =
            $limitMb > 0
                ? min(
                    100,
                    ($usedMb / $limitMb) * 100
                )
                : 0;

        return [
            'bytes' => $bytes,
            'files' => $files,

            'used_mb' =>
                round(
                    $usedMb,
                    2
                ),

            'limit_mb' =>
                $limitMb,

            'used_gb' =>
                round(
                    $usedMb / 1024,
                    3
                ),

            'limit_gb' =>
                round(
                    $limitMb / 1024,
                    2
                ),

            'percentage' =>
                round(
                    $percentage,
                    2
                ),

            'unlimited' =>
                $limitMb === 0,
        ];
    }

    public function summary(Website $website): array
    {
        return [
            'storage' =>
                $this->storage(
                    $website
                ),

            /*
             * ESUBIZ_WEBSITE_MONTHLY_BANDWIDTH_SUMMARY_V1
             *
             * Read actual current-month tenant transfer events.
             */
            'bandwidth' =>
                app(
                    \App\Services\Website\TenantBandwidthUsageService::class
                )->currentMonth(
                    $website
                ),
        ];
    }
}
