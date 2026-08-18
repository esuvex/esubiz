<?php

namespace App\Services\Core;

use InvalidArgumentException;

class EsubizPlatformSaleService
{
    public function __construct(
        protected CoreTransactionService $transactionService
    ) {
    }

    /**
     * Record an Esubiz platform sale.
     *
     * Every platform sale must identify the actual item sold.
     * No product identity is inferred from reference IDs.
     */
    public function record(
        ?int $workspaceId,
        string $sourceType,
        string $itemType,
        int $itemId,
        string $itemName,
        float $amount,
        string $currency = 'NGN',
        ?int $userId = null,
        ?int $websiteId = null,
        string $transactionType = 'purchase',
        array $data = []
    ): void {
        $platformSources = [
            'addons',
            'themes',
            'modules',
            'subscriptions',
            'hybrid',
            'ai_credits',
            'sms_credits',
            'email_credits',
            'whatsapp_credits',
            'marketplace',
            'esubiz_commission',
        ];

        if (!in_array($sourceType, $platformSources, true)) {
            throw new InvalidArgumentException(
                "Unsupported Esubiz platform income source: {$sourceType}"
            );
        }


        if ($itemId <= 0) {
            throw new InvalidArgumentException(
                'A valid platform item_id is required.'
            );
        }

        if (trim($itemName) === '') {
            throw new InvalidArgumentException(
                'A platform item_name is required.'
            );
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException(
                'Platform sale amount must be greater than zero.'
            );
        }

        $this->transactionService->record(
            $workspaceId,
            $sourceType,
            $itemId,
            $transactionType,
            $amount,
            strtoupper(trim($currency)),
            array_merge(
                $data,
                [
                    'user_id' => $userId,
                    'customer_id' => $userId,
                    'website_id' => $websiteId,

                    // Explicit product origin.
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'item_name' => $itemName,

                    // This is platform income.
                    'revenue_owner' => 'esubiz',
                    'is_platform_revenue' => true,
                    'is_esubiz_commissionable' => true,
                    'commissionable' => true,
                ]
            )
        );

        /*
         * Every successful Esubiz platform product sale creates an
         * entitlement for the purchasing user/website.
         *
         * Credit balances are deliberately NOT changed here because
         * this method does not define the quantity purchased. The
         * entitlement proves ownership; the existing credit allocation
         * flow must supply the actual credit quantity.
         */
        if ($transactionType === 'purchase' && $userId !== null) {
            \Illuminate\Support\Facades\DB::table('product_entitlements')->insert([
                'user_id' => $userId,
                'website_id' => $websiteId,
                'workspace_id' => $workspaceId,
                'product_type' => $sourceType,
                'product_id' => $itemId,
                'product_name' => $itemName,
                'status' => 'active',
                'fulfilment_type' => 'purchase',
                'starts_at' => now(),
                'metadata' => json_encode([
                    'item_type' => $itemType,
                    'source_module' => $sourceType,
                    'currency' => strtoupper(trim($currency)),
                    'amount' => $amount,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
