<?php

namespace App\Services\Licensing;

use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * ============================================================
 * ESUBIZ OFF-SERVER DOMAIN LICENCE REGISTRY
 * ============================================================
 *
 * Permanent rule:
 *
 * ONE OFF-SERVER CORE LICENCE = ONE DOMAIN.
 *
 * A licence registered for domain1.com can never subsequently
 * become the licence for domain2.com.
 *
 * A new domain requires a new Esubiz Core licence.
 *
 * Reinstallation/server migration on the SAME domain is a
 * different concern and does not alter the permanent domain
 * entitlement.
 */
class OffServerLicenseRegistrationService
{
    public const STATUS_PENDING =
        'pending';

    public const STATUS_ACTIVE =
        'active';

    public const STATUS_SUSPENDED =
        'suspended';

    public const STATUS_REVOKED =
        'revoked';


    /**
     * Convert user supplied domain/URL into the canonical host
     * used by the Central licence registry.
     */
    public function normalizeDomain(
        string $domain
    ): string {

        $domain =
            strtolower(
                trim($domain)
            );


        if ($domain === '') {
            throw new InvalidArgumentException(
                'Off-server licence domain is required.'
            );
        }


        /*
         * Allow Core to send either:
         *
         * example.com
         * https://example.com
         * https://www.example.com/
         *
         * www is intentionally normalized away so normal www/non-www
         * routing does not consume two licences for one website.
         */
        $candidate =
            str_contains(
                $domain,
                '://'
            )
                ? $domain
                : 'https://' . $domain;


        $host =
            parse_url(
                $candidate,
                PHP_URL_HOST
            );


        if (
            !is_string($host)
            || trim($host) === ''
        ) {
            throw new InvalidArgumentException(
                'Off-server licence domain is invalid.'
            );
        }


        $host =
            strtolower(
                rtrim(
                    trim($host),
                    '.'
                )
            );


        if (
            str_starts_with(
                $host,
                'www.'
            )
        ) {
            $host =
                substr(
                    $host,
                    4
                );
        }


        if (
            $host === ''
            || strlen($host) > 255
            || !str_contains(
                $host,
                '.'
            )
        ) {
            throw new InvalidArgumentException(
                'Off-server licence domain is invalid.'
            );
        }


        return $host;
    }


    public function domainHash(
        string $domain
    ): string {

        return hash(
            'sha256',
            $this->normalizeDomain(
                $domain
            )
        );
    }


    /**
     * Assert that an existing licence registration may be used
     * for the supplied domain.
     *
     * There is deliberately NO transfer behavior here.
     */
    public function assertDomainLock(
        object $registration,
        string $domain
    ): string {

        $domain =
            $this->normalizeDomain(
                $domain
            );


        $registeredDomain =
            trim(
                (string) (
                    $registration->registered_domain
                    ?? ''
                )
            );


        /*
         * First activation is allowed to establish the permanent
         * domain lock.
         */
        if ($registeredDomain === '') {
            return $domain;
        }


        $registeredDomain =
            $this->normalizeDomain(
                $registeredDomain
            );


        if (
            !hash_equals(
                $registeredDomain,
                $domain
            )
        ) {
            throw new RuntimeException(
                'This Esubiz Core licence is permanently registered '
                . 'to '
                . $registeredDomain
                . '. A different domain requires another licence.'
            );
        }


        return $domain;
    }


