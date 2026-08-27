<?php

namespace App\Services\CentralApi;

use App\Models\Website;
use Illuminate\Support\Facades\DB;

class CentralWebsiteDetailService
{
    /**
     * Build the canonical Central management snapshot for a website.
     *
     * IMPORTANT:
     *
     * This service is an aggregator only.
     *
     * It does not create or maintain separate credit, entitlement,
     * theme, module, licence, or Marketplace authorities.
     *
     * Each section reads from its existing canonical source.
     */
    public function get(Website $website): array
    {
        return [
            'website' => $this->website($website),

            'credits' => $this->credits($website),

            'entitlements' => $this->entitlements($website),

            'addons' => $this->addons($website),

            'theme' => $this->theme($website),

            'modules' => $this->modules($website),

            'licence' => $this->licence($website),
        ];
    }


    /**
     * Canonical website registry identity.
     */
    protected function website(Website $website): array
    {
        return [
            'id' => $website->id,

            'website_uuid' =>
                $website->website_uuid,

            'identity' =>
                $website->centralWebsiteIdentity(),

            'name' =>
                $website->name,

            'type' =>
                $website->type,

            'edition' =>
                $website->edition,

            'deployment_type' =>
                $website->deployment_type,

            /*
             * For SaaS websites the deployed subdomain is the live
             * website address and therefore takes precedence over an
             * old wizard/draft registered_domain value.
             */
            'registered_domain' =>
                (
                    $website->isSaas()
                    && !empty($website->subdomain)
                )
                    ? strtolower(
                        trim(
                            (string) $website->subdomain
                        )
                    )
                        . '.esubiz.com'
                    : $website->registered_domain,

            'registered_host' =>
                (
                    $website->isSaas()
                    && !empty($website->subdomain)
                )
                    ? strtolower(
                        trim(
                            (string) $website->subdomain
                        )
                    )
                        . '.esubiz.com'
                    : $website->registeredHost(),

            'registry_status' =>
                $website->registry_status,

            'owner_id' =>
                $website->owner_id,

            'developer_id' =>
                $website->developer_id,

            'workspace_id' =>
                $website->workspace_id,

            'plan_id' =>
                $website->plan_id,

            'status' =>
                $website->status,

            'user_enabled' =>
                (bool) $website->user_enabled,

            'is_saas' =>
                $website->isSaas(),

            'is_off_server' =>
                $website->isOffServer(),

            'registry_active' =>
                $website->isRegistryActive(),
        ];
    }


    /**
     * Central service-credit authority.
     *
     * Never read legacy websites.ai_credits / sms_credits here.
     */
    protected function credits(Website $website): array
    {
        $services = [
            'ai',
            'sms',
            'email',
            'whatsapp',
        ];

        $rows =
            DB::table(
                'central_website_service_credits'
            )
                ->where(
                    'website_id',
                    $website->id
                )
                ->whereIn(
                    'service',
                    $services
                )
                ->get()
                ->keyBy('service');

        $result = [];

        foreach ($services as $service) {
            $row =
                $rows->get(
                    $service
                );

            $result[$service] = [
                'balance' =>
                    (float) (
                        $row->balance
                        ?? 0
                    ),

                'lifetime_credited' =>
                    (float) (
                        $row->lifetime_credited
                        ?? 0
                    ),

                'lifetime_consumed' =>
                    (float) (
                        $row->lifetime_consumed
                        ?? 0
                    ),
            ];
        }

        return $result;
    }


    /**
     * Canonical purchased-product entitlement records.
     */
    protected function entitlements(Website $website)
    {
        return DB::table(
            'product_entitlements'
        )
            ->where(
                'website_id',
                $website->id
            )
            ->whereNull(
                'deleted_at'
            )
            ->orderByDesc(
                'id'
            )
            ->get();
    }


    /**
     * Active Core add-on entitlements for this website.
     *
     * product_entitlements remains the authority.
     */
    protected function addons(Website $website)
    {
        return DB::table(
            'product_entitlements'
        )
            ->where(
                'website_id',
                $website->id
            )
            ->where(
                'product_type',
                'core_addon'
            )
            ->where(
                'status',
                'active'
            )
            ->whereNull(
                'deleted_at'
            )
            ->orderByDesc(
                'id'
            )
            ->get();
    }


    /**
     * Current website theme state.
     *
     * The website.theme field is the current website-side theme
     * selection. If it resolves to a published theme package, include
     * the matching package as additional catalogue information.
     */
    protected function theme(Website $website): array
    {
        $selected =
            $website->theme;

        $package = null;

        if (
            is_string($selected)
            && trim($selected) !== ''
        ) {
            $package =
                DB::table(
                    'theme_packages'
                )
                    ->whereNull(
                        'deleted_at'
                    )
                    ->where(
                        function ($query) use ($selected) {
                            $query
                                ->where(
                                    'slug',
                                    $selected
                                )
                                ->orWhere(
                                    'uuid',
                                    $selected
                                );
                        }
                    )
                    ->orderByDesc(
                        'is_current'
                    )
                    ->orderByDesc(
                        'id'
                    )
                    ->first();
        }

        return [
            'selected' =>
                $selected,

            'package' =>
                $package,
        ];
    }


    /**
     * Module installations currently belong to a workspace.
     *
     * A website without a workspace therefore has no workspace module
     * installation context. We do not manufacture one here.
     */
    protected function modules(Website $website)
    {
        if (!$website->workspace_id) {
            return collect();
        }

        return DB::table(
            'module_installations'
        )
            ->leftJoin(
                'modules',
                'modules.id',
                '=',
                'module_installations.module_id'
            )
            ->where(
                'module_installations.workspace_id',
                $website->workspace_id
            )
            ->whereNull(
                'module_installations.deleted_at'
            )
            ->where(
                'module_installations.status',
                '!=',
                'removed'
            )
            ->select([
                'module_installations.*',
                'modules.id as module_record_id',
            ])
            ->orderByDesc(
                'module_installations.id'
            )
            ->get();
    }


    /**
     * Licence / deployment state.
     *
     * SaaS websites do not require an off-server licence record.
     * Off-server websites read the existing licence authority.
     */
    protected function licence(Website $website): array
    {
        if ($website->isSaas()) {
            return [
                'required' => false,

                'deployment_type' =>
                    Website::DEPLOYMENT_SAAS,

                'licence' => null,
            ];
        }

        $columns =
            collect(
                DB::select(
                    'SHOW COLUMNS FROM off_server_licenses'
                )
            )
                ->pluck('Field');

        $query =
            DB::table(
                'off_server_licenses'
            );

        if (
            $columns->contains(
                'website_id'
            )
        ) {
            $query->where(
                'website_id',
                $website->id
            );
        } elseif (
            $columns->contains(
                'website_uuid'
            )
        ) {
            $query->where(
                'website_uuid',
                $website->website_uuid
            );
        } else {
            return [
                'required' => true,

                'deployment_type' =>
                    Website::DEPLOYMENT_OFF_SERVER,

                'licence' => null,

                'resolution' =>
                    'unsupported_schema',
            ];
        }

        if (
            $columns->contains(
                'deleted_at'
            )
        ) {
            $query->whereNull(
                'deleted_at'
            );
        }

        return [
            'required' => true,

            'deployment_type' =>
                Website::DEPLOYMENT_OFF_SERVER,

            'licence' =>
                $query
                    ->orderByDesc('id')
                    ->first(),
        ];
    }
}
