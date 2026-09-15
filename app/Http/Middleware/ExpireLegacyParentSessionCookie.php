<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpireLegacyParentSessionCookie
{
    /*
     * ESUBIZ_LEGACY_PARENT_SESSION_COOKIE_CLEANUP_V1
     *
     * Transitional cleanup after Esubiz moved Laravel sessions
     * from the shared parent domain ".esubiz.com" to host-only
     * cookies.
     *
     * The active Laravel session cookie is now host-only.
     * This middleware expires ONLY the historical parent-domain
     * cookie so Central and individual SaaS websites no longer
     * receive the same browser session identifier.
     *
     * This middleware may remain temporarily during migration.
     * It does not delete the new host-only session cookie.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        $cookieName = (string) config(
            'session.cookie',
            'esubiz-session'
        );

        /*
         * Expire the old cookie specifically at .esubiz.com.
         *
         * A Set-Cookie deletion with Domain=.esubiz.com targets
         * the historical shared cookie. Laravel's normal session
         * response remains host-only because session.domain is
         * null/empty.
         */
        $response->headers->clearCookie(
            $cookieName,
            '/',
            '.esubiz.com',
            (bool) config('session.secure', false),
            (bool) config('session.http_only', true),
            (string) config('session.same_site', 'lax')
        );

        return $response;
    }
}
