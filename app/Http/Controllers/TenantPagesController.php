<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantPagesController extends Controller
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

        abort_unless(
            session()->get('tenant_cms_authenticated') === true
            && (int) session()->get('tenant_cms_website_id')
                === (int) $website->id,
            403,
            'Please sign in to this website administration area.'
        );

        return $website;
    }


    public function index()
    {
        $website = $this->authorizeCms();

        $db = DB::connection('tenant');

        $settings = $db->table('site_settings')
            ->pluck('value', 'key')
            ->all();

        $pages = $db->table('pages')
            ->orderByDesc('is_homepage')
            ->orderBy('title')
            ->get();

        return view(
            'tenant.admin.pages.index',
            compact(
                'website',
                'settings',
                'pages'
            )
        );
    }


    public function create()
    {
        $website = $this->authorizeCms();

        $settings = DB::connection('tenant')
            ->table('site_settings')
            ->pluck('value', 'key')
            ->all();

        return view(
            'tenant.admin.pages.form',
            [
                'website' => $website,
                'settings' => $settings,
                'page' => null,
            ]
        );
    }


    public function store(Request $request)
    {
        $website = $this->authorizeCms();

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:200',
            ],

            'content' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:draft,published',
            ],

            'is_homepage' => [
                'nullable',
                'boolean',
            ],
        ]);

        $db = DB::connection('tenant');

        $slug = trim(
            (string) ($data['slug'] ?? '')
        );

        if ($slug === '') {
            $slug = Str::slug(
                $data['title']
            );
        } else {
            $slug = Str::slug($slug);
        }

        if ($slug === '') {
            $slug = 'page-' . Str::lower(
                Str::random(8)
            );
        }

        abort_if(
            $db->table('pages')
                ->where('slug', $slug)
                ->exists(),
            422,
            'A page with this URL slug already exists.'
        );

        $isHomepage = $request->boolean(
            'is_homepage'
        );

        $db->transaction(
            function () use (
                $db,
                $data,
                $slug,
                $isHomepage
            ) {
                if ($isHomepage) {
                    $db->table('pages')
                        ->update([
                            'is_homepage' => false,
                            'updated_at' => now(),
                        ]);
                }

                $db->table('pages')
                    ->insert([
                        'title' =>
                            trim($data['title']),

                        'slug' =>
                            $slug,

                        'status' =>
                            $data['status'],

                        'content' =>
                            $data['content'] ?? '',

                        'is_homepage' =>
                            $isHomepage,

                        'settings' =>
                            json_encode([]),

                        'seo' =>
                            json_encode([]),

                        'published_at' =>
                            $data['status'] === 'published'
                                ? now()
                                : null,

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);
            }
        );

        return redirect()
            ->route(
                'tenant.cms.pages.index',
                [
                    'subdomain' =>
                        $website->subdomain,
                ]
            )
            ->with(
                'success',
                'Page created successfully.'
            );
    }


    public function edit(int $page)
    {
        $website = $this->authorizeCms();

        $db = DB::connection('tenant');

        $settings = $db
            ->table('site_settings')
            ->pluck('value', 'key')
            ->all();

        $page = $db->table('pages')
            ->where('id', $page)
            ->first();

        abort_unless($page, 404);

        return view(
            'tenant.admin.pages.form',
            compact(
                'website',
                'settings',
                'page'
            )
        );
    }


    public function update(
        Request $request,
        int $page
    ) {
        $website = $this->authorizeCms();

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'slug' => [
                'required',
                'string',
                'max:200',
            ],

            'content' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:draft,published',
            ],

            'is_homepage' => [
                'nullable',
                'boolean',
            ],
        ]);

        $db = DB::connection('tenant');

        $existing = $db->table('pages')
            ->where('id', $page)
            ->first();

        abort_unless($existing, 404);

        $slug = Str::slug(
            $data['slug']
        );

        abort_if(
            $slug === '',
            422,
            'Page URL slug is required.'
        );

        abort_if(
            $db->table('pages')
                ->where('slug', $slug)
                ->where('id', '!=', $page)
                ->exists(),
            422,
            'A page with this URL slug already exists.'
        );

        $isHomepage = $request->boolean(
            'is_homepage'
        );

        $db->transaction(
            function () use (
                $db,
                $data,
                $slug,
                $isHomepage,
                $existing,
                $page
            ) {
                if ($isHomepage) {
                    $db->table('pages')
                        ->where('id', '!=', $page)
                        ->update([
                            'is_homepage' => false,
                            'updated_at' => now(),
                        ]);
                }

                /*
                 * The current homepage cannot accidentally
                 * become "no homepage" from this form.
                 */
                $finalHomepage =
                    $existing->is_homepage
                    && !$isHomepage
                        ? true
                        : $isHomepage;

                $db->table('pages')
                    ->where('id', $page)
                    ->update([
                        'title' =>
                            trim($data['title']),

                        'slug' =>
                            $slug,

                        'status' =>
                            $data['status'],

                        'content' =>
                            $data['content'] ?? '',

                        'is_homepage' =>
                            $finalHomepage,

                        'published_at' =>
                            $data['status'] === 'published'
                                ? (
                                    $existing->published_at
                                    ?: now()
                                )
                                : null,

                        'updated_at' =>
                            now(),
                    ]);
            }
        );

        return redirect()
            ->route(
                'tenant.cms.pages.index',
                [
                    'subdomain' =>
                        $website->subdomain,
                ]
            )
            ->with(
                'success',
                'Page updated successfully.'
            );
    }


    public function destroy(int $page)
    {
        $website = $this->authorizeCms();

        $db = DB::connection('tenant');

        $existing = $db->table('pages')
            ->where('id', $page)
            ->first();

        abort_unless($existing, 404);

        abort_if(
            (bool) $existing->is_homepage,
            422,
            'The homepage cannot be deleted. Set another page as homepage first.'
        );

        $db->transaction(
            function () use (
                $db,
                $page
            ) {
                /*
                 * Remove menu references first so deleted
                 * pages cannot leave broken navigation items.
                 */
                $db->table('menu_items')
                    ->where('page_id', $page)
                    ->delete();

                $db->table(
                    'page_builder_documents'
                )
                    ->where('page_id', $page)
                    ->delete();

                $db->table('pages')
                    ->where('id', $page)
                    ->delete();
            }
        );

        return redirect()
            ->route(
                'tenant.cms.pages.index',
                [
                    'subdomain' =>
                        $website->subdomain,
                ]
            )
            ->with(
                'success',
                'Page deleted successfully.'
            );
    }
}
