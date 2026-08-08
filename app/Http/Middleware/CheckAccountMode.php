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

        if (session('account_mode') !== $mode) {
            abort(403, 'Switch to this account mode before accessing this area.');
        }

        return $next($request);
    }
}
