<?php

namespace App\Services\Licensing;

use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * ============================================================
 * ESUBIZ OFF-SERVER INSTALLATION IDENTITY
 * ============================================================
 *
 * License identifies commercial/domain entitlement.
 *
 * Website identifies the permanent Central website.
 *
 * Installation identifies the currently authorized copy of Core
 * running for that website.
 *
 * Same-domain reinstall:
 *
 * old installation -> superseded
 * new installation -> current
 *
 * website_id, website_uuid, license and domain remain unchanged.
 */
class OffServerInstallationService
{
    public const STATUS_CURRENT =
        'current';

    public const STATUS_SUPERSEDED =
        'superseded';

    public const STATUS_REVOKED =
        'revoked';


    public function registerSuccessfulInstallation(
        object $registration,
        Website $website,
        string $installationUuid,
        array $metadata = []
    ): object {

        if (
            !$website->isOffServer()
        ) {
            throw new RuntimeException(
                'Installation identity can only be created for an off-server website.'
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
            (int) (
                $registration->website_id
                ?? 0
            )
            !==
            (int) $website->id
        ) {
            throw new RuntimeException(
                'License registration does not belong to this website.'
            );
        }


        if (
            strtolower(
                trim(
                    (string) (
                        $registration->status
                        ?? ''
                    )
                )
            )
            !== OffServerLicenseRegistrationService::STATUS_ACTIVE
        ) {
            throw new RuntimeException(
                'Only an active Esubiz Core license can register an installation.'
            );
        }


        if (
            !Str::isUuid(
                $installationUuid
            )
        ) {
            throw new RuntimeException(
                'Installation UUID is invalid.'
            );
        }


        $domain =
            strtolower(
                trim(
                    (string) (
                        $registration->registered_domain
                        ?? ''
                    )
                )
            );


        if ($domain === '') {
            throw new RuntimeException(
                'Active license has no registered domain.'
            );
        }


        /*
         * Website and license must still agree on the permanent
         * licensed domain.
         */
        $websiteDomain =
            $website->registeredHost();


        if (
            $websiteDomain === null
            || !hash_equals(
                $domain,
                strtolower(
                    trim(
                        $websiteDomain
                    )
                )
            )
        ) {
            throw new RuntimeException(
                'Website domain does not match the active Core license.'
            );
        }


        return DB::transaction(
            function () use (
                $registration,
                $website,
                $installationUuid,
                $domain,
                $metadata
            ) {

                /*
                 * Idempotent retry from the same successful
                 * installation.
                 */
                $existing =
                    DB::table(
                        'off_server_installations'
                    )
                        ->where(
                            'installation_uuid',
                            $installationUuid
                        )
                        ->lockForUpdate()
                        ->first();


                if ($existing) {

                    if (
                        (int) $existing->website_id
                        !==
                        (int) $website->id
                    ) {
                        throw new RuntimeException(
                            'Installation UUID is already registered to another website.'
                        );
                    }


                    DB::table(
                        'off_server_installations'
                    )
                        ->where(
                            'id',
                            $existing->id
                        )
                        ->update([
                            'last_seen_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);


                    return DB::table(
                        'off_server_installations'
                    )
                        ->where(
                            'id',
                            $existing->id
                        )
                        ->first();
                }


                /*
                 * Lock the previous current installation.
                 */
                $previous =
                    DB::table(
                        'off_server_installations'
                    )
                        ->where(
                            'license_registration_id',
                            $registration->id
                        )
                        ->where(
                            'is_current',
                            true
                        )
                        ->lockForUpdate()
                        ->latest('id')
                        ->first();


                /*
                 * New successful same-domain reinstall supersedes
                 * the previous server installation.
                 */
                if ($previous) {

                    DB::table(
                        'off_server_installations'
                    )
                        ->where(
                            'id',
                            $previous->id
                        )
                        ->update([
                            'status' =>
                                static::STATUS_SUPERSEDED,

                            'is_current' =>
                                false,

                            'superseded_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);
                }


                $id =
                    DB::table(
                        'off_server_installations'
                    )
                        ->insertGetId([
                            'uuid' =>
                                (string) Str::uuid(),

                            'license_registration_id' =>
                                $registration->id,

                            'website_id' =>
                                $website->id,

                            'installation_uuid' =>
                                $installationUuid,

                            'registered_domain' =>
                                $domain,

                            'domain_hash' =>
                                hash(
                                    'sha256',
                                    $domain
                                ),

                            /*
                             * Command 2 binds ApiApplication.
                             */
                            'api_application_id' =>
                                null,

                            'status' =>
                                static::STATUS_CURRENT,

                            'is_current' =>
                                true,

                            'supersedes_installation_id' =>
                                $previous->id
                                ?? null,

                            'activated_at' =>
                                now(),

                            'last_seen_at' =>
                                now(),

                            'superseded_at' =>
                                null,

                            'revoked_at' =>
                                null,

                            'metadata' =>
                                json_encode(
                                    $metadata,
                                    JSON_UNESCAPED_SLASHES
                                ),

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);


                /*
                 * Keep canonical registration's active installation
                 * pointer synchronized.
                 */
                DB::table(
                    'off_server_license_registrations'
                )
                    ->where(
                        'id',
                        $registration->id
                    )
                    ->update([
                        'installation_uuid' =>
                            $installationUuid,

                        'last_verified_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);


                $website->forceFill([
                    'last_central_seen_at' =>
                        now(),
                ]);

                $website->save();


                return DB::table(
                    'off_server_installations'
                )
                    ->where(
                        'id',
                        $id
                    )
                    ->first();
            }
        );
    }


    public function currentForWebsite(
        Website $website
    ): ?object {

        return DB::table(
            'off_server_installations'
        )
            ->where(
                'website_id',
                $website->id
            )
            ->where(
                'is_current',
                true
            )
            ->where(
                'status',
                static::STATUS_CURRENT
            )
            ->latest('id')
            ->first();
    }


    /**
     * Protected Central APIs can use this as part of the
     * installation authentication chain.
     */
    public function assertCurrent(
        int $websiteId,
        string $installationUuid
    ): object {

        $installation =
            DB::table(
                'off_server_installations'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->where(
                    'installation_uuid',
                    $installationUuid
                )
                ->where(
                    'is_current',
                    true
                )
                ->where(
                    'status',
                    static::STATUS_CURRENT
                )
                ->first();


        if (!$installation) {
            throw new RuntimeException(
                'Off-server Core installation is not currently authorized.'
            );
        }


        return $installation;
    }
}
