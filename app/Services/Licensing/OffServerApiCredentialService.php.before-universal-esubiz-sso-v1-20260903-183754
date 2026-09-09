<?php

namespace App\Services\Licensing;

use App\Models\ApiApplication;
use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * ============================================================
 * OFF-SERVER CORE CENTRAL API CREDENTIALS
 * ============================================================
 *
 * Successful Core installation:
 *
 * active licence
 *      ↓
 * Central website
 *      ↓
 * current installation
 *      ↓
 * ApiApplication
 *      ↓
 * installation bearer token
 *
 * Same-domain reinstall:
 *
 * previous installation/token -> revoked/superseded
 * new installation/token      -> current
 */
class OffServerApiCredentialService
{
    public const TOKEN_ACTIVE =
        'active';

    public const TOKEN_REVOKED =
        'revoked';


    /**
     * Default Central services available to a valid registered Core.
     *
     * Individual products/credits/entitlements still determine
     * whether a specific paid operation is actually permitted.
     */
    public function defaultScopes(): array
    {
        return [
            'website.identity',
            'license.verify',
            'marketplace.read',
            'marketplace.checkout',
            'marketplace.entitlements',
            'packages.download',
            'credits.read',
            'ai.use',
            'sms.use',
            'email.use',
            'whatsapp.use',
            'social.use',
            'giftcard.validate',
            'giftcard.redeem',
            'payments.connect',
            'updates.read',
        ];
    }


