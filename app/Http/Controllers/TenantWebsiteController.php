<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use App\Services\Website\WebsiteTenantDatabaseService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Multitenancy\Models\Tenant;

class TenantWebsiteController extends Controller
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    /**
     * Public tenant homepage.
     *
     * Theme remains the default landing page.
     *
     * A CMS page only overrides the theme homepage when it contains
     * meaningful administrator-created content/builder data.
     *
     * This deliberately ignores the empty Core "home" placeholder
     * created when a tenant is initialized.
     */
    public function home(Request $request): \Illuminate\Http\Response
    {
        $website = $this->currentWebsite();

        $theme = $this->corporateThemeSettings(
            $website
        );

        $payload = $this->homepagePayload(
            $website
        );

        if (
            $payload
            && $this->hasMeaningfulPageContent(
                $payload['page'],
                $payload['builderContent']
            )
        ) {
            return view(
                'tenant.themes.corporate-default.page',
                [
                    'website' => $website,
                    'theme' => $theme,
                    'page' => $payload['page'],
                    'builderContent' =>
                        $payload['builderContent'],
                ]
            );
        }

        /*
         * No explicit/custom CMS homepage.
         * Preserve the active theme landing page.
         */
        /*
         * ESUBIZ_TENANT_HOMEPAGE_BANDWIDTH_ACCOUNTING_V1
         */
        return $this->bandwidthTrackedView(
            $website,
            'tenant.themes.corporate-default.home',
            compact(
                'website',
                'theme'
            ),
            '/'
        );
    }

    /**
     * Display a published tenant CMS page.
     *
     * All custom CMS pages inherit the theme shell.
     *
     * Built-in theme pages remain the default for:
     * About / Contact / FAQs / Terms / Privacy.
     *
     * Once a matching CMS page contains saved content, that CMS
     * version takes precedence while keeping the same theme shell.
     */
    public function page(
        Request $request,
        string $subdomain,
        string $slug
    ): \Illuminate\Http\Response {
        $website = $this->currentWebsite();

        $slug = strtolower(
            trim($slug)
        );

        $theme = $this->corporateThemeSettings(
            $website
        );

        $payload = $this->pagePayload(
            $website,
            $slug
        );

        if (
            $payload
            && $this->hasMeaningfulPageContent(
                $payload['page'],
                $payload['builderContent']
            )
        ) {
            return $this->bandwidthTrackedView(
                $website,
                'tenant.themes.corporate-default.page',
                [
                    'website' => $website,
                    'theme' => $theme,
                    'page' => $payload['page'],
                    'builderContent' =>
                        $payload['builderContent'],
                ],
                $slug
            );
        }

        /*
         * Theme-supplied standard pages remain available until
         * the website administrator customizes their CMS copy.
         */
        $themePages = [
            'about',
            'contact',
            'faqs',
            'terms',
            'privacy',
        ];

        if (
            in_array(
                $slug,
                $themePages,
                true
            )
        ) {
            return $this->bandwidthTrackedView(
                $website,
                'tenant.themes.corporate-default.'
                . $slug,
                compact(
                    'website',
                    'theme'
                ),
                $slug
            );
        }

        /*
         * A database page may exist but contain no builder blocks.
         * In that case we still render its normal content rather than
         * falling back to the old Esubiz placeholder template.
         */
        if ($payload) {
            return $this->bandwidthTrackedView(
                $website,
                'tenant.themes.corporate-default.page',
                [
                    'website' => $website,
                    'theme' => $theme,
                    'page' => $payload['page'],
                    'builderContent' =>
                        $payload['builderContent'],
                ],
                $slug
            );
        }

        abort(
            404,
            'Published page not found.'
        );
    }

    /*
     * ESUBIZ_TENANT_PUBLIC_HTML_BANDWIDTH_ACCOUNTING_V1
     *
     * Records actual generated public tenant HTML bytes.
     * Tenant admin traffic is intentionally excluded.
     */
    protected function bandwidthTrackedView(
        Website $website,
        string $view,
        array $data,
        string $slug
    ): \Illuminate\Http\Response {
        $html =
            view(
                $view,
                $data
            )->render();

        try {
            app(
                \App\Services\Website\TenantBandwidthUsageService::class
            )->recordBytes(
                $website,
                strlen($html),
                'tenant_public_html',
                null,
                [
                    'slug' => $slug,
                ]
            );
        } catch (\Throwable $e) {
            /*
             * Bandwidth accounting must never block a public page.
             */
        }

        return response(
            $html,
            200,
            [
                'Content-Type' =>
                    'text/html; charset=UTF-8',
            ]
        );
    }


    protected function currentWebsite(): Website
    {
        /** @var WebsiteTenant|null $tenant */
        $tenant = Tenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        return Website::query()
            ->findOrFail(
                $tenant->website_id
            );
    }

    /**
     * Retrieve the published CMS homepage.
     */
    protected function homepagePayload(
        Website $website
    ): ?array {
        $this->tenantDatabaseService
            ->connect($website);

        try {
            $db = $this
                ->tenantDatabaseService
                ->connection();

            $page = $db
                ->table('pages')
                ->where(
                    'is_homepage',
                    true
                )
                ->where(
                    'status',
                    'published'
                )
                ->orderByDesc(
                    'updated_at'
                )
                ->first();

            if (!$page) {
                return null;
            }

            return [
                'page' => $page,
                'builderContent' =>
                    $this->builderContent(
                        $db,
                        $page
                    ),
            ];

        } finally {
            $this->tenantDatabaseService
                ->disconnect();
        }
    }

    /**
     * Retrieve a published CMS page by slug.
     */
    protected function pagePayload(
        Website $website,
        string $slug
    ): ?array {
        $this->tenantDatabaseService
            ->connect($website);

        try {
            $db = $this
                ->tenantDatabaseService
                ->connection();

            $page = $db
                ->table('pages')
                ->where(
                    'slug',
                    $slug
                )
                ->where(
                    'status',
                    'published'
                )
                ->first();

            if (!$page) {
                return null;
            }

            return [
                'page' => $page,
                'builderContent' =>
                    $this->builderContent(
                        $db,
                        $page
                    ),
            ];

        } finally {
            $this->tenantDatabaseService
                ->disconnect();
        }
    }

    /**
     * Resolve Basic Page Builder data.
     *
     * pages.settings.basic_builder is authoritative for the current
     * Basic Page Builder.
     *
     * page_builder_documents remains supported for compatibility.
     */
    protected function builderContent(
        $db,
        object $page
    ): array {
        /*
         * Current Basic Page Builder.
         */
        if (!empty($page->settings)) {
            $settings = json_decode(
                $page->settings,
                true
            );

            if (
                is_array($settings)
                && isset(
                    $settings['basic_builder']
                )
                && is_array(
                    $settings['basic_builder']
                )
            ) {
                $basicBuilder =
                    $settings['basic_builder'];

                /*
                 * Only use Basic Builder settings when they
                 * actually contain page sections.
                 *
                 * Theme homepages may have an empty
                 * settings.basic_builder initializer while their
                 * real seeded homepage lives in
                 * page_builder_documents.
                 */
                $basicSections =
                    isset($basicBuilder['sections'])
                    && is_array(
                        $basicBuilder['sections']
                    )
                        ? $basicBuilder['sections']
                        : (
                            array_is_list($basicBuilder)
                                ? $basicBuilder
                                : []
                        );

                if (!empty($basicSections)) {
                    return $basicSections;
                }
            }
        }

        /*
         * Legacy / future builder document compatibility.
         */
        $document = $db
            ->table(
                'page_builder_documents'
            )
            ->where(
                'page_id',
                $page->id
            )
            ->first();

        if (
            !$document
            || empty($document->content)
        ) {
            return [];
        }

        $decoded = json_decode(
            $document->content,
            true
        );

        if (!is_array($decoded)) {
            return [];
        }

        /*
         * Core initializer stores:
         *
         * {
         *   type: core-default,
         *   version: ...,
         *   sections: []
         * }
         *
         * Do not treat that empty initializer document as a
         * customized homepage.
         */
        if (
            isset($decoded['sections'])
            && is_array(
                $decoded['sections']
            )
        ) {
            return $decoded['sections'];
        }

        /*
         * Current builder is a direct array of sections.
         */
        if (array_is_list($decoded)) {
            return $decoded;
        }

        return [];
    }

    /**
     * Determine whether the administrator has actually customized
     * this page.
     */
    protected function hasMeaningfulPageContent(
        object $page,
        array $builderContent
    ): bool {
        if (!empty($builderContent)) {
            return true;
        }

        return trim(
            (string) (
                $page->content
                ?? ''
            )
        ) !== '';
    }

    /**
     * Business / Corporate Default theme settings.
     */
    protected function corporateThemeSettings(
        Website $website
    ): array {
        $defaults =
            TenantThemeController::defaults();

        $this->tenantDatabaseService
            ->connect($website);

        try {
            /*
             * ESUBIZ_CORE_PUBLIC_CANONICAL_BRANDING_PAYLOAD_V10
             *
             * The public theme needs both its explicit theme overrides
             * and the canonical Core website branding identity.
             *
             * theme.corporate.logo_path / footer_logo_path remain
             * optional explicit overrides.
             *
             * site_logo_path / site_logo_white_path are inherited
             * defaults and must not be duplicated into theme settings.
             */
            $stored = $this
                ->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->where(function ($query) {
                    $query
                        ->where(
                            'key',
                            'like',
                            'theme.corporate.%'
                        )
                        ->orWhereIn(
                            'key',
                            [
                                'site_logo_path',
                                'site_logo_white_path',
                            ]
                        );
                })
                ->pluck(
                    'value',
                    'key'
                )
                ->all();

            foreach (
                $defaults as $key => $value
            ) {
                $defaults[$key] =
                    $stored[
                        'theme.corporate.'
                        . $key
                    ]
                    ?? $value;
            }

            /*
             * Canonical branding stays outside the theme namespace.
             * Expose it in this public settings payload so the view can
             * resolve: explicit theme override -> canonical Core logo.
             */
            foreach (
                [
                    'site_logo_path',
                    'site_logo_white_path',
                ]
                as $canonicalBrandingKey
            ) {
                if (
                    array_key_exists(
                        $canonicalBrandingKey,
                        $stored
                    )
                ) {
                    $defaults[$canonicalBrandingKey] =
                        $stored[$canonicalBrandingKey];
                }
            }

            return $defaults;

        } finally {
            $this->tenantDatabaseService
                ->disconnect();
        }
    }
}
