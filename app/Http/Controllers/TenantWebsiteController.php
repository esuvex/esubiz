<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use App\Services\Website\WebsiteTenantDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Multitenancy\Models\Tenant;

class TenantWebsiteController extends Controller
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    /**
     * Display the public homepage from the tenant Core CMS.
     */
    public function home(Request $request): View
    {
        /** @var WebsiteTenant|null $tenant */
        $tenant = Tenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        $website = Website::query()
            ->findOrFail($tenant->website_id);

        /*
        |--------------------------------------------------------------------------
        | Corporate Default Theme
        |--------------------------------------------------------------------------
        |
        | Every tenant receives this built-in theme when no other theme
        | renderer has been selected.
        |
        | Old saved homepage HTML is deliberately NOT rendered here because
        | some legacy builder content is currently being displayed as text.
        |
        */

        $theme = $this->corporateThemeSettings($website);

        return view(
            'tenant.themes.corporate-default.home',
            compact('website', 'theme')
        );
    }

    /**
     * Display a published public CMS page by slug.
     */
    public function page(
        Request $request,
        string $subdomain,
        string $slug
    ): View {
        /** @var WebsiteTenant|null $tenant */
        $tenant = Tenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        $website = Website::query()
            ->findOrFail($tenant->website_id);

        /*
        |--------------------------------------------------------------------------
        | Built-in Corporate Default Pages
        |--------------------------------------------------------------------------
        */

        $corporatePages = [
            'about',
            'contact',
            'faqs',
            'terms',
            'privacy',
        ];

        if (
            in_array(
                strtolower($slug),
                $corporatePages,
                true
            )
        ) {
            $theme = $this->corporateThemeSettings($website);

            return view(
                'tenant.themes.corporate-default.'
                . strtolower($slug),
                compact('website', 'theme')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Other Published CMS Pages
        |--------------------------------------------------------------------------
        |
        | Normal user-created pages continue through the existing Core CMS
        | renderer and page builder.
        |
        */

        $websiteUrl =
            $request->getScheme()
            . '://'
            . $request->getHost();

        $this->tenantDatabaseService->connect(
            $website
        );

        try {

            $db =
                $this->tenantDatabaseService
                    ->connection();

            $settings =
                $db->table('site_settings')
                    ->pluck('value', 'key')
                    ->all();

            $page =
                $db->table('pages')
                    ->where('slug', $slug)
                    ->where('status', 'published')
                    ->first();

            abort_unless(
                $page,
                404,
                'Published page not found.'
            );

            $builderDocument =
                $db->table(
                    'page_builder_documents'
                )
                    ->where(
                        'page_id',
                        $page->id
                    )
                    ->first();

            $builderContent = [];

            if (
                $builderDocument
                && $builderDocument->content
            ) {

                $decoded = json_decode(
                    $builderDocument->content,
                    true
                );

                if (is_array($decoded)) {
                    $builderContent = $decoded;
                }
            }

            /*
             * Backward-compatible Core Basic Builder.
             */
            if (!empty($page->settings)) {

                $pageSettings = json_decode(
                    $page->settings,
                    true
                );

                if (
                    is_array($pageSettings)
                    && isset(
                        $pageSettings['basic_builder']
                    )
                    && is_array(
                        $pageSettings['basic_builder']
                    )
                ) {
                    $builderContent =
                        $pageSettings[
                            'basic_builder'
                        ];
                }
            }

            $menu =
                $db->table('menus')
                    ->where(
                        'location',
                        'header'
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderBy('id')
                    ->first();

            $menuItems = collect();

            if ($menu) {

                $menuItems =
                    $db->table('menu_items')
                        ->where(
                            'menu_id',
                            $menu->id
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->orderBy(
                            'sort_order'
                        )
                        ->orderBy('id')
                        ->get();
            }

            return view(
                'tenant.website',
                [
                    'website' =>
                        $website,

                    'websiteUrl' =>
                        $websiteUrl,

                    'settings' =>
                        $settings,

                    'page' =>
                        $page,

                    'builderDocument' =>
                        $builderDocument,

                    'builderContent' =>
                        $builderContent,

                    'menu' =>
                        $menu,

                    'menuItems' =>
                        $menuItems,
                ]
            );

        } finally {

            $this
                ->tenantDatabaseService
                ->disconnect();
        }
    }

    protected function corporateThemeSettings(Website $website): array
    {
        $defaults =
            \App\Http\Controllers\TenantThemeController::defaults();

        $this->tenantDatabaseService->connect($website);

        try {
            $stored = $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->where('key', 'like', 'theme.corporate.%')
                ->pluck('value', 'key')
                ->all();

            foreach ($defaults as $key => $value) {
                $defaults[$key] =
                    $stored['theme.corporate.' . $key]
                    ?? $value;
            }

            return $defaults;

        } finally {
            $this->tenantDatabaseService->disconnect();
        }
    }

}
