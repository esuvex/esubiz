<?php

namespace App\Services\Platform;

use Illuminate\Contracts\Auth\Authenticatable;

class CentralRoleAccessService
{
    /*
     * ESUBIZ_CENTRAL_ROLE_ACCESS_V1
     *
     * Central account families:
     *
     * ADMIN
     * - Platform Main Admin
     * - Staff
     * - Investor / Partner
     *
     * USER
     * - Standard Central customer
     *
     * DEVELOPER
     * - Developer account
     * - Includes all normal User capabilities
     * - Does NOT require a separate "User Mode"
     *
     * Workspace rules:
     *
     * Admin:
     *   Admin + User + Developer workspaces.
     *
     * Developer:
     *   Developer workspace only in the mode switcher,
     *   but may use User-family routes/features internally.
     *
     * User:
     *   User workspace only.
     *
     * This replaces the old rule where Admin had to enter
     * User Mode before normal customer functions could work.
     */
    public const FAMILY_ADMIN = 'admin';
    public const FAMILY_USER = 'user';
    public const FAMILY_DEVELOPER = 'developer';

    public const CONTEXT_ADMIN = 'admin';
    public const CONTEXT_USER = 'user';
    public const CONTEXT_DEVELOPER = 'developer';

    public function family(
        ?Authenticatable $user
    ): string {
        if (!$user) {
            return self::FAMILY_USER;
        }

        $role = $this->rawRole(
            $user
        );

        if ($this->isAdminRole($role)) {
            return self::FAMILY_ADMIN;
        }

        if ($this->isDeveloperRole($role)) {
            return self::FAMILY_DEVELOPER;
        }

        return self::FAMILY_USER;
    }

    public function isAdmin(
        ?Authenticatable $user
    ): bool {
        return $this->family($user)
            === self::FAMILY_ADMIN;
    }

    public function isDeveloper(
        ?Authenticatable $user
    ): bool {
        return $this->family($user)
            === self::FAMILY_DEVELOPER;
    }

    public function isUser(
        ?Authenticatable $user
    ): bool {
        return $this->family($user)
            === self::FAMILY_USER;
    }

    /*
     * Visible mode/workspace switcher options.
     *
     * Developer intentionally receives only Developer here.
     * User functionality is merged into the Developer account.
     */
    public function visibleContexts(
        ?Authenticatable $user
    ): array {
        return match (
            $this->family($user)
        ) {
            self::FAMILY_ADMIN => [
                self::CONTEXT_ADMIN,
                self::CONTEXT_USER,
                self::CONTEXT_DEVELOPER,
            ],

            self::FAMILY_DEVELOPER => [
                self::CONTEXT_DEVELOPER,
            ],

            default => [
                self::CONTEXT_USER,
            ],
        };
    }

    /*
     * Route-family access differs from visible mode options.
     *
     * Developers may access User routes/features because normal
     * customer functionality is part of the Developer account.
     *
     * This lets existing User controllers remain reusable while
     * removing the separate "User Mode" concept for developers.
     */
    public function canAccessContext(
        ?Authenticatable $user,
        string $context
    ): bool {
        $context = $this->normalizeContext(
            $context
        );

        if ($context === null) {
            return false;
        }

        return match (
            $this->family($user)
        ) {
            self::FAMILY_ADMIN =>
                in_array(
                    $context,
                    [
                        self::CONTEXT_ADMIN,
                        self::CONTEXT_USER,
                        self::CONTEXT_DEVELOPER,
                    ],
                    true
                ),

            self::FAMILY_DEVELOPER =>
                in_array(
                    $context,
                    [
                        self::CONTEXT_USER,
                        self::CONTEXT_DEVELOPER,
                    ],
                    true
                ),

            default =>
                $context === self::CONTEXT_USER,
        };
    }

    public function defaultContext(
        ?Authenticatable $user
    ): string {
        return match (
            $this->family($user)
        ) {
            self::FAMILY_ADMIN =>
                self::CONTEXT_ADMIN,

            self::FAMILY_DEVELOPER =>
                self::CONTEXT_DEVELOPER,

            default =>
                self::CONTEXT_USER,
        };
    }

