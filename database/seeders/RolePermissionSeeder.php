<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RolePermission::truncate();

        $platformAdmin = Role::where('slug', 'platform-admin')->first();
        $developer = Role::where('slug', 'developer')->first();
        $user = Role::where('slug', 'user')->first();

        // Platform Admin gets every permission
        foreach (Permission::all() as $permission) {

            RolePermission::create([
                'role_id' => $platformAdmin->id,
                'permission_id' => $permission->id,
            ]);

        }

        // Developer permissions
        $developerPermissions = [
            'developer.console',
            'modules.build',
            'themes.build',
            'api.manage',
            'websites.manage',
            'websites.publish',
        ];

        foreach ($developerPermissions as $slug) {

            $permission = Permission::where('slug', $slug)->first();

            if ($permission) {
                RolePermission::create([
                    'role_id' => $developer->id,
                    'permission_id' => $permission->id,
                ]);
            }

        }

        // User permissions
        $userPermissions = [
            'websites.manage',
            'websites.publish',
            'crm.access',
            'hr.access',
            'finance.access',
            'marketplace.access',
            'ai.access',
        ];

        foreach ($userPermissions as $slug) {

            $permission = Permission::where('slug', $slug)->first();

            if ($permission) {
                RolePermission::create([
                    'role_id' => $user->id,
                    'permission_id' => $permission->id,
                ]);
            }

        }
    }
}
