<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * ESUBIZ_CORE_PARTNER_INVESTOR_FOUNDATION_V1
     *
     * Core-owned Partners / Investors architecture.
     *
     * Head Administrator controls:
     * - partner/investor assignment
     * - investment percentage
     * - Gross Profit or Net Profit earning basis
     *
     * Partners / Investors receive a dedicated restricted dashboard.
     *
     * Actual Gross/Net Profit values are NOT duplicated here.
     * The dashboard will later read the canonical Core financial source.
     */
    public function up(): void
    {
        $schema = Schema::connection('website_tenant');
        $db = DB::connection('website_tenant');

        if (
            !$schema->hasTable('site_roles')
            || !$schema->hasTable('site_permissions')
            || !$schema->hasTable('site_role_permissions')
            || !$schema->hasTable('site_user_roles')
            || !$schema->hasTable('site_users')
        ) {
            throw new RuntimeException(
                'Core RBAC must be installed before Partners / Investors.'
            );
        }

        /*
         * Protected Core system role.
         */
        $partnerRoleId = $db
            ->table('site_roles')
            ->where('slug', 'partners_investors')
            ->value('id');

        if (!$partnerRoleId) {
            $partnerRoleId = $db
                ->table('site_roles')
                ->insertGetId([
                    'name' => 'Partners / Investors',
                    'slug' => 'partners_investors',
                    'description' =>
                        'Partners and investors with access to their dedicated investment earnings dashboard.',
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        /*
         * Core permissions.
         */
        $permissions = [
            [
                'name' => 'View Partner Dashboard',
                'key' => 'partners.dashboard.view',
                'description' =>
                    'View the dedicated Partners / Investors dashboard.',
            ],
            [
                'name' => 'Manage Partner Investments',
                'key' => 'partners.manage',
                'description' =>
                    'Configure partner investment percentage and profit basis.',
            ],
        ];

        $permissionIds = [];

        foreach ($permissions as $permission) {
            $permissionId = $db
                ->table('site_permissions')
                ->where('key', $permission['key'])
                ->value('id');

            if (!$permissionId) {
                $permissionId = $db
                    ->table('site_permissions')
                    ->insertGetId([
                        'name' => $permission['name'],
                        'key' => $permission['key'],
                        'description' => $permission['description'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            $permissionIds[$permission['key']] = (int) $permissionId;
        }

        /*
         * Partner role receives dashboard access only.
         */
        $db->table('site_role_permissions')->updateOrInsert(
            [
                'role_id' => (int) $partnerRoleId,
                'permission_id' =>
                    $permissionIds['partners.dashboard.view'],
            ],
            [
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        /*
         * Administrator remains the controlling Head Admin role
         * and receives both partner permissions.
         */
        $administratorRoleId = $db
            ->table('site_roles')
            ->where('slug', 'administrator')
            ->value('id');

        if ($administratorRoleId) {
            foreach ($permissionIds as $permissionId) {
                $db->table('site_role_permissions')->updateOrInsert(
                    [
                        'role_id' => (int) $administratorRoleId,
                        'permission_id' => (int) $permissionId,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        /*
         * Partner financial configuration.
         *
         * One active investment configuration per Core user.
         * user_id deliberately uses an indexed BIGINT UNSIGNED without
         * forcing a legacy database FK onto site_users.
         */
        if (!$schema->hasTable('site_partner_investments')) {
            $schema->create(
                'site_partner_investments',
                function (Blueprint $table) {
                    $table->id();

                    $table
                        ->unsignedBigInteger('user_id')
                        ->unique('uq_spi_user');

                    $table
                        ->decimal('investment_percentage', 8, 4)
                        ->default(0);

                    $table
                        ->string('profit_basis', 20)
                        ->default('net');

                    $table
                        ->boolean('is_active')
                        ->default(true);

                    $table
                        ->text('notes')
                        ->nullable();

                    $table->timestamps();

                    $table->index(
                        ['profit_basis', 'is_active'],
                        'idx_spi_basis_active'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('website_tenant');
        $db = DB::connection('website_tenant');

        if ($schema->hasTable('site_partner_investments')) {
            $schema->drop('site_partner_investments');
        }

        if (
            $schema->hasTable('site_roles')
            && $schema->hasTable('site_user_roles')
        ) {
            $roleId = $db
                ->table('site_roles')
                ->where('slug', 'partners_investors')
                ->value('id');

            if ($roleId) {
                $db
                    ->table('site_user_roles')
                    ->where('role_id', $roleId)
                    ->delete();

                if ($schema->hasTable('site_role_permissions')) {
                    $db
                        ->table('site_role_permissions')
                        ->where('role_id', $roleId)
                        ->delete();
                }

                $db
                    ->table('site_roles')
                    ->where('id', $roleId)
                    ->delete();
            }
        }

        if ($schema->hasTable('site_permissions')) {
            $permissionIds = $db
                ->table('site_permissions')
                ->whereIn('key', [
                    'partners.dashboard.view',
                    'partners.manage',
                ])
                ->pluck('id');

            if (
                $permissionIds->isNotEmpty()
                && $schema->hasTable('site_role_permissions')
            ) {
                $db
                    ->table('site_role_permissions')
                    ->whereIn('permission_id', $permissionIds)
                    ->delete();
            }

            $db
                ->table('site_permissions')
                ->whereIn('key', [
                    'partners.dashboard.view',
                    'partners.manage',
                ])
                ->delete();
        }
    }
};
