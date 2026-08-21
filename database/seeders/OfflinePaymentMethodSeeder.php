<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfflinePaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Bank Transfer',
                'slug' => 'bank-transfer',
                'type' => 'bank_transfer',
                'priority' => 1,
            ],
            [
                'name' => 'Cash',
                'slug' => 'cash',
                'type' => 'cash',
                'priority' => 2,
            ],
            [
                'name' => 'Manual Payment',
                'slug' => 'manual-payment',
                'type' => 'manual',
                'priority' => 3,
            ],
        ];

        foreach ($methods as $method) {
            $existing = DB::table('offline_payment_methods')
                ->where('slug', $method['slug'])
                ->whereNull('deleted_at')
                ->first();

            $data = [
                'name' => $method['name'],
                'slug' => $method['slug'],
                'type' => $method['type'],
                'priority' => $method['priority'],
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('offline_payment_methods')
                    ->where('id', $existing->id)
                    ->update($data);
            } else {
                DB::table('offline_payment_methods')->insert([
                    'uuid' => (string) Str::uuid(),
                    ...$data,
                    'instructions' => null,
                    'settings' => null,
                    'is_active' => false,
                    'is_default' => false,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
