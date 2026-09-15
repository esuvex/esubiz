<?php

namespace App\Services\Core;

use App\Models\EmailServiceSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ESUBIZ_EMAIL_SERVICE_SETTING_SERVICE_V1
 *
 * Read-only runtime boundary for Central-controlled email service
 * presentation and existing Email Credit products.
 *
 * Both SaaS and off-server Core consume this same Central truth.
 */
class EmailServiceSettingService
{
    public function services(): Collection
    {
        return EmailServiceSetting::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function service(
        string $key
    ): ?EmailServiceSetting {
        return EmailServiceSetting::query()
            ->where('service_key', $key)
            ->where('is_enabled', true)
            ->first();
    }

    /**
     * Existing customer-facing Email Credit packages.
     *
     * No second package/product architecture is created.
     */
    public function emailCreditPackages(): array
    {
        /*
         * ESUBIZ_CENTRAL_EMAIL_CREDIT_PACKAGES_V4
         *
         * Email Credits are an Esubiz Central-managed Core service.
         *
         * This catalog is authoritative for BOTH:
         * - SaaS Core
         * - off-server Core
         *
         * Core does not define package names, quantities, prices
         * or balances locally.
         *
         * Every active Email Credit package with a published
         * canonical Marketplace credit_package listing appears
         * automatically.
         */
        return DB::table('credit_packages as packages')
            ->join(
                'marketplace_listings as listings',
                function ($join) {
                    $join
                        ->on(
                            'listings.product_id',
                            '=',
                            'packages.id'
                        )
                        ->where(
                            'listings.product_type',
                            '=',
                            'credit_package'
                        )
                        ->where(
                            'listings.status',
                            '=',
                            'published'
                        );
                }
            )
            ->where(
                'packages.credit_type',
                'email_credits'
            )
            ->where(
                'packages.is_active',
                true
            )
            ->orderBy('packages.sort_order')
            ->orderBy('packages.credit_quantity')
            ->orderBy('packages.id')
            ->get([
                'packages.id',
                'packages.catalog_product_id',
                'packages.name',
                'packages.credit_quantity',
                'packages.price',
                'packages.currency',
                'packages.description',
                'packages.expiry_days',
                'listings.id as marketplace_listing_id',
            ])
            ->map(
                static fn ($package) => [
                    /*
                     * Stable Central identities.
                     */
                    'id' =>
                        (int) $package->id,

                    'marketplace_listing_id' =>
                        (int) $package
                            ->marketplace_listing_id,

                    'catalog_product_id' =>
                        $package->catalog_product_id
                            !== null
                                ? (int) $package
                                    ->catalog_product_id
                                : null,

                    /*
                     * Central Admin-controlled presentation.
                     */
                    'name' =>
                        (string) $package->name,

                    'credits' =>
                        (float) $package
                            ->credit_quantity,

                    'price' =>
                        (float) $package->price,

                    'currency' =>
                        (string) (
                            $package->currency
                            ?: 'NGN'
                        ),

                    'description' =>
                        $package->description !== null
                            ? (string) $package
                                ->description
                            : null,

                    'expiry_days' =>
                        $package->expiry_days !== null
                            ? (int) $package
                                ->expiry_days
                            : null,

                    /*
                     * Generic Marketplace purchase identity.
                     *
                     * The consuming deployment decides HOW to
                     * request its Central checkout handoff:
                     *
                     * SaaS:
                     *   SaasCheckoutLinkService
                     *
                     * off-server:
                     *   Central API ->
                     *   OffServerCheckoutLinkService
                     *
                     * The package itself is deployment-neutral.
                     */
                    'product_type' =>
                        'credit_package',

                    'product_id' =>
                        (int) $package->id,
                ]
            )
            ->values()
            ->all();
    }

    /**
     * ESUBIZ_PREMIUM_EMAIL_DASHBOARD_DATA_V2
     *
     * Central-owned customer-facing data for the Core Email sender
     * selector.
     *
     * The Website is authoritative for both SaaS and off-server Core.
     * Core receives presentation/balance/package/log data but never
     * becomes the owner of Email Credits.
     */
    public function dashboardData(
        int $websiteId,
        int $page = 1,
        int $perPage = 10
    ): array {
        $page = max(1, $page);

        /*
         * Core Email transaction log is fixed at 10 rows/page.
         * Keep the argument bounded for safe reuse while preserving
         * the Email UI contract.
         */
        $perPage = max(
            1,
            min(10, $perPage)
        );

        $services = $this->services()
            ->keyBy('service_key');

        $default = $services->get('default');
        $premium = $services->get('premium');

        $credit = DB::table(
            'central_website_service_credits'
        )
            ->where(
                'website_id',
                $websiteId
            )
            ->where(
                'service',
                'email'
            )
            ->first();

        $balance = (float) (
            $credit->balance
            ?? 0
        );

        /*
         * ESUBIZ_EMAIL_PACKAGE_DASHBOARD_CONTRACT_V5
         *
         * emailCreditPackages() is already the normalized,
         * deployment-neutral Central package contract.
         *
         * Do not remap it here or convert package arrays back
         * into an object-shaped contract.
         */
        $packages =
            $this->emailCreditPackages();

        $query = DB::table(
            'central_website_service_credit_transactions'
        )
            ->where(
                'website_id',
                $websiteId
            )
            ->where(
                'service',
                'email'
            )
            ->orderByDesc('id');

        $rows = $query
            ->offset(
                ($page - 1) * $perPage
            )
            ->limit($perPage + 1)
            ->get();

        $hasNext =
            $rows->count() > $perPage;

        $rows = $rows
            ->take($perPage)
            ->values();

        return [
            'selected_transport_default' =>
                CoreMailGateway::TRANSPORT_PHP,

            'services' => [
                'default' =>
                    $this->serializeService(
                        $default
                    ),

                'premium' =>
                    $this->serializeService(
                        $premium
                    ),
            ],

            'premium' => [
                'balance' =>
                    $balance,

                'packages' =>
                    $packages,

                'transactions' =>
                    $rows->map(
                        fn ($row) => [
                            'id' =>
                                (int) $row->id,

                            'direction' =>
                                (string) $row->direction,

                            'amount' =>
                                (float) $row->amount,

                            'balance_before' =>
                                (float) $row->balance_before,

                            'balance_after' =>
                                (float) $row->balance_after,

                            'source_type' =>
                                $row->source_type,

                            'source_id' =>
                                $row->source_id,

                            'created_at' =>
                                (string) $row->created_at,
                        ]
                    )
                    ->all(),

                'pagination' => [
                    'page' =>
                        $page,

                    'per_page' =>
                        $perPage,

                    'has_previous' =>
                        $page > 1,

                    'has_next' =>
                        $hasNext,
                ],
            ],
        ];
    }

    protected function serializeService(
        ?EmailServiceSetting $service
    ): ?array {
        if (!$service) {
            return null;
        }

        return [
            'key' =>
                (string) $service->service_key,

            'name' =>
                (string) $service->name,

            'short_label' =>
                (string) $service->short_label,

            'description' =>
                $service->description,

            'info_content' =>
                $service->info_content,

            'daily_limit' =>
                $service->daily_limit,

            'period_label' =>
                $service->period_label,

            'enabled' =>
                (bool) $service->is_enabled,
        ];
    }

    public function defaultTransport(): string
    {
        return CoreMailGateway::TRANSPORT_PHP;
    }

    public function normalizeTransport(
        ?string $transport
    ): string {
        return $transport ===
            CoreMailGateway::TRANSPORT_PREMIUM_SMTP
                ? CoreMailGateway::TRANSPORT_PREMIUM_SMTP
                : CoreMailGateway::TRANSPORT_PHP;
    }
}
