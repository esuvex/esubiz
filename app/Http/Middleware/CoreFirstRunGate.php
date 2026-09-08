<?php

namespace App\Http\Middleware;

use App\Services\Core\Installation\CoreInstallationState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ESUBIZ_CORE_FIRST_RUN_GATE_V1
 *
 * Deployment-aware first-run gate for the shared Esubiz Core.
 *
 * IMPORTANT:
 *
 * This middleware NEVER starts the installer on SaaS.
 *
 * SaaS:
 * Esubiz Central provisioning owns initial website creation.
 *
 * Off-server:
 * The website root becomes the first-run entry point when Core
 * has not yet completed its initial setup.
 *
 * There is no public /install lifecycle.
 */
class CoreFirstRunGate
{
    public function __construct(
        protected CoreInstallationState $installation
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
         * First-run setup is strictly an off-server Core concern.
         *
         * Never infer off-server mode merely because an installation
         * lock is absent.
         */
        if (!$this->isOffServer()) {
            return $next($request);
        }

        /*
         * Once Core is installed, this middleware becomes effectively
         * transparent forever.
         */
        if ($this->installation->isInstalled()) {
            return $next($request);
        }

        /*
         * Allow setup requests themselves through so we never create
         * a redirect loop.
         *
         * The setup route is intentionally internal/named infrastructure.
         * The customer enters installation through their website root.
         */
        if (
            $request->routeIs(
                'core.setup.*'
            )
        ) {
            return $next($request);
        }

        /*
         * Before installation, any normal browser entry into Core
         * should converge on first-run setup.
         *
         * The visible customer entry point remains:
         *
         * https://example.com/
         */
        return redirect()->route(
            'core.setup.start'
        );
    }

    protected function isOffServer(): bool
    {
        return strtolower(
            trim(
                (string) config(
                    'services.esubiz.deployment',
                    env(
                        'ESUBIZ_DEPLOYMENT',
                        'saas'
                    )
                )
            )
        ) === 'off_server';
    }
}
