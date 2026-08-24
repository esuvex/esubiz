<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Sso\SsoService;
use Illuminate\Support\Str;

class TenantCmsController extends Controller
{
    /**
     * Get the current website from the active tenant.
     */
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


    /**
     * Tenant CMS entry point.
     *
     * Future:
     * - Local website administrator login
     * - Esubiz SSO login
     *
     * For the current development stage this takes the
     * authenticated website owner into the CMS dashboard.
     */
    public function admin(
        Request $request,
        SsoService $sso
    ) {

        /*
         * WEBSITE-DASHBOARD-DIRECT-SSO
         *
         * Accept the short-lived owner handoff generated from
         * Esubiz -> User Mode -> My Websites.
         */
        if ($request->boolean('sso')) {

            $websiteId = (int) $request->query('website');
            $userId = (int) $request->query('user');
            $expires = (int) $request->query('expires');
            $signature = (string) $request->query('signature');

            abort_if(
                !$websiteId
                || !$userId
                || !$expires
                || !$signature,
                403,
                'Invalid website SSO request.'
            );

            abort_if(
                $expires < now()->timestamp,
                403,
                'Website SSO request has expired.'
            );

            $payload = implode('|', [
                $websiteId,
                $userId,
                $expires,
            ]);

            $expected = hash_hmac(
                'sha256',
                $payload,
                config('app.key')
            );

            abort_unless(
                hash_equals($expected, $signature),
                403,
                'Invalid website SSO signature.'
            );

            /*
             * Verify the requested website against the current
             * tenant host before creating the CMS session.
             */
            $host = strtolower($request->getHost());

            $subdomain = explode('.', $host)[0] ?? null;

            $website = \App\Models\Website::query()
                ->where('id', $websiteId)
                ->where('owner_id', $userId)
                ->where('subdomain', $subdomain)
                ->where('status', 'active')
                ->where('user_enabled', true)
                ->first();

            abort_unless(
                $website,
                403,
                'Website SSO authorization failed.'
            );

            session([
                'tenant_cms_authenticated' => true,
                'tenant_cms_user_id' => $userId,
                'tenant_cms_website_id' => $websiteId,
                'tenant_cms_auth_method' => 'esubiz_sso',
            ]);

            $request->session()->regenerate();

            return redirect('/admin/dashboard');
        }


        $website = $this->currentWebsite();

        /*
         * Already authenticated specifically for this website.
         */
        if (
            session()->get('tenant_cms_authenticated') === true
            && (int) session()->get('tenant_cms_website_id')
                === (int) $website->id
        ) {
            return redirect()->route(
                'tenant.cms.dashboard',
                [
                    'subdomain' => $website->subdomain,
                ]
            );
        }

        /*
         * Every website provisioned by Esubiz may have its own
         * central SSO application registration.
         */
        $application = $sso->findWebsiteApplication(
            (int) $website->id
        );

        $ssoUrl = null;

        if ($application) {

            $redirectUri =
                'https://'
                . $website->subdomain
                . '.esubiz.com/sso/callback';

            /*
             * State protects the tenant login request and lets us
             * return specifically to the CMS after authorization.
             */
            $state = Str::random(48);

            session([
                'tenant_cms_sso_state' =>
                    $state,

                'tenant_cms_sso_website_id' =>
                    (int) $website->id,

                'tenant_cms_sso_destination' =>
                    '/admin/dashboard',
            ]);

            $ssoUrl =
                'https://esubiz.com/oauth/authorize?'
                . http_build_query([
                    'client_id' =>
                        $application->client_id,

                    'redirect_uri' =>
                        $redirectUri,

                    'state' =>
                        $state,
                ]);
        }

        return view(
            'tenant.admin.login',
            compact(
                'website',
                'ssoUrl'
            )
        );
    }


    /**
     * Core CMS dashboard.
     */

