<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountModeController extends Controller
{
    /**
     * Switch the authenticated user's active account mode.
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'mode' => ['required', 'in:user,developer,admin'],
        ]);

        $user = $request->user();
        $mode = $request->input('mode');

        $roleIds = UserRole::where('user_id', $user->id)
            ->pluck('role_id');

        $roles = Role::whereIn('id', $roleIds)
            ->where('is_active', true)
            ->pluck('slug');

        $allowed = match ($mode) {
            'user' => $roles->contains('user')
                || $roles->contains('developer')
                || $roles->contains('platform-admin'),

            'developer' => $roles->contains('developer')
                || $roles->contains('platform-admin'),

            'admin' => $roles->contains('platform-admin'),

            default => false,
        };

        if (!$allowed) {
            abort(403, 'You are not authorized to enter this account mode.');
        }

        session(['account_mode' => $mode]);

        return match ($mode) {
            'user' => redirect()->route('user.dashboard'),
            'developer' => redirect()->route('developer.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
        };
    }
}
