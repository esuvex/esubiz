<?php

namespace App\Services\SiteAi\Support;

use App\Models\Ai\AiPersona;
use App\Models\Website;

/**
 * Resolve the official Esubiz AI persona selected by the
 * current workspace.
 *
 * IMPORTANT:
 *
 * This service is globally available across Esubiz Central,
 * Developer and Tenant layouts.
 *
 * It therefore MUST NOT require a tenant database connection
 * simply to be instantiated.
 */
class SiteAiPersona
{
    public function current(): array
    {
        $selectedId =
            $this->tenantSelectedPersonaId();


        $persona = null;


        if ($selectedId) {

            $persona =
                AiPersona::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->find(
                        $selectedId
                    );
        }


        if (!$persona) {

            $persona =
                AiPersona::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderByDesc(
                        'is_default'
                    )
                    ->orderBy(
                        'sort_order'
                    )
                    ->orderBy(
                        'id'
                    )
                    ->first();
        }


        if (!$persona) {

            return [
                'id' =>
                    null,

                'name' =>
                    'Esubiz AI',

                'avatar_url' =>
                    null,

                'type' =>
                    'esubiz',
            ];
        }


        return [
            'id' =>
                $persona->id,

            'name' =>
                $persona->name,

            'avatar_url' =>
                $persona->avatarUrl(),

            'type' =>
                'esubiz',
        ];
    }


    protected function tenantSelectedPersonaId(): ?int
    {
        /*
         * Central Admin / Developer Central / other non-tenant
         * pages never attempt a tenant database connection.
         */
        $subdomain =
            request()->route(
                'subdomain'
            );


        if (
            !$subdomain
            || !is_string(
                $subdomain
            )
        ) {
            return null;
        }


        try {

            $website =
                Website::query()
                    ->where(
                        'subdomain',
                        $subdomain
                    )
                    ->first();


            if (!$website) {
                return null;
            }


            /*
             * Resolve the REAL tenant DB service lazily.
             *
             * This class is the same service already used by
             * TenantThemeController:
             *
             * App\Services\Website\WebsiteTenantDatabaseService
             */
            $tenantDatabaseService =
                app(
                    \App\Services\Website\WebsiteTenantDatabaseService::class
                );


            $tenantDatabaseService
                ->connect(
                    $website
                );


            try {

                $value =
                    $tenantDatabaseService
                        ->connection()
                        ->table(
                            'site_settings'
                        )
                        ->where(
                            'key',
                            'ai.persona_id'
                        )
                        ->value(
                            'value'
                        );


                return is_numeric(
                    $value
                )
                    ? (int) $value
                    : null;


            } finally {

                $tenantDatabaseService
                    ->disconnect();
            }


        } catch (\Throwable $e) {

            /*
             * AI persona selection is optional UI state.
             * It must NEVER bring down Central or Tenant pages.
             */
            report(
                $e
            );

            return null;
        }
    }
}
