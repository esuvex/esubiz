<?php

namespace App\Http\Middleware;

use App\Services\Core\CorePermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ESUBIZ_CORE_PERMISSION_MIDDLEWARE_V1
 *
 * Universal Core permission gate.
 *
 * This middleware never returns a 403 page.
 *
 * Usage:
 *
 * ->middleware(CorePermissionMiddleware::class . ':forms.view')
 *
 * or with multiple acceptable permissions:
 *
 * ->middleware(
 *     CorePermissionMiddleware::class
 *     . ':forms.create,forms.edit'
 * )
 *
 * Multiple supplied permissions use ANY semantics.
 *
 * Core-owned only.
 * No SaaS/off-server branching.
 */
class CorePermissionMiddleware
{
    public function __construct(
        protected CorePermissionService $permissions
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string ...$required
    ): Response {
        $required = array_values(
            array_filter(
                array_map('trim', $required)
            )
        );

        if ($required === []) {
            return $next($request);
        }

        if ($this->permissions->canAny($required)) {
            return $next($request);
        }

        return redirect(
            $this->permissions->deniedRedirectTarget()
        );
    }
}
