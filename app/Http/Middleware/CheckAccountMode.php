<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $mode
    ): Response {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $allowed = match ($mode) {
            'admin' => $user->hasRole('platform-admin'),

            'developer' =>
                $user->hasRole('developer')
                || $user->hasRole('platform-admin'),

            'user' =>
                $user->hasRole('user')
                || $user->hasRole('developer')
                || $user->hasRole('platform-admin'),

            default => false,
        };

        if (!$allowed) {
            abort(403, 'You are not authorized to use this account mode.');
        }

        /*
         * The central Admin area is itself an explicit request to enter
         * Admin Mode. A verified platform administrator must therefore
         * be allowed to establish Admin Mode here before the controller
         * is reached.
         *
         * Developer/User mode switching remains explicit and continues
         * to use the existing account-mode session.
         */
        if (
            $mode === 'admin'
            && $user->hasRole('platform-admin')
        ) {
            session(['account_mode' => 'admin']);

            return $next($request);
        }

        /*
         * User-mode functionality is the shared SaaS/customer layer.
         *
         * Developers and Platform Admins may use these exact User-mode
         * routes without switching their active account mode. Their
         * Developer/Admin session remains unchanged.
         *
         * Developer and Admin routes remain mode-specific.
         */
        if ($mode === 'user') {
            return $next($request);
        }

        if (session('account_mode') !== $mode) {
            abort(
                403,
                'Switch to this account mode before accessing this area.'
            );
        }

        return $next($request);
    }
}
