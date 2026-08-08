<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeveloperAccountController extends Controller
{
    /**
     * Display the developer account upgrade page.
     */
    public function create(Request $request)
    {
        return view('developer-account.create');
    }

    /**
     * Upgrade the authenticated user to a developer account.
     *
     * Developer upgrades are automatically approved for now.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $role = Role::where('slug', 'developer')
            ->where('is_active', true)
            ->first();

        if (!$role) {
            return back()->withErrors([
                'developer' => 'Developer registration is currently unavailable. Please try again later.',
            ]);
        }

        UserRole::where('user_id', $user->id)
            ->update(['is_primary' => false]);

        UserRole::updateOrCreate(
            [
                'user_id' => $user->id,
                'role_id' => $role->id,
            ],
            [
                'is_primary' => true,
            ]
        );

        session([
            'account_mode' => 'developer',
        ]);

        return redirect()->route('developer.dashboard');
    }
}
