<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ESUBIZ_CORE_PERMISSION_SYNCHRONIZER_V1
 *
 * Synchronizes the universal Core permission registry into the
 * currently connected Core website database.
 *
 * Rules:
 *
 * - Core-owned.
 * - No SaaS/off-server branching.
 * - Never deletes unknown/existing permissions.
 * - New registered capabilities are inserted automatically.
 * - Existing permission labels may be refreshed safely.
 * - Administrator always receives every registered permission.
 * - Existing coarse V1 permissions are migrated forward into
 *   their action-level equivalents without removing legacy keys.
 */
class CorePermissionSynchronizer
{
    public function __construct(
        protected CorePermissionRegistry $registry
    ) {
    }

    public function sync(): array
    {
        $connection = 'website_tenant';

        if (
            !Schema::connection($connection)->hasTable('site_permissions') ||
            !Schema::connection($connection)->hasTable('site_roles') ||
            !Schema::connection($connection)->hasTable('site_role_permissions')
        ) {
            return [
                'synced' => 0,
                'administrator_grants' => 0,
                'legacy_grants' => 0,
            ];
        }

        $db = DB::connection($connection);

        return $db->transaction(function () use ($db) {
            $now = now();
            $synced = 0;

            foreach ($this->registry->permissions() as $definition) {
                $existing = $db
                    ->table('site_permissions')
                    ->where('key', $definition['key'])
                    ->first();

                $description = sprintf(
                    '%s — %s',
                    $definition['group_label'],
                    $definition['name']
                );

                if ($existing) {
                    $db->table('site_permissions')
                        ->where('id', $existing->id)
                        ->update([
                            'name' => $definition['name'],
                            'description' => $description,
                            'updated_at' => $now,
                        ]);
                } else {
                    $db->table('site_permissions')->insert([
                        'name' => $definition['name'],
                        'key' => $definition['key'],
                        'description' => $description,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $synced++;
            }

            /*
             * Administrator is a protected Core system role and
             * automatically receives every permission registered by
             * Core or by an installed Core-powered capability.
             */
            $administratorRoleId = $db
                ->table('site_roles')
                ->where('slug', 'administrator')
                ->value('id');

            $administratorGrants = 0;

            if ($administratorRoleId) {
                $registeredKeys = array_keys(
                    $this->registry->permissions()
                );

                $permissionIds = $db
                    ->table('site_permissions')
                    ->whereIn('key', $registeredKeys)
                    ->pluck('id');

                foreach ($permissionIds as $permissionId) {
                    $exists = $db
                        ->table('site_role_permissions')
                        ->where('role_id', $administratorRoleId)
                        ->where('permission_id', $permissionId)
                        ->exists();

                    if (!$exists) {
                        $db->table('site_role_permissions')->insert([
                            'role_id' => $administratorRoleId,
                            'permission_id' => $permissionId,
                        ]);

                        $administratorGrants++;
                    }
                }
            }

            /*
             * Forward compatibility from the first coarse RBAC model.
             *
             * We DO NOT remove the old permissions yet because live
             * Core code may still reference them.
             *
             * Instead, any role that had an old *.manage permission
             * receives its equivalent action-level permissions.
             */
            $legacyMap = [
                'users.manage' => [
                    'users.view',
                    'users.create',
                    'users.edit',
                    'users.delete',
                ],

                'roles.manage' => [
                    'roles.view',
                    'roles.create',
                    'roles.edit',
                    'roles.delete',
                ],

                'settings.manage' => [
                    'settings.view',
                    'settings.edit',
                ],

                'forms.manage' => [
                    'forms.view',
                    'forms.create',
                    'forms.edit',
                    'forms.delete',
                ],
            ];

            $legacyGrants = 0;

            foreach ($legacyMap as $legacyKey => $newKeys) {
                $legacyPermissionId = $db
                    ->table('site_permissions')
                    ->where('key', $legacyKey)
                    ->value('id');

                if (!$legacyPermissionId) {
                    continue;
                }

                $roleIds = $db
                    ->table('site_role_permissions')
                    ->where('permission_id', $legacyPermissionId)
                    ->pluck('role_id');

                if ($roleIds->isEmpty()) {
                    continue;
                }

                $newPermissions = $db
                    ->table('site_permissions')
                    ->whereIn('key', $newKeys)
                    ->pluck('id');

                foreach ($roleIds as $roleId) {
                    foreach ($newPermissions as $permissionId) {
                        $exists = $db
                            ->table('site_role_permissions')
                            ->where('role_id', $roleId)
                            ->where('permission_id', $permissionId)
                            ->exists();

                        if (!$exists) {
                            $db
                                ->table('site_role_permissions')
                                ->insert([
                                    'role_id' => $roleId,
                                    'permission_id' => $permissionId,
                                ]);

                            $legacyGrants++;
                        }
                    }
                }
            }

            return [
                'synced' => $synced,
                'administrator_grants' => $administratorGrants,
                'legacy_grants' => $legacyGrants,
            ];
        });
    }
}
