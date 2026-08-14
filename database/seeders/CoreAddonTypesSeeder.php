<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreAddonTypesSeeder extends Seeder
{
    public function run(): void
    {
        $addons = [

            // Quantity-based Add-ons
            [
                'name' => 'Email Boxes',
                'slug' => 'email-boxes',
                'category' => 'communication',
                'value_type' => 'quantity',
                'unit' => 'mailboxes',
                'default_value' => 2,
            ],

            [
                'name' => 'HR',
                'slug' => 'hr',
                'category' => 'hr',
                'value_type' => 'quantity',
                'unit' => 'employees',
                'default_value' => 10,
            ],

            [
                'name' => 'CRM',
                'slug' => 'crm',
                'category' => 'core_service',
                'value_type' => 'quantity',
                'unit' => 'clients',
                'default_value' => 10,
            ],

            [
                'name' => 'Online Payment Gateway',
                'slug' => 'online-payment-gateway',
                'category' => 'finance',
                'value_type' => 'quantity',
                'unit' => 'gateways',
                'default_value' => 1,
            ],

            [
                'name' => 'Live Chat',
                'slug' => 'live-chat',
                'category' => 'communication',
                'value_type' => 'quantity',
                'unit' => 'agents',
                'default_value' => 2,
            ],

            [
                'name' => 'POS',
                'slug' => 'pos',
                'category' => 'commerce',
                'value_type' => 'quantity',
                'unit' => 'terminals',
                'default_value' => 10,
            ],

            // Premium Add-ons
            [
                'name' => 'Page Builder',
                'slug' => 'page-builder',
                'category' => 'website',
                'value_type' => 'boolean',
                'unit' => null,
                'default_value' => 1,
            ],

            [
                'name' => 'Form Builder',
                'slug' => 'form-builder',
                'category' => 'website',
                'value_type' => 'boolean',
                'unit' => null,
                'default_value' => 1,
            ],

            [
                'name' => 'Widget',
                'slug' => 'widget',
                'category' => 'website',
                'value_type' => 'boolean',
                'unit' => null,
                'default_value' => 1,
            ],

            [
                'name' => 'Security',
                'slug' => 'security',
                'category' => 'system',
                'value_type' => 'boolean',
                'unit' => null,
                'default_value' => 1,
            ],

            [
                'name' => '360 Degree Panorama',
                'slug' => '360-degree-panorama',
                'category' => 'website',
                'value_type' => 'boolean',
                'unit' => null,
                'default_value' => 1,
            ],

            // Expandable Add-ons
            [
                'name' => 'QR Code Generator',
                'slug' => 'qr-code-generator',
                'category' => 'website',
                'value_type' => 'quantity',
                'unit' => 'codes',
                'default_value' => 1,
            ],

            [
                'name' => 'Storage',
                'slug' => 'storage',
                'category' => 'system',
                'value_type' => 'storage',
                'unit' => 'GB',
                'default_value' => 1,
            ],

            [
                'name' => 'Bandwidth',
                'slug' => 'bandwidth',
                'category' => 'system',
                'value_type' => 'storage',
                'unit' => 'GB',
                'default_value' => 40,
            ],
        ];

        $allowedSlugs = collect($addons)
            ->pluck('slug')
            ->all();

        DB::table('addon_types')
            ->whereNotIn('slug', $allowedSlugs)
            ->update([
                'can_purchase' => false,
                'is_core' => false,
                'is_active' => false,
                'updated_at' => now(),
            ]);

        foreach ($addons as $addon) {
            DB::table('addon_types')->updateOrInsert(
                ['slug' => $addon['slug']],
                [
                    'name' => $addon['name'],
                    'description' => 'Esubiz Add-on capability that can extend the Core CMS.',
                    'category' => $addon['category'],
                    'value_type' => $addon['value_type'],
                    'unit' => $addon['unit'],
                    'default_value' => $addon['default_value'],
                    'can_purchase' => true,
                    'is_core' => false,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
