<?php

namespace App\Services\Platform;

class CentralRegistrationAccessService
{
    /*
     * ESUBIZ_CENTRAL_REGISTRATION_ACCESS_V1
     *
     * Public Central registration account types:
     *
     * - User              (default)
     * - Developer
     * - Investor / Partner
     *
     * Developer and Investor/Partner registration approval
     * behaviour is controlled by Central Admin:
     *
     * - automatic
     * - manual
     * - payment
     *
     * Pending/payment-required accounts may authenticate and
     * access their dashboard/status area, but protected account
     * functionality stays locked until approval is complete.
     */
    public const ROLE_USER = 'user';
    public const ROLE_DEVELOPER = 'developer';
    public const ROLE_PARTNER_INVESTOR = 'partner_investor';

    public const APPROVAL_AUTOMATIC = 'automatic';
    public const APPROVAL_MANUAL = 'manual';
    public const APPROVAL_PAYMENT = 'payment';

    public const STATUS_APPROVED = 'approved';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAYMENT_REQUIRED = 'payment_required';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    public function __construct(
        protected CentralSiteSettingsService $settings
    ) {
    }

    public function publicRoles(): array
    {
        return [
            self::ROLE_USER => [
                'value' => self::ROLE_USER,
                'label' => 'User',
                'description' =>
                    'Create a standard Esubiz account.',
                'default' => true,
                'enabled' => $this->roleEnabled(
                    self::ROLE_USER
                ),
                'approval_mode' =>
                    $this->approvalMode(
                        self::ROLE_USER
                    ),
            ],

            self::ROLE_DEVELOPER => [
                'value' => self::ROLE_DEVELOPER,
                'label' => 'Developer',
                'description' =>
                    'Create websites, use developer tools and access developer features.',
                'default' => false,
                'enabled' => $this->roleEnabled(
                    self::ROLE_DEVELOPER
                ),
                'approval_mode' =>
                    $this->approvalMode(
                        self::ROLE_DEVELOPER
                    ),
            ],

            self::ROLE_PARTNER_INVESTOR => [
                'value' =>
                    self::ROLE_PARTNER_INVESTOR,
                'label' =>
                    'Investor / Partner',
                'description' =>
                    'Apply for an Esubiz investor or partner account.',
                'default' => false,
                'enabled' => $this->roleEnabled(
                    self::ROLE_PARTNER_INVESTOR
                ),
                'approval_mode' =>
                    $this->approvalMode(
                        self::ROLE_PARTNER_INVESTOR
                    ),
            ],
        ];
    }

    public function enabledPublicRoles(): array
    {
        return array_values(
            array_filter(
                $this->publicRoles(),
                static fn (array $role) =>
                    (bool) $role['enabled']
            )
        );
    }

    public function defaultRole(): string
    {
        return self::ROLE_USER;
    }

    public function normalizeRole(
        ?string $role
    ): ?string {
        if ($role === null) {
            return null;
        }

        $role = strtolower(
            trim($role)
        );

        $aliases = [
            'user' =>
                self::ROLE_USER,

            'customer' =>
                self::ROLE_USER,

            'developer' =>
                self::ROLE_DEVELOPER,

            'dev' =>
                self::ROLE_DEVELOPER,

            'investor' =>
                self::ROLE_PARTNER_INVESTOR,

            'partner' =>
                self::ROLE_PARTNER_INVESTOR,

            'investor_partner' =>
                self::ROLE_PARTNER_INVESTOR,

            'partner_investor' =>
                self::ROLE_PARTNER_INVESTOR,

            'partners_investors' =>
                self::ROLE_PARTNER_INVESTOR,
        ];

        return $aliases[$role]
            ?? null;
    }

    public function roleEnabled(
        string $role
    ): bool {
        $role = $this->normalizeRole(
            $role
        );

        if ($role === null) {
            return false;
        }

        $value = $this->settings->get(
            'auth.registration.roles.'
                . $role
                . '.enabled'
        );

        /*
         * Public registration defaults:
         *
         * User / Developer / Investor-Partner are available
         * unless Central Admin explicitly disables one.
         */
        if ($value === null || $value === '') {
            return true;
        }

        return $this->boolean(
            $value
        );
    }

    public function approvalMode(
        string $role
    ): string {
        $role = $this->normalizeRole(
            $role
        );

        if ($role === null) {
            return self::APPROVAL_MANUAL;
        }

        $configured = strtolower(
            trim(
                (string) $this->settings->get(
                    'auth.registration.roles.'
                        . $role
                        . '.approval_mode',
                    ''
                )
            )
        );

        if (
            in_array(
                $configured,
                [
                    self::APPROVAL_AUTOMATIC,
                    self::APPROVAL_MANUAL,
                    self::APPROVAL_PAYMENT,
                ],
                true
            )
        ) {
            return $configured;
        }

        /*
         * Safe defaults:
         *
         * Standard User:
         * automatic.
         *
         * Developer + Investor/Partner:
         * manual until Admin explicitly chooses otherwise.
         */
        return $role === self::ROLE_USER
            ? self::APPROVAL_AUTOMATIC
            : self::APPROVAL_MANUAL;
    }

