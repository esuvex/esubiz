<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $providers = DB::table('payment_providers')
            ->whereIn('slug', [
                'paystack',
                'flutterwave',
                'paypal',
                'nowpayments',
            ])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('slug');

        $methods = [
            [
                'provider' => 'paystack',
                'name' => 'Paystack Card',
                'slug' => 'paystack-card',
                'type' => 'card',
                'priority' => 1,
            ],
            [
                'provider' => 'paystack',
                'name' => 'Paystack Bank Transfer',
                'slug' => 'paystack-bank-transfer',
                'type' => 'bank_transfer',
                'priority' => 2,
            ],
            [
                'provider' => 'flutterwave',
                'name' => 'Flutterwave Card',
                'slug' => 'flutterwave-card',
                'type' => 'card',
                'priority' => 3,
            ],
            [
                'provider' => 'flutterwave',
                'name' => 'Flutterwave Bank Transfer',
                'slug' => 'flutterwave-bank-transfer',
                'type' => 'bank_transfer',
                'priority' => 4,
            ],
            [
                'provider' => 'paypal',
                'name' => 'PayPal',
                'slug' => 'paypal',
                'type' => 'wallet',
                'priority' => 5,
            ],
            [
                'provider' => 'nowpayments',
                'name' => 'NOWPayments Crypto',
                'slug' => 'nowpayments-crypto',
                'type' => 'crypto',
                'priority' => 6,
            ],
        ];

        foreach ($methods as $method) {
            $provider = $providers->get($method['provider']);

            if (!$provider) {
                continue;
            }

            DB::table('payment_methods')->updateOrInsert(
                ['slug' => $method['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'payment_provider_id' => $provider->id,
                    'name' => $method['name'],
                    'type' => $method['type'],
                    'supported_currencies' => json_encode([]),
                    'minimum_amount' => null,
                    'maximum_amount' => null,
                    'icon' => null,
                    'is_active' => true,
                    'priority' => $method['priority'],
                    'updated_at' => now(),
                ]
            );
        }

        echo "Online payment methods seeded." . PHP_EOL;
    }
}
