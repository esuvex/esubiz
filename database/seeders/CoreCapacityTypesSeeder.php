<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreCapacityTypesSeeder extends Seeder
{
    public function run(): void
    {
        $capacities = [
            ['name' => 'Site Pages', 'slug' => 'site-pages', 'category' => 'website', 'value_type' => 'quantity', 'unit' => 'pages', 'default_value' => 10],
            ['name' => 'Site Widgets', 'slug' => 'site-widgets', 'category' => 'website', 'value_type' => 'quantity', 'unit' => 'widgets', 'default_value' => 10],
            ['name' => 'Forms', 'slug' => 'forms', 'category' => 'website', 'value_type' => 'quantity', 'unit' => 'forms', 'default_value' => 5],
            ['name' => 'Page Builder', 'slug' => 'page-builder', 'category' => 'website', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
            ['name' => 'Form Builder', 'slug' => 'form-builder', 'category' => 'website', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
            ['name' => 'SEO', 'slug' => 'seo', 'category' => 'website', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
            ['name' => 'Site Security', 'slug' => 'site-security', 'category' => 'system', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
            ['name' => 'QR Generator', 'slug' => 'qr-generator', 'category' => 'website', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],

            ['name' => 'HR', 'slug' => 'hr', 'category' => 'hr', 'value_type' => 'quantity', 'unit' => 'employees', 'default_value' => 10],
            ['name' => 'CRM', 'slug' => 'crm', 'category' => 'core_service', 'value_type' => 'quantity', 'unit' => 'clients', 'default_value' => 10],
            ['name' => 'POS', 'slug' => 'pos', 'category' => 'commerce', 'value_type' => 'quantity', 'unit' => 'terminals', 'default_value' => 10],
            ['name' => 'Tickets', 'slug' => 'tickets', 'category' => 'core_service', 'value_type' => 'quantity', 'unit' => 'tickets', 'default_value' => 10],

            ['name' => 'Authentication', 'slug' => 'authentication', 'category' => 'core_service', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
            ['name' => 'Live Chat', 'slug' => 'livechat', 'category' => 'communication', 'value_type' => 'quantity', 'unit' => 'agents', 'default_value' => 2],
            ['name' => 'Email Box', 'slug' => 'email-box', 'category' => 'communication', 'value_type' => 'quantity', 'unit' => 'mailboxes', 'default_value' => 2],

            ['name' => 'Online Payment Gateways', 'slug' => 'online-payment-gateways', 'category' => 'finance', 'value_type' => 'quantity', 'unit' => 'gateways', 'default_value' => 1],
            ['name' => 'Offline Payment Methods', 'slug' => 'offline-payment-methods', 'category' => 'finance', 'value_type' => 'boolean', 'unit' => 'unlimited', 'default_value' => 1],

            ['name' => 'Storage', 'slug' => 'storage', 'category' => 'system', 'value_type' => 'storage', 'unit' => 'GB', 'default_value' => 1],
            ['name' => 'Bandwidth', 'slug' => 'bandwidth', 'category' => 'system', 'value_type' => 'storage', 'unit' => 'GB', 'default_value' => 40],

            ['name' => 'Notifications', 'slug' => 'notifications', 'category' => 'communication', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
            ['name' => 'Activity Logs', 'slug' => 'activity-logs', 'category' => 'system', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
            ['name' => 'Media Library', 'slug' => 'media-library', 'category' => 'website', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
            ['name' => 'Site Navigation', 'slug' => 'site-navigation', 'category' => 'website', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
            ['name' => 'Site Settings', 'slug' => 'site-settings', 'category' => 'website', 'value_type' => 'boolean', 'unit' => null, 'default_value' => 1],
        ];

        foreach ($capacities as $capacity) {
            DB::table('capacity_types')->updateOrInsert(
                ['slug' => $capacity['slug']],
                [
                    'name' => $capacity['name'],
                    'description' => 'Core Esubiz website capability.',
                    'category' => $capacity['category'],
                    'value_type' => $capacity['value_type'],
                    'unit' => $capacity['unit'],
                    'default_value' => $capacity['default_value'],
                    'can_purchase' => true,
                    'is_core' => true,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
