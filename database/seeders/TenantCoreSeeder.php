<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantCoreSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Core Installation
        |--------------------------------------------------------------------------
        */

        DB::table('core_installations')->updateOrInsert(
            [
                'core_name' => 'core',
            ],
            [
                'core_version' => config('esubiz_core.version', '1.0.0'),
                'status' => 'installed',
                'installed_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Core Default Resources
        |--------------------------------------------------------------------------
        */

        $resources = config('esubiz_core.resources', []);

        foreach ($resources as $resource => $definition) {
            $limit = $definition['limit'] ?? null;
            $unit = $definition['unit'] ?? null;

            DB::table('resource_entitlements')->updateOrInsert(
                [
                    'resource' => $resource,
                ],
                [
                    'base_limit' => $limit,
                    'addon_limit' => 0,
                    'is_unlimited' => false,
                    'unit' => $unit,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            DB::table('resource_usage')->updateOrInsert(
                [
                    'resource' => $resource,
                ],
                [
                    'used' => 0,
                    'limit' => $limit,
                    'is_unlimited' => false,
                    'unit' => $unit,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Unlimited Core Resources
        |--------------------------------------------------------------------------
        */

        foreach (config('esubiz_core.unlimited', []) as $resource) {
            DB::table('resource_entitlements')->updateOrInsert(
                [
                    'resource' => $resource,
                ],
                [
                    'base_limit' => null,
                    'addon_limit' => 0,
                    'is_unlimited' => true,
                    'unit' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            DB::table('resource_usage')->updateOrInsert(
                [
                    'resource' => $resource,
                ],
                [
                    'used' => 0,
                    'limit' => null,
                    'is_unlimited' => true,
                    'unit' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Default Core Theme
        |--------------------------------------------------------------------------
        */

        DB::table('site_settings')->updateOrInsert(
            [
                'key' => 'core_default_theme',
            ],
            [
                'value' => 'default',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Default Homepage
        |--------------------------------------------------------------------------
        */

        $pageId = DB::table('pages')->where(
            'slug',
            'home'
        )->value('id');

        if (!$pageId) {
            $pageId = DB::table('pages')->insertGetId([
                'title' => 'Home',
                'slug' => 'home',
                'status' => 'published',
                'content' => '',
                'is_homepage' => true,
                'settings' => json_encode([]),
                'seo' => json_encode([]),
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Default Page Builder Document
        |--------------------------------------------------------------------------
        */

        DB::table('page_builder_documents')->updateOrInsert(
            [
                'page_id' => $pageId,
            ],
            [
                'content' => json_encode([
                    'type' => 'core-default',
                    'version' => '1.0.0',
                    'sections' => [],
                ]),
                'builder_version' => '1.0.0',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Default Main Menu
        |--------------------------------------------------------------------------
        */

        $menuId = DB::table('menus')->where(
            'name',
            'Main Menu'
        )->value('id');

        if (!$menuId) {
            $menuId = DB::table('menus')->insertGetId([
                'name' => 'Main Menu',
                'location' => 'header',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Homepage Menu Item
        |--------------------------------------------------------------------------
        */

        DB::table('menu_items')->updateOrInsert(
            [
                'menu_id' => $menuId,
                'label' => 'Home',
            ],
            [
                'parent_id' => null,
                'type' => 'page',
                'url' => '/',
                'page_id' => $pageId,
                'sort_order' => 1,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Core License
        |--------------------------------------------------------------------------
        */

        $licenseKey = 'CORE-' . strtoupper(Str::random(24));

        DB::table('core_licenses')->updateOrInsert(
            [
                'product_type' => 'core',
                'product_name' => 'core',
            ],
            [
                'license_key' => $licenseKey,
                'product_version' => config('esubiz_core.version', '1.0.0'),
                'status' => 'active',
                'issued_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}
