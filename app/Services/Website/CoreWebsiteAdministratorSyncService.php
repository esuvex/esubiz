<?php

namespace App\Services\Website;

use App\Models\Website;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CoreWebsiteAdministratorSyncService
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    /**
     * Synchronize the Central Website administrator into
     * the SaaS Core local site_users table.
     *
     * Central Website admin_* remains the provisioning authority.
     *
     * Avatar is intentionally excluded because profile avatars
     * are always owned by the Core installation.
     */
    public function sync(
        Website $website,
        ?string $previousAdminEmail = null
    ): void {
        /*
         * Off-server Core owns its own local administrator.
         * Central-to-tenant database synchronization applies only
         * to SaaS-hosted websites.
         */
        if (
            (string) $website->deployment_type
            !== Website::DEPLOYMENT_SAAS
        ) {
            return;
        }

        $newEmail = strtolower(
            trim(
                (string) (
                    $website->admin_email
                    ?? ''
                )
            )
        );

        if ($newEmail === '') {
            throw new RuntimeException(
                'The Central Website administrator email is empty.'
            );
        }

        $oldEmail = strtolower(
            trim(
                (string) (
                    $previousAdminEmail
                    ?? ''
                )
            )
        );

        try {
            $this->tenantDatabaseService->connect(
                $website
            );

            $db =
                $this->tenantDatabaseService
                    ->connection();

            $schema = $db->getSchemaBuilder();

            if (!$schema->hasTable('site_users')) {
                throw new RuntimeException(
                    'The Core site_users table does not exist.'
                );
            }

            /*
             * Locate the existing administrator by the PREVIOUS
             * Central email first.
             *
             * This is critical when the Central administrator
             * email itself is being changed.
             */
            $profileUser = null;

            if ($oldEmail !== '') {
                $profileUser = $db
                    ->table('site_users')
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [$oldEmail]
                    )
                    ->first();
            }

            /*
             * Fallback to the new email.
             *
             * This handles already-synchronized records and
             * older websites whose Central email already changed.
             */
            if (!$profileUser) {
                $profileUser = $db
                    ->table('site_users')
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [$newEmail]
                    )
                    ->first();
            }

            /*
             * Do not allow an administrator email change to
             * collide with another local Core account.
             */
            if ($profileUser) {
                $emailConflict = $db
                    ->table('site_users')
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [$newEmail]
                    )
                    ->where(
                        'id',
                        '<>',
                        (int) $profileUser->id
                    )
                    ->exists();

                if ($emailConflict) {
                    throw new RuntimeException(
                        'The new administrator email is already '
                        . 'used by another Core account.'
                    );
                }
            }

            $wizardData =
                is_array($website->wizard_data)
                    ? $website->wizard_data
                    : [];

            $phone =
                isset($wizardData['admin_phone'])
                && trim(
                    (string) $wizardData['admin_phone']
                ) !== ''
                    ? trim(
                        (string) $wizardData['admin_phone']
                    )
                    : null;

            $name = trim(
                (string) (
                    $website->admin_name
                    ?: $website->name
                )
            );

            $payload = [
                'name' => $name,
                'email' => $newEmail,
                'phone' => $phone,
                'is_active' => true,
                'updated_at' => now(),
            ];

            /*
             * Central already stores a Laravel password hash.
             *
             * Copy that exact hash into Core.
             * Never Hash::make() an existing Central hash.
             */
            $centralPasswordHash =
                trim(
                    (string) (
                        $website->admin_password
                        ?? ''
                    )
                );

            if ($centralPasswordHash !== '') {
                $payload['password'] =
                    $centralPasswordHash;
            }

            if ($profileUser) {
                /*
                 * Preserve the local Core row ID and avatar_path.
                 *
                 * avatar_path is not included in this update.
                 */
                $db
                    ->table('site_users')
                    ->where(
                        'id',
                        (int) $profileUser->id
                    )
                    ->update($payload);

                return;
            }

            /*
             * Missing administrator:
             *
             * Create the Core account from the Central authority.
             * Normally admin_password is already present. The
             * random fallback exists only to keep an incomplete
             * legacy Central record from creating an unusable
             * plaintext/null password.
             */
            if (
                !array_key_exists(
                    'password',
                    $payload
                )
            ) {
                $payload['password'] =
                    Hash::make(
                        Str::random(64)
                    );
            }

            $payload['created_at'] = now();

            $db
                ->table('site_users')
                ->insert($payload);

        } catch (Throwable $syncError) {
            throw $syncError;
        } finally {
            try {
                $this->tenantDatabaseService
                    ->disconnect();
            } catch (Throwable $disconnectError) {
                report($disconnectError);
            }
        }
    }
}
