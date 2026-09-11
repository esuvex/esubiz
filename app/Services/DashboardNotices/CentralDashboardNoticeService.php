<?php

namespace App\Services\DashboardNotices;

use App\Models\DashboardNotice;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CentralDashboardNoticeService
{
    /*
     * ESUBIZ_CENTRAL_DASHBOARD_NOTICE_DELIVERY_V22
     *
     * Central Dashboard Notice delivery is based only on trusted
     * server-side identity:
     *
     * - authenticated Central user ID
     * - that user's current active Central role IDs
     *
     * No client-submitted role, role name or role slug is trusted.
     */
    public function forUser(User $user): Collection
    {
        $roleIds =
            DB::table('user_roles as ur')
                ->join(
                    'roles as r',
                    'r.id',
                    '=',
                    'ur.role_id'
                )
                ->where(
                    'ur.user_id',
                    $user->id
                )
                ->where(
                    'r.is_active',
                    true
                )
                ->pluck(
                    'ur.role_id'
                )
                ->map(
                    fn ($id) => (int) $id
                )
                ->unique()
                ->values()
                ->all();

        $now = now();

        return DashboardNotice::query()
            ->where(
                'status',
                'published'
            )
            ->where(
                function ($query) use ($now) {
                    $query
                        ->whereNull('published_at')
                        ->orWhere(
                            'published_at',
                            '<=',
                            $now
                        );
                }
            )
            ->where(
                function ($query) use ($now) {
                    $query
                        ->whereNull('expires_at')
                        ->orWhere(
                            'expires_at',
                            '>',
                            $now
                        );
                }
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->filter(
                function (
                    DashboardNotice $notice
                ) use (
                    $user,
                    $roleIds
                ) {
                    if (
                        !$notice->deliversTo(
                            'central'
                        )
                    ) {
                        return false;
                    }

                    /*
                     * All Central means every authenticated
                     * Central account, including a user who has
                     * no assigned role.
                     */
                    if (
                        $notice
                            ->targetsAllCentralRoles()
                    ) {
                        return true;
                    }

                    /*
                     * Selected Central targeting uses OR:
                     *
                     * explicitly selected user
                     * OR
                     * membership of any selected active role.
                     */
                    if (
                        $notice->targetsCentralUser(
                            (int) $user->id
                        )
                    ) {
                        return true;
                    }

                    return $notice
                        ->targetsAnyCentralRole(
                            $roleIds
                        );
                }
            )
            ->values();
    }
}
