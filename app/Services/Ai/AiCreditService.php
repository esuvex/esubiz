<?php

namespace App\Services\Ai;

use App\Models\Ai\AiCreditTransaction;
use App\Models\Website;
use App\Services\Credits\WebsiteCreditService;

/**
 * AI compatibility facade.
 *
 * CentralAiEngine can continue using AiCreditService,
 * while the underlying accounting engine is now generic.
 */
class AiCreditService
{
    public function __construct(
        protected WebsiteCreditService $credits
    ) {
    }


    public function balance(
        Website|int $website
    ): float {

        return $this->credits->balance(
            $website,
            'ai_credits'
        );
    }


    public function has(
        Website|int $website,
        float $credits
    ): bool {

        return $this->credits->has(
            $website,
            'ai_credits',
            $credits
        );
    }


    public function debit(
        Website|int $website,
        float $credits,
        string $requestKey,
        array $context = []
    ) {

        return $this->credits->debit(
            $website,
            'ai_credits',
            $credits,
            $requestKey,
            $context
        );
    }


    public function credit(
        Website|int $website,
        float $credits,
        string $requestKey,
        array $context = []
    ) {

        return $this->credits->credit(
            $website,
            'ai_credits',
            $credits,
            $requestKey,
            $context
        );
    }


    public function refund(
        $transaction,
        string $requestKey,
        ?string $description = null
    ) {

        /*
         * Generic ledger refunds will be used for all
         * new AI transactions.
         */
        return $this->credits->refund(
            $transaction,
            $requestKey,
            $description
        );
    }
}
