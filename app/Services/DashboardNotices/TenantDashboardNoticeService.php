<?php

namespace App\Services\DashboardNotices;

use App\Models\DashboardNotice;
use App\Models\Website;
use Illuminate\Support\Collection;

class TenantDashboardNoticeService
{
    public function forWebsite(Website $website): Collection
    {
        return $this->forCoreContext(
            'saas',
            (int) $website->id
        );
    }

    public function forOffServerCore(): Collection
    {
        return $this->forCoreContext('off_server');
    }

    public function forCoreContext(
        string $context,
        ?int $websiteId = null
    ): Collection {
        $now = now();

        return DashboardNotice::query()
            ->where('status', 'published')
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            })
            ->where(
                function ($query) use ($context) {
                    /*
                     * ESUBIZ_DASHBOARD_NOTICE_CHANNEL_FILTER_V15
                     *
                     * New notices use delivery_channels.
                     * Legacy delivery_scope remains a fallback.
                     */
                    $query
                        ->whereJsonContains(
                            'delivery_channels',
                            $context
                        )
                        ->orWhere(
                            function ($legacy) use ($context) {
                                $legacy
                                    ->whereNull(
                                        'delivery_channels'
                                    )
                                    ->where(
                                        function ($scope) use ($context) {
                                            $scope
                                                ->where(
                                                    'delivery_scope',
                                                    'all'
                                                )
                                                ->orWhere(
                                                    'delivery_scope',
                                                    $context
                                                );
                                        }
                                    );
                            }
                        );
                }
            )
            ->latest('published_at')
            ->latest('id')
            ->get()
            ->filter(function (DashboardNotice $notice) use (
                $context,
                $websiteId
            ) {
                /*
                 * Off-server Core installations are not Central Website
                 * records, so SaaS tenant targeting does not apply there.
                 */
                if ($context === 'off_server') {
                    return true;
                }

                if ($notice->target_type === 'all') {
                    return true;
                }

                if (
                    $notice->target_type !== 'selected'
                    || !$websiteId
                ) {
                    return false;
                }

                $tenantIds = array_map(
                    'intval',
                    $notice->tenant_ids ?? []
                );

                return in_array(
                    $websiteId,
                    $tenantIds,
                    true
                );
            })
            ->values();
    }
}