    public function bind(
        object $registration,
        Website $website,
        object $installation
    ): array {

        if (
            !$website->isOffServer()
        ) {
            throw new RuntimeException(
                'Central API credentials can only be issued to an off-server website.'
            );
        }


        if (
            !$website->isRegistryActive()
        ) {
            throw new RuntimeException(
                'Off-server website registry is not active.'
            );
        }


        if (
            (int) $installation->website_id
            !==
            (int) $website->id
        ) {
            throw new RuntimeException(
                'Installation does not belong to this website.'
            );
        }


        if (
            !(bool) $installation->is_current
            || $installation->status
                !== OffServerInstallationService::STATUS_CURRENT
        ) {
            throw new RuntimeException(
                'API credentials can only be issued to the current installation.'
            );
        }


        return DB::transaction(
            function () use (
                $registration,
                $website,
                $installation
            ) {

                /*
                 * ------------------------------------------------
                 * REVOKE OLD INSTALLATION TOKENS
                 * ------------------------------------------------
                 *
                 * Same-domain reinstall must prevent the previous
                 * server from continuing to access protected APIs.
                 */
                DB::table(
                    'off_server_installation_tokens'
                )
                    ->where(
                        'website_id',
                        $website->id
                    )
                    ->where(
                        'installation_id',
                        '<>',
                        $installation->id
                    )
                    ->where(
                        'status',
                        static::TOKEN_ACTIVE
                    )
                    ->update([
                        'status' =>
                            static::TOKEN_REVOKED,

                        'revoked_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);


                /*
                 * ------------------------------------------------
                 * API APPLICATION
                 * ------------------------------------------------
                 *
                 * Reuse the current installation application when
                 * one is already bound.
                 */
                $application = null;


                if (
                    !empty(
                        $installation->api_application_id
                    )
                ) {
                    $application =
                        ApiApplication::query()
                            ->find(
                                $installation->api_application_id
                            );
                }


                if (!$application) {

                    $application =
                        new ApiApplication();


                    /*
                     * Existing Esubiz installations may have evolved
                     * ApiApplication schema over time.
                     *
                     * Populate only columns that actually exist.
                     */
                    $values = [];


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'user_id'
                        )
                    ) {
                        $values['user_id'] =
                            (int) $website->owner_id;
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'workspace_id'
                        )
                    ) {
                        $values['workspace_id'] =
                            $website->workspace_id;
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'uuid'
                        )
                    ) {
                        $values['uuid'] =
                            (string) Str::uuid();
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'name'
                        )
                    ) {
                        $values['name'] =
                            'Esubiz Core - '
                            . (
                                $website->registeredHost()
                                ?: $website->website_uuid
                            );
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'slug'
                        )
                    ) {
                        $values['slug'] =
                            'core-'
                            . strtolower(
                                Str::random(20)
                            );
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'client_id'
                        )
                    ) {
                        $values['client_id'] =
                            'core_'
                            . Str::lower(
                                Str::random(40)
                            );
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'client_secret'
                        )
                    ) {
                        /*
                         * This is not the Core bearer token.
                         *
                         * Existing SSO/application flows may require
                         * a client secret, so create a strong random
                         * application secret when the schema supports it.
                         */
                        $values['client_secret'] =
                            Str::random(80);
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'redirect_urls'
                        )
                    ) {
                        $domain =
                            $website->registeredHost();

                        $values['redirect_urls'] =
                            $domain
                                ? [
                                    'https://'
                                    . $domain
                                    . '/admin/esubiz/callback',
                                ]
                                : [];
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'allowed_scopes'
                        )
                    ) {
                        $values['allowed_scopes'] =
                            $this->defaultScopes();
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'scopes'
                        )
                    ) {
                        $values['scopes'] =
                            $this->defaultScopes();
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'is_active'
                        )
                    ) {
                        $values['is_active'] =
                            true;
                    }


                    if (
                        Schema::hasColumn(
                            'api_applications',
                            'status'
                        )
                    ) {
                        $values['status'] =
                            'active';
                    }


                    $application->forceFill(
                        $values
                    );

                    $application->save();


                    DB::table(
                        'off_server_installations'
                    )
                        ->where(
                            'id',
                            $installation->id
                        )
                        ->update([
                            'api_application_id' =>
                                $application->id,

                            'updated_at' =>
                                now(),
                        ]);


                    DB::table(
                        'off_server_license_registrations'
                    )
                        ->where(
                            'id',
                            $registration->id
                        )
                        ->update([
                            'api_application_id' =>
                                $application->id,

                            'updated_at' =>
                                now(),
                        ]);
                }


                /*
                 * ------------------------------------------------
                 * ROTATE CURRENT INSTALLATION TOKEN
                 * ------------------------------------------------
                 *
                 * Calling activation again for the same current
                 * installation intentionally rotates the bearer token.
                 */
                DB::table(
                    'off_server_installation_tokens'
                )
                    ->where(
                        'installation_id',
                        $installation->id
                    )
                    ->where(
                        'status',
                        static::TOKEN_ACTIVE
                    )
                    ->update([
                        'status' =>
                            static::TOKEN_REVOKED,

                        'revoked_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);


                $rawToken =
                    'esubiz_core_'
                    . Str::random(96);


                $tokenId =
                    DB::table(
                        'off_server_installation_tokens'
                    )
                        ->insertGetId([
                            'uuid' =>
                                (string) Str::uuid(),

                            'installation_id' =>
                                $installation->id,

                            'website_id' =>
                                $website->id,

                            'api_application_id' =>
                                $application->id,

                            'token_hash' =>
                                hash(
                                    'sha256',
                                    $rawToken
                                ),

                            'scopes' =>
                                json_encode(
                                    $this->defaultScopes(),
                                    JSON_UNESCAPED_SLASHES
                                ),

                            'status' =>
                                static::TOKEN_ACTIVE,

                            'last_used_at' =>
                                null,

                            /*
                             * Installation tokens remain valid while
                             * this installation + licence remain current.
                             * They are explicitly revoked on reinstall,
                             * suspension or future administrative action.
                             */
                            'expires_at' =>
                                null,

                            'revoked_at' =>
                                null,

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);


                return [
                    'api_application_id' =>
                        $application->id,

                    'token_id' =>
                        $tokenId,

                    /*
                     * Returned once. Database stores hash only.
                     */
                    'access_token' =>
                        $rawToken,

                    'token_type' =>
                        'Bearer',

                    'scopes' =>
                        $this->defaultScopes(),
                ];
            }
        );
    }


    /**
     * Resolve an incoming bearer token into a trusted current
     * off-server installation context.
     */
    public function resolveBearerToken(
        string $rawToken
    ): array {

        $rawToken =
            trim(
                $rawToken
            );


        if ($rawToken === '') {
            throw new RuntimeException(
                'Esubiz Core access token is required.'
            );
        }


        $token =
            DB::table(
                'off_server_installation_tokens'
            )
                ->where(
                    'token_hash',
                    hash(
                        'sha256',
                        $rawToken
                    )
                )
                ->where(
                    'status',
                    static::TOKEN_ACTIVE
                )
                ->whereNull(
                    'revoked_at'
                )
                ->first();


        if (!$token) {
            throw new RuntimeException(
                'Esubiz Core access token is invalid or revoked.'
            );
        }


        if (
            !empty(
                $token->expires_at
            )
            && now()->greaterThan(
                $token->expires_at
            )
        ) {
            throw new RuntimeException(
                'Esubiz Core access token has expired.'
            );
        }


        $installation =
            app(
                OffServerInstallationService::class
            )->assertCurrent(
                (int) $token->website_id,
                (string) DB::table(
                    'off_server_installations'
                )
                    ->where(
                        'id',
                        $token->installation_id
                    )
                    ->value(
                        'installation_uuid'
                    )
            );


        $website =
            Website::query()
                ->find(
                    $token->website_id
                );


        if (
            !$website
            || !$website->isOffServer()
            || !$website->isRegistryActive()
        ) {
            throw new RuntimeException(
                'Off-server website is not currently authorized.'
            );
        }


        $license =
            app(
                OffServerLicenseRegistrationService::class
            )->assertActiveForWebsite(
                $website
            );


        DB::table(
            'off_server_installation_tokens'
        )
            ->where(
                'id',
                $token->id
            )
            ->update([
                'last_used_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);


        return [
            'token' =>
                $token,

            'installation' =>
                $installation,

            'website' =>
                $website,

            'license' =>
                $license,

            'scopes' =>
                json_decode(
                    (string) (
                        $token->scopes
                        ?? '[]'
                    ),
                    true
                )
                ?: [],
        ];
    }
}
