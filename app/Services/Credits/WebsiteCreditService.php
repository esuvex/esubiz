<?php

namespace App\Services\Credits;

use App\Models\CreditTransaction;
use App\Models\Website;
use App\Support\Credits\CreditTypeRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WebsiteCreditService
{
    /*
     * CHECKPOINT_8_CENTRAL_CREDIT_BRIDGE
     *
     * WebsiteCreditService remains the compatibility facade used by
     * existing Marketplace fulfilment and service callers.
     *
     * CentralWebsiteCreditService is now the authoritative balance
     * ledger for:
     *
     * - AI
     * - SMS
     * - Email
     * - WhatsApp
     */
    protected function centralCredits():
        \App\Services\CentralApi\CentralWebsiteCreditService
    {
        return app(
            \App\Services\CentralApi\CentralWebsiteCreditService::class
        );
    }


    protected function centralServiceName(
        string $creditType
    ): string {

        return match (
            strtolower(
                trim(
                    $creditType
                )
            )
        ) {

            'ai',
            'ai_credit',
            'ai_credits' =>
                \App\Services\CentralApi\CentralWebsiteCreditService::SERVICE_AI,

            'sms',
            'sms_credit',
            'sms_credits' =>
                \App\Services\CentralApi\CentralWebsiteCreditService::SERVICE_SMS,

            'email',
            'email_credit',
            'email_credits' =>
                \App\Services\CentralApi\CentralWebsiteCreditService::SERVICE_EMAIL,

            'whatsapp',
            'whatsapp_credit',
            'whatsapp_credits' =>
                \App\Services\CentralApi\CentralWebsiteCreditService::SERVICE_WHATSAPP,

            default =>
                throw new \InvalidArgumentException(
                    "Unsupported Esubiz credit type [{$creditType}]."
                ),
        };
    }


    public function __construct(
        protected CreditTypeRegistry $registry
    ) {
    }


    public function balance(
        Website|int $website,
        string $creditType
    ): float {

        $websiteId =
            $website instanceof Website
                ? $website->id
                : $website;


        $column =
            $this->registry
                ->balanceColumn(
                    $creditType
                );


        return (float)
            Website::query()
                ->whereKey(
                    $websiteId
                )
                ->value(
                    $column
                );
    }


    public function has(
        Website|int $website,
        string $creditType,
        float $credits
    ): bool {

        return $this->balance(
            $website,
            $creditType
        ) >= $credits;
    }


    public function credit(
        Website|int $website,
        string $creditType,
        float $credits,
        string $requestKey,
        array $context = []
    ): CreditTransaction {

        return $this->move(
            $website,
            $creditType,
            $credits,
            $requestKey,
            'credit',
            $context
        );
    }


    public function debit(
        Website|int $website,
        string $creditType,
        float $credits,
        string $requestKey,
        array $context = []
    ): CreditTransaction {

        return $this->move(
            $website,
            $creditType,
            $credits,
            $requestKey,
            'debit',
            $context
        );
    }


    public function refund(
        CreditTransaction $transaction,
        string $requestKey,
        ?string $description = null
    ): CreditTransaction {

        if (
            $transaction->direction
            !== 'debit'
        ) {
            throw new RuntimeException(
                'Only debit transactions can be refunded.'
            );
        }


        return $this->credit(
            $transaction->website_id,
            $transaction->credit_type,
            (float) $transaction->credits,
            $requestKey,
            [
                'user_id' =>
                    $transaction->user_id,

                'workspace_id' =>
                    $transaction->workspace_id,

                'type' =>
                    'credit_refund',

                'source_type' =>
                    CreditTransaction::class,

                'source_id' =>
                    $transaction->id,

                'description' =>
                    $description
                    ?? 'Credit usage refund',

                'metadata' => [
                    'original_transaction_id' =>
                        $transaction->id,

                    'original_reference' =>
                        $transaction->reference,
                ],
            ]
        );
    }


    protected function move(
        Website|int $website,
        string $creditType,
        float $credits,
        string $requestKey,
        string $direction,
        array $context
    ): CreditTransaction {

        if ($credits <= 0) {
            throw new RuntimeException(
                'Credit quantity must be greater than zero.'
            );
        }


        $column =
            $this->registry
                ->balanceColumn(
                    $creditType
                );


        $websiteId =
            $website instanceof Website
                ? $website->id
                : $website;


        return DB::transaction(
            function () use (
                $websiteId,
                $creditType,
                $credits,
                $requestKey,
                $direction,
                $column,
                $context
            ) {

                /*
                 * One source event can never apply twice.
                 */
                $existing =
                    CreditTransaction::query()
                        ->where(
                            'request_key',
                            $requestKey
                        )
                        ->first();


                if ($existing) {
                    return $existing;
                }


                /*
                 * Lock website row to stop concurrent
                 * double-spending.
                 */
                $website =
                    Website::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $websiteId
                        );


                $before =
                    (float) (
                        $website
                            ->getAttribute(
                                $column
                            )
                        ?? 0
                    );


                if (
                    $direction === 'debit'
                    && $before < $credits
                ) {
                    throw new RuntimeException(
                        'Insufficient '
                        . str_replace(
                            '_',
                            ' ',
                            $creditType
                        )
                        . '.'
                    );
                }


                $after =
                    $direction === 'debit'
                        ? $before - $credits
                        : $before + $credits;


                $website->forceFill([
                    $column =>
                        $after,
                ])->save();


                return CreditTransaction::query()
                    ->create([
                        'website_id' =>
                            $website->id,

                        'user_id' =>
                            $context['user_id']
                            ?? $website->owner_id
                            ?? null,

                        'workspace_id' =>
                            $context['workspace_id']
                            ?? $website->workspace_id
                            ?? null,

                        'credit_type' =>
                            $creditType,

                        'uuid' =>
                            (string)
                            Str::uuid(),

                        'request_key' =>
                            $requestKey,

                        'reference' =>
                            'CRD-'
                            . strtoupper(
                                Str::random(18)
                            ),

                        'direction' =>
                            $direction,

                        'type' =>
                            $context['type']
                            ?? (
                                $direction === 'debit'
                                    ? 'credit_usage'
                                    : 'credit_allocation'
                            ),

                        'credits' =>
                            $credits,

                        'balance_before' =>
                            $before,

                        'balance_after' =>
                            $after,

                        'source_type' =>
                            $context['source_type']
                            ?? null,

                        'source_id' =>
                            isset(
                                $context['source_id']
                            )
                                ? (string)
                                    $context['source_id']
                                : null,

                        'status' =>
                            'completed',

                        'description' =>
                            $context['description']
                            ?? null,

                        'metadata' =>
                            $context['metadata']
                            ?? [],
                    ]);
            },
            5
        );
    }


    /**
     * Canonical Central balance lookup.
     */
    public function centralBalance(
        int $websiteId,
        string $creditType
    ): float {

        return $this->centralCredits()
            ->balance(
                $websiteId,
                $this->centralServiceName(
                    $creditType
                )
            );
    }


    /**
     * Canonical Central purchase/funding credit.
     */
    public function centralCredit(
        int $websiteId,
        string $creditType,
        float $amount,
        ?string $requestKey = null,
        array $context = []
    ): array {

        return $this->centralCredits()
            ->credit(
                $websiteId,
                $this->centralServiceName(
                    $creditType
                ),
                $amount,
                $requestKey,
                $context
            );
    }


    /**
     * Canonical Central service consumption.
     */
    public function centralConsume(
        int $websiteId,
        string $creditType,
        float $amount,
        string $requestKey,
        array $context = []
    ): array {

        return $this->centralCredits()
            ->consume(
                $websiteId,
                $this->centralServiceName(
                    $creditType
                ),
                $amount,
                $requestKey,
                $context
            );
    }


    public function centralBalances(
        int $websiteId
    ): array {

        return $this->centralCredits()
            ->balances(
                $websiteId
            );
    }

}
