<?php

namespace App\Services\Core;

use App\Services\Core\ReferralRevenueProcessor;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class EsubizCommissionProcessor
{
    public function process(
        int $revenueEventId,
        ?int $workspaceId,
        ?int $userId,
        float $paidAmount,
        string $currency,
        bool $paymentSuccessful,
        string $hostingScope,
        string $commissionPlan,
        bool $commissionPrivilegeGranted = false
    ): array {
        if (!$paymentSuccessful) {
            return [
                'commissionable' => false,
                'reason' => 'payment_not_successful',
            ];
        }

        if ($hostingScope !== 'esubiz_hosted') {
            return [
                'commissionable' => false,
                'reason' => 'website_not_hosted_on_esubiz',
            ];
        }

        if ($commissionPlan !== 'commission') {
            return [
                'commissionable' => false,
                'reason' => 'website_not_on_commission_plan',
            ];
        }

        /*
         * Esubiz Commission is a privileged capability.
         * The caller must provide the result of the platform's
         * actual custom-user-privilege authorization check.
         */
        if (!$commissionPrivilegeGranted) {
            return [
                'commissionable' => false,
                'reason' => 'commission_privilege_not_granted',
            ];
        }

        $existing = DB::table('esubiz_commission_invoices')
            ->where('revenue_event_id', $revenueEventId)
            ->first();

        if ($existing) {
            return [
                'commissionable' => true,
                'duplicate' => true,
                'invoice_id' => $existing->id,
                'commission_amount' => $existing->commission_amount,
                'status' => $existing->status,
            ];
        }

        $settings = DB::table('site_settings')
            ->where(function ($query) use ($workspaceId) {
                $query
                    ->where('workspace_id', $workspaceId)
                    ->orWhereNull('workspace_id');
            })
            ->whereIn('key', [
                'esubiz_commission_type',
                'esubiz_commission_value',
                'esubiz_commission_invoice_due_days',
            ])
            ->get()
            ->keyBy('key');

        $type = $settings->get('esubiz_commission_type')?->value
            ?? 'percentage';

        $value = (float) (
            $settings->get('esubiz_commission_value')?->value
            ?? 0
        );

        $dueDaysSetting =
            $settings->get('esubiz_commission_invoice_due_days');

        if (
            !$dueDaysSetting ||
            !is_numeric($dueDaysSetting->value) ||
            (int) $dueDaysSetting->value < 1
        ) {
            throw new RuntimeException(
                'Esubiz commission invoice deadline must be configured by an administrator.'
            );
        }

        $dueDays = (int) $dueDaysSetting->value;

        if (!in_array($type, ['fixed', 'percentage'], true)) {
            throw new RuntimeException(
                'Invalid Esubiz commission type.'
            );
        }

        if ($value < 0) {
            throw new RuntimeException(
                'Esubiz commission value cannot be negative.'
            );
        }

        if ($type === 'percentage') {
            $commission = round(
                $paidAmount * ($value / 100),
                2
            );
        } else {
            $commission = round($value, 2);
        }

        if ($commission <= 0) {
            return [
                'commissionable' => true,
                'commission_created' => false,
                'reason' => 'commission_value_is_zero',
            ];
        }

        return DB::transaction(function () use (
            $revenueEventId,
            $workspaceId,
            $userId,
            $paidAmount,
            $currency,
            $type,
            $value,
            $commission,
            $dueDays
        ) {
            $reference = 'ESUBIZ-COM-' .
                strtoupper(substr(
                    str_replace('-', '', (string) \Illuminate\Support\Str::uuid()),
                    0,
                    16
                ));

            $now = now();
            $dueAt = $now->copy()->addDays(
                max(1, $dueDays)
            );

            $invoiceId = DB::table(
                'esubiz_commission_invoices'
            )->insertGetId([
                'workspace_id' => $workspaceId,
                'user_id' => $userId,
                'revenue_event_id' => $revenueEventId,
                'invoice_reference' => $reference,
                'base_amount' => $paidAmount,
                'commission_rate' => $value,
                'commission_type' => $type,
                'commission_amount' => $commission,
                'currency' => strtoupper($currency),
                'issued_at' => $now,
                'due_at' => $dueAt,
                'status' => 'unpaid',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            /*
             * The original revenue event belongs to the website
             * merchant and MUST remain merchant revenue.
             *
             * Esubiz's commission is a separate platform revenue
             * event containing only the commission amount.
             */
            $sourceRevenueEvent = DB::table('revenue_events')
                ->where('id', $revenueEventId)
                ->first();

            if (!$sourceRevenueEvent) {
                throw new RuntimeException(
                    'Source revenue event was not found.'
                );
            }

            /*
             * Reuse the source event's already-valid database status.
             * Do not hardcode a revenue_events status value because
             * the application's enum is schema-defined.
             */
            $esubizRevenueEventId = DB::table('revenue_events')
                ->insertGetId([
                    'workspace_id' => $sourceRevenueEvent->workspace_id ?? $workspaceId,
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'source_module' => 'esubiz_commission',
                    'event_type' => 'commission',
                    'reference_type' => 'revenue_event',
                    'reference_id' => $revenueEventId,
                    'user_id' => $sourceRevenueEvent->user_id ?? $userId,
                    'gross_amount' => $commission,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'net_amount' => $commission,
                    'currency' => strtoupper($currency),
                    'is_commissionable' => true,
                    'is_processed' => true,
                    'status' => 'processed',
                    'revenue_owner' => 'esubiz',
                    'is_platform_revenue' => true,
                    'is_esubiz_commissionable' => true,
                    'hosting_scope' => 'esubiz_hosted',
                    'commission_plan' => 'commission',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            /*
             * Esubiz platform income is itself referral-commissionable.
             * Run the existing referral engine against the Esubiz
             * revenue event so eligible users/developers can earn
             * according to the configured referral rules.
             */
            $esubizRevenueEvent = DB::table('revenue_events')
                ->where('id', $esubizRevenueEventId)
                ->first();

            if ($esubizRevenueEvent) {
                app(ReferralRevenueProcessor::class)
                    ->process($esubizRevenueEvent);
            }

            DB::table('esubiz_commission_invoices')
                ->where('revenue_event_id', $revenueEventId)
                ->update([
                    'revenue_event_id' => $revenueEventId,
                    'updated_at' => $now,
                ]);

            return [
                'commissionable' => true,
                'duplicate' => false,
                'commission_created' => true,
                'invoice_id' => $invoiceId,
                'esubiz_revenue_event_id' => $esubizRevenueEventId,
                'invoice_reference' => $reference,
                'base_amount' => number_format(
                    $paidAmount,
                    2,
                    '.',
                    ''
                ),
                'commission_amount' => number_format(
                    $commission,
                    2,
                    '.',
                    ''
                ),
                'currency' => strtoupper($currency),
                'status' => 'unpaid',
                'due_at' => $dueAt->toDateTimeString(),
            ];
        });
    }
}
