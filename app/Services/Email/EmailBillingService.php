<?php

namespace App\Services\Email;

use App\Models\Email\EmailUsageLog;
use App\Models\Website;
use App\Services\CentralApi\CentralServiceCreditConsumptionService;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class EmailBillingService
{
    public function __construct(
        protected EmailPricingService $pricing,
        protected CentralServiceCreditConsumptionService $credits
    ) {
    }

    /**
     * Prepare a Premium Email send before provider dispatch.
     *
     * This performs:
     * - dynamic commercial quote
     * - authoritative Central balance preflight
     * - immutable pending usage record
     *
     * It does NOT debit credits.
     */
    public function preparePremiumSend(
        Website|int $website,
        int $recipientCount,
        array $context = []
    ): array {
        $websiteId =
            $website instanceof Website
                ? (int) $website->id
                : (int) $website;

        if ($websiteId < 1) {
            throw new InvalidArgumentException(
                'A valid website is required for Premium Email.'
            );
        }

        if ($recipientCount < 1) {
            throw new InvalidArgumentException(
                'Recipient count must be at least 1.'
            );
        }

        $quote =
            $this->pricing->quote(
                $recipientCount
            );

        if (!($quote['available'] ?? false)) {
            throw new RuntimeException(
                'Premium Email pricing is unavailable: '
                . (
                    $quote['unavailable_reason']
                    ?? 'unknown'
                )
            );
        }

        $creditsRequired =
            (float) (
                $quote['credits']
                ?? 0
            );

        if ($creditsRequired <= 0) {
            throw new RuntimeException(
                'Premium Email produced an invalid credit charge.'
            );
        }

        $balance =
            $this->credits->emailBalance(
                $websiteId
            );

        if ($balance < $creditsRequired) {
            throw new RuntimeException(
                'Insufficient Esubiz EMAIL credits.'
            );
        }

        $requestUuid =
            (string) (
                $context['request_uuid']
                ?? Str::uuid()
            );

        $deploymentScope =
            $this->deploymentScope(
                $context['deployment_scope']
                ?? 'saas'
            );

        $emailType =
            trim(
                (string) (
                    $context['email_type']
                    ?? 'core_transactional'
                )
            );

        if ($emailType === '') {
            throw new InvalidArgumentException(
                'Email type is required.'
            );
        }

        $usage =
            EmailUsageLog::query()
                ->where(
                    'request_uuid',
                    $requestUuid
                )
                ->first();

        if ($usage) {
            return [
                'success' => true,
                'already_prepared' => true,
                'request_uuid' =>
                    $requestUuid,
                'usage_log_id' =>
                    (int) $usage->id,
                'credits_required' =>
                    (float) $usage->credits_quoted,
                'balance' =>
                    $balance,
                'quote' =>
                    $quote,
                'usage' =>
                    $usage,
            ];
        }

        $snapshot =
            $quote['pricing_snapshot']
            ?? [];

        $metadata =
            array_merge(
                [
                    'pricing_snapshot' =>
                        $snapshot,
                ],
                is_array(
                    $context['metadata']
                    ?? null
                )
                    ? $context['metadata']
                    : []
            );

        $usage =
            EmailUsageLog::query()
                ->create([
                    'request_uuid' =>
                        $requestUuid,

                    'website_id' =>
                        $websiteId,

                    'user_id' =>
                        isset($context['user_id'])
                            ? (int) $context['user_id']
                            : null,

                    'deployment_scope' =>
                        $deploymentScope,

                    'sender_mode' =>
                        'premium',

                    'email_type' =>
                        $emailType,

                    'provider' =>
                        (string) (
                            $quote['provider']
                            ?? 'aws'
                        ),

                    'provider_service' =>
                        (string) (
                            $quote['provider_service']
                            ?? 'ses'
                        ),

                    'provider_region' =>
                        $snapshot[
                            'aws_service_region'
                        ]
                        ?? null,

                    'status' =>
                        'pending',

                    'recipient_count' =>
                        $recipientCount,

                    'provider_unit_cost' =>
                        $quote[
                            'provider_unit_cost'
                        ]
                        ?? null,

                    'provider_total_cost' =>
                        $quote[
                            'provider_total_cost'
                        ]
                        ?? null,

                    'provider_currency' =>
                        $quote[
                            'provider_currency'
                        ]
                        ?? null,

                    'email_markup_type' =>
                        $quote[
                            'email_markup_type'
                        ]
                        ?? null,

                    'email_markup_value' =>
                        $quote[
                            'email_markup_value'
                        ]
                        ?? null,

                    'email_markup_amount' =>
                        $quote[
                            'email_markup_amount'
                        ]
                        ?? null,

                    'selling_cost_provider_currency' =>
                        $quote[
                            'selling_cost_provider_currency'
                        ]
                        ?? null,

                    'exchange_rate' =>
                        $quote[
                            'exchange_rate'
                        ]
                        ?? null,

                    'exchange_rate_provider' =>
                        $quote[
                            'exchange_rate_provider'
                        ]
                        ?? null,

                    'exchange_rate_date' =>
                        $quote[
                            'exchange_rate_date'
                        ]
                        ?? null,

                    'central_currency' =>
                        $quote[
                            'central_currency'
                        ]
                        ?? null,

                    'selling_cost_central_currency' =>
                        $quote[
                            'selling_cost_central_currency'
                        ]
                        ?? null,

                    'uncapped_rate_per_recipient_base_currency' =>
                        $quote[
                            'uncapped_rate_per_recipient_base_currency'
                        ]
                        ?? null,

                    'max_rate_per_recipient_base_currency' =>
                        $quote[
                            'max_rate_per_recipient_base_currency'
                        ]
                        ?? null,

                    'cap_applied' =>
                        (bool) (
                            $quote[
                                'cap_applied'
                            ]
                            ?? false
                        ),

                    'selling_rate_per_recipient_base_currency' =>
                        $quote[
                            'selling_rate_per_recipient_base_currency'
                        ]
                        ?? null,

                    'email_credit_value' =>
                        $quote[
                            'email_credit_value'
                        ]
                        ?? null,

                    /*
                     * Quote is recorded before provider dispatch.
                     *
                     * credits_charged remains zero until the
                     * authoritative Central ledger debit succeeds.
                     */
                    'credits_quoted' =>
                        $creditsRequired,

                    'credits_charged' =>
                        0,

                    'source_type' =>
                        isset($context['source_type'])
                            ? (string) $context['source_type']
                            : null,

                    'source_id' =>
                        isset($context['source_id'])
                            ? (string) $context['source_id']
                            : null,

                    'metadata' =>
                        $metadata,
                ]);

        return [
            'success' => true,
            'already_prepared' => false,
            'request_uuid' =>
                $requestUuid,
            'usage_log_id' =>
                (int) $usage->id,
            'credits_required' =>
                $creditsRequired,
            'balance' =>
                $balance,
            'quote' =>
                $quote,
            'usage' =>
                $usage,
        ];
    }

    /**
     * Finalize billing only after the provider has accepted the send.
     *
     * Central request_key is deterministic from the Email request UUID,
     * making retries safe.
     */
    public function finalizeProviderAccepted(
        EmailUsageLog|string $usage,
        string $providerMessageId,
        array $context = []
    ): array {
        $usage =
            $usage instanceof EmailUsageLog
                ? $usage->fresh()
                : EmailUsageLog::query()
                    ->where(
                        'request_uuid',
                        $usage
                    )
                    ->firstOrFail();

        if (!$usage) {
            throw new RuntimeException(
                'Premium Email usage could not be refreshed.'
            );
        }

        if (
            $usage->sender_mode
            !== 'premium'
        ) {
            throw new InvalidArgumentException(
                'Only Premium Email uses Email Credit billing.'
            );
        }

        if (
            !$usage->website_id
            || (int) $usage->website_id < 1
        ) {
            throw new RuntimeException(
                'Premium Email usage has no valid website.'
            );
        }

        $creditsRequired =
            (float) $usage->credits_quoted;

        if ($creditsRequired <= 0) {
            throw new RuntimeException(
                'Premium Email usage has no valid quoted credit charge.'
            );
        }

        $providerMessageId =
            trim($providerMessageId);

        if ($providerMessageId === '') {
            throw new InvalidArgumentException(
                'Provider message ID is required.'
            );
        }

        /*
         * ESUBIZ_EMAIL_PROVIDER_ACCEPTANCE_IDEMPOTENCY_V1
         *
         * Once provider acceptance has been recorded, its provider
         * message identity is immutable for this logical send.
         *
         * A retry may complete billing for the SAME provider message,
         * but it must never replace that identity with another message
         * ID because doing so could hide an accidental second dispatch.
         */
        $storedProviderMessageId =
            trim(
                (string) (
                    $usage->provider_message_id
                    ?? ''
                )
            );

        if (
            $usage->provider_accepted_at
            && $storedProviderMessageId !== ''
            && !hash_equals(
                $storedProviderMessageId,
                $providerMessageId
            )
        ) {
            throw new RuntimeException(
                'Provider acceptance is already recorded with a '
                . 'different provider message ID.'
            );
        }

        /*
         * If billing already completed, this lifecycle operation is
         * finished. Do not rewrite status here: an asynchronous SES
         * event may already have advanced it to delivered, bounced,
         * complained, rejected, or another later provider state.
         */
        if (
            (float) $usage->credits_charged > 0
        ) {
            return [
                'success' => true,
                'request_uuid' =>
                    (string) $usage->request_uuid,
                'usage_log_id' =>
                    (int) $usage->id,
                'provider_message_id' =>
                    $storedProviderMessageId !== ''
                        ? $storedProviderMessageId
                        : $providerMessageId,
                'credits_charged' =>
                    (float) $usage->credits_charged,
                'credit_transaction_id' =>
                    $usage->credit_transaction_reference,
                'already_processed' =>
                    true,
                'balance_before' =>
                    null,
                'balance_after' =>
                    null,
            ];
        }

        /*
         * Persist provider acceptance before attempting the financial
         * mutation.
         *
         * On the first call we capture provider identity and acceptance
         * time. On a billing retry we preserve both values.
         */
        $acceptanceUpdates = [
            'status' =>
                'provider_accepted_billing_pending',

            'provider_accepted_at' =>
                $usage->provider_accepted_at
                    ?: now(),

            'error_message' =>
                null,
        ];

        if ($storedProviderMessageId === '') {
            $acceptanceUpdates[
                'provider_message_id'
            ] = $providerMessageId;
        }

        $usage->forceFill(
            $acceptanceUpdates
        )->save();

        /*
         * Refresh after acceptance persistence so all following
         * metadata uses the immutable provider identity stored on the
         * usage row.
         */
        $usage->refresh();

        $providerMessageId =
            trim(
                (string) $usage->provider_message_id
            );

        if ($providerMessageId === '') {
            throw new RuntimeException(
                'Provider acceptance was recorded without a provider '
                . 'message ID.'
            );
        }

        $requestKey =
            'email:premium:'
            . $usage->request_uuid;

        /*
         * CentralWebsiteCreditService performs the financial mutation
         * inside its own transaction and guarantees idempotency by
         * service + request_key.
         *
         * Therefore a billing-pending retry reuses this exact key and
         * can finish the debit without creating another charge.
         */
        try {
            $debit =
                $this->credits->consumeEmail(
                    (int) $usage->website_id,
                    $creditsRequired,
                    $requestKey,
                    [
                        'source_type' =>
                            EmailUsageLog::class,

                        'source_id' =>
                            (string) $usage->request_uuid,

                        'user_id' =>
                            $usage->user_id,

                        'installation_id' =>
                            $context[
                                'installation_id'
                            ]
                            ?? null,

                        'metadata' => [
                            'email_usage_log_id' =>
                                (int) $usage->id,

                            'request_uuid' =>
                                (string) $usage->request_uuid,

                            'provider' =>
                                (string) $usage->provider,

                            'provider_service' =>
                                (string) $usage->provider_service,

                            'provider_message_id' =>
                                $providerMessageId,

                            'recipient_count' =>
                                (int) $usage->recipient_count,

                            'email_type' =>
                                (string) $usage->email_type,

                            'sender_mode' =>
                                (string) $usage->sender_mode,

                            'deployment_scope' =>
                                (string) $usage->deployment_scope,

                            'pricing_snapshot' =>
                                $usage->metadata[
                                    'pricing_snapshot'
                                ]
                                ?? null,
                        ],
                    ]
                );
        } catch (Throwable $error) {
            /*
             * Preserve the provider acceptance fact. This is not a
             * provider/send failure.
             *
             * A later retry may call this method again with the SAME
             * provider message ID and deterministic ledger request key.
             */
            $usage->forceFill([
                'status' =>
                    'provider_accepted_billing_pending',

                'error_message' =>
                    'Provider accepted the email, but Email Credit '
                    . 'billing is pending: '
                    . $error->getMessage(),
            ])->save();

            throw $error;
        }

        /*
         * Billing is complete. provider_message_id and
         * provider_accepted_at remain untouched.
         */
        $usage->forceFill([
            'status' =>
                'provider_accepted',

            'credits_charged' =>
                $creditsRequired,

            'credit_transaction_reference' =>
                isset($debit['transaction_id'])
                    ? (string) $debit[
                        'transaction_id'
                    ]
                    : (
                        $usage->credit_transaction_reference
                        ?: null
                    ),

            'error_message' =>
                null,
        ])->save();

        return [
            'success' => true,
            'request_uuid' =>
                (string) $usage->request_uuid,
            'usage_log_id' =>
                (int) $usage->id,
            'provider_message_id' =>
                $providerMessageId,
            'credits_charged' =>
                $creditsRequired,
            'credit_transaction_id' =>
                $debit['transaction_id']
                ?? $usage->credit_transaction_reference,
            'already_processed' =>
                (bool) (
                    $debit['already_processed']
                    ?? false
                ),
            'balance_before' =>
                $debit['balance_before']
                ?? null,
            'balance_after' =>
                $debit['balance_after']
                ?? null,
        ];
    }

    /**
     * Record provider failure before acceptance.
     *
     * No credit mutation occurs here.
     */
    public function markProviderFailed(
        EmailUsageLog|string $usage,
        Throwable|string $error
    ): EmailUsageLog {
        $usage =
            $usage instanceof EmailUsageLog
                ? $usage
                : EmailUsageLog::query()
                    ->where(
                        'request_uuid',
                        $usage
                    )
                    ->firstOrFail();

        /*
         * Do not overwrite a send that has already reached provider
         * acceptance. Delivery/bounce/complaint processing belongs
         * to the SES event layer.
         */
        if (
            $usage->status
            === 'provider_accepted'
            || $usage->provider_accepted_at
        ) {
            return $usage;
        }

        $message =
            $error instanceof Throwable
                ? $error->getMessage()
                : trim($error);

        $usage->forceFill([
            'status' =>
                'failed',

            'error_message' =>
                $message !== ''
                    ? $message
                    : 'Provider send failed.',

            'completed_at' =>
                now(),
        ])->save();

        return $usage;
    }

    protected function deploymentScope(
        string $scope
    ): string {
        $scope =
            strtolower(
                trim($scope)
            );

        if (
            !in_array(
                $scope,
                [
                    'central',
                    'saas',
                    'off_server',
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Unsupported Email deployment scope.'
            );
        }

        return $scope;
    }
}
