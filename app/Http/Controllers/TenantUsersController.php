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

        return view(
            'tenant.admin.users.index',
            compact(
                'website',
                'users',
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
            'phone' => [
                'nullable',
                'string',
                'max:50',
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
            &$userId
        ) {
            $now = now();

            $userId = $db
                ->table('site_users')
                ->insertGetId([
                    'name' => trim($data['name']),
                    'email' => $email,
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
            'phone' => [
                'nullable',
                'string',
                'max:50',
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
            $user
        ) {
            $updates = [
                'name' =>
                    trim($data['name']),
                'email' =>
                    $email,
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
                'min:0',
                'max:100',
            ],

            'partner_profit_basis' => [
                'required',
                'in:gross,net',
            ],

            'partner_investment_active' => [
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
                            4
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
