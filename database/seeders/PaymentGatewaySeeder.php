<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'Paystack',
                'slug' => 'paystack',
                'type' => 'card',
                'supports_recurring' => true,
                'supports_refunds' => true,
                'supports_webhooks' => true,
                'supported_currencies' => ['NGN', 'GHS', 'ZAR', 'USD'],
                'supported_countries' => ['NG', 'GH', 'ZA'],
                'priority' => 1,
            ],
            [
                'name' => 'Flutterwave',
                'slug' => 'flutterwave',
                'type' => 'card',
                'supports_recurring' => true,
                'supports_refunds' => true,
                'supports_webhooks' => true,
                'supported_currencies' => [
                    'NGN', 'USD', 'GBP', 'EUR',
                    'KES', 'GHS', 'ZAR', 'TZS',
                    'UGX', 'RWF', 'ZMW',
                ],
                'supported_countries' => ['NG', 'GH', 'KE', 'ZA', 'TZ', 'UG', 'RW', 'ZM'],
                'priority' => 2,
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'type' => 'card',
                'supports_recurring' => true,
                'supports_refunds' => true,
                'supports_webhooks' => true,
                'supported_currencies' => [
                    'USD', 'EUR', 'GBP', 'AUD', 'CAD',
                    'JPY', 'SGD', 'HKD', 'NZD', 'CHF',
                ],
                'supported_countries' => [],
                'priority' => 3,
            ],
            [
                'name' => 'NOWPayments',
                'slug' => 'nowpayments',
                'type' => 'crypto',
                'supports_recurring' => false,
                'supports_refunds' => false,
                'supports_webhooks' => true,
                'supported_currencies' => [
                    'BTC', 'ETH', 'USDT', 'USDC',
                    'LTC', 'TRX', 'BNB',
                ],
                'supported_countries' => [],
                'priority' => 4,
            ],
        ];

        foreach ($gateways as $gateway) {
            $existing = DB::table('payment_providers')
                ->where('slug', $gateway['slug'])
                ->whereNull('deleted_at')
                ->first();

            $data = [
                'name' => $gateway['name'],
                'slug' => $gateway['slug'],
                'type' => $gateway['type'],
                'supports_recurring' => $gateway['supports_recurring'],
                'supports_refunds' => $gateway['supports_refunds'],
                'supports_webhooks' => $gateway['supports_webhooks'],
                'supported_currencies' => json_encode($gateway['supported_currencies']),
                'supported_countries' => json_encode($gateway['supported_countries']),
                'priority' => $gateway['priority'],
                'is_active' => $existing?->is_active ?? false,
                'is_default' => $existing?->is_default ?? false,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('payment_providers')
                    ->where('id', $existing->id)
                    ->update($data);
            } else {
                DB::table('payment_providers')->insert([
                    'uuid' => (string) Str::uuid(),
                    ...$data,
                    'credentials' => null,
                    'settings' => json_encode([
                        'environment' => 'sandbox',
                    ]),
                    'created_at' => now(),
                ]);
            }
        }
    }
}
