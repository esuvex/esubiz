<?php

namespace App\Services\Website;

use App\Models\Website;
use App\Models\WebsiteMailbox;
use Illuminate\Support\Str;
use RuntimeException;

class WebsiteMailboxService
{
    /*
     * ESUBIZ_WEBSITE_MAILBOX_LIFECYCLE_V1
     *
     * Central authority for website mailbox identity/lifecycle.
     *
     * SaaS identity rule:
     *
     * johnstore.esubiz.com -> johnstore@esubiz.com
     * fax.esubiz.com       -> fax@esubiz.com
     */

    public function __construct(
        protected DirectAdminMailboxService $directAdmin,
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    public function managedSaasAddress(
        Website $website
    ): string {
        if (!$website->isSaas()) {
            throw new RuntimeException(
                'Managed SaaS mailbox requires a SaaS Website.'
            );
        }

        $localPart =
            strtolower(
                trim(
                    (string) $website->subdomain
                )
            );

        if ($localPart === '') {
            throw new RuntimeException(
                'SaaS website has no subdomain for mailbox provisioning.'
            );
        }

        /*
         * WebsiteService already normalizes SaaS subdomains, but the
         * mailbox boundary validates again before talking to DirectAdmin.
         */
        if (
            !preg_match(
                '/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/',
                $localPart
            )
        ) {
            throw new RuntimeException(
                'Website subdomain is not a valid managed-mail local part.'
            );
        }

        $domain =
            strtolower(
                trim(
                    (string) config(
                        'esubiz_mail.managed_domain',
                        'esubiz.com'
                    )
                )
            );

        if ($domain === '') {
            throw new RuntimeException(
                'Managed mail domain is not configured.'
            );
        }

        return $localPart . '@' . $domain;
    }

    /**
     * Idempotently provision the one Esubiz-managed SaaS mailbox.
     */
    public function provisionSaas(
        Website $website
    ): WebsiteMailbox {
        $address =
            $this->managedSaasAddress(
                $website
            );

        [$localPart, $domain] =
            explode(
                '@',
                $address,
                2
            );

        $mailbox =
            WebsiteMailbox::query()
                ->firstOrNew([
                    'email_address' => $address,
                ]);

        if (
            $mailbox->exists
            && (int) $mailbox->website_id
                !== (int) $website->id
        ) {
            throw new RuntimeException(
                "Managed mailbox [{$address}] already belongs to another Website."
            );
        }

        if (
            $mailbox->exists
            && $mailbox->status === 'active'
        ) {
            /*
             * ESUBIZ_SAAS_MAILBOX_AUTO_CORE_SYNC_V5
             *
             * Reconciliation path:
             * an already-provisioned Central mailbox must always be
             * represented in the tenant Core mailbox registry.
             */
            $this->syncManagedMailboxToCore(
                $website,
                $mailbox
            );

            return $mailbox;
        }

        /*
         * ESUBIZ_SAAS_MAILBOX_DIRECTADMIN_SAFE_CREDENTIAL_V4
         *
         * The mailbox is a real mail-service credential and must not
         * depend on the Core Administrator password.
         *
         * A previously ACTIVE mailbox keeps its existing credential
         * through the reconciliation path above.
         *
         * A new or FAILED provisioning attempt receives a fresh,
         * DirectAdmin-safe credential. This prevents a rejected
         * credential from being reused indefinitely during Central
         * Migration & Updates retries.
         *
         * The credential is stored encrypted by WebsiteMailbox and is
         * used server-side by Core Email. Core administrators never
         * need a separate mailbox login.
         */
        $password =
            Str::password(
                length: 32,
                letters: true,
                numbers: true,
                symbols: false,
                spaces: false
            );

        $mailbox->fill([
            'website_id' => $website->id,
            'provider' => 'directadmin',
            'connection_type' => 'managed',
            'domain' => $domain,
            'local_part' => $localPart,
            'password' => $password,
            'status' => 'provisioning',
            'last_error' => null,
        ])->save();

        try {
            $this->directAdmin->create(
                $domain,
                $localPart,
                $password,
                $this->storageLimitMb(
                    $website
                )
            );

            $mailbox->forceFill([
                'status' => 'active',
                'provisioned_at' => now(),
                'deleted_at_remote' => null,
                'last_error' => null,
            ])->save();

            /*
             * ESUBIZ_SAAS_MANAGED_MAIL_SITE_EMAIL_SYNC_V2
             *
             * The automatically provisioned mailbox becomes the
             * canonical Core business/contact/sender email.
             */
            $this->syncSiteEmail(
                $website,
                $address
            );

            /*
             * Successful provisioning must immediately register the
             * real mailbox inside Core so Email is ready without a
             * separate tenant setup/login step.
             */
            $this->syncManagedMailboxToCore(
                $website,
                $mailbox
            );

            /*
             * ESUBIZ_SAAS_MAILBOX_CREDENTIAL_CONSUMED_V3
             *
             * The WebsiteMailbox retains its encrypted connection
             * credential for the Core mail client.
             *
             * The one-time Website deployment credential is no longer
             * needed once DirectAdmin accepted mailbox creation.
             */
            if (
                filled(
                    $website->mailbox_provisioning_password
                )
            ) {
                $website->forceFill([
                    'mailbox_provisioning_password' => null,
                ])->save();

                $website->refresh();
            }

            return $mailbox->fresh();

        } catch (\Throwable $e) {
            $mailbox->forceFill([
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ])->save();

            throw $e;
        }
    }

    /**
     * Return the Website's complete current Core storage entitlement.
     *
     * This is only a DirectAdmin mailbox safety ceiling. It does not
     * create a second storage allocation for email.
     */
    protected function storageLimitMb(
        Website $website
    ): int {
        $limitMb =
            (int) (
                $website->storage_mb
                ?? 0
            );

        return $limitMb > 0
            ? $limitMb
            : 1024;
    }

    /**
     * Synchronize the generated managed mailbox into the canonical
     * Core site_settings.site_email setting.
     */
    protected function syncSiteEmail(
        Website $website,
        string $address
    ): void {
        if (!$website->isSaas()) {
            return;
        }

        try {
            $this->tenantDatabaseService->connect(
                $website
            );

            $db =
                $this->tenantDatabaseService
                    ->connection();

            if (
                !$db->getSchemaBuilder()
                    ->hasTable('site_settings')
            ) {
                throw new RuntimeException(
                    'Core site_settings table does not exist.'
                );
            }

            $db->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'site_email',
                    ],
                    [
                        'value' =>
                            strtolower(
                                trim($address)
                            ),
                    ]
                );

        } finally {
            try {
                $this->tenantDatabaseService
                    ->disconnect();
            } catch (\Throwable $disconnectError) {
                report(
                    $disconnectError
                );
            }
        }
    }

    /**
     * Permanently remove Esubiz-managed SaaS mailboxes before the
     * Website itself is permanently deleted.
     */
    public function deleteManagedForWebsite(
        Website $website
    ): void {
        $mailboxes =
            WebsiteMailbox::query()
                ->where(
                    'website_id',
                    $website->id
                )
                ->where(
                    'provider',
                    'directadmin'
                )
                ->where(
                    'connection_type',
                    'managed'
                )
                ->get();

        foreach ($mailboxes as $mailbox) {
            if ($mailbox->status !== 'deleted') {
                $this->directAdmin->delete(
                    (string) $mailbox->domain,
                    (string) $mailbox->local_part
                );

                $mailbox->forceFill([
                    'status' => 'deleted',
                    'deleted_at_remote' => now(),
                    'last_storage_bytes' => 0,
                    'storage_checked_at' => now(),
                    'last_error' => null,
                ])->save();
            }
        }
    }

    /**
     * Live mailbox usage for the Website.
     *
     * This is intentionally recalculated from DirectAdmin. Deleting
     * messages therefore frees website storage without decrement
     * bookkeeping that can drift out of sync.
     */
    public function storageBytes(
        Website $website
    ): int {
        $bytes = 0;

        $mailboxes =
            WebsiteMailbox::query()
                ->where(
                    'website_id',
                    $website->id
                )
                ->where(
                    'status',
                    'active'
                )
                ->where(
                    'provider',
                    'directadmin'
                )
                ->get();

        foreach ($mailboxes as $mailbox) {
            try {
                $mailboxBytes =
                    $this->directAdmin->storageBytes(
                        (string) $mailbox->domain,
                        (string) $mailbox->local_part
                    );

                $mailbox->forceFill([
                    'last_storage_bytes' =>
                        $mailboxBytes,

                    'storage_checked_at' =>
                        now(),

                    'last_error' =>
                        null,
                ])->save();

                $bytes +=
                    $mailboxBytes;

            } catch (\Throwable $e) {
                /*
                 * A temporary DirectAdmin outage must not make existing
                 * mailbox data disappear from the Core storage meter.
                 *
                 * Use the last known successful usage and preserve the
                 * error for diagnostics.
                 */
                $bytes +=
                    max(
                        0,
                        (int) $mailbox->last_storage_bytes
                    );

                $mailbox->forceFill([
                    'last_error' =>
                        $e->getMessage(),
                ])->save();

                report($e);
            }
        }

        return $bytes;
    }

    /**
     * ESUBIZ_SAAS_MAILBOX_TENANT_REGISTRY_SYNC_V4
     *
     * Central owns managed mailbox provisioning.
     * Tenant Core owns the Email workspace.
     *
     * This registers an already-existing managed mailbox inside
     * Core without copying DirectAdmin administrative credentials.
     */
    public function syncManagedMailboxToCore(
        Website $website,
        \App\Models\WebsiteMailbox $mailbox
    ): void {
        if (
            !$website->isSaas() ||
            strtolower((string) $mailbox->status) !== 'active'
        ) {
            return;
        }

        $address = strtolower(
            trim((string) $mailbox->email_address)
        );

        if ($address === '') {
            return;
        }

        try {
            $this->tenantDatabaseService->connect($website);

            $schema =
                \Illuminate\Support\Facades\Schema::connection(
                    'website_tenant'
                );

            if (!$schema->hasTable('email_boxes')) {
                throw new \RuntimeException(
                    'Tenant Core email_boxes table is unavailable.'
                );
            }

            $db =
                \Illuminate\Support\Facades\DB::connection(
                    'website_tenant'
                );

            $existing = $db
                ->table('email_boxes')
                ->whereRaw(
                    'LOWER(email) = ?',
                    [$address]
                )
                ->first();

            $primaryExists = $db
                ->table('email_boxes')
                ->where('is_primary', true)
                ->where('status', 'active')
                ->exists();

            $settings = json_encode(
                [
                    'provider' =>
                        (string) $mailbox->provider,

                    'central_mailbox_id' =>
                        (int) $mailbox->id,

                    'managed_by_esubiz' =>
                        true,
                ],
                JSON_UNESCAPED_SLASHES
            );

            $values = [
                'name' => $address,
                'email' => $address,

                'username' =>
                    (string) $mailbox->local_part,

                'connection_type' => 'managed',
                'status' => 'active',
                'settings' => $settings,
                'updated_at' => now(),
            ];

            if ($existing) {
                $db->table('email_boxes')
                    ->where('id', $existing->id)
                    ->update($values);

                if (!$primaryExists) {
                    $db->table('email_boxes')
                        ->where('id', $existing->id)
                        ->update([
                            'is_primary' => true,
                        ]);
                }
            } else {
                $values['is_primary'] =
                    !$primaryExists;

                $values['created_at'] = now();

                $db->table('email_boxes')
                    ->insert($values);
            }

            /*
             * Primary mailbox remains canonical site_email.
             */
            if ($schema->hasTable('site_settings')) {
                $primary = $db
                    ->table('email_boxes')
                    ->where('is_primary', true)
                    ->where('status', 'active')
                    ->orderBy('id')
                    ->first();

                if (
                    $primary &&
                    trim((string) $primary->email) !== ''
                ) {
                    $db->table('site_settings')
                        ->updateOrInsert(
                            [
                                'key' => 'site_email',
                            ],
                            [
                                'value' => strtolower(
                                    trim(
                                        (string) $primary->email
                                    )
                                ),
                            ]
                        );
                }
            }
        } finally {
            try {
                $this->tenantDatabaseService->disconnect();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }


    /**
     * ESUBIZ_DOMAIN_AWARE_MANAGED_MAIL_V6
     *
     * Generic managed-mail identity boundary.
     *
     * Esubiz-hosted SaaS mail is always managed by Esubiz.
     *
     * esubiz.com:
     *   included -> john@esubiz.com
     *   extra    -> sales.john@esubiz.com
     *
     * Website-owned managed domain:
     *   info@john.com
     *   sales@john.com
     */
    public function managedMailboxAddress(
        Website $website,
        string $requestedLocalPart,
        ?string $requestedDomain = null,
        bool $includedMailbox = false
    ): string {
        if (!$website->isSaas()) {
            throw new RuntimeException(
                'Esubiz managed-mail provisioning requires a SaaS Website.'
            );
        }

        $tenant = strtolower(
            trim((string) $website->subdomain)
        );

        if (
            $tenant === '' ||
            !preg_match(
                '/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/',
                $tenant
            )
        ) {
            throw new RuntimeException(
                'Website has no valid SaaS subdomain namespace.'
            );
        }

        $managedDomain = strtolower(
            trim(
                (string) config(
                    'esubiz_mail.managed_domain',
                    'esubiz.com'
                )
            )
        );

        $domain = strtolower(
            trim($requestedDomain ?: $managedDomain)
        );

        if (
            $domain === '' ||
            !filter_var(
                'mail@' . $domain,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'Invalid managed mailbox domain.'
            );
        }

        /*
         * A custom domain is eligible only when it belongs to this
         * Website and is hosted/managed by Esubiz.
         *
         * The concrete domain-lifecycle service will register these
         * domains with DirectAdmin. This boundary deliberately does
         * not accept arbitrary third-party mail domains.
         */
        if (
            $domain !== $managedDomain &&
            !$this->websiteOwnsManagedMailDomain(
                $website,
                $domain
            )
        ) {
            throw new RuntimeException(
                "Domain [{$domain}] is not an Esubiz-managed domain for this Website."
            );
        }

        $requestedLocalPart = strtolower(
            trim($requestedLocalPart)
        );

        if ($includedMailbox && $domain === $managedDomain) {
            $localPart = $tenant;
        } elseif ($domain === $managedDomain) {
            if ($requestedLocalPart === '') {
                throw new RuntimeException(
                    'Additional mailbox name is required.'
                );
            }

            $this->assertManagedLocalPart(
                $requestedLocalPart
            );

            /*
             * Tenant namespace prevents collisions between SaaS sites.
             *
             * sales + john -> sales.john@esubiz.com
             */
            $localPart =
                $requestedLocalPart . '.' . $tenant;
        } else {
            if ($requestedLocalPart === '') {
                throw new RuntimeException(
                    'Mailbox name is required.'
                );
            }

            $this->assertManagedLocalPart(
                $requestedLocalPart
            );

            /*
             * The custom domain itself supplies tenant isolation.
             *
             * info + john.com -> info@john.com
             */
            $localPart = $requestedLocalPart;
        }

        $this->assertManagedLocalPart(
            $localPart,
            true
        );

        return $localPart . '@' . $domain;
    }

    /**
     * Provision an additional Esubiz-hosted SaaS mailbox.
     *
     * ESUBIZ_ADDITIONAL_MANAGED_MAILBOX_V6
     *
     * This is a server/mail service, not a Core Add-on.
     * Payment authorization belongs above this service boundary.
     */
    public function provisionAdditionalManagedMailbox(
        Website $website,
        string $localPart,
        string $password,
        ?string $domain = null
    ): WebsiteMailbox {
        $password = trim($password);

        if ($password === '') {
            throw new RuntimeException(
                'Mailbox password is required.'
            );
        }

        $address = $this->managedMailboxAddress(
            $website,
            $localPart,
            $domain,
            false
        );

        [$resolvedLocalPart, $resolvedDomain] =
            explode('@', $address, 2);

        $mailbox = WebsiteMailbox::query()
            ->firstOrNew([
                'email_address' => $address,
            ]);

        if (
            $mailbox->exists &&
            (int) $mailbox->website_id !==
                (int) $website->id
        ) {
            throw new RuntimeException(
                "Mailbox [{$address}] belongs to another Website."
            );
        }

        if (
            $mailbox->exists &&
            $mailbox->status === 'active'
        ) {
            $this->syncManagedMailboxToCore(
                $website,
                $mailbox
            );

            return $mailbox;
        }

        $mailbox->fill([
            'website_id' => $website->id,
            'provider' => 'directadmin',
            'connection_type' => 'managed',
            'domain' => $resolvedDomain,
            'local_part' => $resolvedLocalPart,
            'password' => $password,
            'status' => 'provisioning',
            'last_error' => null,
        ])->save();

        try {
            $this->directAdmin->create(
                $resolvedDomain,
                $resolvedLocalPart,
                $password,
                $this->storageLimitMb($website)
            );

            $mailbox->forceFill([
                'status' => 'active',
                'provisioned_at' => now(),
                'deleted_at_remote' => null,
                'last_error' => null,
            ])->save();

            $mailbox = $mailbox->fresh();

            $this->syncManagedMailboxToCore(
                $website,
                $mailbox
            );

            return $mailbox;
        } catch (\Throwable $e) {
            $mailbox->forceFill([
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ])->save();

            throw $e;
        }
    }

    /**
     * Return Esubiz-managed mail domains currently known to belong
     * to the Website.
     *
     * The permanent Esubiz namespace is always available.
     */
    public function managedMailDomains(
        Website $website
    ): array {
        $domains = [
            strtolower(
                trim(
                    (string) config(
                        'esubiz_mail.managed_domain',
                        'esubiz.com'
                    )
                )
            ),
        ];

        /*
         * ESUBIZ_MANAGED_CUSTOM_DOMAIN_DISCOVERY_V6
         *
         * Use existing Website identity fields without inventing a
         * second domain registry. registeredHost() is already the
         * Website model's canonical registered-host boundary.
         */
        try {
            $host = strtolower(
                trim(
                    (string) $website->registeredHost()
                )
            );

            if (
                $host !== '' &&
                $host !== strtolower(
                    trim((string) $website->subdomain)
                    . '.'
                    . trim(
                        (string) config(
                            'esubiz_mail.managed_domain',
                            'esubiz.com'
                        )
                    )
                ) &&
                $host !== $domains[0]
            ) {
                $domains[] = $host;
            }
        } catch (\Throwable $e) {
            /*
             * No attached custom host yet.
             */
        }

        return array_values(
            array_unique(
                array_filter($domains)
            )
        );
    }

    private function websiteOwnsManagedMailDomain(
        Website $website,
        string $domain
    ): bool {
        return in_array(
            strtolower(trim($domain)),
            $this->managedMailDomains($website),
            true
        );
    }

    private function assertManagedLocalPart(
        string $localPart,
        bool $allowDot = false
    ): void {
        $pattern = $allowDot
            ? '/^[a-z0-9](?:[a-z0-9._-]{0,62}[a-z0-9])?$/'
            : '/^[a-z0-9](?:[a-z0-9_-]{0,62}[a-z0-9])?$/';

        if (
            strlen($localPart) > 64 ||
            !preg_match($pattern, $localPart)
        ) {
            throw new RuntimeException(
                'Invalid managed mailbox name.'
            );
        }
    }

}
