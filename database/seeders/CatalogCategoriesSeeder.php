<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Core',
                'slug' => 'core',
                'description' => 'Esubiz Core software and core platform products.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Website',
                'slug' => 'website',
                'description' => 'Preconfigured Esubiz website types and website products.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Themes',
                'slug' => 'themes',
                'description' => 'Website themes for Esubiz websites.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Modules',
                'slug' => 'modules',
                'description' => 'Modules that extend website functionality.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Addons',
                'slug' => 'addons',
                'description' => 'Additional website capabilities and resource upgrades.',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('catalog_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'parent_id' => null,
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'is_featured' => false,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
