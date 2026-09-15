<?php

namespace App\Services\Platform;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Schema;

class CentralAccountGateService
{
    /*
     * ESUBIZ_CENTRAL_ACCOUNT_GATE_V1
     *
     * Authentication is deliberately separate from functional
     * access.
     *
     * A Central account may sign in and enter its dashboard while
     * still blocked from other internal pages because of:
     *
     * - required Email verification
     * - required SMS verification
     * - required WhatsApp verification
     * - pending/manual role approval
     * - payment-required role approval
     * - rejected/suspended account state
     *
     * Dashboard remains available so the user can complete
     * verification/payment and see approval status.
     */
    public function __construct(
        protected CentralRegistrationAccessService $registration,
        protected CentralRoleAccessService $roles
    ) {
    }

    public function status(
        ?Authenticatable $user
    ): array {
        if (!$user) {
            return [
                'authenticated' => false,
                'dashboard_allowed' => false,
                'functions_allowed' => false,
                'blocked' => true,
                'blockers' => [
                    'authentication_required',
                ],
                'verification' => [],
                'approval' => null,
                'account_status' => null,
            ];
        }

        $blockers = [];

        $accountStatus = strtolower(
            trim(
                (string) (
                    $user->account_status
                    ?? 'active'
                )
            )
        );

        /*
         * Suspended accounts are not treated as ordinary pending
         * accounts. Middleware/login policy can later decide the
         * exact suspension experience.
         */
        $dashboardAllowed = !in_array(
            $accountStatus,
            [
                'suspended',
                'disabled',
                'blocked',
            ],
            true
        );

        if (!$dashboardAllowed) {
            $blockers[] = 'account_' . (
                $accountStatus !== ''
                    ? $accountStatus
                    : 'blocked'
            );
        }

        $verification = [
            'email' => $this->verificationState(
                $user,
                'email'
            ),

            'sms' => $this->verificationState(
                $user,
                'sms'
            ),

            'whatsapp' => $this->verificationState(
                $user,
                'whatsapp'
            ),
        ];

        foreach ($verification as $channel => $state) {
            if (
                $state['required']
                && !$state['verified']
            ) {
                $blockers[] =
                    $channel . '_verification_required';
            }
        }

        $approvalStatus =
            $this->registration->normalizeStatus(
                $user->approval_status
                    ?? null
            );

        if (
            !$this->registration->canUseFunctions(
                $approvalStatus
            )
        ) {
            $blockers[] = match (
                $approvalStatus
            ) {
                CentralRegistrationAccessService::STATUS_PAYMENT_REQUIRED =>
                    'role_payment_required',

                CentralRegistrationAccessService::STATUS_REJECTED =>
                    'role_not_approved',

                CentralRegistrationAccessService::STATUS_SUSPENDED =>
                    'role_suspended',

                default =>
                    'role_approval_pending',
            };
        }

        $functionsAllowed =
            $dashboardAllowed
            && count($blockers) === 0;

        return [
            'authenticated' => true,

            'dashboard_allowed' =>
                $dashboardAllowed,

            'functions_allowed' =>
                $functionsAllowed,

            /*
             * dashboard_only is the key middleware state:
             *
             * true = allow dashboard/status/verification routes,
             *        deny all other internal feature routes.
             */
            'dashboard_only' =>
                $dashboardAllowed
                && !$functionsAllowed,

            'blocked' =>
                !$functionsAllowed,

            'blockers' =>
                array_values(
                    array_unique(
                        $blockers
                    )
                ),

            'verification' =>
                $verification,

            'approval' => [
                'status' =>
                    $approvalStatus,

                'label' =>
                    $this->registration->statusLabel(
                        $approvalStatus
                    ),

                'approved' =>
                    $this->registration->canUseFunctions(
                        $approvalStatus
                    ),
            ],

            'account_status' =>
                $accountStatus,

            'account_family' =>
                $this->roles->family(
                    $user
                ),
        ];
    }

    public function canAccessDashboard(
        ?Authenticatable $user
    ): bool {
        return (bool) (
            $this->status($user)[
                'dashboard_allowed'
            ]
            ?? false
        );
    }

    public function canUseInternalFunctions(
        ?Authenticatable $user
    ): bool {
        /*
         * ESUBIZ_CENTRAL_ADMIN_GATE_AUTHORITY_V17
         *
         * Existing Admin context authority overrides stale
         * account_role data. Mode switching changes UI experience,
         * not the underlying Admin permission level.
         */
        $centralGateUser = $user;

        if ($centralGateUser) {
            $centralRoleAccess = app(
                \App\Services\Platform\CentralRoleAccessService::class
            );

            $centralContexts = collect(
                $centralRoleAccess->visibleContexts(
                    $centralGateUser
                )
            )
                ->map(
                    fn ($context) => strtolower(
                        trim((string) $context)
                    )
                );

            if (
                $centralRoleAccess
                    ->isAdminFamily($centralGateUser)
                || $centralContexts->contains('admin')
            ) {
                return true;
            }
        }


        /*
         * ESUBIZ_CENTRAL_ADMIN_GATE_BYPASS_V12
         *
         * Admin-family accounts retain full backend authority.
         * User/Developer Mode remains an experience/context switch,
         * not a permission downgrade.
         */
        $centralGateUser = $user;

        if (
            app(
                \App\Services\Platform\CentralRoleAccessService::class
            )->isAdminFamily($centralGateUser)
        ) {
            return true;
        }

        return (bool) (
            $this->status($user)[
                'functions_allowed'
            ]
            ?? false
        );
    }

    public function isDashboardOnly(
        ?Authenticatable $user
    ): bool {
        return (bool) (
            $this->status($user)[
                'dashboard_only'
            ]
            ?? false
        );
    }

    public function blockers(
        ?Authenticatable $user
    ): array {
        return (array) (
            $this->status($user)[
                'blockers'
            ]
            ?? []
        );
    }

    protected function verificationState(
        Authenticatable $user,
        string $channel
    ): array {
        $required = match ($channel) {
            'email' =>
                $this->registration
                    ->emailVerificationEnabled(),

            'sms' =>
                $this->registration
                    ->smsVerificationEnabled(),

            'whatsapp' =>
                $this->registration
                    ->whatsappVerificationEnabled(),

            default =>
                false,
        };

        $verified = match ($channel) {
            'email' =>
                $this->timestampVerified(
                    $user,
                    'email_verified_at'
                ),

            'sms' =>
                $this->timestampVerified(
                    $user,
                    'sms_verified_at'
                ),

            'whatsapp' =>
                $this->timestampVerified(
                    $user,
                    'whatsapp_verified_at'
                ),

            default =>
                false,
        };

        /*
         * A disabled verification channel never blocks access.
         */
        return [
            'required' => $required,
            'verified' =>
                !$required
                    ? true
                    : $verified,
        ];
    }

    protected function timestampVerified(
        Authenticatable $user,
        string $field
    ): bool {
        try {
            if (
                method_exists(
                    $user,
                    'getTable'
                )
                && !Schema::hasColumn(
                    $user->getTable(),
                    $field
                )
            ) {
                return false;
            }
        } catch (\Throwable $exception) {
            return false;
        }

        return !empty(
            $user->{$field}
                ?? null
        );
    }
}
