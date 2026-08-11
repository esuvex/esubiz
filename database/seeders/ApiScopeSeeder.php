<?php

namespace Database\Seeders;

use App\Models\ApiScope;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApiScopeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApiScope::firstOrCreate(
            ['slug' => 'identity.read'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Read Identity',
                'description' => 'Allows an application to read the authenticated Esubiz user identity.',
                'group' => 'Identity',
                'type' => 'read',
                'is_system' => true,
                'is_active' => true,
            ]
        );
    }
}
