<?php

namespace App\Services\Core;

use App\Models\Website;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class AdminProductGrantService
{
    /*
     * ESUBIZ_GENERIC_ADMIN_PRODUCT_GRANT_V1
     *
     * Single Central authority for products/resources granted directly
     * to a website by an Esubiz administrator.
     *
     * Admin grants are operational/financially traceable but are NOT
     * represented as customer payments or platform cash revenue.
     */
    protected array $serviceCredits = [
        'ai',
        'sms',
        'email',
        'whatsapp',
    ];

    /**
     * Return authoritative Central service-credit balances.
     */
    public function serviceCreditBalances(
        int $websiteId
    ): array {
        $rows = DB::table('central_website_service_credits')
            ->where('website_id', $websiteId)
            ->whereIn('service', $this->serviceCredits)
            ->get()
            ->keyBy('service');

        $result = [];

        foreach ($this->serviceCredits as $service) {
            $result[$service] = (int) (
                $rows->get($service)->balance
                ?? 0
            );
        }

        return $result;
    }

    /**
     * Set an Admin-controlled service-credit balance.
     *
     * Positive movement is an administrative credit grant.
     * Negative movement is an administrative balance adjustment.
     * Neither movement pretends that a payment occurred.
     */
    public function setServiceCreditBalance(
        Website $website,
        string $service,
        int $targetBalance,
        int $adminId
    ): void {
        $service = strtolower(trim($service));

        if (!in_array($service, $this->serviceCredits, true)) {
            throw new InvalidArgumentException(
                "Unsupported Central service credit [{$service}]."
            );
        }

        if ($targetBalance < 0) {
            throw new InvalidArgumentException(
                'Service-credit balance cannot be negative.'
            );
        }

        DB::transaction(function () use (
            $website,
            $service,
            $targetBalance,
            $adminId
        ) {
            $row = DB::table('central_website_service_credits')
                ->where('website_id', $website->id)
                ->where('service', $service)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('central_website_service_credits')
                    ->insert([
                        'website_id' => $website->id,
                        'service' => $service,
                        'balance' => 0,
                        'lifetime_credited' => 0,
                        'lifetime_consumed' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                $row = DB::table('central_website_service_credits')
                    ->where('website_id', $website->id)
                    ->where('service', $service)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$row) {
                throw new RuntimeException(
                    'Central service-credit account could not be created.'
                );
            }

            $before = (int) $row->balance;
            $delta = $targetBalance - $before;

            if ($delta === 0) {
                return;
            }

            $direction = $delta > 0
                ? 'credit'
                : 'debit';

            $amount = abs($delta);

            $updates = [
                'balance' => $targetBalance,
                'updated_at' => now(),
            ];

            /*
             * Administrative reductions are adjustments, not service
             * consumption, so lifetime_consumed is not increased.
             */
            if ($delta > 0) {
                $updates['lifetime_credited'] =
                    (int) $row->lifetime_credited + $amount;
            }

            DB::table('central_website_service_credits')
                ->where('id', $row->id)
                ->update($updates);

            $requestKey = implode(':', [
                'admin',
                'service-credit',
                $website->id,
                $service,
                $adminId,
                now()->format('YmdHisv'),
            ]);

            DB::table(
                'central_website_service_credit_transactions'
            )->insert([
                'website_id' => $website->id,
                'service' => $service,
                'direction' => $direction,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $targetBalance,
                'request_key' => $requestKey,
                'source_type' => 'admin',
                'source_id' => $adminId,
                'user_id' => $this->primaryBeneficiaryId($website),
                'installation_id' => null,
                'metadata' => json_encode([
                    'grant_source' => 'admin',
                    'admin_user_id' => $adminId,
                    'operation' =>
                        $delta > 0
                            ? 'grant'
                            : 'adjustment',
                    'website_id' => $website->id,
                    'service' => $service,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->recordFinancialEntries(
                $website,
                $adminId,
                'service_credit',
                $service,
                strtoupper($service) . ' Credits',
                0.00,
                'NGN',
                sprintf(
                    'Admin %s of %s %s credits; balance %s → %s.',
                    $delta > 0 ? 'grant' : 'adjustment',
                    number_format($amount),
                    strtoupper($service),
                    number_format($before),
                    number_format($targetBalance)
                )
            );
        });
    }

    /*
     * ESUBIZ_ADMIN_CREDIT_PACKAGE_GRANT_V1
     *
     * Credit products are granted only through real active packages.
     * Quantity and monetary value therefore always originate from the
     * configured product/package catalogue rather than Admin free text.
     */
    /*
     * ESUBIZ_ADMIN_CREDIT_PACKAGE_GRANT_QUANTITY_V2
     *
     * Admin grants real configured credit packages.
     *
     * $units = number of packages selected by Admin.
     * $packageCreditQuantity = credits contained in one package.
     * $totalCredits = package credits × units.
     * $totalCatalogueValue = package price × units.
     *
     * Complimentary Admin grants retain catalogue value for reporting
     * but never create fake cash revenue.
     */
    public function grantCreditPackage(
        Website $website,
        int $creditPackageId,
        int $adminId,
        int $units = 1
    ): array {
        $units = max(1, $units);

        return DB::transaction(function () use (
            $website,
            $creditPackageId,
            $adminId,
            $units
        ) {
            $package = DB::table('credit_packages')
                ->where('id', $creditPackageId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$package) {
                throw new InvalidArgumentException(
                    'The selected credit package is unavailable.'
                );
            }

            $service = strtolower(
                trim((string) $package->credit_type)
            );

            $service = preg_replace(
                '/_credits?$/',
                '',
                $service
            );

            if (!in_array($service, $this->serviceCredits, true)) {
                throw new InvalidArgumentException(
                    'The selected package is not a supported service-credit product.'
                );
            }

            $packageCreditQuantity =
                (int) $package->credit_quantity;

            if ($packageCreditQuantity < 1) {
                throw new RuntimeException(
                    'The selected credit package has no valid credit quantity.'
                );
            }

            $unitPrice =
                (float) $package->price;

            $totalCredits =
                $packageCreditQuantity * $units;

            $totalCatalogueValue =
                $unitPrice * $units;

            $currency = strtoupper(
                trim(
                    (string) (
                        $package->currency ?: 'NGN'
                    )
                )
            );

            $account = DB::table(
                'central_website_service_credits'
            )
                ->where(
                    'website_id',
                    $website->id
                )
                ->where(
                    'service',
                    $service
                )
                ->lockForUpdate()
                ->first();

            if (!$account) {
                DB::table(
                    'central_website_service_credits'
                )->insert([
                    'website_id' =>
                        $website->id,

                    'service' =>
                        $service,

                    'balance' =>
                        0,

                    'lifetime_credited' =>
                        0,

                    'lifetime_consumed' =>
                        0,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

                $account = DB::table(
                    'central_website_service_credits'
                )
                    ->where(
                        'website_id',
                        $website->id
                    )
                    ->where(
                        'service',
                        $service
                    )
                    ->lockForUpdate()
                    ->first();
            }

            if (!$account) {
                throw new RuntimeException(
                    'Unable to create the Central service-credit account.'
                );
            }

            $before =
                (int) $account->balance;

            $after =
                $before + $totalCredits;

            DB::table(
                'central_website_service_credits'
            )
                ->where(
                    'id',
                    $account->id
                )
                ->update([
                    'balance' =>
                        $after,

                    'lifetime_credited' =>
                        (int) $account->lifetime_credited
                        + $totalCredits,

                    'updated_at' =>
                        now(),
                ]);

            $reference = implode('-', [
                'ADMIN',
                strtoupper($service),
                $website->id,
                $creditPackageId,
                $units,
                now()->format('YmdHisv'),
            ]);

            /*
             * Authoritative per-website service-credit ledger.
             */
            DB::table(
                'central_website_service_credit_transactions'
            )->insert([
                'website_id' =>
                    $website->id,

                'service' =>
                    $service,

                'direction' =>
                    'credit',

                'amount' =>
                    $totalCredits,

                'balance_before' =>
                    $before,

                'balance_after' =>
                    $after,

                'request_key' =>
                    $reference,

                'source_type' =>
                    'admin',

                'source_id' =>
                    $adminId,

                'user_id' =>
                    $this->primaryBeneficiaryId(
                        $website
                    ),

                'installation_id' =>
                    null,

                'metadata' => json_encode([
                    'grant_source' =>
                        'admin',

                    'admin_user_id' =>
                        $adminId,

                    'credit_package_id' =>
                        $package->id,

                    'catalog_product_id' =>
                        $package->catalog_product_id,

                    'package_units' =>
                        $units,

                    'credits_per_package' =>
                        $packageCreditQuantity,

                    'credit_quantity' =>
                        $totalCredits,

                    'unit_price' =>
                        $unitPrice,

                    'catalogue_value' =>
                        $totalCatalogueValue,

                    'currency' =>
                        $currency,
                ]),

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);

            /*
             * Generic credit ledger stays synchronized.
             */
            if (
                DB::getSchemaBuilder()->hasTable(
                    'credit_transactions'
                )
            ) {
                DB::table(
                    'credit_transactions'
                )->insert([
                    'website_id' =>
                        $website->id,

                    'user_id' =>
                        $this->primaryBeneficiaryId(
                            $website
                        ),

                    'workspace_id' =>
                        $this->workspaceId(
                            $website
                        ),

                    'credit_type' =>
                        $service,

                    'uuid' =>
                        (string) \Illuminate\Support\Str::uuid(),

                    'request_key' =>
                        $reference,

                    'reference' =>
                        $reference,

                    'direction' =>
                        'credit',

                    'type' =>
                        'admin_grant',

                    'credits' =>
                        $totalCredits,

                    'balance_before' =>
                        $before,

                    'balance_after' =>
                        $after,

                    'source_type' =>
                        'admin',

                    'source_id' =>
                        $adminId,

                    'status' =>
                        'completed',

                    'description' =>
                        "Admin granted {$units} × {$package->name}.",

                    'metadata' => json_encode([
                        'credit_package_id' =>
                            $package->id,

                        'catalog_product_id' =>
                            $package->catalog_product_id,

                        'package_units' =>
                            $units,

                        'credits_per_package' =>
                            $packageCreditQuantity,

                        'total_credits' =>
                            $totalCredits,

                        'unit_price' =>
                            $unitPrice,

                        'catalogue_value' =>
                            $totalCatalogueValue,

                        'currency' =>
                            $currency,

                        'admin_user_id' =>
                            $adminId,
                    ]),

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);
            }

            /*
             * Authoritative Esubiz financial ledger.
             *
             * Catalogue value is retained for reporting.
             * This remains a complimentary Admin acquisition,
             * not a cash payment.
             */
            $this->recordFinancialEntries(
                $website,
                $adminId,
                $service . '_credits',
                (string) $package->id,
                (string) $package->name,
                $totalCatalogueValue,
                $currency,
                sprintf(
                    'Admin granted %s × %s (%s total credits; catalogue value %s %.2f).',
                    number_format($units),
                    $package->name,
                    number_format($totalCredits),
                    $currency,
                    $totalCatalogueValue
                )
            );

            /*
             * Central reporting value event.
             *
             * gross = real catalogue value
             * discount = full complimentary value
             * net = zero
             *
             * Therefore this never appears as paid revenue.
             */
            $ownerId =
                $this->primaryBeneficiaryId(
                    $website
                );

            $developerId =
                $website->developer?->id;

            DB::table(
                'revenue_events'
            )->insert([
                'workspace_id' =>
                    $this->workspaceId(
                        $website
                    ),

                'website_id' =>
                    $website->id,

                'uuid' =>
                    (string) \Illuminate\Support\Str::uuid(),

                'source_module' =>
                    'admin',

                'event_type' =>
                    'admin_product_grant',

                'reference_type' =>
                    'credit_package',

                'item_type' =>
                    $service . '_credits',

                'item_id' =>
                    $package->id,

                'item_name' =>
                    $package->name,

                'reference_id' =>
                    $package->id,

                'user_id' =>
                    $ownerId,

                'financial_account_user_id' =>
                    $ownerId,

                'financial_account_developer_id' =>
                    $developerId ?: null,

                'financial_account_type' =>
                    'admin_grant',

                'gross_amount' =>
                    $totalCatalogueValue,

                'discount_amount' =>
                    $totalCatalogueValue,

                'tax_amount' =>
                    0,

                'net_amount' =>
                    0,

                'currency' =>
                    $currency,

                'revenue_owner' =>
                    'esubiz',

                'is_platform_revenue' =>
                    false,

                'is_esubiz_commissionable' =>
                    false,

                'hosting_scope' =>
                    !empty($website->subdomain)
                        ? 'saas'
                        : 'off_server',

                'commission_plan' =>
                    null,

                'is_commissionable' =>
                    false,

                'is_processed' =>
                    true,

                'status' =>
                    'processed',

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);

            return [
                'service' =>
                    $service,

                'package_id' =>
                    (int) $package->id,

                'catalog_product_id' =>
                    $package->catalog_product_id
                        ? (int) $package->catalog_product_id
                        : null,

                'package_name' =>
                    $package->name,

                /*
                 * Keep quantity as total credited amount for
                 * compatibility with existing callers.
                 */
                'quantity' =>
                    $totalCredits,

                'units' =>
                    $units,

                'credits_per_package' =>
                    $packageCreditQuantity,

                'total_credits' =>
                    $totalCredits,

                'unit_price' =>
                    $unitPrice,

                'price' =>
                    $totalCatalogueValue,

                'catalogue_value' =>
                    $totalCatalogueValue,

                'currency' =>
                    $currency,

                'balance_before' =>
                    $before,

                'balance_after' =>
                    $after,
            ];
        });
    }

    /**
     * Generic non-credit product entitlement grant.
     *
     * Future Admin UI/API integrations can call this method for themes,
     * modules, add-ons, licenses, subscriptions and other products.
     *
     * Product-specific runtime provisioning may be performed before or
     * after this method by its registered fulfilment handler while this
     * method remains the common entitlement + financial audit authority.
     */
    /*
     * ESUBIZ_ADMIN_CORE_PRODUCT_FULFILMENT_V1
     *
     * Complimentary Admin grants for Core Add-ons and Bundles use
     * the exact authoritative Core Marketplace fulfilment service.
     */
    public function grantCoreProduct(
        Website $website,
        int $productId,
        string $productType,
        int $adminId
    ): array {
        $productType = match (
            strtolower(trim($productType))
        ) {
            'addon',
            'core-addon',
            'core_addon'
                => 'core_addon',

            'bundle',
            'core-bundle',
            'core_bundle'
                => 'core_bundle',

            default =>
                throw new \RuntimeException(
                    'Unsupported Core product grant type.'
                ),
        };

        $deploymentType =
            !empty($website->subdomain)
                ? 'saas'
                : 'off_server';

        $userId =
            (int) (
                $website->owner_id
                ?? $website->user_id
                ?? $website->developer_id
                ?? 0
            );

        if ($userId <= 0) {
            throw new \RuntimeException(
                'Website owner is required for product fulfilment.'
            );
        }

        $workspaceId =
            isset($website->workspace_id)
            && $website->workspace_id
                ? (int) $website->workspace_id
                : null;

        $result = app(
            \App\Services\Core\CoreAddonMarketplaceFulfilmentService::class
        )->fulfil(
            $userId,
            $productId,
            $productType,
            $deploymentType,
            (int) $website->id,
            $workspaceId,
            null,
            'ADMIN-GRANT-'
                . $adminId
                . '-'
                . $website->id
                . '-'
                . now()->format('YmdHis')
        );

        $table =
            $productType === 'core_addon'
                ? 'core_addons'
                : 'core_addon_bundles';

        $product =
            DB::table($table)
                ->where('id', $productId)
                ->first();

        if (!$product) {
            throw new \RuntimeException(
                'Granted Core product no longer exists.'
            );
        }

        $catalogueValue =
            $deploymentType === 'saas'
                ? (float) (
                    $product->saas_price
                    ?? 0
                )
                : (float) (
                    $product->off_server_price
                    ?? 0
                );

        $currency =
            strtoupper(
                (string) (
                    $deploymentType === 'saas'
                        ? (
                            $product->saas_currency
                            ?? 'NGN'
                        )
                        : (
                            $product->off_server_currency
                            ?? 'NGN'
                        )
                )
            );

        /*
         * ESUBIZ_ADMIN_CORE_PRODUCT_FINANCIAL_CALL_V2
         *
         * Complimentary Admin grant:
         * - retains real catalogue value
         * - records Admin as acquisition source
         * - does not fabricate paid revenue
         */
        $this->recordFinancialEntries(
            $website,
            $adminId,
            $productType,
            (string) $productId,
            (string) (
                $product->name
                ?? $productType
            ),
            $catalogueValue,
            $currency,
            sprintf(
                'Admin granted %s; catalogue value %s %.2f.',
                (string) (
                    $product->name
                    ?? $productType
                ),
                $currency,
                $catalogueValue
            )
        );

        return [
            'product_type' =>
                $productType,

            'product_id' =>
                $productId,

            'product_name' =>
                (string) (
                    $product->name
                    ?? $productType
                ),

            'deployment_type' =>
                $deploymentType,

            'catalogue_value' =>
                $catalogueValue,

            'currency' =>
                $currency,

            'fulfilment' =>
                $result,
        ];
    }

    public function grantEntitlement(
        Website $website,
        int $adminId,
        string $productType,
        int $productId,
        string $productName,
        string $fulfilmentType = 'grant',
        float $productValue = 0.00,
        string $currency = 'NGN',
        array $metadata = [],
        mixed $startsAt = null,
        mixed $expiresAt = null
    ): int {
        $productType = trim($productType);
        $productName = trim($productName);

        if ($productType === '') {
            throw new InvalidArgumentException(
                'Product type is required.'
            );
        }

        if ($productId <= 0) {
            throw new InvalidArgumentException(
                'A valid product ID is required.'
            );
        }

        if ($productName === '') {
            throw new InvalidArgumentException(
                'Product name is required.'
            );
        }

        return DB::transaction(function () use (
            $website,
            $adminId,
            $productType,
            $productId,
            $productName,
            $fulfilmentType,
            $productValue,
            $currency,
            $metadata,
            $startsAt,
            $expiresAt
        ) {
            $userId = $this->primaryBeneficiaryId($website);
            $workspaceId = $this->workspaceId($website);

            $existing = DB::table('product_entitlements')
                ->where('website_id', $website->id)
                ->where('product_type', $productType)
                ->where('product_id', $productId)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                return (int) $existing->id;
            }

            $entitlementId = DB::table('product_entitlements')
                ->insertGetId([
                    'user_id' => $userId,
                    'website_id' => $website->id,
                    'workspace_id' => $workspaceId,
                    'product_type' => $productType,
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'order_id' => null,
                    'order_reference' => null,
                    'status' => 'active',
                    'fulfilment_type' => $fulfilmentType,
                    'starts_at' => $startsAt ?: now(),
                    'expires_at' => $expiresAt,
                    'metadata' => json_encode(array_merge(
                        $metadata,
                        [
                            'grant_source' => 'admin',
                            'admin_user_id' => $adminId,
                            'granted_at' => now()->toIso8601String(),
                        ]
                    )),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->recordFinancialEntries(
                $website,
                $adminId,
                $productType,
                (string) $productId,
                $productName,
                max(0, $productValue),
                strtoupper($currency),
                "Admin granted {$productName} to website #{$website->id}."
            );

            return $entitlementId;
        });
    }

    /**
     * Create matching Central and beneficiary financial records.
     *
     * source=admin distinguishes complimentary/admin grants from sales.
     * No payment transaction or revenue_event is fabricated.
     */
    /*
     * ESUBIZ_ADMIN_GRANT_ZERO_VALUE_FINANCIAL_LEDGER_V1
     *
     * Complimentary Admin fulfilment is not a cash sale.
     *
     * Platform:
     *   credit/revenue = 0
     *
     * Beneficiary:
     *   debit/expense = 0
     *
     * Catalogue value is retained as gross value with an equal
     * complimentary discount on the platform revenue event.
     */
    protected function recordFinancialEntries(
        Website $website,
        int $adminId,
        string $productType,
        string $productReference,
        string $productName,
        float $amount,
        string $currency,
        string $description
    ): void {
        $workspaceId = $this->workspaceId($website);
        $occurredAt = now();
        $currency = strtoupper($currency);

        /*
         * Keep the generic audit ledger, but Admin grants always
         * carry zero monetary value.
         */
        if (
            DB::getSchemaBuilder()->hasTable(
                'esubiz_financial_entries'
            )
        ) {
            $common = [
                'website_id' => $website->id,
                'workspace_id' => $workspaceId,
                'entry_type' => 'admin_grant',
                'source' => $productType,
                'description' =>
                    $description
                    . " Product: {$productType}:{$productReference}."
                    . " Admin user #{$adminId}.",
                'reference_type' => 'admin_user',
                'reference_id' => $adminId,
                'amount' => 0.0,
                'currency' => $currency,
                'status' => 'processed',
                'occurred_at' => $occurredAt,
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ];

            DB::table('esubiz_financial_entries')
                ->insert(array_merge(
                    $common,
                    [
                        'user_id' => null,
                        'is_platform_entry' => true,
                    ]
                ));

            foreach (
                $this->beneficiaryIds($website)
                as $userId
            ) {
                DB::table('esubiz_financial_entries')
                    ->insert(array_merge(
                        $common,
                        [
                            'user_id' => $userId,
                            'is_platform_entry' => false,
                        ]
                    ));
            }
        }

        /*
         * Authoritative Admin / Esubiz revenue ledger.
         *
         * Gross catalogue value is retained for reporting,
         * but it is completely discounted because Admin grants
         * are complimentary.
         */
        if (
            DB::getSchemaBuilder()->hasTable(
                'revenue_events'
            )
        ) {
            DB::table('revenue_events')->insert([
                'workspace_id' => $workspaceId,
                'website_id' => $website->id,
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'source_module' => $productType,
                'event_type' => 'admin_grant',
                'reference_type' => 'admin_user',
                'item_type' => $productType,
                'item_id' => $productReference,
                'item_name' => $productName,
                'reference_id' => $adminId,
                'user_id' => null,
                'financial_account_user_id' => null,
                'financial_account_developer_id' => null,
                'financial_account_type' => 'platform',
                'gross_amount' => $amount,
                'discount_amount' => $amount,
                'tax_amount' => 0.0,
                'net_amount' => 0.0,
                'currency' => $currency,
                'revenue_owner' => 'platform',
                'is_platform_revenue' => true,
                'is_esubiz_commissionable' => false,
                'is_commissionable' => false,
                'is_processed' => true,
                'status' => 'processed',
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ]);
        }

        /*
         * ESUBIZ_ADMIN_GRANT_BENEFICIARY_REVENUE_V1
         *
         * Keep Admin-granted non-credit products aligned with the
         * existing working Credit grant financial-account pattern.
         *
         * User/Developer dashboards currently read revenue_events
         * through financial_account_* ownership fields. This zero-net
         * row exposes the complimentary acquisition to those ledgers
         * without creating paid revenue.
         */
        if (
            DB::getSchemaBuilder()->hasTable(
                'revenue_events'
            )
        ) {
            $ownerId =
                $website->owner?->id
                ?: $this->primaryBeneficiaryId($website);

            $developerId =
                $website->developer?->id;

            DB::table('revenue_events')->insert([
                'workspace_id' => $workspaceId,
                'website_id' => $website->id,

                'uuid' =>
                    (string) \Illuminate\Support\Str::uuid(),

                'source_module' => 'admin',

                'event_type' =>
                    'admin_product_grant',

                'reference_type' =>
                    $productType,

                'item_type' =>
                    $productType,

                'item_id' =>
                    $productReference,

                'item_name' =>
                    $productName,

                'reference_id' =>
                    $productReference,

                'user_id' =>
                    $ownerId,

                'financial_account_user_id' =>
                    $ownerId,

                'financial_account_developer_id' =>
                    $developerId ?: null,

                'financial_account_type' =>
                    'admin_grant',

                'gross_amount' =>
                    $amount,

                'discount_amount' =>
                    $amount,

                'tax_amount' =>
                    0,

                'net_amount' =>
                    0,

                'currency' =>
                    $currency,

                'revenue_owner' =>
                    'esubiz',

                /*
                 * This is the beneficiary-facing acquisition record,
                 * not additional platform revenue.
                 */
                'is_platform_revenue' =>
                    false,

                'is_esubiz_commissionable' =>
                    false,

                'hosting_scope' =>
                    !empty($website->subdomain)
                        ? 'saas'
                        : 'off_server',

                'commission_plan' =>
                    null,

                'is_commissionable' =>
                    false,

                'is_processed' =>
                    true,

                'status' =>
                    'processed',

                'created_at' =>
                    $occurredAt,

                'updated_at' =>
                    $occurredAt,
            ]);
        }

        /*
         * Authoritative beneficiary expense ledger.
         */
        if (
            DB::getSchemaBuilder()->hasTable(
                'expense_events'
            )
        ) {
            foreach (
                $this->beneficiaryIds($website)
                as $userId
            ) {
                DB::table('expense_events')->insert([
                    'user_id' => $userId,
                    'website_id' => $website->id,
                    'workspace_id' => $workspaceId,
                    'source' => $productType,
                    'expense_type' => 'admin_grant',
                    'item_type' => $productType,
                    'item_id' => $productReference,
                    'item_name' => $productName,
                    'description' =>
                        $description
                        . " Complimentary Admin grant."
                        . " Catalogue value {$currency} "
                        . number_format($amount, 2, '.', ''),
                    'reference_type' => 'admin_user',
                    'reference_id' => $adminId,
                    'amount' => 0.0,
                    'currency' => $currency,
                    'status' => 'processed',
                    'occurred_at' => $occurredAt,
                    'created_at' => $occurredAt,
                    'updated_at' => $occurredAt,
                ]);
            }
        }
    }

    protected function beneficiaryIds(
        Website $website
    ): array {
        $website->loadMissing([
            'owner',
            'developer',
            'workspace',
        ]);

        $ids = [];

        foreach ([
            $website->owner?->id,
            $website->developer?->id,
        ] as $id) {
            if ($id !== null && (int) $id > 0) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    protected function primaryBeneficiaryId(
        Website $website
    ): ?int {
        return $this->beneficiaryIds($website)[0] ?? null;
    }

    protected function workspaceId(
        Website $website
    ): ?int {
        $website->loadMissing('workspace');

        return $website->workspace?->id
            ? (int) $website->workspace->id
            : null;
    }
}