    /**
     * Permanently bind a purchased licence registration to its
     * first successfully activated domain and Central website.
     *
     * Existing licence validation/purchase authority will call
     * this service after it confirms that license_key is genuine.
     */
    public function activateDomain(
        int $registrationId,
        string $domain,
        ?string $installationUuid = null
    ): object {

        return DB::transaction(
            function () use (
                $registrationId,
                $domain,
                $installationUuid
            ) {

                $registration =
                    DB::table(
                        'off_server_license_registrations'
                    )
                        ->where(
                            'id',
                            $registrationId
                        )
                        ->lockForUpdate()
                        ->first();


                if (!$registration) {
                    throw new RuntimeException(
                        'Off-server licence registration was not found.'
                    );
                }


                if (
                    $registration->status
                    === static::STATUS_REVOKED
                ) {
                    throw new RuntimeException(
                        'This Esubiz Core licence has been revoked.'
                    );
                }


                $domain =
                    $this->assertDomainLock(
                        $registration,
                        $domain
                    );


                /*
                 * If a website already exists for this registration,
                 * its domain must also agree with the permanent lock.
                 */
                $website = null;

                if (
                    !empty(
                        $registration->website_id
                    )
                ) {
                    $website =
                        Website::query()
                            ->find(
                                $registration->website_id
                            );


                    if (!$website) {
                        throw new RuntimeException(
                            'The registered off-server website record is missing.'
                        );
                    }


                    if (
                        !$website->isOffServer()
                    ) {
                        throw new RuntimeException(
                            'The licence is not attached to an off-server website.'
                        );
                    }


                    $websiteDomain =
                        $website->registeredHost();


                    if (
                        $websiteDomain !== null
                        && !hash_equals(
                            $this->normalizeDomain(
                                $websiteDomain
                            ),
                            $domain
                        )
                    ) {
                        throw new RuntimeException(
                            'Central website domain does not match the licence domain.'
                        );
                    }
                }


                $updates = [
                    'registered_domain' =>
                        $domain,

                    'domain_hash' =>
                        hash(
                            'sha256',
                            $domain
                        ),

                    'status' =>
                        static::STATUS_ACTIVE,

                    'activated_at' =>
                        $registration->activated_at
                        ?: now(),

                    'last_verified_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ];


                if (
                    $installationUuid !== null
                    && trim(
                        $installationUuid
                    ) !== ''
                ) {
                    if (
                        !Str::isUuid(
                            $installationUuid
                        )
                    ) {
                        throw new InvalidArgumentException(
                            'Installation UUID is invalid.'
                        );
                    }

                    $updates[
                        'installation_uuid'
                    ] =
                        $installationUuid;
                }


                DB::table(
                    'off_server_license_registrations'
                )
                    ->where(
                        'id',
                        $registration->id
                    )
                    ->update(
                        $updates
                    );


                return DB::table(
                    'off_server_license_registrations'
                )
                    ->where(
                        'id',
                        $registration->id
                    )
                    ->first();
            }
        );
    }



    /**
     * ============================================================
     * ACTIVATE LICENCE + REGISTER CENTRAL OFF-SERVER WEBSITE
     * ============================================================
     *
     * Call this only AFTER the canonical Esubiz licence engine has
     * confirmed that the licence key is genuine and belongs to the
     * supplied Central Esubiz user.
     *
     * First legitimate activation:
     *
     * licence
     *   -> permanent domain lock
     *   -> Central Website record
     *   -> permanent website_id / website_uuid
     *
     * Repeated activation on the SAME domain returns the same
     * website identity.
     *
     * A different domain is rejected and requires another licence.
     */
    public function activateAndRegisterWebsite(
        int $registrationId,
        string $domain,
        ?string $installationUuid = null
    ): array {

        return DB::transaction(
            function () use (
                $registrationId,
                $domain,
                $installationUuid
            ) {

                $registration =
                    DB::table(
                        'off_server_license_registrations'
                    )
                        ->where(
                            'id',
                            $registrationId
                        )
                        ->lockForUpdate()
                        ->first();


                if (!$registration) {
                    throw new RuntimeException(
                        'Off-server licence registration was not found.'
                    );
                }


                if (
                    $registration->status
                    === static::STATUS_REVOKED
                ) {
                    throw new RuntimeException(
                        'This Esubiz Core licence has been revoked.'
                    );
                }


                $domain =
                    $this->assertDomainLock(
                        $registration,
                        $domain
                    );


                /*
                 * ------------------------------------------------
                 * EXISTING WEBSITE BINDING
                 * ------------------------------------------------
                 *
                 * A licence can only ever own one Central website.
                 */
                $website = null;


                if (
                    !empty(
                        $registration->website_id
                    )
                ) {

                    $website =
                        Website::query()
                            ->find(
                                $registration->website_id
                            );


                    if (!$website) {
                        throw new RuntimeException(
                            'The Central website attached to this licence is missing.'
                        );
                    }


                    if (
                        (int) $website->owner_id
                        !==
                        (int) $registration->user_id
                    ) {
                        throw new RuntimeException(
                            'The licence owner does not match the registered website owner.'
                        );
                    }


                    if (
                        !$website->isOffServer()
                    ) {
                        throw new RuntimeException(
                            'The licence is attached to a non off-server website.'
                        );
                    }


                    $websiteDomain =
                        $website->registeredHost();


                    if (
                        $websiteDomain !== null
                        && !hash_equals(
                            $this->normalizeDomain(
                                $websiteDomain
                            ),
                            $domain
                        )
                    ) {
                        throw new RuntimeException(
                            'The Central website domain does not match this licence.'
                        );
                    }

                } else {

                    /*
                     * ------------------------------------------------
                     * FIRST VALID ACTIVATION
                     * ------------------------------------------------
                     *
                     * Create the permanent Central website identity.
                     *
                     * forceFill() deliberately avoids creating a
                     * second licensing-specific Website model path.
                     */
                    $website =
                        new Website();


                    $website->forceFill([
                        'owner_id' =>
                            (int) $registration->user_id,

                        'deployment_type' =>
                            Website::DEPLOYMENT_OFF_SERVER,

                        'registered_domain' =>
                            $domain,

                        'registry_status' =>
                            Website::REGISTRY_ACTIVE,

                        'registered_at' =>
                            now(),

                        'last_central_seen_at' =>
                            now(),
                    ]);


                    /*
                     * Off-server websites do not consume an
                     * *.esubiz.com SaaS subdomain.
                     *
                     * Leave subdomain null when the schema permits it.
                     * If the existing application requires a value,
                     * use a deterministic internal registry identifier
                     * that is never treated as the public domain.
                     */
                    if (
                        \Illuminate\Support\Facades\Schema::hasColumn(
                            'websites',
                            'subdomain'
                        )
                    ) {

                        $column =
                            collect(
                                \Illuminate\Support\Facades\DB::select(
                                    "SHOW COLUMNS FROM websites WHERE Field = 'subdomain'"
                                )
                            )
                                ->first();


                        $nullable =
                            strtolower(
                                (string) (
                                    $column->Null
                                    ?? 'YES'
                                )
                            )
                            === 'yes';


                        if (!$nullable) {
                            $website->subdomain =
                                'offserver-'
                                . strtolower(
                                    substr(
                                        str_replace(
                                            '-',
                                            '',
                                            (string) Str::uuid()
                                        ),
                                        0,
                                        16
                                    )
                                );
                        }
                    }


                    $website->save();


                    DB::table(
                        'off_server_license_registrations'
                    )
                        ->where(
                            'id',
                            $registration->id
                        )
                        ->update([
                            'website_id' =>
                                $website->id,

                            'updated_at' =>
                                now(),
                        ]);


                    /*
                     * Reload locked registration context after binding.
                     */
                    $registration =
                        DB::table(
                            'off_server_license_registrations'
                        )
                            ->where(
                                'id',
                                $registration->id
                            )
                            ->first();
                }


                /*
                 * ------------------------------------------------
                 * ACTIVATE PERMANENT DOMAIN LOCK
                 * ------------------------------------------------
                 */
                $activated =
                    $this->activateDomain(
                        $registration->id,
                        $domain,
                        $installationUuid
                    );


                /*
                 * Keep Central website registry synchronized.
                 */
                $website->forceFill([
                    'deployment_type' =>
                        Website::DEPLOYMENT_OFF_SERVER,

                    'registered_domain' =>
                        $domain,

                    'registry_status' =>
                        Website::REGISTRY_ACTIVE,

                    'last_central_seen_at' =>
                        now(),
                ]);

                $website->save();


                return [
                    'registration_id' =>
                        $activated->id,

                    'license_key' =>
                        $activated->license_key,

                    'website_id' =>
                        $website->id,

                    'website_uuid' =>
                        $website->website_uuid,

                    'owner_id' =>
                        $website->owner_id,

                    'deployment_type' =>
                        $website->deployment_type,

                    'registered_domain' =>
                        $website->registered_domain,

                    'registry_status' =>
                        $website->registry_status,

                    'license_status' =>
                        $activated->status,

                    'installation_uuid' =>
                        $activated->installation_uuid,

                    'domain_locked' =>
                        true,
                ];
            }
        );
    }


    /**
     * Central protected services can use this check before
     * authorizing off-server Marketplace/API operations.
     */
    public function assertActiveForWebsite(
        Website $website
    ): object {

        if (
            !$website->isOffServer()
        ) {
            throw new RuntimeException(
                'Website is not an off-server deployment.'
            );
        }


        if (
            !$website->isRegistryActive()
        ) {
            throw new RuntimeException(
                'Off-server website registry is not active.'
            );
        }


        $registration =
            DB::table(
                'off_server_license_registrations'
            )
                ->where(
                    'website_id',
                    $website->id
                )
                ->where(
                    'status',
                    static::STATUS_ACTIVE
                )
                ->first();


        if (!$registration) {
            throw new RuntimeException(
                'A valid active Esubiz Core licence is required '
                . 'for this off-server website.'
            );
        }


        $websiteDomain =
            $website->registeredHost();


        if ($websiteDomain === null) {
            throw new RuntimeException(
                'Off-server website has no registered domain.'
            );
        }


        $this->assertDomainLock(
            $registration,
            $websiteDomain
        );


        return $registration;
    }


    /**
     * ESUBIZ_SAME_DOMAIN_REINSTALL_CONTRACT
     *
     * Determine whether a licence can be used by an installer.
     *
     * Rules:
     *
     * PENDING:
     * - may be tested on a domain
     * - no permanent domain mutation happens here
     *
     * ACTIVE:
     * - same registered domain is valid
     * - a new installation UUID means reinstall/server migration
     * - different domain is permanently rejected
     *
     * This method is READ-ONLY.
     */
    public function installationEligibility(
        object $registration,
        string $domain,
        ?string $installationUuid = null
    ): array {

        $normalizedDomain =
            $this->assertDomainLock(
                $registration,
                $domain
            );


        $status =
            strtolower(
                trim(
                    (string) (
                        $registration->status
                        ?? ''
                    )
                )
            );


        $registeredDomain =
            trim(
                (string) (
                    $registration->registered_domain
                    ?? ''
                )
            );


        $currentInstallationUuid =
            trim(
                (string) (
                    $registration->installation_uuid
                    ?? ''
                )
            );


        $requestedInstallationUuid =
            trim(
                (string) (
                    $installationUuid
                    ?? ''
                )
            );


        $domainLocked =
            $registeredDomain !== '';


        $sameInstallation =
            $currentInstallationUuid !== ''
            && $requestedInstallationUuid !== ''
            && hash_equals(
                $currentInstallationUuid,
                $requestedInstallationUuid
            );


        /*
         * An active licence that passed assertDomainLock() is on
         * its permanently registered domain.
         *
         * A different installation UUID therefore represents a
         * legitimate reinstall/server migration, not licence theft
         * to another domain.
         */
        $reinstallation =
            $status === self::STATUS_ACTIVE
            && $domainLocked
            && !$sameInstallation;


        return [
            'allowed' =>
                true,

            'status' =>
                $status,

            'domain' =>
                $normalizedDomain,

            'domain_locked' =>
                $domainLocked,

            'same_installation' =>
                $sameInstallation,

            'reinstallation' =>
                $reinstallation,

            'website_id' =>
                $registration->website_id
                ?? null,

            'current_installation_uuid' =>
                $currentInstallationUuid !== ''
                    ? $currentInstallationUuid
                    : null,

            'requested_installation_uuid' =>
                $requestedInstallationUuid !== ''
                    ? $requestedInstallationUuid
                    : null,
        ];
    }


    /**
     * Complete a SAME-DOMAIN reinstall after the new Core
     * installation has succeeded.
     *
     * IMPORTANT:
     *
     * - licence key remains unchanged
     * - registered domain remains unchanged
     * - domain hash remains unchanged
     * - Central website_id remains unchanged
     * - Central website_uuid remains unchanged
     *
     * Only the current installation identity is rebound.
     *
     * Checkpoint 4 will use this same successful rebind event to
     * rotate/revoke API application credentials.
     */
    public function completeSameDomainReinstallation(
        object $registration,
        string $domain,
        string $installationUuid
    ): array {

        $eligibility =
            $this->installationEligibility(
                $registration,
                $domain,
                $installationUuid
            );


        if (
            strtolower(
                (string) (
                    $registration->status
                    ?? ''
                )
            ) !== self::STATUS_ACTIVE
        ) {
            throw new \RuntimeException(
                'Only an active Esubiz Core licence can be reinstalled.'
            );
        }


        if (
            empty(
                $registration->website_id
            )
        ) {
            throw new \RuntimeException(
                'The active Esubiz Core licence has no Central website identity.'
            );
        }


        /*
         * Same installation calling activation again is idempotent.
         */
        if (
            $eligibility[
                'same_installation'
            ]
        ) {

            $website =
                \App\Models\Website::query()
                    ->findOrFail(
                        (int) $registration->website_id
                    );


            return [
                'success' =>
                    true,

                'reinstalled' =>
                    false,

                'same_installation' =>
                    true,

                'license_status' =>
                    self::STATUS_ACTIVE,

                'registered_domain' =>
                    $eligibility['domain'],

                'installation_uuid' =>
                    $registration->installation_uuid,

                'website_id' =>
                    $website->id,

                'website_uuid' =>
                    $website->website_uuid,

                'deployment_type' =>
                    $website->deployment_type,

                'registry_status' =>
                    $website->registry_status,
            ];
        }


        /*
         * The domain has already been verified by assertDomainLock().
         * Rebind ONLY the installation UUID.
         *
         * Never modify registered_domain/domain_hash here.
         */
        \Illuminate\Support\Facades\DB::table(
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


        $website =
            \App\Models\Website::query()
                ->findOrFail(
                    (int) $registration->website_id
                );


        return [
            'success' =>
                true,

            'reinstalled' =>
                true,

            'same_installation' =>
                false,

            'license_status' =>
                self::STATUS_ACTIVE,

            'registered_domain' =>
                $eligibility['domain'],

            'installation_uuid' =>
                $installationUuid,

            'website_id' =>
                $website->id,

            'website_uuid' =>
                $website->website_uuid,

            'deployment_type' =>
                $website->deployment_type,

            'registry_status' =>
                $website->registry_status,
        ];
    }

}
