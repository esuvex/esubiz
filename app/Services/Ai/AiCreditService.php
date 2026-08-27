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


    /*
     * CHECKPOINT 8 — CENTRAL AI CREDIT AUTHORITY
     *
     * AI balance and consumption are authoritative in the
     * Central Esubiz website service-credit ledger.
     *
     * SaaS tenant databases and off-server Core databases
     * cannot manufacture or alter the authoritative balance.
     */

    public function balance(
        Website|int $website
    ): float {

        $websiteId =
            $website instanceof Website
                ? (int) $website->id
                : (int) $website;


        return $this->credits->centralBalance(
            $websiteId,
            'ai'
        );
    }


    public function has(
        Website|int $website,
        float $credits
    ): bool {

        return $this->balance(
            $website
        ) >= $credits;
    }


    public function debit(
        Website|int $website,
        float $credits,
        string $requestKey,
        array $context = []
    ) {

        $websiteId =
            $website instanceof Website
                ? (int) $website->id
                : (int) $website;


        /*
         * centralConsume() provides:
         *
         * - authoritative Central balance
         * - row locking / concurrency protection
         * - insufficient-credit protection
         * - request-key idempotency
         *
         * Therefore ai-usage:<request_uuid> can never charge
         * the same AI request twice.
         */
        return $this->credits->centralConsume(
            $websiteId,
            'ai',
            $credits,
            $requestKey,
            [
                'user_id' =>
                    $context['user_id']
                    ?? null,

                'installation_id' =>
                    $context['installation_id']
                    ?? null,

                'source_type' =>
                    $context['source_type']
                    ?? 'central_ai_engine',

                'source_id' =>
                    $context['source_id']
                    ?? null,

                'metadata' => [
                    'type' =>
                        $context['type']
                        ?? 'ai_usage',

                    'description' =>
                        $context['description']
                        ?? 'Esubiz AI usage',

                    'workspace_id' =>
                        $context['workspace_id']
                        ?? null,

                    'ai_usage_log_id' =>
                        $context['ai_usage_log_id']
                        ?? null,

                    'ai_model_id' =>
                        $context['ai_model_id']
                        ?? null,

                    'route_key' =>
                        $context['route_key']
                        ?? null,

                    'usage_metadata' =>
                        $context['metadata']
                        ?? [],
                ],
            ]
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
