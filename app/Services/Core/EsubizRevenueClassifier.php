<?php

namespace App\Services\Core;

class EsubizRevenueClassifier
{
    /**
     * Classify a transaction before it can become Esubiz revenue.
     *
     * Merchant income and referral commissions are never
     * automatically treated as Esubiz revenue.
     *
     * Esubiz commission is eligible only when:
     * - the transaction belongs to an Esubiz-hosted site
     * - the site's plan is a commission plan
     * - the transaction is paid/qualifying
     */
    public function classify(array $context): array
    {
        $transactionType = $context['transaction_type'] ?? null;
        $hostingScope = $context['hosting_scope'] ?? null;
        $commissionPlan = $context['commission_plan'] ?? null;
        $paymentSuccessful = (bool) (
            $context['payment_successful'] ?? false
        );

        /*
         * Explicit Esubiz platform-sale boundary.
         *
         * Platform income is trusted only when the platform-sale
         * service explicitly marks it as Esubiz revenue.
         * It is never inferred from source_module alone.
         */
        if (
            ($context['revenue_owner'] ?? null) === 'esubiz'
            && !empty($context['is_platform_revenue'])
            && !empty($context['is_esubiz_commissionable'])
        ) {
            return [
                'revenue_owner' => 'esubiz',
                'is_platform_revenue' => true,
                'is_esubiz_commissionable' => true,
                'hosting_scope' =>
                    $context['hosting_scope'] ?? 'esubiz_platform',
                'commission_plan' =>
                    $context['commission_plan'] ?? null,
            ];
        }

        /*
         * Referral commissions belong to the merchant/referral
         * system and are never Esubiz platform revenue.
         */
        if (
            $transactionType === 'referral_commission' ||
            $transactionType === 'commission'
        ) {
            return [
                'revenue_owner' => 'merchant',
                'is_platform_revenue' => false,
                'is_esubiz_commissionable' => false,
                'hosting_scope' => $hostingScope,
                'commission_plan' => $commissionPlan,
            ];
        }

        /*
         * Only websites actually hosted on Esubiz qualify.
         */
        $esubizHosted =
            $hostingScope === 'esubiz_hosted';

        $hasCommissionPlan =
            $commissionPlan === 'commission';

        $eligible =
            $esubizHosted &&
            $hasCommissionPlan &&
            $paymentSuccessful;

        return [
            'revenue_owner' =>
                $eligible ? 'esubiz' : 'merchant',

            'is_platform_revenue' =>
                $eligible,

            'is_esubiz_commissionable' =>
                $eligible,

            'hosting_scope' =>
                $hostingScope,

            'commission_plan' =>
                $commissionPlan,
        ];
    }

    public function isEsubizRevenue(array $context): bool
    {
        return $this->classify($context)['is_platform_revenue'];
    }
}
