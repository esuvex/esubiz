<?php

namespace App\Services\Core;

use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ESUBIZ_CORE_PERMISSION_SERVICE_V1
 *
 * Universal authorization service for Core.
 *
 * Used by:
 * - Dashboard widgets
 * - Sidebar/navigation
 * - Pages
 * - Buttons/actions
 * - Controllers
 * - Middleware
 * - Core features
 * - Add-ons
 * - Bundles
 * - Themes
 * - Modules
 * - Products
 * - Site functions
 *
 * This service is Core-owned.
 * It contains no SaaS/off-server authorization branching.
 */
class CorePermissionService
{
    protected string $connection = 'website_tenant';

    /**
     * Cached authorization context for the current request.
     */
    protected ?array $context = null;

    /**
     * Determine whether the current Core user has a permission.
     */
    public function can(string $permission): bool
    {
        $context = $this->context();

        if (!$context['authenticated']) {
            return false;
        }

        /*
         * Administrator is the protected Head Admin role.
         * It always has full Core access, including permissions
         * registered later by future Core-powered features.
         */
        if ($context['administrator']) {
            return true;
        }

        return in_array(
            $permission,
            $context['permissions'],
            true
        );
    }

    /**
     * Determine whether the current user lacks a permission.
     */
    public function cannot(string $permission): bool
    {
        return !$this->can($permission);
    }

    /**
     * User must have at least one permission.
     */
    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can((string) $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * User must have every supplied permission.
     */
    public function canAll(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can((string) $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Require a Core permission without ever exposing a 403 page.
     *
     * Permission denial rules:
     *
     * - unauthenticated visitor -> /login
     * - authenticated admin request -> /admin
     * - authenticated public request -> /
     *
     * The shared /admin dashboard remains the safe landing page.
     * Dashboard cards/features are separately permission-driven.
     */
    public function authorize(string $permission): void
    {
        if ($this->can($permission)) {
            return;
        }

        throw new HttpResponseException(
            redirect($this->deniedRedirectTarget())
        );
    }

    /**
     * Safe redirect destination for denied Core access.
     */
    public function deniedRedirectTarget(): string
    {
        $authenticated = $this->userId() !== null;

        if (!$authenticated) {
            return '/login';
        }

        if (request()->is('admin') || request()->is('admin/*')) {
            /*
             * Avoid a redirect loop if authorization is ever
             * accidentally applied directly to the dashboard route.
             */
            if (request()->is('admin')) {
                return '/';
            }

            return '/admin';
        }

        return '/';
    }

    /**
     * Return all permission keys available to current Core user.
     */
    public function permissions(): array
    {
        return $this->context()['permissions'];
    }

    /**
     * Return current role slugs.
     */
    public function roles(): array
    {
        return $this->context()['roles'];
    }

    /**
     * Determine whether current user has a role.
     */
    public function hasRole(string $role): bool
    {
        return in_array(
            $role,
            $this->roles(),
            true
        );
    }

    /**
     * Is current Core user the protected Administrator?
     */
    public function isAdministrator(): bool
    {
        return $this->context()['administrator'];
    }

    /**
     * Current Core user ID from the existing tenant CMS session.
     *
     * We deliberately support the established Core session identity
     * rather than Laravel's central application Auth user.
     */
    public function userId(): ?int
    {
        $candidates = [
            session('tenant_cms_user_id'),
            session('site_user_id'),
        ];

        foreach ($candidates as $candidate) {
            if (
                $candidate !== null &&
                filter_var(
                    $candidate,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                ) !== false
            ) {
                return (int) $candidate;
            }
        }

        return null;
    }

    /**
     * Clear request cache when role assignments change.
     */
    public function forget(): void
    {
        $this->context = null;
    }

    /**
     * Build current authorization context once per request.
     */
    protected function context(): array
    {
        if ($this->context !== null) {
            return $this->context;
        }

        $userId = $this->userId();

        if (!$userId || !$this->rbacAvailable()) {
            return $this->context = [
                'authenticated' => false,
                'administrator' => false,
                'roles' => [],
                'permissions' => [],
            ];
        }

        $db = DB::connection($this->connection);

        $userExists = $db
            ->table('site_users')
            ->where('id', $userId)
            ->where('is_active', true)
            ->exists();

        if (!$userExists) {
            return $this->context = [
                'authenticated' => false,
                'administrator' => false,
                'roles' => [],
                'permissions' => [],
            ];
        }

        $roles = $db
            ->table('site_user_roles as sur')
            ->join(
                'site_roles as sr',
                'sr.id',
                '=',
                'sur.role_id'
            )
            ->where('sur.user_id', $userId)
            ->pluck('sr.slug')
            ->map(fn ($slug) => (string) $slug)
            ->unique()
            ->values()
            ->all();

        $administrator = in_array(
            'administrator',
            $roles,
            true
        );

        /*
         * Administrator does not need DB permission lookup for access,
         * but returning the registered catalogue is useful to dashboard
         * and UI consumers.
         */
        if ($administrator) {
            $permissions = Schema::connection($this->connection)
                ->hasTable('site_permissions')
                    ? $db->table('site_permissions')
                        ->pluck('key')
                        ->map(fn ($key) => (string) $key)
                        ->unique()
                        ->values()
                        ->all()
                    : [];

            return $this->context = [
                'authenticated' => true,
                'administrator' => true,
                'roles' => $roles,
                'permissions' => $permissions,
            ];
        }

        $permissions = $db
            ->table('site_user_roles as sur')
            ->join(
                'site_role_permissions as srp',
                'srp.role_id',
                '=',
                'sur.role_id'
            )
            ->join(
                'site_permissions as sp',
                'sp.id',
                '=',
                'srp.permission_id'
            )
            ->where('sur.user_id', $userId)
            ->pluck('sp.key')
            ->map(fn ($key) => (string) $key)
            ->unique()
            ->values()
            ->all();

        return $this->context = [
            'authenticated' => true,
            'administrator' => false,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }

    /**
     * Check only the universal Core RBAC tables.
     */
    protected function rbacAvailable(): bool
    {
        $schema = Schema::connection($this->connection);

        foreach ([
            'site_users',
            'site_roles',
            'site_permissions',
            'site_user_roles',
            'site_role_permissions',
        ] as $table) {
            if (!$schema->hasTable($table)) {
                return false;
            }
        }

        return true;
    }
}