    /*
     * When restoring/switching workspace:
     *
     * - Admin may actively switch into all three.
     * - Developer never actively switches to User Mode.
     * - User cannot enter Developer/Admin.
     */
    public function resolveActiveContext(
        ?Authenticatable $user,
        ?string $requestedContext
    ): string {
        $requested =
            $this->normalizeContext(
                $requestedContext
            );

        $family =
            $this->family($user);

        if ($family === self::FAMILY_ADMIN) {
            if (
                $requested !== null
                && $this->canAccessContext(
                    $user,
                    $requested
                )
            ) {
                return $requested;
            }

            return self::CONTEXT_ADMIN;
        }

        if (
            $family
            === self::FAMILY_DEVELOPER
        ) {
            return self::CONTEXT_DEVELOPER;
        }

        return self::CONTEXT_USER;
    }

    public function canUpgradeToDeveloper(
        ?Authenticatable $user
    ): bool {
        return $this->family($user)
            === self::FAMILY_USER;
    }

    public function includesUserFeatures(
        ?Authenticatable $user
    ): bool {
        return in_array(
            $this->family($user),
            [
                self::FAMILY_USER,
                self::FAMILY_DEVELOPER,
                self::FAMILY_ADMIN,
            ],
            true
        );
    }

    public function includesDeveloperFeatures(
        ?Authenticatable $user
    ): bool {
        return in_array(
            $this->family($user),
            [
                self::FAMILY_DEVELOPER,
                self::FAMILY_ADMIN,
            ],
            true
        );
    }

    public function normalizeContext(
        ?string $context
    ): ?string {
        if ($context === null) {
            return null;
        }

        $context = strtolower(
            trim($context)
        );

        return in_array(
            $context,
            [
                self::CONTEXT_ADMIN,
                self::CONTEXT_USER,
                self::CONTEXT_DEVELOPER,
            ],
            true
        )
            ? $context
            : null;
    }

    /*
     * Flexible role extraction while the existing Central role
     * records are progressively normalized.
     */
    protected function rawRole(
        Authenticatable $user
    ): string {
        foreach (
            [
                /*
                 * ESUBIZ_CENTRAL_ACCOUNT_ROLE_AUTHORITY_V2
                 *
                 * account_role is the canonical Central role field.
                 * Older fields remain as migration/legacy fallbacks.
                 */
                'account_role',
                'role_slug',
                'role',
                'user_type',
                'type',
                'account_type',
            ] as $field
        ) {
            $value =
                $user->{$field}
                ?? null;

            if (
                is_scalar($value)
                && trim(
                    (string) $value
                ) !== ''
            ) {
                return $this->normalizeRole(
                    (string) $value
                );
            }
        }

        /*
         * Support common role relationship implementations
         * without making this service dependent on one package.
         */
        try {
            if (
                method_exists(
                    $user,
                    'getRoleNames'
                )
            ) {
                $roles =
                    $user->getRoleNames();

                foreach ($roles as $role) {
                    if (
                        is_scalar($role)
                    ) {
                        return $this->normalizeRole(
                            (string) $role
                        );
                    }
                }
            }
        } catch (\Throwable $exception) {
            // Fall back to User family.
        }

        return 'user';
    }

    protected function isAdminRole(
        string $role
    ): bool {
        return in_array(
            $role,
            [
                'admin',
                'administrator',
                'platform_admin',
                'platform_main_admin',
                'main_admin',
                'super_admin',
                'staff',
                'employee',
                'partner',
                'partners',
                'investor',
                'partner_investor',
                'partners_investors',
                'investor_partner',
            ],
            true
        );
    }

    protected function isDeveloperRole(
        string $role
    ): bool {
        return in_array(
            $role,
            [
                'developer',
                'dev',
                'marketplace_developer',
            ],
            true
        );
    }

    protected function normalizeRole(
        string $role
    ): string {
        $role = strtolower(
            trim($role)
        );

        $role = preg_replace(
            '/[^a-z0-9]+/',
            '_',
            $role
        );

        return trim(
            (string) $role,
            '_'
        );
    }
}
