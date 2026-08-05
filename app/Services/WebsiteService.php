<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebsiteService
{
    /**
     * Create a new website.
     */
    public function create(array $data): Website
    {
        return Website::create([
            'owner_id'     => Auth::id(),
            'workspace_id' => $data['workspace_id'] ?? null,
            'name'         => $data['name'],
            'slug'         => $this->generateSlug($data['name']),
            'status'       => 'draft',
        ]);
    }

    /**
     * Generate a unique website slug.
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
}
