<?php

namespace App\Services;

use App\Models\Website;
use App\Services\Website\WebsiteProvisioningService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebsiteService
{
    protected WebsiteProvisioningService $provisioningService;

    public function __construct(WebsiteProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }

    /**
     * Create a new website.
     */
    public function create(array $data): Website
    {
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

            'storage_mb'      => 0,
            'bandwidth_mb'    => 0,

            'enabled_modules' => [],
            'enabled_features'=> [],
            'settings'        => [],

            'status'          => 'provisioning',
        ]);

        $this->provisioningService->provision($website);

        return $website;
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
