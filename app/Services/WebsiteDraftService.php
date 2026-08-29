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

        /*
        |--------------------------------------------------------------------------
        | Website Identity
        |--------------------------------------------------------------------------
        |
        | Website Name is a real website identity field, not only wizard data.
        |
        */

        if (!empty($data['name'])) {
            $website->update([
                'name' => trim($data['name']),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Canonical Subdomain
        |--------------------------------------------------------------------------
        |
        | ESUBIZ_WIZARD_CANONICAL_SUBDOMAIN_SYNC_V1
        |
        | A draft begins with a temporary draft-* subdomain. Once the user has
        | selected a real subdomain in the wizard, that value becomes part of
        | the website's central identity and must be persisted to admin_core.
        |
        */

        if (!empty($data['subdomain'])) {
            $subdomain = \Illuminate\Support\Str::slug(
                $data['subdomain']
            );

            if ($subdomain === '') {
                throw new \InvalidArgumentException(
                    'The selected website subdomain is invalid.'
                );
            }

            $taken = Website::query()
                ->where('subdomain', $subdomain)
                ->where('id', '!=', $website->id)
                ->exists();

            if ($taken) {
                throw new \RuntimeException(
                    'The selected website subdomain is already in use.'
                );
            }

            /*
             * ESUBIZ_WIZARD_REGISTERED_DOMAIN_SYNC_V1
             *
             * A SaaS website's registered domain must follow the
             * canonical wizard subdomain instead of retaining the
             * temporary draft-* hostname.
             */
            $identity = [
                'subdomain' => $subdomain,
            ];

            if (($website->deployment_type ?? 'saas') === 'saas') {
                $centralHost = (string) (
                    parse_url(
                        config('app.url'),
                        PHP_URL_HOST
                    )
                    ?: 'esubiz.com'
                );

                $centralHost = preg_replace(
                    '/^www\./i',
                    '',
                    $centralHost
                );

                $identity['registered_domain'] =
                    $subdomain . '.' . $centralHost;
            }

            $website->update($identity);
        }

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
