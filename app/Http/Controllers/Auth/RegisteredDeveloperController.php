<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredDeveloperController extends Controller
{
    /**
     * Display the developer registration view.
     */
    public function create(): View
    {
        return view('auth.developer-register');
    }

    /**
     * Handle an incoming developer registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::where('slug', 'developer')
            ->where('is_active', true)
            ->first();

        if (!$role) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'Developer registration is currently unavailable. Please try again later.',
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

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->put('account_mode', 'developer');

        return redirect()->route('developer.dashboard');
    }
}
