<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoreFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            [
                'key' => 'knowledgebase',
                'name' => 'Knowledgebase',
                'category' => 'support',
                'description' => 'Central Esubiz knowledgebase for articles and documentation.',
                'type' => 'feature',
                'is_core' => true,
                'is_active' => true,
                'limits' => [
                    ['limit_key' => 'articles', 'name' => 'Knowledgebase Articles', 'value_type' => 'quantity', 'default_value' => 10, 'unit' => 'articles'],
                ],
            ],
            [
                'key' => 'crm',
                'name' => 'CRM',
                'category' => 'business',
                'description' => 'Core customer relationship management.',
                'type' => 'feature',
                'is_core' => true,
                'is_active' => true,
                'limits' => [
                    ['limit_key'=>'clients','name'=>'Clients','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'companies','name'=>'Companies','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'leads','name'=>'Leads','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'deals','name'=>'Deals','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'projects','name'=>'Projects','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'tasks','name'=>'Tasks','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'invoices','name'=>'Invoices','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'estimates','name'=>'Estimates','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'quotations','name'=>'Quotations','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'proposals','name'=>'Proposals','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'contracts','name'=>'Contracts','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'expenses','name'=>'Expenses','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'subscriptions','name'=>'Subscriptions','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'calendar','name'=>'Calendar Events','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'time_tracking','name'=>'Time Entries','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'reminders','name'=>'Reminders','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'income','name'=>'Income','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'payments','name'=>'Payments','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                    ['limit_key'=>'receipts','name'=>'Receipts','value_type'=>'quantity','default_value'=>10,'unit'=>'records'],
                ],
            ],
        ];

        foreach ($features as $definition) {
            $feature = DB::table('core_features')
                ->where('key', $definition['key'])
                ->first();

            $data = [
                'uuid' => $feature?->uuid ?? (string) Str::uuid(),
                'key' => $definition['key'],
                'name' => $definition['name'],
                'category' => $definition['category'],
                'description' => $definition['description'],
                'type' => $definition['type'],
                'is_core' => $definition['is_core'],
                'is_active' => $definition['is_active'],
                'updated_at' => now(),
            ];

            if ($feature) {
                DB::table('core_features')->where('id', $feature->id)->update($data);
                $featureId = $feature->id;
            } else {
                $featureId = DB::table('core_features')->insertGetId($data + ['created_at' => now()]);
            }

            foreach ($definition['limits'] as $limit) {
                DB::table('core_feature_limits')->updateOrInsert(
                    [
                        'core_feature_id' => $featureId,
                        'limit_key' => $limit['limit_key'],
                    ],
                    [
                        'name' => $limit['name'],
                        'value_type' => $limit['value_type'],
                        'default_value' => $limit['default_value'],
                        'unit' => $limit['unit'],
                        'is_unlimited' => false,
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        // Remove resources that belong to central Core/HR rather than CRM.
        $crm = DB::table('core_features')->where('key', 'crm')->first();

        if ($crm) {
            DB::table('core_feature_limits')
                ->where('core_feature_id', $crm->id)
                ->whereIn('limit_key', [
                    'tickets',
                    'knowledgebase',
                    'team_members',
                    'instant_messaging',
                ])
                ->delete();
        }

        echo "Esubiz Core feature configuration seeded successfully." . PHP_EOL;
        echo "CRM limits: 19" . PHP_EOL;
        echo "CRM Team Members: HR-owned" . PHP_EOL;
        echo "CRM Tickets: Central Core-owned" . PHP_EOL;
        echo "CRM Knowledgebase: Central Core-owned" . PHP_EOL;
        echo "Knowledgebase articles: 10" . PHP_EOL;
        echo "Site users: global resource, not CRM/HR limited" . PHP_EOL;
    }
}
