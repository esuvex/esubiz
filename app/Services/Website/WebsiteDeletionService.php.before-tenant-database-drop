<?php

namespace App\Services\Website;

use App\Models\Website;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WebsiteDeletionService
{
    public function __construct(
        protected WebsiteDatabaseCleanupService $databaseCleanup
    ) {
    }

    public function delete(Website $website): void
    {
        /*
         * SaaS tenant database must be removed first.
         *
         * If this fails, abort before Central identity is deleted.
         */
        if (
            $website->isSaas()
            && $website->databaseConnection
        ) {
            $this->databaseCleanup->cleanup(
                $website
            );
        }

        DB::transaction(function () use ($website) {

            $websiteId =
                (int) $website->id;

            /*
             * Off-server identity chain.
             */
            DB::table(
                'off_server_installation_tokens'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            DB::table(
                'off_server_installations'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            DB::table(
                'off_server_license_registrations'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            /*
             * Central service-credit authority.
             */
            DB::table(
                'central_website_service_credit_transactions'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            DB::table(
                'central_website_service_credits'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            /*
             * Product / entitlement state.
             */
            DB::table(
                'product_entitlements'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            DB::table(
                'core_addon_admin_grants'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            /*
             * Operational usage/state.
             */
            DB::table(
                'ai_usage_logs'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            DB::table(
                'ai_credit_transactions'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            DB::table(
                'credit_transactions'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            DB::table(
                'marketplace_product_stagings'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            /*
             * Checkout sessions are operational retry state.
             *
             * Completed order/payment/financial history remains.
             */
            DB::table(
                'marketplace_checkout_sessions'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            /*
             * Domain + database connection rows.
             */
            DB::table(
                'domains'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            DB::table(
                'website_database_connections'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->delete();

            /*
             * API applications are detached/deleted explicitly.
             */
            $apiApplications =
                DB::table(
                    'api_applications'
                )
                    ->where(
                        'website_id',
                        $websiteId
                    )
                    ->pluck('id');

            if ($apiApplications->isNotEmpty()) {

                DB::table(
                    'api_authorizations'
                )
                    ->whereIn(
                        'api_application_id',
                        $apiApplications
                    )
                    ->delete();

                DB::table(
                    'api_applications'
                )
                    ->whereIn(
                        'id',
                        $apiApplications
                    )
                    ->delete();
            }

            /*
             * Preserve immutable financial/accounting history:
             *
             * - revenue_events
             * - expense_events
             * - esubiz_financial_entries
             *
             * Detach the live website relation instead of deleting
             * the historical transaction.
             */
            foreach (
                [
                    'revenue_events',
                    'expense_events',
                    'esubiz_financial_entries',
                ]
                as $table
            ) {
                if (
                    \Illuminate\Support\Facades\Schema::hasTable(
                        $table
                    )
                ) {
                    DB::table($table)
                        ->where(
                            'website_id',
                            $websiteId
                        )
                        ->update([
                            'website_id' =>
                                null,
                        ]);
                }
            }

            /*
             * Finally remove canonical Central registry identity.
             */
            $website->delete();
        });
    }
}
