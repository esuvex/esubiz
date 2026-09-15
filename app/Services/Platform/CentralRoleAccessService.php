<?php

namespace App\Services\Platform;

use Illuminate\Contracts\Auth\Authenticatable;

class CentralRoleAccessService
{

    /**
     * ESUBIZ_CENTRAL_ASSIGNED_ROLE_RESOLVER_V23
     *
     * Return roles actually assigned to a Central account.
     *
     * Plug-and-play compatibility:
     * - Spatie/getRoleNames()
     * - roles() relationships
     * - loaded roles collections
     * - legacy Central scalar role fields
     * - established Admin helper authority
     */
    public function assignedRoleNames($user): array
    {
        if (!$user) {
            return [];
        }

        $roles = collect();

        /*
         * Spatie-style role source.
         */
        try {
            if (method_exists($user, 'getRoleNames')) {
                $roles = $roles->merge(
                    collect(
                        $user->getRoleNames()
                    )
                );
            }
        } catch (\Throwable $e) {
            // Continue to other supported role authorities.
        }

        /*
         * Generic roles relationship.
         */
        try {
            if (method_exists($user, 'roles')) {
                $assigned = $user->roles()->get();

                foreach ($assigned as $role) {
                    foreach ([
                        'name',
                        'display_name',
                        'title',
                        'slug',
                    ] as $field) {
                        $value = data_get(
                            $role,
                            $field
                        );

                        if (
                            is_scalar($value)
                            && trim((string) $value) !== ''
                        ) {
                            $roles->push(
                                (string) $value
                            );
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Continue.
        }

        /*
         * Already-loaded generic roles collection.
         */
        try {
            $loadedRoles =
                $user->getRelationValue('roles');

            if (
                $loadedRoles
                instanceof \Illuminate\Support\Collection
            ) {
                foreach ($loadedRoles as $role) {
                    foreach ([
                        'name',
                        'display_name',
                        'title',
                        'slug',
                    ] as $field) {
                        $value = data_get(
                            $role,
                            $field
                        );

                        if (
                            is_scalar($value)
                            && trim((string) $value) !== ''
                        ) {
                            $roles->push(
                                (string) $value
                            );
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Continue.
        }

        /*
         * Existing Central/legacy scalar fields.
         */
        foreach ([
            'role',
            'user_type',
            'type',
            'account_role',
        ] as $field) {
            try {
                $value = $user->{$field} ?? null;

                if (
                    is_scalar($value)
                    && trim((string) $value) !== ''
                ) {
                    $roles->push(
                        (string) $value
                    );
                }
            } catch (\Throwable $e) {
                // Continue.
            }
        }

        $roles = $roles
            ->map(
                function ($role) {
                    $role = trim(
                        (string) $role
                    );

                    return strtolower(
                        str_replace(
                            [' ', '-'],
                            '_',
                            $role
                        )
                    );
                }
            )
            ->filter()
            ->unique()
            ->values();

        /*
         * A stale legacy "user" value must not override an
         * actual stronger assigned role.
         */
        if ($roles->count() > 1) {
            $roles = $roles->reject(
                fn ($role) => $role === 'user'
            )->values();
        }

        /*
         * Existing Admin authority remains a compatibility fallback.
         */
        if (
            $roles->isEmpty()
            && $this->isAdminFamily($user)
        ) {
            $roles->push('admin');
        }

        return $roles->all();
    }

    /**
     * Return the primary assigned Central role.
     */
    public function primaryAssignedRole(
        $user,
        string $fallback = 'user'
    ): string {
        $roles = $this->assignedRoleNames(
            $user
        );

        if (!$roles) {
            return $fallback;
        }

        /*
         * Existing built-in roles get deterministic precedence.
         * Unknown future roles still work automatically.
         */
        $priority = [
            'super_admin',
            'superadmin',
            'platform_admin',
            'admin',
            'administrator',
            'staff',
            'investor',
            'partner',
            'investor_partner',
            'developer',
            'user',
        ];

        foreach ($priority as $candidate) {
            if (in_array(
                $candidate,
                $roles,
                true
            )) {
                return $candidate;
            }
        }

        /*
         * Custom future role: no code change required.
         */
        return (string) $roles[0];
    }

    /**
     * Human-readable role label for Central UI.
     */
    public function assignedRoleLabel(
        $user,
        string $fallback = 'User'
    ): string {
        $role = $this->primaryAssignedRole(
            $user,
            ''
        );

        if ($role === '') {
            return $fallback;
        }

        return match ($role) {
            'super_admin',
            'superadmin',
            'platform_admin',
            'admin',
            'administrator'
                => 'Administrator',

            'investor_partner'
                => 'Investor / Partner',

            default => ucwords(
                str_replace(
                    '_',
                    ' ',
                    $role
                )
            ),
        };
    }


    /**
     * ESUBIZ_CENTRAL_ADMIN_FAMILY_AUTHORITY_V12
     *
     * Preserve existing Central Admin authority even when a
     * legacy account_role value still says "user".
     */
    public function isAdminFamily($user): bool
    {
        /*
         * ESUBIZ_CENTRAL_ADMIN_FAMILY_AUTHORITY_V14
         *
         * Existing Central Admin authority takes precedence over a
         * stale account_role value such as "user".
         */

        if (!$user) {
            return false;
        }

        /*
         * Support role packages / User-model helpers without binding
         * Central authority to one implementation.
         */
        foreach ([
            'admin',
            'administrator',
            'super_admin',
            'superadmin',
            'platform_admin',
        ] as $roleName) {
            try {
                if (
                    method_exists($user, 'hasRole')
                    && $user->hasRole($roleName)
                ) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Continue through other established authorities.
            }
        }

        /*
         * Existing model helper methods.
         */
        foreach ([
            'isAdmin',
            'isAdministrator',
            'isSuperAdmin',
        ] as $method) {
            try {
                if (
                    method_exists($user, $method)
                    && (bool) $user->{$method}()
                ) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Continue.
            }
        }

        /*
         * Relationship-based roles.
         */
        try {
            if (
                method_exists($user, 'roles')
                && $user->roles()
            ) {
                $roleNames = $user->roles()
                    ->pluck('name')
                    ->map(
                        fn ($name) => strtolower(
                            str_replace(
                                [' ', '-'],
                                '_',
                                trim((string) $name)
                            )
                        )
                    );

                if (
                    $roleNames->intersect([
                        'admin',
                        'administrator',
                        'super_admin',
                        'superadmin',
                        'platform_admin',
                        'staff',
                        'investor',
                        'partner',
                        'investor_partner',
                    ])->isNotEmpty()
                ) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // Continue.
        }


        if (!$user) {
            return false;
        }

        foreach ([
            'isAdmin',
            'isAdministrator',
            'isSuperAdmin',
        ] as $method) {
            try {
                if (
                    method_exists($user, $method)
                    && (bool) $user->{$method}()
                ) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Continue through established role sources.
            }
        }

        foreach ([
            'role',
            'user_type',
            'type',
            'account_role',
        ] as $field) {
            $value = $user->{$field} ?? null;

            if (!is_scalar($value)) {
                continue;
            }

            $role = strtolower(
                str_replace(
                    [' ', '-'],
                    '_',
                    trim((string) $value)
                )
            );

            if (in_array(
                $role,
                [
                    'admin',
                    'administrator',
                    'superadmin',
                    'super_admin',
                    'platform_admin',
                    'staff',
                    'investor',
                    'partner',
                    'investor_partner',
                    'investor/partner',
                ],
                true
            )) {
                return true;
            }
        }

        return false;
    }

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
        /*
         * ESUBIZ_CENTRAL_ADMIN_VISIBLE_CONTEXTS_V12
         */
        if ($this->isAdminFamily($user)) {
            return ['admin', 'developer', 'user'];
        }

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
        /*
         * ESUBIZ_CENTRAL_ADMIN_VISIBLE_AUTHORITY_V17
         *
         * Admin-family helper remains an unconditional superset.
         */

        /*
         * ESUBIZ_CENTRAL_ADMIN_CONTEXT_ACCESS_V12
         */
        if ($this->isAdminFamily($user)) {
            return true;
        }

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
        /*
         * ESUBIZ_CENTRAL_ADMIN_USER_FEATURES_V12
         */
        if ($this->isAdminFamily($user)) {
            return true;
        }

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
        /*
         * ESUBIZ_CENTRAL_ADMIN_DEVELOPER_FEATURES_V12
         */
        if ($this->isAdminFamily($user)) {
            return true;
        }

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
