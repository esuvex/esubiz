<?php

namespace App\Listeners\Core;

use App\Events\Core\CoreTransactionRecorded;
use App\Listeners\Core\ProcessCoreRevenueEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecordCoreRevenueEvent
{
    public function handle(CoreTransactionRecorded $event): void
    {
        $itemType = $event->data['item_type']
            ?? $event->data['product_type']
            ?? null;

        $itemId = $event->data['item_id']
            ?? $event->data['product_id']
            ?? null;

        $itemName = $event->data['item_name']
            ?? $event->data['product_name']
            ?? $event->data['name']
            ?? $event->data['title']
            ?? null;

        /*
         * Resolve the actual sold platform product when the
         * transaction identifies the source product directly.
         *
         * Confirmed product sources:
         * addons  -> addon_products
         * themes  -> catalog_products(product_type=theme)
         * modules -> catalog_products(product_type=module)
         */
        if (!$itemId && $event->sourceId) {
            if ($event->sourceType === 'addons') {
                $product = DB::table('addon_products')
                    ->where('id', $event->sourceId)
                    ->first();

                if ($product) {
                    $itemType = 'addon';
                    $itemId = $product->id;
                    $itemName = $product->name;
                }
            } elseif (in_array($event->sourceType, ['themes', 'modules'], true)) {
                $expectedType = $event->sourceType === 'themes'
                    ? 'theme'
                    : 'module';

                $product = DB::table('catalog_products')
                    ->where('id', $event->sourceId)
                    ->where('product_type', $expectedType)
                    ->first();

                if ($product) {
                    $itemType = $expectedType;
                    $itemId = $product->id;
                    $itemName = $product->name;
                }
            }
        }

        /*
         * Marketplace payments can call the success endpoint more than once.
         * Do not create duplicate financial revenue events for the same
         * canonical payment transaction.
         */
        $paymentTransactionId = $event->data['payment_transaction_id'] ?? null;

        if ($paymentTransactionId) {
            $duplicate = DB::table('revenue_events')
                ->where('source_module', $event->sourceType)
                ->where('reference_type', 'payment_transaction')
                ->where('reference_id', $paymentTransactionId)
                ->exists();

            if ($duplicate) {
                return;
            }
        }

        /*
         * Referral commissions are double-sided:
         * Esubiz records an expense while the referrer records revenue.
         */
        if (($event->data['financial_direction'] ?? null) === 'expense'
            && ($event->data['recipient_financial_direction'] ?? null) === 'revenue') {

            $expenseData = $event->data;
            $expenseData['financial_account_type'] = 'esubiz';
            $expenseData['financial_account_user_id'] = null;
            $expenseData['financial_account_developer_id'] = null;

            DB::table('expense_events')->insert([
                'source_module' => 'referral_commission',
                'source_type' => $event->sourceType,
                'source_id' => $event->sourceId,
                'transaction_type' => 'expense',
                'reference_type' => $expenseData['reference_type'] ?? null,
                'reference_id' => $expenseData['reference_id'] ?? $event->sourceId,
                'workspace_id' => $expenseData['workspace_id'] ?? null,
                'website_id' => $expenseData['website_id'] ?? null,
                'user_id' => null,
                'financial_account_user_id' => null,
                'financial_account_developer_id' => null,
                'financial_account_type' => 'esubiz',
                'currency' => $event->currency,
                'gross_amount' => $event->amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'net_amount' => $event->amount,
                'metadata' => json_encode([
                    'direction' => 'expense',
                    'owner' => 'esubiz',
                    'referrer_user_id' => $expenseData['recipient_financial_account_user_id'] ?? null,
                    'referrer_developer_id' => $expenseData['recipient_financial_account_developer_id'] ?? null,
                ]),
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $event->data['financial_account_user_id'] =
                $event->data['recipient_financial_account_user_id'] ?? null;

            $event->data['financial_account_developer_id'] =
                $event->data['recipient_financial_account_developer_id'] ?? null;

            $event->data['financial_account_type'] =
                $event->data['financial_account_developer_id']
                    ? 'developer_referral'
                    : 'referral';

            $event->data['financial_direction'] = 'revenue';
        }

        /*
         * Recipient-side revenue for referral commissions.
         * Esubiz's expense and the recipient's revenue are separate
         * financial records but originate from the same transaction.
         */
        if (($event->data['recipient_financial_direction'] ?? null) === 'revenue') {
            DB::table('revenue_events')->insert([
                'source_module' => 'referral_commission',
                'source_type' => $event->sourceType,
                'source_id' => $event->sourceId,
                'transaction_type' => 'revenue',
                'reference_type' => $event->data['reference_type'] ?? null,
                'reference_id' => $event->data['reference_id'] ?? $event->sourceId,
                'workspace_id' => $event->data['workspace_id'] ?? null,
                'website_id' => $event->data['website_id'] ?? null,
                'user_id' => $event->data['recipient_financial_account_user_id'] ?? null,
                'financial_account_user_id' => $event->data['recipient_financial_account_user_id'] ?? null,
                'financial_account_developer_id' => $event->data['recipient_financial_account_developer_id'] ?? null,
                'financial_account_type' => ($event->data['recipient_financial_account_developer_id'] ?? null)
                    ? 'developer_referral'
                    : 'referral',
                'revenue_owner' => 'user',
                'is_platform_revenue' => false,
                'currency' => $event->currency,
                'gross_amount' => $event->amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'net_amount' => $event->amount,
                'metadata' => json_encode([
                    'direction' => 'revenue',
                    'referral_commission' => true,
                    'esubiz_expense' => true,
                ]),
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $id = DB::table('revenue_events')->insertGetId([
            'workspace_id' => $event->data['workspace_id'] ?? null,
            'website_id' => $event->data['website_id'] ?? null,
            'uuid' => (string) Str::uuid(),

            'source_module' => $event->sourceType,
            'event_type' => $event->transactionType,

            'reference_type' => $event->data['reference_type']
                ?? ($paymentTransactionId ? 'payment_transaction' : null),
            'item_type' => $itemType
                ?? $event->data['reference_type']
                ?? null,
            'item_id' => $itemId
                ?? $event->data['reference_id']
                ?? null,
            'item_name' => $itemName,
            'reference_id' => $paymentTransactionId
                ?? $event->sourceId,

            'user_id' => $event->data['customer_id']
                ?? $event->data['user_id']
                ?? $event->data['financial_account_user_id']
                ?? null,

            'financial_account_user_id' => $event->data['financial_account_user_id']
                ?? $event->data['user_id']
                ?? null,

            'financial_account_developer_id' => $event->data['financial_account_developer_id']
                ?? null,

            'financial_account_type' => $event->data['financial_account_type']
                ?? 'user',

            'gross_amount' => $event->amount,
            'discount_amount' => $event->data['discount_amount'] ?? 0,
            'tax_amount' => $event->data['tax_amount'] ?? 0,
            'net_amount' => $event->data['net_amount'] ?? $event->amount,

            'currency' => $event->currency,

            /*
             * Preserve explicit platform ownership supplied by the
             * Esubiz platform-sale service. These values are trusted
             * only when explicitly provided by the caller.
             */
            'revenue_owner' => $event->data['revenue_owner'] ?? 'merchant',
            'is_platform_revenue' => (bool) (
                $event->data['is_platform_revenue'] ?? false
            ),
            'is_esubiz_commissionable' => (bool) (
                $event->data['is_esubiz_commissionable'] ?? false
            ),

            'is_commissionable' => $event->data['commissionable'] ?? true,
            'is_processed' => false,

            'status' => 'pending',

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $revenueEvent = DB::table('revenue_events')
            ->where('id', $id)
            ->first();

        if ($revenueEvent) {
            app(ProcessCoreRevenueEvent::class)
                ->process($revenueEvent);
        }
    }
}