    public function initialStatus(
        string $role
    ): string {
        return match (
            $this->approvalMode($role)
        ) {
            self::APPROVAL_AUTOMATIC =>
                self::STATUS_APPROVED,

            self::APPROVAL_PAYMENT =>
                self::STATUS_PAYMENT_REQUIRED,

            default =>
                self::STATUS_PENDING,
        };
    }

    public function canRegisterAs(
        string $role
    ): bool {
        $role = $this->normalizeRole(
            $role
        );

        return $role !== null
            && $this->roleEnabled(
                $role
            );
    }

    /*
     * Dashboard access is intentionally independent from
     * functional approval.
     *
     * A pending Developer/Investor can sign in and view the
     * dashboard, application status, payment requirement and
     * any Admin messages without using protected functionality.
     */
    public function canAccessDashboard(
        ?string $status
    ): bool {
        $status = $this->normalizeStatus(
            $status
        );

        return !in_array(
            $status,
            [
                self::STATUS_SUSPENDED,
            ],
            true
        );
    }

    public function canUseFunctions(
        ?string $status
    ): bool {
        return $this->normalizeStatus(
            $status
        ) === self::STATUS_APPROVED;
    }

    public function requiresApproval(
        string $role
    ): bool {
        return $this->approvalMode(
            $role
        ) !== self::APPROVAL_AUTOMATIC;
    }

    public function requiresPayment(
        string $role
    ): bool {
        return $this->approvalMode(
            $role
        ) === self::APPROVAL_PAYMENT;
    }

    public function normalizeStatus(
        ?string $status
    ): string {
        $status = strtolower(
            trim(
                (string) $status
            )
        );

        return in_array(
            $status,
            [
                self::STATUS_APPROVED,
                self::STATUS_PENDING,
                self::STATUS_PAYMENT_REQUIRED,
                self::STATUS_REJECTED,
                self::STATUS_SUSPENDED,
            ],
            true
        )
            ? $status
            : self::STATUS_PENDING;
    }

    public function statusLabel(
        ?string $status
    ): string {
        return match (
            $this->normalizeStatus($status)
        ) {
            self::STATUS_APPROVED =>
                'Approved',

            self::STATUS_PAYMENT_REQUIRED =>
                'Payment Required',

            self::STATUS_REJECTED =>
                'Not Approved',

            self::STATUS_SUSPENDED =>
                'Suspended',

            default =>
                'Pending Approval',
        };
    }

    /*
     * ESUBIZ_CENTRAL_REGISTRATION_FORM_POLICY_V2
     *
     * Admin may hide Account Type selection entirely.
     *
     * When hidden, public registration always creates
     * a standard User account.
     */
    public function roleSelectionEnabled(): bool
    {
        $value = $this->settings->get(
            'auth.registration.role_selection_enabled'
        );

        if ($value === null || $value === '') {
            return true;
        }

        return $this->boolean(
            $value
        );
    }

    public function requestedPublicRole(
        ?string $requestedRole
    ): string {
        if (!$this->roleSelectionEnabled()) {
            return self::ROLE_USER;
        }

        $role = $this->normalizeRole(
            $requestedRole
        );

        if (
            $role === null
            || !$this->roleEnabled($role)
        ) {
            return self::ROLE_USER;
        }

        return $role;
    }

    /*
     * Verification channels are independently controlled
     * by Central Admin.
     */
    public function emailVerificationEnabled(): bool
    {
        return $this->verificationEnabled(
            'email'
        );
    }

    public function smsVerificationEnabled(): bool
    {
        return $this->verificationEnabled(
            'sms'
        );
    }

    public function whatsappVerificationEnabled(): bool
    {
        return $this->verificationEnabled(
            'whatsapp'
        );
    }

    public function enabledVerificationChannels(): array
    {
        $channels = [];

        if ($this->emailVerificationEnabled()) {
            $channels[] = 'email';
        }

        if ($this->smsVerificationEnabled()) {
            $channels[] = 'sms';
        }

        if ($this->whatsappVerificationEnabled()) {
            $channels[] = 'whatsapp';
        }

        return $channels;
    }

    protected function verificationEnabled(
        string $channel
    ): bool {
        $value = $this->settings->get(
            'auth.registration.verification.'
                . $channel
                . '.enabled'
        );

        /*
         * Safe initial default:
         * disabled until Admin explicitly enables a channel.
         */
        if ($value === null || $value === '') {
            return false;
        }

        return $this->boolean(
            $value
        );
    }

    protected function boolean(
        mixed $value
    ): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return in_array(
            strtolower(
                trim((string) $value)
            ),
            [
                '1',
                'true',
                'yes',
                'on',
                'enabled',
            ],
            true
        );
    }
}
