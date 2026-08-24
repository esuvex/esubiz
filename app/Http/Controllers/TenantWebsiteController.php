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

        $websiteUrl = $request->getScheme() . '://' . $request->getHost();

        $this->tenantDatabaseService->connect($website);

        try {
            $db = $this->tenantDatabaseService->connection();

            /*
             * --------------------------------------------------------------
             * Site Settings
             * --------------------------------------------------------------
             */
            $settings = $db->table('site_settings')
                ->pluck('value', 'key')
                ->all();

            /*
             * --------------------------------------------------------------
             * Published Homepage
             * --------------------------------------------------------------
             */
            $page = $db->table('pages')
                ->where('is_homepage', true)
                ->where('status', 'published')
                ->first();

            if (!$page) {
                $page = $db->table('pages')
                    ->where('slug', 'home')
                    ->where('status', 'published')
                    ->first();
            }

            abort_unless(
                $page,
                404,
                'Published homepage not found.'
            );

            /*
             * --------------------------------------------------------------
             * Page Builder Document
             * --------------------------------------------------------------
             */
            $builderDocument = $db->table('page_builder_documents')
                ->where('page_id', $page->id)
                ->first();

            $builderContent = [];

            if ($builderDocument && $builderDocument->content) {
                $decoded = json_decode(
                    $builderDocument->content,
                    true
                );

                if (is_array($decoded)) {
                    $builderContent = $decoded;
                }
            }

            /*
             * --------------------------------------------------------------
             * Header Menu
             * --------------------------------------------------------------
             */
            $menu = $db->table('menus')
                ->where('location', 'header')
                ->where('is_active', true)
                ->orderBy('id')
                ->first();

            $menuItems = collect();

            if ($menu) {
                $menuItems = $db->table('menu_items')
                    ->where('menu_id', $menu->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();
            }

            return view('tenant.website', [
                'website' => $website,
                'websiteUrl' => $websiteUrl,
                'settings' => $settings,
                'page' => $page,
                'builderDocument' => $builderDocument,
                'builderContent' => $builderContent,
                'menu' => $menu,
                'menuItems' => $menuItems,
            ]);

        } finally {
            $this->tenantDatabaseService->disconnect();
        }
    }
}