    public function dashboard()
    {
        $website = $this->currentWebsite();

        abort_unless(
            session()->get('tenant_cms_authenticated') === true
            && (int) session()->get('tenant_cms_website_id')
                === (int) $website->id,
            403,
            'Please sign in to this website administration area.'
        );

        $db = DB::connection('tenant');

        $settings = $db->table('site_settings')
            ->pluck('value', 'key')
            ->all();


        /*
        |--------------------------------------------------------------------------
        | Safe Tenant Table Helpers
        |--------------------------------------------------------------------------
        |
        | Core and installed modules do not all have the same tables.
        | Dashboard cards therefore appear only when the relevant
        | resource exists in this tenant database.
        |
        */

        $schema = \Illuminate\Support\Facades\Schema::connection(
            'tenant'
        );

        $countTable = function (
            array $tables
        ) use (
            $db,
            $schema
        ): ?int {

            foreach ($tables as $table) {

                if ($schema->hasTable($table)) {
                    return (int) $db
                        ->table($table)
                        ->count();
                }
            }

            return null;
        };


        $sumTable = function (
            array $tables,
            array $amountColumns
        ) use (
            $db,
            $schema
        ): ?float {

            foreach ($tables as $table) {

                if (!$schema->hasTable($table)) {
                    continue;
                }

                foreach ($amountColumns as $column) {

                    if (
                        $schema->hasColumn(
                            $table,
                            $column
                        )
                    ) {
                        return (float) $db
                            ->table($table)
                            ->sum($column);
                    }
                }
            }

            return null;
        };


        /*
        |--------------------------------------------------------------------------
        | Module / Core Statistics
        |--------------------------------------------------------------------------
        */

        $dashboardStats = [];


        $orders = $countTable([
            'orders',
            'ecommerce_orders',
            'store_orders',
        ]);

        if ($orders !== null) {
            $dashboardStats[] = [
                'label' => 'Orders',
                'value' => number_format($orders),
                'icon' => 'orders',
            ];
        }


        $sales = $sumTable(
            [
                'orders',
                'ecommerce_orders',
                'store_orders',
                'sales',
            ],
            [
                'total_amount',
                'grand_total',
                'total',
                'amount',
            ]
        );

        if ($sales !== null) {
            $dashboardStats[] = [
                'label' => 'Sales',
                'value' =>
                    ($settings['currency'] ?? 'NGN')
                    . ' '
                    . number_format(
                        $sales,
                        2
                    ),
                'icon' => 'sales',
            ];
        }


        $products = $countTable([
            'products',
            'ecommerce_products',
            'store_products',
        ]);

        if ($products !== null) {
            $dashboardStats[] = [
                'label' => 'Products',
                'value' => number_format($products),
                'icon' => 'products',
            ];
        }


        $clients = $countTable([
            'crm_contacts',
            'clients',
            'customers',
        ]);

        if ($clients !== null) {
            $dashboardStats[] = [
                'label' => 'Clients',
                'value' => number_format($clients),
                'icon' => 'clients',
            ];
        }


        $staff = $countTable([
            'hr_employees',
            'employees',
            'staff',
        ]);

        if ($staff !== null) {
            $dashboardStats[] = [
                'label' => 'Staff',
                'value' => number_format($staff),
                'icon' => 'staff',
            ];
        }


        $tickets = $countTable([
            'tickets',
            'support_tickets',
        ]);

        if ($tickets !== null) {
            $dashboardStats[] = [
                'label' => 'Tickets',
                'value' => number_format($tickets),
                'icon' => 'tickets',
            ];
        }


        $bookings = $countTable([
            'bookings',
            'hotel_bookings',
            'reservations',
        ]);

        if ($bookings !== null) {
            $dashboardStats[] = [
                'label' => 'Bookings',
                'value' => number_format($bookings),
                'icon' => 'bookings',
            ];
        }


        /*
         * Always keep useful Core stats visible even before
         * a specialized module has been installed.
         */
        if (empty($dashboardStats)) {

            $pages = $countTable([
                'pages',
            ]) ?? 0;

            $users = $countTable([
                'site_users',
            ]) ?? 0;

            $dashboardStats = [
                [
                    'label' => 'Pages',
                    'value' => number_format($pages),
                    'icon' => 'pages',
                ],
                [
                    'label' => 'Users',
                    'value' => number_format($users),
                    'icon' => 'users',
                ],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Income / Expense Chart
        |--------------------------------------------------------------------------
        |
        | Financial Tracker tables will feed this automatically
        | when compatible income/expense tables exist.
        |
        */

        $chartLabels = [];
        $chartIncome = [];
        $chartExpenses = [];

        for ($i = 6; $i >= 0; $i--) {

            $day = now()
                ->copy()
                ->subDays($i);

            $chartLabels[] =
                $day->format('D');

            $chartIncome[] = 0;
            $chartExpenses[] = 0;
        }


        /*
         * Try common Core/module financial tables without
         * assuming that every website has them.
         */
        $financialTables = [
            'financial_transactions',
            'finance_transactions',
            'transactions',
        ];

        foreach ($financialTables as $table) {

            if (!$schema->hasTable($table)) {
                continue;
            }

            $dateColumn = null;

            foreach (
                [
                    'created_at',
                    'occurred_at',
                    'transaction_date',
                    'date',
                ] as $candidate
            ) {
                if (
                    $schema->hasColumn(
                        $table,
                        $candidate
                    )
                ) {
                    $dateColumn = $candidate;
                    break;
                }
            }

            $amountColumn = null;

            foreach (
                [
                    'amount',
                    'total',
                    'total_amount',
                ] as $candidate
            ) {
                if (
                    $schema->hasColumn(
                        $table,
                        $candidate
                    )
                ) {
                    $amountColumn = $candidate;
                    break;
                }
            }

            $typeColumn = null;

            foreach (
                [
                    'type',
                    'transaction_type',
                    'entry_type',
                ] as $candidate
            ) {
                if (
                    $schema->hasColumn(
                        $table,
                        $candidate
                    )
                ) {
                    $typeColumn = $candidate;
                    break;
                }
            }

            if (
                !$dateColumn
                || !$amountColumn
                || !$typeColumn
            ) {
                continue;
            }


            for ($i = 6; $i >= 0; $i--) {

                $day = now()
                    ->copy()
                    ->subDays($i);

                $index = 6 - $i;

                $rows = $db
                    ->table($table)
                    ->whereDate(
                        $dateColumn,
                        $day->toDateString()
                    )
                    ->get([
                        $amountColumn,
                        $typeColumn,
                    ]);

                foreach ($rows as $row) {

                    $type = strtolower(
                        (string) $row->{$typeColumn}
                    );

                    $amount = (float)
                        $row->{$amountColumn};

                    if (
                        in_array(
                            $type,
                            [
                                'income',
                                'credit',
                                'sale',
                                'revenue',
                            ],
                            true
                        )
                    ) {
                        $chartIncome[$index] +=
                            $amount;
                    }

                    if (
                        in_array(
                            $type,
                            [
                                'expense',
                                'debit',
                                'cost',
                            ],
                            true
                        )
                    ) {
                        $chartExpenses[$index] +=
                            $amount;
                    }
                }
            }

            break;
        }


        return view(
            'tenant.admin.dashboard',
            compact(
                'website',
                'settings',
                'dashboardStats',
                'chartLabels',
                'chartIncome',
                'chartExpenses'
            )
        );
    }




    /**
     * Save the first editable Core CMS settings.
     */
    public function updateHomepage(Request $request)
    {
        $website = $this->currentWebsite();

        abort_unless(
            session()->get('tenant_cms_authenticated') === true
            && (int) session()->get('tenant_cms_website_id')
                === (int) $website->id,
            403,
            'Please sign in to this website administration area.'
        );

        $data = $request->validate([
            'website_name' => [
                'required',
                'string',
                'max:150',
            ],

            'homepage_title' => [
                'required',
                'string',
                'max:200',
            ],

            'homepage_content' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'home_menu_label' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $db = DB::connection('tenant');

        $db->transaction(function () use (
            $db,
            $data
        ) {

            /*
             * SITE NAME
             */
            $db->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' => 'website_name',
                    ],
                    [
                        'value' => trim(
                            $data['website_name']
                        ),

                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );


            /*
             * HOMEPAGE
             */
            $homepage = $db->table('pages')
                ->where('is_homepage', true)
                ->first();

            if (!$homepage) {
                $homepage = $db->table('pages')
                    ->where('slug', 'home')
                    ->first();
            }

            abort_unless(
                $homepage,
                422,
                'Homepage does not exist.'
            );

            $db->table('pages')
                ->where('id', $homepage->id)
                ->update([
                    'title' => trim(
                        $data['homepage_title']
                    ),

                    'content' =>
                        $data['homepage_content']
                        ?? '',

                    'status' => 'published',

                    'published_at' =>
                        $homepage->published_at
                            ?: now(),

                    'updated_at' => now(),
                ]);


            /*
             * HOME MENU LABEL
             */
            if (
                !empty(
                    trim(
                        (string) (
                            $data['home_menu_label']
                            ?? ''
                        )
                    )
                )
            ) {

                $menu = $db->table('menus')
                    ->where(
                        'location',
                        'header'
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();

                if ($menu) {

                    $homeItem = $db
                        ->table('menu_items')
                        ->where(
                            'menu_id',
                            $menu->id
                        )
                        ->where(
                            'page_id',
                            $homepage->id
                        )
                        ->first();

                    if ($homeItem) {

                        $db->table('menu_items')
                            ->where(
                                'id',
                                $homeItem->id
                            )
                            ->update([
                                'label' => trim(
                                    $data[
                                        'home_menu_label'
                                    ]
                                ),

                                'updated_at' =>
                                    now(),
                            ]);
                    }
                }
            }
        });

        return back()->with(
            'success',
            'Website updated successfully.'
        );
    }

    public function logout(Request $request)
    {
        $request->session()->forget([
            'tenant_cms_authenticated',
            'tenant_cms_website_id',
            'tenant_cms_user_id',
            'tenant_cms_authenticated_via',
            'tenant_cms_sso_state',
            'tenant_cms_sso_website_id',
            'tenant_cms_sso_destination',
        ]);

        $request->session()->regenerateToken();

        return redirect()->route(
            'tenant.cms.admin',
            [
                'subdomain' =>
                    request()->route('subdomain'),
            ]
        )->with(
            'success',
            'You have been logged out of this website.'
        );
    }

}
