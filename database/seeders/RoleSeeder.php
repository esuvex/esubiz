<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [

            [
                'name' => 'Platform Admin',
                'slug' => 'platform-admin',
                'description' => 'Full access to the entire Esubiz platform.',
            ],

            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Can build themes, modules, APIs and use developer tools.',
            ],

            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Standard Esubiz user with access based on subscription and permissions.',
            ],

        ];

        foreach ($roles as $role) {

            Role::updateOrCreate(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'is_system' => true,
                    'is_active' => true,
                ]
            );

        }
    }
}
