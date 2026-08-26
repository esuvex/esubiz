<?php

namespace App\Services\Ai;

use App\Models\Ai\AiCreditTransaction;
use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AiCreditService
{
    /**
     * Return current central AI-credit balance.
     */
    public function balance(
        Website|int $website
    ): float {
        $websiteId = $website instanceof Website
            ? $website->id
            : $website;

        return (float) Website::query()
            ->whereKey($websiteId)
            ->value('ai_credits');
    }

    /**
     * Determine whether a website has enough credits.
     */
    public function has(
        Website|int $website,
        float $credits
    ): bool {
        return $this->balance($website) >= $credits;
    }

    /**
     * Atomically debit AI credits.
     *
     * request_key is UNIQUE. Therefore the same AI execution
     * cannot debit the website twice.
     */
    public function debit(
        Website|int $website,
        float $credits,
        string $requestKey,
        array $context = []
    ): AiCreditTransaction {

        if ($credits <= 0) {
            throw new RuntimeException(
                'AI credit debit must be greater than zero.'
            );
        }

        $websiteId = $website instanceof Website
            ? $website->id
            : $website;

        return DB::transaction(function () use (
            $websiteId,
            $credits,
            $requestKey,
            $context
        ) {
            /*
             * Idempotency check before locking.
             */
            $existing = AiCreditTransaction::query()
                ->where('request_key', $requestKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            /*
             * Lock the website balance.
             *
             * Concurrent AI requests cannot spend the same
             * credits simultaneously.
             */
            $website = Website::query()
                ->lockForUpdate()
                ->findOrFail($websiteId);

            $before = (float) $website->ai_credits;

            if ($before < $credits) {
                throw new RuntimeException(
                    'Insufficient AI credits.'
                );
            }

            $after = $before - $credits;

            $website->forceFill([
                'ai_credits' => $after,
            ])->save();

            return AiCreditTransaction::query()->create([
                'website_id' => $website->id,

                'user_id' =>
                    $context['user_id']
                    ?? $website->owner_id
                    ?? null,

                'workspace_id' =>
                    $context['workspace_id']
                    ?? $website->workspace_id
                    ?? null,

                'uuid' => (string) Str::uuid(),

                'request_key' => $requestKey,

                'reference' =>
                    'AIC-' .
                    strtoupper(Str::random(18)),

                'direction' => 'debit',

                'type' =>
                    $context['type']
                    ?? 'ai_usage',

                'credits' => $credits,

                'balance_before' => $before,
                'balance_after' => $after,

                'ai_usage_log_id' =>
                    $context['ai_usage_log_id']
                    ?? null,

                'ai_model_id' =>
                    $context['ai_model_id']
                    ?? null,

                'route_key' =>
                    $context['route_key']
                    ?? null,

                'source_type' =>
                    $context['source_type']
                    ?? 'central_ai',

                'source_id' =>
                    isset($context['source_id'])
                        ? (string) $context['source_id']
                        : null,

                'status' => 'completed',

                'description' =>
                    $context['description']
                    ?? 'Central Esubiz AI usage',

                'metadata' =>
                    $context['metadata']
                    ?? [],
            ]);
        }, 5);
    }

    /**
     * Credit AI balance.
     *
     * Used later for package purchases, admin adjustments,
     * promotions and refunds.
     */
    public function credit(
        Website|int $website,
        float $credits,
        string $requestKey,
        array $context = []
    ): AiCreditTransaction {

        if ($credits <= 0) {
            throw new RuntimeException(
                'AI credit amount must be greater than zero.'
            );
        }

        $websiteId = $website instanceof Website
            ? $website->id
            : $website;

        return DB::transaction(function () use (
            $websiteId,
            $credits,
            $requestKey,
            $context
        ) {
            $existing = AiCreditTransaction::query()
                ->where('request_key', $requestKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            $website = Website::query()
                ->lockForUpdate()
                ->findOrFail($websiteId);

            $before = (float) $website->ai_credits;
            $after = $before + $credits;

            $website->forceFill([
                'ai_credits' => $after,
            ])->save();

            return AiCreditTransaction::query()->create([
                'website_id' => $website->id,

                'user_id' =>
                    $context['user_id']
                    ?? $website->owner_id
                    ?? null,

                'workspace_id' =>
                    $context['workspace_id']
                    ?? $website->workspace_id
                    ?? null,

                'uuid' => (string) Str::uuid(),

                'request_key' => $requestKey,

                'reference' =>
                    'AIC-' .
                    strtoupper(Str::random(18)),

                'direction' =>
                    $context['direction']
                    ?? 'credit',

                'type' =>
                    $context['type']
                    ?? 'credit_allocation',

                'credits' => $credits,

                'balance_before' => $before,
                'balance_after' => $after,

                'ai_usage_log_id' =>
                    $context['ai_usage_log_id']
                    ?? null,

                'ai_model_id' =>
                    $context['ai_model_id']
                    ?? null,

                'route_key' =>
                    $context['route_key']
                    ?? null,

                'source_type' =>
                    $context['source_type']
                    ?? null,

                'source_id' =>
                    isset($context['source_id'])
                        ? (string) $context['source_id']
                        : null,

                'status' => 'completed',

                'description' =>
                    $context['description']
                    ?? 'AI credit allocation',

                'metadata' =>
                    $context['metadata']
                    ?? [],
            ]);
        }, 5);
    }

    /**
     * Refund a previous AI debit.
     *
     * refund request key must also be unique.
     */
    public function refund(
        AiCreditTransaction $transaction,
        string $requestKey,
        ?string $description = null
    ): AiCreditTransaction {

        if ($transaction->direction !== 'debit') {
            throw new RuntimeException(
                'Only AI debit transactions can be refunded.'
            );
        }

        return $this->credit(
            $transaction->website_id,
            (float) $transaction->credits,
            $requestKey,
            [
                'user_id' => $transaction->user_id,
                'workspace_id' =>
                    $transaction->workspace_id,

                'direction' => 'refund',
                'type' => 'ai_refund',

                'ai_usage_log_id' =>
                    $transaction->ai_usage_log_id,

                'ai_model_id' =>
                    $transaction->ai_model_id,

                'route_key' =>
                    $transaction->route_key,

                'source_type' =>
                    AiCreditTransaction::class,

                'source_id' =>
                    $transaction->id,

                'description' =>
                    $description
                    ?? 'AI usage refund',

                'metadata' => [
                    'original_transaction_id' =>
                        $transaction->id,

                    'original_reference' =>
                        $transaction->reference,
                ],
            ]
        );
    }
}
