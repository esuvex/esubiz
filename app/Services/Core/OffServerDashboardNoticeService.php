<?php

namespace App\Services\Core;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use stdClass;
use Throwable;

class OffServerDashboardNoticeService
{
    /**
     * ESUBIZ_OFF_SERVER_DASHBOARD_NOTICE_CLIENT_V1
     *
     * Off-server Core -> Central Esubiz Dashboard Notices.
     *
     * Security:
     *
     * - reuses the encrypted installation bearer token
     * - requires website.identity scope
     * - bearer token never reaches Blade / JavaScript
     * - Central determines authoritative website identity
     *
     * Reliability:
     *
     * - short timeout
     * - short local cache
     * - Central failure never breaks Core dashboard
     * - invalid responses fail closed to an empty collection
     */
    public function __construct(
        protected CoreCentralConnectionService $central
    ) {
    }


    /**
     * Return currently available Central Dashboard Notices
     * for this off-server Core installation.
     */
    public function notices(): Collection
    {
        try {
            $connection =
                $this->central->current();

            if (
                !$connection
                || !$connection->active
            ) {
                return collect();
            }

            /*
             * Dashboard Notices use the already-authorized
             * website.identity service scope.
             */
            if (
                !$this->central->hasScope(
                    'website.identity'
                )
            ) {
                return collect();
            }

            $installationKey =
                (string) (
                    $connection->installation_uuid
                    ?: $connection->id
                );

            $cacheKey =
                'esubiz.core.dashboard-notices.'
                . hash(
                    'sha256',
                    $installationKey
                );

            /*
             * Notices are intentionally cached only briefly.
             *
             * Admin publish/disable/expiry changes should reach
             * off-server Core installations quickly without
             * causing a Central API request on every page load.
             */
            return Cache::remember(
                $cacheKey,
                now()->addSeconds(60),
                function (): Collection {
                    return $this->fetch();
                }
            );
        } catch (Throwable $e) {
            /*
             * Central availability must never determine whether
             * the Core admin dashboard itself can load.
             */
            Log::warning(
                'Esubiz off-server Dashboard Notices could not be loaded.',
                [
                    'exception' =>
                        $e->getMessage(),
                ]
            );

            return collect();
        }
    }


    /**
     * Fetch the authenticated notice feed from Central.
     */
    protected function fetch(): Collection
    {
        try {
            $url =
                $this->central->centralUrl()
                . '/api/v1/core/dashboard-notices';

            $response =
                $this->central
                    ->request(6)
                    ->get($url);

            if (
                !$response->successful()
            ) {
                Log::warning(
                    'Central Esubiz Dashboard Notice API returned a non-success response.',
                    [
                        'status' =>
                            $response->status(),
                    ]
                );

                return collect();
            }

            $payload =
                $response->json();

            if (
                !is_array(
                    $payload
                )
            ) {
                return collect();
            }

            /*
             * Accept the Central API's normal notices/data
             * envelope while remaining backward-compatible
             * with a direct list payload.
             */
            $items =
                $payload['notices']
                ?? $payload['data']
                ?? $payload;

            if (
                !is_array(
                    $items
                )
            ) {
                return collect();
            }

            return collect(
                $items
            )
                ->filter(
                    fn ($notice) =>
                        is_array($notice)
                        && !empty(
                            $notice['id']
                            ?? null
                        )
                )
                ->map(
                    function (
                        array $notice
                    ): stdClass {
                        return $this->normalize(
                            $notice
                        );
                    }
                )
                ->values();
        } catch (Throwable $e) {
            Log::warning(
                'Central Esubiz Dashboard Notice request failed.',
                [
                    'exception' =>
                        $e->getMessage(),
                ]
            );

            return collect();
        }
    }


    /**
     * Normalize Central API payload into the same property
     * contract used by the hosted SaaS Dashboard Notice view.
     *
     * This allows SaaS and off-server Core to share one
     * rendering/runtime implementation.
     *
     * @param array<string,mixed> $notice
     */
    protected function normalize(
        array $notice
    ): stdClass {
        $normalized =
            new stdClass();

        $normalized->id =
            $notice['id']
            ?? null;

        $normalized->title =
            (string) (
                $notice['title']
                ?? ''
            );

        /*
         * Central API transports trusted Admin-authored HTML
         * as message_html.
         *
         * The Core Blade contract uses ->message.
         */
        $normalized->message =
            (string) (
                $notice['message_html']
                ?? $notice['message']
                ?? ''
            );

        $variant =
            strtolower(
                (string) (
                    $notice['variant']
                    ?? 'info'
                )
            );

        $allowedVariants = [
            'info',
            'success',
            'warning',
            'danger',
            'primary',
            'secondary',
        ];

        $normalized->variant =
            in_array(
                $variant,
                $allowedVariants,
                true
            )
                ? $variant
                : 'info';

        /*
         * For off-server Core, Central sends the generated
         * public media URL as transport.
         *
         * image_path remains a Central-only storage concern.
         */
        $normalized->image_path =
            null;

        $normalized->image_url =
            !empty(
                $notice['image_url']
            )
                ? (string) $notice['image_url']
                : null;

        $normalized->dismissible =
            array_key_exists(
                'dismissible',
                $notice
            )
                ? (bool) $notice['dismissible']
                : true;

        $normalized->rotation_enabled =
            array_key_exists(
                'rotation_enabled',
                $notice
            )
                ? (bool) $notice[
                    'rotation_enabled'
                ]
                : true;

        $normalized->rotation_seconds =
            max(
                3,
                min(
                    120,
                    (int) (
                        $notice[
                            'rotation_seconds'
                        ]
                        ?? 8
                    )
                )
            );

        $normalized->published_at =
            $notice['published_at']
            ?? null;

        $normalized->expires_at =
            $notice['expires_at']
            ?? null;

        return $normalized;
    }


    /**
     * Explicitly clear the local notice cache.
     *
     * Useful for future Core maintenance/admin tools.
     */
    public function forget(): void
    {
        try {
            $connection =
                $this->central->current();

            if (
                !$connection
            ) {
                return;
            }

            $installationKey =
                (string) (
                    $connection->installation_uuid
                    ?: $connection->id
                );

            Cache::forget(
                'esubiz.core.dashboard-notices.'
                . hash(
                    'sha256',
                    $installationKey
                )
            );
        } catch (Throwable $e) {
            /*
             * Cache cleanup should never break Core.
             */
        }
    }
}
