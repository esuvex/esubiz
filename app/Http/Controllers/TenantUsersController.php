<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TenantUsersController extends Controller
{
    protected function currentWebsite(): Website
    {
        $tenant = WebsiteTenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        return Website::query()
            ->where('id', $tenant->website_id)
            ->where('status', 'active')
            ->where('user_enabled', true)
            ->firstOrFail();
    }

    protected function authorizeCms(): Website
    {
        $website = $this->currentWebsite();

        $websiteId = (int) $website->id;

        $authenticated =
            session()->get(
                "tenant_cms_sites.{$websiteId}.authenticated"
            ) === true
            || (
                session()->get('tenant_cms_authenticated') === true
                && (int) session()->get('tenant_cms_website_id')
                    === $websiteId
            );

        if (!$authenticated) {
            session()->put(
                'url.intended',
                request()->fullUrl()
            );

            redirect()->to('/login')->send();
            exit;
        }

        return $website;
    }

    protected function db()
    {
        return DB::connection('website_tenant');
    }

    protected function ensureSchema(): void
    {
        $schema = Schema::connection('website_tenant');

        abort_unless(
            $schema->hasTable('site_users')
            && $schema->hasTable('site_roles')
            && $schema->hasTable('site_permissions')
            && $schema->hasTable('site_user_roles')
            && $schema->hasTable('site_role_permissions'),
            500,
            'Core Users schema is unavailable.'
        );
    }

    protected function currentUserId(
        Website $website
    ): ?int {
        $websiteId = (int) $website->id;

        $userId =
            session()->get(
                "tenant_cms_sites.{$websiteId}.user_id"
            )
            ?? session()->get('tenant_cms_user_id');

        return $userId ? (int) $userId : null;
    }

    public function index()
    {
        app(\App\Services\Core\CorePermissionService::class)->authorize('users.view');

        $website = $this->authorizeCms();

        $this->ensureSchema();

        $db = $this->db();

        $users = $db->table('site_users')
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($db) {
                $user->roles = $db
                    ->table('site_user_roles')
                    ->join(
                        'site_roles',
                        'site_roles.id',
                        '=',
                        'site_user_roles.role_id'
                    )
                    ->where(
                        'site_user_roles.user_id',
                        $user->id
                    )
                    ->orderBy('site_roles.name')
                    ->get([
                        'site_roles.id',
                        'site_roles.name',
                        'site_roles.slug',
                    ]);

                return $user;
            });

        $currentUserId =
            $this->currentUserId($website);

        /*
         * ESUBIZ_USERS_TABLE_V84
         *
         * Live roles are supplied to the Users table so the
         * role filter is never hard-coded. Any role created
         * through Core Roles automatically becomes available.
         */
        $roles = $db
            ->table('site_roles')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
            ]);

        return view(
            'tenant.admin.users.index',
            compact(
                'website',
                'users',
                'roles',
                'currentUserId'
            )
        );
    }

    public function create()
    {
        app(\App\Services\Core\CorePermissionService::class)->authorize('users.create');

        $website = $this->authorizeCms();

        $this->ensureSchema();

        $roles = $this->db()
            ->table('site_roles')
            ->orderBy('name')
            ->get();

        return view(
            'tenant.admin.users.form',
            [
                'website' => $website,
                'user' => null,
                'roles' => $roles,
                'selectedRoleIds' => [],
                'partnerRoleId' => $this->partnerRoleId(),
                'partnerInvestment' => null,
                'canManagePartners' => app(
                    \App\Services\Core\CorePermissionService::class
                )->can('partners.manage'),
            ]
        );
    }

    public function store(Request $request)
    {
        app(\App\Services\Core\CorePermissionService::class)->authorize('users.create');

        $website = $this->authorizeCms();

        $this->ensureSchema();

        $db = $this->db();

        /*
         * ESUBIZ_CORE_PARTNER_MANAGEMENT_WIRED_V1
         *
         * Partner / Investor assignment is protected separately
         * from ordinary user-role management.
         */
        if ($this->requestedPartnerRole($request)) {
            app(
                \App\Services\Core\CorePermissionService::class
            )->authorize('partners.manage');
        }

        $validatedPartner =
            $this->validatePartnerInvestment($request);


        $roleIds = $db->table('site_roles')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'country_code' => [
                'required',
                'string',
                'size:2',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9]+$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'roles' => [
                'nullable',
                'array',
            ],
            'roles.*' => [
                'integer',
                Rule::in($roleIds),
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $email = strtolower(
            trim($data['email'])
        );

        $countryCode = strtoupper(
            trim(
                (string) (
                    $data['country_code']
                    ?? 'NG'
                )
            )
        );

        if ($countryCode === '') {
            $countryCode = 'NG';
        }

        $countryCatalog = app(
            \App\Services\Core\CorePhoneCountryCatalog::class
        );

        if (!$countryCatalog->findByCountryCode($countryCode)) {
            return back()
                ->withInput()
                ->withErrors([
                    'country_code' =>
                        'Please select a valid country.',
                ]);
        }

        $allowedCountryCodes =
            $website->allowed_country_codes
                ?? ['ALL'];

        if (is_string($allowedCountryCodes)) {
            $decodedAllowedCountries =
                json_decode(
                    $allowedCountryCodes,
                    true
                );

            $allowedCountryCodes =
                is_array($decodedAllowedCountries)
                    ? $decodedAllowedCountries
                    : ['ALL'];
        }

        if (
            is_array($allowedCountryCodes)
            && !in_array(
                'ALL',
                $allowedCountryCodes,
                true
            )
            && !in_array(
                $countryCode,
                $allowedCountryCodes,
                true
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'country_code' =>
                        'The selected country is not available for this website.',
                ]);
        }

        if (
            $db->table('site_users')
                ->whereRaw(
                    'LOWER(email) = ?',
                    [$email]
                )
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' =>
                        'A Core user already uses this email address.',
                ]);
        }

        $userId = null;

        $db->transaction(function () use (
            $db,
            $data,
            $email,
            $countryCode,
            &$userId
        ) {
            $now = now();

            $userId = $db
                ->table('site_users')
                ->insertGetId([
                    'name' => trim($data['name']),
                    'email' => $email,
                    'country_code' => $countryCode,
                    'phone' =>
                        isset($data['phone'])
                        && trim(
                            (string) $data['phone']
                        ) !== ''
                            ? trim(
                                (string) $data['phone']
                            )
                            : null,
                    'password' =>
                        Hash::make(
                            $data['password']
                        ),
                    'is_active' =>
                        (bool) (
                            $data['is_active']
                            ?? false
                        ),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            foreach (
                array_unique(
                    array_map(
                        'intval',
                        $data['roles'] ?? []
                    )
                )
                as $roleId
            ) {
                $db->table('site_user_roles')
                    ->insert([
                        'user_id' => $userId,
                        'role_id' => $roleId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
            }
        });

        $this->syncPartnerInvestment(
            (int) $userId,
            $request,
            $validatedPartner
        );



        return redirect()
            ->route(
                'tenant.cms.users.edit',
                [
                    'subdomain' =>
                        $website->subdomain,
                    'user' => $userId,
                ]
            )
            ->with(
                'success',
                'Core user created successfully.'
            );
    }

    public function edit(
        string $subdomain,
        int $user
    ) {
        app(\App\Services\Core\CorePermissionService::class)->authorize('users.edit');

        $website = $this->authorizeCms();

        $this->ensureSchema();

        $db = $this->db();

        $userRecord = $db
            ->table('site_users')
            ->where('id', $user)
            ->first();

        abort_unless($userRecord, 404);

        $roles = $db->table('site_roles')
            ->orderBy('name')
            ->get();

        $selectedRoleIds =
            $db->table('site_user_roles')
                ->where('user_id', $user)
                ->pluck('role_id')
                ->map(fn ($id) => (int) $id)
                ->all();

        return view(
            'tenant.admin.users.form',
            [
                'website' => $website,
                'user' => $userRecord,
                'roles' => $roles,
                'selectedRoleIds' =>
                    $selectedRoleIds,
                'partnerRoleId' => $this->partnerRoleId(),
                'partnerInvestment' =>
                    $this->partnerInvestmentForUser(
                        (int) $user
                    ),
                'canManagePartners' => app(
                    \App\Services\Core\CorePermissionService::class
                )->can('partners.manage'),
            ]
        );
    }

    public function update(
        Request $request,
        string $subdomain,
        int $user
    ) {
        app(\App\Services\Core\CorePermissionService::class)->authorize('users.edit');


        $this->assertAdministratorUpdateIsSafe(
            $request,
            (int) $user
        );

        $website = $this->authorizeCms();

        $this->ensureSchema();

        $db = $this->db();

        $partnerRoleId = $this->partnerRoleId();

        $currentlyPartner = false;

        if ($partnerRoleId) {
            $currentlyPartner = $db
                ->table('site_user_roles')
                ->where('user_id', $user)
                ->where('role_id', $partnerRoleId)
                ->exists();
        }

        $requestedPartner =
            $this->requestedPartnerRole($request);

        /*
         * Both assigning and removing the protected Partner role,
         * and changing an existing Partner configuration, require
         * partners.manage.
         */
        if ($currentlyPartner || $requestedPartner) {
            app(
                \App\Services\Core\CorePermissionService::class
            )->authorize('partners.manage');
        }

        $validatedPartner =
            $this->validatePartnerInvestment($request);


        $userRecord = $db
            ->table('site_users')
            ->where('id', $user)
            ->first();

        abort_unless($userRecord, 404);

        $roleIds = $db->table('site_roles')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'country_code' => [
                'required',
                'string',
                'size:2',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9]+$/',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'roles' => [
                'nullable',
                'array',
            ],
            'roles.*' => [
                'integer',
                Rule::in($roleIds),
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $email = strtolower(
            trim($data['email'])
        );

        $countryCode = strtoupper(
            trim(
                (string) (
                    $data['country_code']
                    ?? 'NG'
                )
            )
        );

        if ($countryCode === '') {
            $countryCode = 'NG';
        }

        $countryCatalog = app(
            \App\Services\Core\CorePhoneCountryCatalog::class
        );

        if (!$countryCatalog->findByCountryCode($countryCode)) {
            return back()
                ->withInput()
                ->withErrors([
                    'country_code' =>
                        'Please select a valid country.',
                ]);
        }

        $allowedCountryCodes =
            $website->allowed_country_codes
                ?? ['ALL'];

        if (is_string($allowedCountryCodes)) {
            $decodedAllowedCountries =
                json_decode(
                    $allowedCountryCodes,
                    true
                );

            $allowedCountryCodes =
                is_array($decodedAllowedCountries)
                    ? $decodedAllowedCountries
                    : ['ALL'];
        }

        if (
            is_array($allowedCountryCodes)
            && !in_array(
                'ALL',
                $allowedCountryCodes,
                true
            )
            && !in_array(
                $countryCode,
                $allowedCountryCodes,
                true
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'country_code' =>
                        'The selected country is not available for this website.',
                ]);
        }

        $emailExists = $db
            ->table('site_users')
            ->whereRaw(
                'LOWER(email) = ?',
                [$email]
            )
            ->where('id', '<>', $user)
            ->exists();

        if ($emailExists) {

        $this->syncPartnerInvestment(
            (int) $user,
            $request,
            $validatedPartner
        );


            return back()
                ->withInput()
                ->withErrors([
                    'email' =>
                        'Another Core user already uses this email address.',
                ]);
        }

        $currentUserId =
            $this->currentUserId($website);

        if (
            $currentUserId === $user
            && !(bool) (
                $data['is_active']
                ?? false
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'is_active' =>
                        'You cannot deactivate your own Core account.',
                ]);
        }

        $db->transaction(function () use (
            $db,
            $data,
            $email,
            $countryCode,
            $user
        ) {
            $updates = [
                'name' =>
                    trim($data['name']),
                'email' =>
                    $email,
                'country_code' =>
                    $countryCode,
                'phone' =>
                    isset($data['phone'])
                    && trim(
                        (string) $data['phone']
                    ) !== ''
                        ? trim(
                            (string) $data['phone']
                        )
                        : null,
                'is_active' =>
                    (bool) (
                        $data['is_active']
                        ?? false
                    ),
                'updated_at' => now(),
            ];

            if (!empty($data['password'])) {
                $updates['password'] =
                    Hash::make(
                        $data['password']
                    );
            }

            $db->table('site_users')
                ->where('id', $user)
                ->update($updates);

            $db->table('site_user_roles')
                ->where('user_id', $user)
                ->delete();

            $now = now();

            foreach (
                array_unique(
                    array_map(
                        'intval',
                        $data['roles'] ?? []
                    )
                )
                as $roleId
            ) {
                $db->table('site_user_roles')
                    ->insert([
                        'user_id' => $user,
                        'role_id' => $roleId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
            }
        });

        /*
         * ESUBIZ_PARTNER_EDIT_PERSISTENCE_V30
         *
         * The normal Core Save User workflow is authoritative for
         * Partner / Investor settings shown on the user edit page.
         *
         * Data is persisted to the existing site_partner_investments
         * table and is therefore read back by partnerInvestmentForUser().
         */
        if ($requestedPartner) {
            $partnerNow = now();

            $existingPartnerCreatedAt = $db
                ->table('site_partner_investments')
                ->where('user_id', $user)
                ->value('created_at');

            $db
                ->table('site_partner_investments')
                ->updateOrInsert(
                    [
                        'user_id' => $user,
                    ],
                    [
                        'investment_percentage' =>
                            round(
                                (float) (
                                    $validatedPartner[
                                        'partner_investment_percentage'
                                    ] ?? 0
                                ),
                                2
                            ),

                        'profit_basis' =>
                            $validatedPartner[
                                'partner_profit_basis'
                            ] ?? 'net',

                        'is_active' =>
                            ((string) $request->input('partner_is_active', '0') === '1'),

                        'notes' =>
                            array_key_exists(
                                'partner_notes',
                                $validatedPartner
                            )
                                ? (
                                    trim(
                                        (string) $validatedPartner[
                                            'partner_notes'
                                        ]
                                    ) !== ''
                                        ? trim(
                                            (string) $validatedPartner[
                                                'partner_notes'
                                            ]
                                        )
                                        : null
                                )
                                : null,

                        'created_at' =>
                            $existingPartnerCreatedAt
                                ?: $partnerNow,

                        'updated_at' =>
                            $partnerNow,
                    ]
                );
        }

        return back()->with(
            'success',
            'Core user updated successfully.'
        );
    }

    public function destroy(
        string $subdomain,
        int $user
    ) {
        app(\App\Services\Core\CorePermissionService::class)->authorize('users.delete');


        $this->assertAdministratorDeleteIsSafe(
            (int) $user
        );

        $website = $this->authorizeCms();

        $this->ensureSchema();

        $currentUserId =
            $this->currentUserId($website);

        abort_if(
            $currentUserId === $user,
            422,
            'You cannot delete your own Core account.'
        );

        $deleted = $this->db()
            ->table('site_users')
            ->where('id', $user)
            ->delete();

        abort_unless($deleted, 404);

        return redirect()
            ->route(
                'tenant.cms.users.index',
                [
                    'subdomain' =>
                        $website->subdomain,
                ]
            )
            ->with(
                'success',
                'Core user deleted successfully.'
            );
    }

    /*
     * ESUBIZ_CORE_LAST_ADMIN_PROTECTION_V1
     *
     * Core must always retain at least one active Administrator.
     * These checks are Core-owned and contain no deployment-specific logic.
     */
    protected function administratorRoleId(): ?int
    {
        $id = $this->db()
            ->table('site_roles')
            ->where('slug', 'administrator')
            ->value('id');

        return $id ? (int) $id : null;
    }

    protected function userHasAdministratorRole(int $userId): bool
    {
        $roleId = $this->administratorRoleId();

        if (!$roleId) {
            return false;
        }

        return $this->db()
            ->table('site_user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();
    }

    protected function activeAdministratorCount(): int
    {
        $roleId = $this->administratorRoleId();

        if (!$roleId) {
            return 0;
        }

        return (int) $this->db()
            ->table('site_user_roles as sur')
            ->join('site_users as su', 'su.id', '=', 'sur.user_id')
            ->where('sur.role_id', $roleId)
            ->where('su.is_active', true)
            ->distinct()
            ->count('sur.user_id');
    }

    protected function requestedRoleIds(Request $request): array
    {
        return collect($request->input('role_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function assertAdministratorUpdateIsSafe(
        Request $request,
        int $userId
    ): void {
        if (!$this->userHasAdministratorRole($userId)) {
            return;
        }

        $administratorRoleId = $this->administratorRoleId();

        if (!$administratorRoleId) {
            return;
        }

        $requestedRoles = $this->requestedRoleIds($request);

        $removingAdministratorRole = !in_array(
            $administratorRoleId,
            $requestedRoles,
            true
        );

        $deactivating = !$request->boolean('is_active');

        if (!$removingAdministratorRole && !$deactivating) {
            return;
        }

        if ($this->activeAdministratorCount() <= 1) {
            abort(
                422,
                'The final active Administrator cannot be demoted or deactivated.'
            );
        }
    }

    protected function assertAdministratorDeleteIsSafe(int $userId): void
    {
        if (
            $this->userHasAdministratorRole($userId)
            && $this->activeAdministratorCount() <= 1
        ) {
            abort(
                422,
                'The final active Administrator cannot be deleted.'
            );
        }
    }




    /*
    |--------------------------------------------------------------------------
    | ESUBIZ_CORE_PARTNER_INVESTMENT_MANAGEMENT_V1
    |--------------------------------------------------------------------------
    */


    /*
     * ESUBIZ_CORE_PARTNER_ADMIN_CONFIG_V1
     *
     * Dedicated Administrator management surface for
     * Partner / Investor investment configuration.
     *
     * Uses the existing role and site_partner_investments
     * architecture. No duplicate investment data.
     */
    public function partners(
        string $subdomain
    ) {
        app(
            \App\Services\Core\CorePermissionService::class
        )->authorize('partners.manage');

        $website = $this->currentWebsite();

        $db = \Illuminate\Support\Facades\DB::connection(
            'website_tenant'
        );

        $partners = collect();

        if (
            \Illuminate\Support\Facades\Schema::connection(
                'website_tenant'
            )->hasTable('site_users')
            && \Illuminate\Support\Facades\Schema::connection(
                'website_tenant'
            )->hasTable('site_roles')
            && \Illuminate\Support\Facades\Schema::connection(
                'website_tenant'
            )->hasTable('site_user_roles')
        ) {
            $partners = $db
                ->table('site_users as users')
                ->join(
                    'site_user_roles as user_roles',
                    'user_roles.user_id',
                    '=',
                    'users.id'
                )
                ->join(
                    'site_roles as roles',
                    'roles.id',
                    '=',
                    'user_roles.role_id'
                )
                ->leftJoin(
                    'site_partner_investments as investments',
                    'investments.user_id',
                    '=',
                    'users.id'
                )
                ->where(
                    'roles.slug',
                    'partners_investors'
                )
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.is_active as user_is_active',

                    'investments.investment_percentage',
                    'investments.profit_basis',
                    'investments.is_active as investment_is_active',
                    'investments.notes',
                    'investments.created_at as investment_created_at',
                    'investments.updated_at as investment_updated_at',
                ])
                ->orderBy('users.name')
                ->get();
        }

        return view(
            'tenant.admin.users.partners',
            compact(
                'website',
                'partners'
            )
        );
    }


    public function updatePartner(
        \Illuminate\Http\Request $request,
        string $subdomain,
        int $user
    ) {
        app(
            \App\Services\Core\CorePermissionService::class
        )->authorize('partners.manage');

        $validated = $request->validate([
            'partner_investment_percentage' => [
                'required',
                'numeric',
                'min:0.01',
                'max:100',
            ],

            'partner_profit_basis' => [
                'required',
                'in:gross,net',
            ],

            'partner_is_active' => [
                'nullable',
                'boolean',
            ],

            'partner_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $db = \Illuminate\Support\Facades\DB::connection(
            'website_tenant'
        );

        $isPartner = $db
            ->table('site_user_roles as user_roles')
            ->join(
                'site_roles as roles',
                'roles.id',
                '=',
                'user_roles.role_id'
            )
            ->where(
                'user_roles.user_id',
                $user
            )
            ->where(
                'roles.slug',
                'partners_investors'
            )
            ->exists();

        if (!$isPartner) {
            return redirect()
                ->to('/admin/users/partners')
                ->withErrors([
                    'partner' =>
                        'This user is not assigned the Partners / Investors role.',
                ]);
        }

        $now = now();

        /*
         * ESUBIZ_PARTNER_INVESTMENT_PERSISTENCE_V28
         *
         * One authoritative row per Partner / Investor.
         * updateOrInsert prevents create/update divergence and ensures
         * subsequent Partner screens read the exact values just saved.
         */
        $db
            ->table('site_partner_investments')
            ->updateOrInsert(
                [
                    'user_id' => $user,
                ],
                [
                    'investment_percentage' =>
                        round(
                            (float) $validated[
                                'partner_investment_percentage'
                            ],
                            2
                        ),

                    'profit_basis' =>
                        $validated['partner_profit_basis'],

                    'is_active' =>
                        ((string) $request->input('partner_is_active', '0') === '1'),

                    'notes' =>
                        isset($validated['partner_notes'])
                            ? trim(
                                (string) $validated[
                                    'partner_notes'
                                ]
                            )
                            : null,

                    'updated_at' => $now,

                    /*
                     * Preserve the original creation time where the
                     * row already exists. For a new row MySQL receives
                     * this value as created_at.
                     */
                    'created_at' =>
                        $db
                            ->table(
                                'site_partner_investments'
                            )
                            ->where(
                                'user_id',
                                $user
                            )
                            ->value('created_at')
                            ?: $now,
                ]
            );

        return redirect()
            ->to('/admin/users/partners')
            ->with(
                'success',
                'Partner investment configuration updated successfully.'
            );
    }


    protected function partnerInvestmentForUser(
        int $userId
    ): ?object {
        if (
            !\Illuminate\Support\Facades\Schema::connection(
                'website_tenant'
            )->hasTable('site_partner_investments')
        ) {
            return null;
        }

        return \Illuminate\Support\Facades\DB::connection(
            'website_tenant'
        )
            ->table('site_partner_investments')
            ->where('user_id', $userId)
            ->first();
    }

    protected function partnerRoleId(): ?int
    {
        if (
            !\Illuminate\Support\Facades\Schema::connection(
                'website_tenant'
            )->hasTable('site_roles')
        ) {
            return null;
        }

        $id = \Illuminate\Support\Facades\DB::connection(
            'website_tenant'
        )
            ->table('site_roles')
            ->where('slug', 'partners_investors')
            ->value('id');

        return $id ? (int) $id : null;
    }

    protected function requestedPartnerRole(
        \Illuminate\Http\Request $request
    ): bool {
        $partnerRoleId = $this->partnerRoleId();

        if (!$partnerRoleId) {
            return false;
        }

        $roles = $request->input('roles', []);

        if (!is_array($roles)) {
            $roles = [];
        }

        return in_array(
            $partnerRoleId,
            array_map('intval', $roles),
            true
        );
    }

    protected function validatePartnerInvestment(
        \Illuminate\Http\Request $request
    ): array {
        if (!$this->requestedPartnerRole($request)) {
            return [];
        }

        app(
            \App\Services\Core\CorePermissionService::class
        )->authorize('partners.manage');

        return $request->validate([
            'partner_investment_percentage' => [
                'required',
                'numeric',
                'min:0.01',
                'max:100',
            ],

            'partner_profit_basis' => [
                'required',
                'in:gross,net',
            ],

            'partner_is_active' => [
                'nullable',
                'boolean',
            ],

            'partner_investment_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }

    protected function syncPartnerInvestment(
        int $userId,
        \Illuminate\Http\Request $request,
        array $validatedPartner
    ): void {
        if (
            !\Illuminate\Support\Facades\Schema::connection(
                'website_tenant'
            )->hasTable('site_partner_investments')
        ) {
            return;
        }

        $hasPartnerRole =
            $this->requestedPartnerRole($request);

        $db = \Illuminate\Support\Facades\DB::connection(
            'website_tenant'
        );

        if (!$hasPartnerRole) {
            /*
             * Preserve historical investment/ledger records.
             * Removing the Partner role only deactivates the
             * investment configuration.
             */
            $db->table('site_partner_investments')
                ->where('user_id', $userId)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            return;
        }

        app(
            \App\Services\Core\CorePermissionService::class
        )->authorize('partners.manage');

        $db->table('site_partner_investments')
            ->updateOrInsert(
                [
                    'user_id' => $userId,
                ],
                [
                    'investment_percentage' =>
                        round(
                            (float) $validatedPartner[
                                'partner_investment_percentage'
                            ],
                            2
                        ),

                    'profit_basis' =>
                        $validatedPartner[
                            'partner_profit_basis'
                        ],

                    'is_active' =>
                        $request->boolean(
                            'partner_investment_active'
                        ),

                    'notes' =>
                        $validatedPartner[
                            'partner_investment_notes'
                        ] ?? null,

                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
    }

}
