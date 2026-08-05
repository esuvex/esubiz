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
            'owner_id'     => Auth::id(),
            'workspace_id' => $data['workspace_id'] ?? null,
            'name'         => $data['name'],
            'type'         => $data['type'],
            'slug'         => $this->generateSlug($data['name']),
            'subdomain'    => $this->generateSubdomain($data['name']),
            'status'       => 'provisioning',
        ]);

        $this->provisioningService->provision($website);

        return $website;
    }

    /**
     * Generate a unique slug.
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
     * Generate a unique subdomain.
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
