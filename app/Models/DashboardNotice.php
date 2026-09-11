<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardNotice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tenant_ids' => 'array',
        'delivery_channels' => 'array',
        'central_role_ids' => 'array',
            'central_user_ids' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'dismissible' => 'boolean',
        'rotation_enabled' => 'boolean',
        'rotation_seconds' => 'integer',
    ];

    public function isActive(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $now = now();

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function targetsAllTenants(): bool
    {
        return $this->target_type === 'all';
    }

    public function targetsTenant(int $websiteId): bool
    {
        if ($this->targetsAllTenants()) {
            return true;
        }

        return in_array(
            $websiteId,
            array_map('intval', $this->tenant_ids ?? []),
            true
        );
    }


    /*
     * ============================================================
     * ESUBIZ_DASHBOARD_NOTICE_CENTRAL_ROLE_FOUNDATION_V15
     * ============================================================
     */

    public function deliveryChannels(): array
    {
        $channels = array_values(
            array_filter(
                array_map(
                    'strval',
                    $this->delivery_channels ?? []
                )
            )
        );

        if (!empty($channels)) {
            return $channels;
        }

        return match ((string) $this->delivery_scope) {
            'saas' => ['saas'],
            'off_server' => ['off_server'],
            default => ['saas', 'off_server'],
        };
    }

    public function deliversTo(string $channel): bool
    {
        return in_array(
            $channel,
            $this->deliveryChannels(),
            true
        );
    }

    public function targetsAllCentralRoles(): bool
    {
        return ($this->central_target_type ?? 'all') === 'all';
    }

    public function targetsCentralRole(int $roleId): bool
    {
        if (!$this->deliversTo('central')) {
            return false;
        }

        if ($this->targetsAllCentralRoles()) {
            return true;
        }

        return in_array(
            $roleId,
            array_map(
                'intval',
                $this->central_role_ids ?? []
            ),
            true
        );
    }

    public function targetsAnyCentralRole(array $roleIds): bool
    {
        if (!$this->deliversTo('central')) {
            return false;
        }

        if ($this->targetsAllCentralRoles()) {
            return true;
        }

        $allowed = array_map(
            'intval',
            $this->central_role_ids ?? []
        );

        foreach ($roleIds as $roleId) {
            if (
                in_array(
                    (int) $roleId,
                    $allowed,
                    true
                )
            ) {
                return true;
            }
        }

        return false;
    }


    /*
     * ESUBIZ_DASHBOARD_NOTICE_CENTRAL_USERS_V20
     *
     * Explicit Central users are stored independently from roles.
     * Central delivery later matches selected user OR selected role.
     */
    public function targetsCentralUser(int $userId): bool
    {
        return in_array(
            $userId,
            array_map(
                'intval',
                $this->central_user_ids ?? []
            ),
            true
        );
    }

}
