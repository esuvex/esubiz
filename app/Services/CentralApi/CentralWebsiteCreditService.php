<?php

namespace App\Services\CentralApi;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;


/**
 * ================================================================
 * CHECKPOINT 8 — CENTRAL WEBSITE SERVICE CREDIT AUTHORITY
 * ================================================================
 *
 * Central Esubiz owns all service balances.
 *
 * SaaS/off-server Core may display balances and request usage,
 * but they are never authoritative for:
 *
 * AI
 * SMS
 * Email
 * WhatsApp
 *
 * Every mutation is transactional and can carry an idempotent
 * request key.
 */
class CentralWebsiteCreditService
{
    public const SERVICE_AI =
        'ai';

    public const SERVICE_SMS =
        'sms';

    public const SERVICE_EMAIL =
        'email';

    public const SERVICE_WHATSAPP =
        'whatsapp';


    public static function services(): array
    {
        return [
            static::SERVICE_AI,
            static::SERVICE_SMS,
            static::SERVICE_EMAIL,
            static::SERVICE_WHATSAPP,
        ];
    }


    public function balance(
        int $websiteId,
        string $service
    ): float {

        $service =
            $this->assertService(
                $service
            );


        return (float) (
            DB::table(
                'central_website_service_credits'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->where(
                    'service',
                    $service
                )
                ->value('balance')
            ?? 0
        );
    }


    public function balances(
        int $websiteId
    ): array {

        $result = [];

        foreach (
            static::services()
            as $service
        ) {
            $result[$service] =
                $this->balance(
                    $websiteId,
                    $service
                );
        }

        return $result;
    }


    public function credit(
        int $websiteId,
        string $service,
        float $amount,
        ?string $requestKey = null,
        array $context = []
    ): array {

        return $this->mutate(
            $websiteId,
            $service,
            $amount,
            'credit',
            $requestKey,
            $context
        );
    }


    public function consume(
        int $websiteId,
        string $service,
        float $amount,
        string $requestKey,
        array $context = []
    ): array {

        if (
            trim($requestKey)
            === ''
        ) {
            throw new InvalidArgumentException(
                'Central service consumption requires a request key.'
            );
        }


        return $this->mutate(
            $websiteId,
            $service,
            $amount,
            'consume',
            $requestKey,
            $context
        );
    }


    protected function mutate(
        int $websiteId,
        string $service,
        float $amount,
        string $direction,
        ?string $requestKey,
        array $context
    ): array {

        $service =
            $this->assertService(
                $service
            );


        if ($websiteId < 1) {
            throw new InvalidArgumentException(
                'A valid Central website ID is required.'
            );
        }


        if ($amount <= 0) {
            throw new InvalidArgumentException(
                'Service credit amount must be greater than zero.'
            );
        }


        $requestKey =
            $requestKey !== null
                ? trim($requestKey)
                : null;


        return DB::transaction(
            function () use (
                $websiteId,
                $service,
                $amount,
                $direction,
                $requestKey,
                $context
            ) {

                /*
                 * Idempotency is checked inside the same transaction
                 * as the balance mutation.
                 */
                if ($requestKey) {

                    $existing =
                        DB::table(
                            'central_website_service_credit_transactions'
                        )
                            ->where(
                                'service',
                                $service
                            )
                            ->where(
                                'request_key',
                                $requestKey
                            )
                            ->first();


                    if ($existing) {

                        /*
                         * A request key can never be replayed against
                         * another website.
                         */
                        if (
                            (int) $existing->website_id
                            !== $websiteId
                        ) {
                            throw new RuntimeException(
                                'This service request key belongs to another website.'
                            );
                        }


                        return [
                            'success' => true,
                            'already_processed' => true,
                            'transaction_id' =>
                                (int) $existing->id,
                            'website_id' =>
                                $websiteId,
                            'service' =>
                                $service,
                            'direction' =>
                                $existing->direction,
                            'amount' =>
                                (float) $existing->amount,
                            'balance_before' =>
                                (float) $existing->balance_before,
                            'balance_after' =>
                                (float) $existing->balance_after,
                            'request_key' =>
                                $requestKey,
                        ];
                    }
                }


                $row =
                    DB::table(
                        'central_website_service_credits'
                    )
                        ->where(
                            'website_id',
                            $websiteId
                        )
                        ->where(
                            'service',
                            $service
                        )
                        ->lockForUpdate()
                        ->first();


                if (!$row) {

                    DB::table(
                        'central_website_service_credits'
                    )->insert([
                        'website_id' =>
                            $websiteId,

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


                    $row =
                        DB::table(
                            'central_website_service_credits'
                        )
                            ->where(
                                'website_id',
                                $websiteId
                            )
                            ->where(
                                'service',
                                $service
                            )
                            ->lockForUpdate()
                            ->first();
                }


                $before =
                    (float) $row->balance;


                if (
                    $direction
                    === 'consume'
                    && $before < $amount
                ) {
                    throw new RuntimeException(
                        'Insufficient Esubiz '
                        . strtoupper($service)
                        . ' credits.'
                    );
                }


                $after =
                    $direction === 'credit'
                        ? $before + $amount
                        : $before - $amount;


                $updates = [
                    'balance' =>
                        $after,

                    'updated_at' =>
                        now(),
                ];


                if ($direction === 'credit') {

                    $updates['lifetime_credited'] =
                        (float) $row->lifetime_credited
                        + $amount;

                } else {

                    $updates['lifetime_consumed'] =
                        (float) $row->lifetime_consumed
                        + $amount;
                }


                DB::table(
                    'central_website_service_credits'
                )
                    ->where(
                        'id',
                        $row->id
                    )
                    ->update(
                        $updates
                    );


                $transactionId =
                    DB::table(
                        'central_website_service_credit_transactions'
                    )
                        ->insertGetId([
                            'website_id' =>
                                $websiteId,

                            'service' =>
                                $service,

                            'direction' =>
                                $direction,

                            'amount' =>
                                $amount,

                            'balance_before' =>
                                $before,

                            'balance_after' =>
                                $after,

                            'request_key' =>
                                $requestKey,

                            'source_type' =>
                                $context['source_type']
                                ?? null,

                            'source_id' =>
                                $context['source_id']
                                ?? null,

                            'user_id' =>
                                $context['user_id']
                                ?? null,

                            'installation_id' =>
                                $context['installation_id']
                                ?? null,

                            'metadata' =>
                                !empty($context['metadata'])
                                    ? json_encode(
                                        $context['metadata']
                                    )
                                    : null,

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);


                return [
                    'success' => true,
                    'already_processed' => false,
                    'transaction_id' =>
                        (int) $transactionId,
                    'website_id' =>
                        $websiteId,
                    'service' =>
                        $service,
                    'direction' =>
                        $direction,
                    'amount' =>
                        $amount,
                    'balance_before' =>
                        $before,
                    'balance_after' =>
                        $after,
                    'request_key' =>
                        $requestKey,
                ];
            }
        );
    }


    protected function assertService(
        string $service
    ): string {

        $service =
            strtolower(
                trim(
                    $service
                )
            );


        if (
            !in_array(
                $service,
                static::services(),
                true
            )
        ) {
            throw new InvalidArgumentException(
                "Unsupported Esubiz service credit [{$service}]."
            );
        }


        return $service;
    }
}
