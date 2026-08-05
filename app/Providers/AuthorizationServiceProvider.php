<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {

            $roleIds = UserRole::where('user_id', $user->id)
                ->pluck('role_id');

            if ($roleIds->isEmpty()) {
                return null;
            }

            $permission = Permission::where('slug', $ability)->first();

            if (!$permission) {
                return null;
            }

            return RolePermission::whereIn('role_id', $roleIds)
                ->where('permission_id', $permission->id)
                ->exists();
        });
    }
}
