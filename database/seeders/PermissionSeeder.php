<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            // Identity Core
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'Identity'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'group' => 'Identity'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'group' => 'Identity'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'group' => 'Identity'],

            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'group' => 'Identity'],
            ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'group' => 'Identity'],

            // Website Builder Core
            ['name' => 'Manage Websites', 'slug' => 'websites.manage', 'group' => 'Website Builder'],
            ['name' => 'Publish Websites', 'slug' => 'websites.publish', 'group' => 'Website Builder'],

            // Business Core
            ['name' => 'Access CRM', 'slug' => 'crm.access', 'group' => 'Business'],
            ['name' => 'Access HR', 'slug' => 'hr.access', 'group' => 'Business'],

            // Finance Core
            ['name' => 'Access Finance', 'slug' => 'finance.access', 'group' => 'Finance'],

            // Marketplace Core
            ['name' => 'Access Marketplace', 'slug' => 'marketplace.access', 'group' => 'Marketplace'],

            // AI Core
            ['name' => 'Access AI', 'slug' => 'ai.access', 'group' => 'AI'],

            // Developer Core
            ['name' => 'Developer Console', 'slug' => 'developer.console', 'group' => 'Developer'],
            ['name' => 'Build Modules', 'slug' => 'modules.build', 'group' => 'Developer'],
            ['name' => 'Build Themes', 'slug' => 'themes.build', 'group' => 'Developer'],
            ['name' => 'Manage APIs', 'slug' => 'api.manage', 'group' => 'Developer'],

        ];

        foreach ($permissions as $permission) {

            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'group' => $permission['group'],
                    'is_system' => true,
                    'is_active' => true,
                ]
            );

        }
    }
}
