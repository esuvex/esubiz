<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $roleIds = UserRole::where('user_id', $user->id)
            ->pluck('role_id');

        if ($roleIds->isEmpty()) {
            abort(403, 'No role assigned.');
        }

        $permissionModel = Permission::where('slug', $permission)->first();

        if (!$permissionModel) {
            abort(403, 'Permission not found.');
        }

        $allowed = RolePermission::whereIn('role_id', $roleIds)
            ->where('permission_id', $permissionModel->id)
            ->exists();

        if (!$allowed) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
