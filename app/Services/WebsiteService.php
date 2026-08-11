<?php

namespace App\Services;

use App\Models\Website;
use App\Services\Website\WebsiteProvisioningService;
use App\Services\Sso\SsoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebsiteService
{
    protected WebsiteProvisioningService $provisioningService;
    protected SsoService $ssoService;

    public function __construct(
        WebsiteProvisioningService $provisioningService,
        SsoService $ssoService
    ) {
        $this->provisioningService = $provisioningService;
        $this->ssoService = $ssoService;
    }

    /**
     * Create or deploy a website.
     */
    public function create(array $data): Website
    {
        /*
        |--------------------------------------------------------------------------
        | Existing draft
        |--------------------------------------------------------------------------
        */

        if (!empty($data['website_id'])) {

            $website = Website::findOrFail($data['website_id']);

            $website->update([
                'owner_id'     => $website->owner_id ?? Auth::id(),
                'workspace_id' => $data['workspace_id'] ?? $website->workspace_id,
                'plan_id'      => $data['plan_id'] ?? $website->plan_id,

                'name'       => $data['name'] ?? $website->name,
                'type'       => $data['type'] ?? $website->type,
                'edition'    => $data['edition'] ?? $website->edition,
                'owner_type' => $data['owner_type'] ?? $website->owner_type,

                'domain' => $data['domain'] ?? $website->domain,

                'industry' => $data['industry'] ?? $website->industry,
                'theme'    => $data['theme'] ?? $website->theme,
                'template' => $data['template'] ?? $website->template,

                'status' => 'provisioning',
            ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | New website
            |--------------------------------------------------------------------------
            */

            $website = Website::create([

                'owner_id'        => Auth::id(),
                'workspace_id'    => $data['workspace_id'] ?? null,
                'plan_id'         => $data['plan_id'] ?? null,

                'uuid'            => (string) Str::uuid(),
                'website_code'    => strtoupper(Str::random(10)),

                'name'            => $data['name'],
                'type'            => $data['type'],
                'edition'         => $data['edition'] ?? 'saas',
                'owner_type'      => $data['owner_type'] ?? 'owner',

                'slug'            => $this->generateSlug($data['name']),
                'subdomain'       => $this->generateSubdomain($data['name']),
                'domain'          => $data['domain'] ?? null,

                'industry'        => $data['industry'] ?? null,
                'theme'           => $data['theme'] ?? null,
                'template'        => $data['template'] ?? null,

                'multi_branch'    => false,
                'branch_limit'    => 1,

                'ai_credits'      => 0,
                'sms_credits'     => 0,

                'storage_mb'     => 0,
                'bandwidth_mb'   => 0,

                'enabled_modules'  => [],
                'enabled_features' => [],
                'settings'         => [],

                'status' => 'provisioning',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Provision website
        |--------------------------------------------------------------------------
        */

        $this->provisioningService->provision($website);

        /*
        |--------------------------------------------------------------------------
        | Ensure SSO application exists
        |--------------------------------------------------------------------------
        */

        if (!$website->apiApplication()->exists()) {

            $this->ssoService->registerApplication(
                name: $website->name . ' SSO',
                slug: $website->slug . '-sso',
                userId: $website->owner_id,
                workspaceId: $website->workspace_id,
                websiteId: $website->id,
                redirectUrls: [
                    'https://' . $website->subdomain . '.esubiz.com/sso/callback',
                ],
            );
        }

        return $website->fresh();
    }

    /**
     * Generate unique slug.
     */
    protected function generateSlug(string $name): string
    {
        $slug = Str::slug($name);

        $original = $slug;
        $count = 1;

        while (Website::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Generate unique subdomain.
     */
    protected function generateSubdomain(string $name): string
    {
        $subdomain = Str::slug($name);

        $original = $subdomain;
        $count = 1;

        while (Website::where('subdomain', $subdomain)->exists()) {
            $subdomain = "{$original}{$count}";
            $count++;
        }

        return $subdomain;
    }
}
