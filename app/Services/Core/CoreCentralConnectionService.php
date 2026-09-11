<?php

namespace App\Services\Core;

use App\Models\CoreCentralConnection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CoreCentralConnectionService
{
    /**
     * ESUBIZ_OFF_SERVER_CENTRAL_CONNECTION_SERVICE_V1
     *
     * Universal Core -> Central Esubiz authenticated bridge.
     *
     * Dashboard Notices, AI, SMS, WhatsApp, Email,
     * Marketplace and future Central services should
     * reuse this service instead of storing their own
     * bearer tokens.
     */

    /**
     * ESUBIZ_CORE_CENTRAL_CONNECTION_SAFE_DETECTION_V3
     *
     * Hosted SaaS Core installations do not require the local
     * off-server Central connection table.
     *
     * Therefore a missing/unavailable local connection store
     * must resolve to null rather than breaking the dashboard.
     */
    public function current(): ?CoreCentralConnection
    {
        try {
            if (
                !\Illuminate\Support\Facades\Schema::hasTable(
                    'core_central_connections'
                )
            ) {
                return null;
            }

            return CoreCentralConnection::query()
                ->where(
                    'active',
                    true
                )
                ->latest(
                    'id'
                )
                ->first();
        } catch (\Throwable $e) {
            /*
             * Connection discovery is optional for hosted SaaS.
             *
             * A failure here means "no usable off-server
             * Central connection" rather than a dashboard error.
             */
            return null;
        }
    }


    /**
     * Persist Central API credentials returned after
     * successful off-server Core activation.
     *
     * @param array<string,mixed> $activation
     */
    public function storeFromActivation(
        array $activation,
        ?string $centralUrl = null
    ): CoreCentralConnection {
        $centralApi =
            $activation[
                'central_api'
            ]
            ?? null;

        if (
            !is_array(
                $centralApi
            )
        ) {
            throw new RuntimeException(
                'The Core activation response has no Central API credentials.'
            );
        }

        $accessToken =
            trim(
                (string) (
                    $centralApi[
                        'access_token'
                    ]
                    ?? ''
                )
            );

        if (
            $accessToken === ''
        ) {
            throw new RuntimeException(
                'The Core activation response has no Central access token.'
            );
        }

        $resolvedCentralUrl =
            rtrim(
                (string) (
                    $centralUrl
                    ?: config(
                        'services.esubiz.marketplace_url',
                        config(
                            'app.url'
                        )
                    )
                ),
                '/'
            );

        if (
            $resolvedCentralUrl === ''
        ) {
            throw new RuntimeException(
                'The Central Esubiz URL is not configured.'
            );
        }

        return DB::transaction(
            function () use (
                $activation,
                $centralApi,
                $accessToken,
                $resolvedCentralUrl
            ): CoreCentralConnection {
                /*
                 * Only the newest successful installation
                 * credential remains active locally.
                 */
                CoreCentralConnection::query()
                    ->where(
                        'active',
                        true
                    )
                    ->update([
                        'active' =>
                            false,
                    ]);

                $website =
                    is_array(
                        $activation[
                            'website'
                        ]
                        ?? null
                    )
                        ? $activation[
                            'website'
                        ]
                        : [];

                $installation =
                    is_array(
                        $activation[
                            'installation'
                        ]
                        ?? null
                    )
                        ? $activation[
                            'installation'
                        ]
                        : [];

                return CoreCentralConnection::create([
                    'central_url' =>
                        $resolvedCentralUrl,

                    'website_id' =>
                        $centralApi[
                            'website_id'
                        ]
                        ?? $website[
                            'id'
                        ]
                        ?? null,

                    'website_uuid' =>
                        $centralApi[
                            'website_uuid'
                        ]
                        ?? $website[
                            'uuid'
                        ]
                        ?? null,

                    'installation_id' =>
                        $centralApi[
                            'installation_id'
                        ]
                        ?? null,

                    'installation_uuid' =>
                        $centralApi[
                            'installation_uuid'
                        ]
                        ?? $installation[
                            'uuid'
                        ]
                        ?? null,

                    'api_application_id' =>
                        $centralApi[
                            'api_application_id'
                        ]
                        ?? null,

                    /*
                     * Automatically encrypted by model cast.
                     */
                    'access_token' =>
                        $accessToken,

                    'token_type' =>
                        $centralApi[
                            'token_type'
                        ]
                        ?? 'Bearer',

                    'scopes' =>
                        is_array(
                            $centralApi[
                                'scopes'
                            ]
                            ?? null
                        )
                            ? $centralApi[
                                'scopes'
                            ]
                            : [],

                    'active' =>
                        true,

                    'last_verified_at' =>
                        now(),
                ]);
            }
        );
    }


    /**
     * Build an authenticated Central request.
     *
     * The token never needs to be exposed to controllers,
     * views or JavaScript.
     */
    public function request(
        ?int $timeout = 8
    ): PendingRequest {
        $connection =
            $this->current();

        if (
            !$connection
        ) {
            throw new RuntimeException(
                'This Core installation is not connected to Central Esubiz.'
            );
        }

        $token =
            trim(
                (string) $connection->access_token
            );

        if (
            $token === ''
        ) {
            throw new RuntimeException(
                'This Core installation has no Central Esubiz access token.'
            );
        }

        $connection->forceFill([
            'last_used_at' =>
                now(),
        ])->save();

        return Http::acceptJson()
            ->asJson()
            ->withToken(
                $token,
                $connection->token_type
                    ?: 'Bearer'
            )
            ->timeout(
                max(
                    2,
                    min(
                        30,
                        (int) (
                            $timeout
                            ?? 8
                        )
                    )
                )
            );
    }


    public function centralUrl(): string
    {
        $connection =
            $this->current();

        if (
            !$connection
        ) {
            throw new RuntimeException(
                'This Core installation is not connected to Central Esubiz.'
            );
        }

        $url =
            rtrim(
                (string) $connection->central_url,
                '/'
            );

        if (
            $url === ''
        ) {
            throw new RuntimeException(
                'The Central Esubiz URL is missing from this Core installation.'
            );
        }

        return $url;
    }


    /**
     * Confirm that the stored credential contains a scope
     * before making an optional Central request.
     */
    public function hasScope(
        string $scope
    ): bool {
        $connection =
            $this->current();

        if (
            !$connection
        ) {
            return false;
        }

        return in_array(
            $scope,
            $connection->scopes
                ?? [],
            true
        );
    }
}
