<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebsiteDraftService
{
    /**
     * Create a brand-new website draft.
     */
    public function create(array $data = []): Website
    {
        return Website::create([

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            'owner_id' => Auth::id(),

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            'uuid' => (string) Str::uuid(),

            'website_code' => strtoupper(Str::random(10)),

            /*
            |--------------------------------------------------------------------------
            | Draft
            |--------------------------------------------------------------------------
            */

            'name' => 'Untitled Website',

            'type' => 'business',

            'edition' => 'saas',

            'owner_type' => 'owner',

            'slug' => 'draft-'.Str::lower(Str::random(8)),

            'subdomain' => 'draft-'.Str::lower(Str::random(8)),

            /*
            |--------------------------------------------------------------------------
            | Wizard
            |--------------------------------------------------------------------------
            */

            'status' => 'draft',

            'current_step' => 1,

            'wizard_data' => $data,

            'last_saved_at' => now(),

        ]);
    }

    /**
     * Save one wizard step.
     */
    public function save(
        Website $website,
        array $data,
        int $step
    ): Website {

        $website->saveWizard(
            $data,
            $step
        );

        return $website->fresh();
    }

    /**
     * Resume an existing draft.
     */
    public function resume(
        Website $website
    ): Website {

        return $website->fresh();

    }

    /**
     * Return authenticated user's latest draft.
     */
    public function latestDraft(): ?Website
    {
        return Website::where('owner_id', Auth::id())
            ->where('status', 'draft')
            ->latest()
            ->first();
    }
}
