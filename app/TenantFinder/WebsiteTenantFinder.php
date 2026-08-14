<?php

namespace App\TenantFinder;

use App\Models\Website;
use App\Models\WebsiteDatabaseConnection;
use App\Models\WebsiteTenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class WebsiteTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $host = strtolower($request->getHost());

        if ($host === 'esubiz.com' || $host === 'www.esubiz.com') {
            return null;
        }

        $website = Website::query()
            ->where('domain', $host)
            ->where('status', 'active')
            ->first();

        if (!$website) {
            $subdomain = $this->extractSubdomain($host);

            if ($subdomain !== null) {
                $website = Website::query()
                    ->where('subdomain', $subdomain)
                    ->where('status', 'active')
                    ->first();
            }
        }

        if (!$website) {
            return null;
        }

        $connection = WebsiteDatabaseConnection::query()
            ->where('website_id', $website->id)
            ->where('status', 'active')
            ->first();

        if (!$connection) {
            return null;
        }

        return new WebsiteTenant([
            'id' => $connection->id,
            'website_id' => $connection->website_id,
            'host' => $connection->host,
            'port' => $connection->port,
            'database' => $connection->database,
            'status' => $connection->status,
        ]);
    }

    protected function extractSubdomain(string $host): ?string
    {
        $baseDomain = 'esubiz.com';

        if (!str_ends_with($host, '.' . $baseDomain)) {
            return null;
        }

        $subdomain = substr(
            $host,
            0,
            -strlen('.' . $baseDomain)
        );

        if ($subdomain === '' || str_contains($subdomain, '.')) {
            return null;
        }

        return $subdomain;
    }
}
