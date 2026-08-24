<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $portal = $request->input('portal');

        // Validate a dedicated portal against the user's actual role.
        if ($portal === 'admin' && !$user->hasRole('platform-admin')) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'You are not authorized to access the Admin Portal.']);
        }

        if ($portal === 'developer' && !$user->hasRole('developer') && !$user->hasRole('platform-admin')) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'You are not authorized to access the Developer Portal.']);
        }

        // Determine the initial mode.
        // A dedicated portal explicitly selects the mode.
        // Universal /login selects the highest authorized account experience.
        if ($portal === 'admin') {
            $mode = 'admin';
        } elseif ($portal === 'developer') {
            $mode = 'developer';
        } elseif ($user->hasRole('platform-admin')) {
            $mode = 'admin';
        } elseif ($user->hasRole('developer')) {
            $mode = 'developer';
        } else {
            $mode = 'user';
        }

        $request->session()->put('account_mode', $mode);

        /*
         * Respect the URL that originally required authentication.
         *
         * This is required for flows such as:
         *
         * Tenant /admin
         * -> Continue with Esubiz
         * -> Central login
         * -> Website Dashboard SSO handoff
         * -> Tenant /admin/dashboard
         *
         * Normal Esubiz logins still fall back to the correct
         * User / Developer / Admin dashboard.
         */
        return redirect()->intended(
            route($mode . '.dashboard')
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
