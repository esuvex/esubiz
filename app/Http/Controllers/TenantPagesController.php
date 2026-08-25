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

        /*
         * Ensure the active Business theme's editable standard pages
         * exist in this tenant's CMS.
         *
         * Existing records are NEVER overwritten here.
         */
        $this->syncBusinessThemePages(
            $website
        );

        /*
         * Existing tenants created before the theme-homepage rule
         * may still have the untouched Core placeholder marked as
         * homepage. Clear only that legacy placeholder.
         *
         * Real/custom CMS homepages are never changed here.
         */
        $this->normalizeLegacyCoreHomepage(
            $website
        );

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

        $schema = \Illuminate\Support\Facades\Schema::connection(
            'tenant'
        );

        $forms = collect();

        if ($schema->hasTable('forms')) {
            $forms = DB::connection('tenant')
                ->table('forms')
                ->orderBy('name')
                ->get();
        }

        return view(
            'tenant.admin.pages.form',
            [
                'website' => $website,
                'settings' => $settings,
                'page' => null,
                'forms' => $forms,
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

            'builder_json' => [
                'nullable',
                'string',
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
                            json_encode([
                                'basic_builder' =>
                                    json_decode(
                                        $data['builder_json']
                                            ?? '[]',
                                        true
                                    ) ?: [],
                            ]),

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


    public function edit(
        string $subdomain,
        int $page
    ) {
        $website = $this->authorizeCms();

        $db = DB::connection('tenant');

        $settings = $db
            ->table('site_settings')
            ->pluck('value', 'key')
            ->all();

        $schema = \Illuminate\Support\Facades\Schema::connection(
            'tenant'
        );

        $forms = collect();

        if ($schema->hasTable('forms')) {
            $forms = $db
                ->table('forms')
                ->orderBy('name')
                ->get();
        }

        $page = $db->table('pages')
            ->where('id', $page)
            ->first();

        abort_unless($page, 404);

        return view(
            'tenant.admin.pages.form',
            compact(
                'website',
                'settings',
                'page',
                'forms'
            )
        );
    }


    public function update(
        Request $request,
        string $subdomain,
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

            'builder_json' => [
                'nullable',
                'string',
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

        $pageSettings = $existing->settings
            ? (
                json_decode(
                    $existing->settings,
                    true
                ) ?: []
            )
            : [];

        $pageSettings['basic_builder'] =
            json_decode(
                $data['builder_json']
                    ?? '[]',
                true
            ) ?: [];

        $db->transaction(
            function () use (
                $db,
                $data,
                $slug,
                $isHomepage,
                $existing,
                $page,
                $pageSettings
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
                 * A tenant is allowed to have no CMS homepage.
                 *
                 * When no CMS page has is_homepage=true,
                 * TenantWebsiteController falls back to the
                 * active theme's default landing page.
                 *
                 * Selecting another CMS homepage still clears
                 * the previous one above.
                 */
                $finalHomepage =
                    $isHomepage;

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

                        'settings' =>
                            json_encode(
                                $pageSettings
                            ),

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


    public function destroy(
        string $subdomain,
        int $page
    ) {
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


    /**
     * Ensure Business theme standard pages exist as editable CMS pages.
     *
     * Important boundaries:
     *
     * - This only writes to the current tenant database.
     * - Existing tenant pages are never overwritten.
     * - Theme homepage is NOT inserted here.
     * - Theme landing page remains controlled by Theme Settings.
     * - These records begin empty so the theme's built-in content
     *   continues to render until the administrator customizes them.
     */
    protected function syncBusinessThemePages(
        Website $website
    ): void {
        $db = DB::connection('tenant');

        /*
         * Business theme standard-page defaults.
         *
         * These are CMS data, not theme HTML.
         * The active theme continues to own:
         * header, navigation, footer, typography and presentation.
         */
        $definitions = [

            'about' => [
                'title' => 'About',
                'sections' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'About us',
                            'heading' => 'People first. Purpose always.',
                            'text' => 'Learn more about our company, what drives us and how we approach the work we do.',
                            'buttonText' => '',
                            'buttonUrl' => '',
                            'image' => '',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'Doing meaningful work, the right way.',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'Our company exists to create practical solutions and positive experiences for the people we serve. We believe the strongest businesses grow through consistency, trust and a genuine commitment to delivering value.',
                        ],
                    ],
                    [
                        'type' => 'cards',
                        'data' => [
                            'heading' => '',
                            'items' => [
                                [
                                    'title' => 'Our Vision',
                                    'text' => 'To be trusted for thoughtful solutions, meaningful service and lasting value.',
                                    'image' => '',
                                    'buttonText' => '',
                                    'buttonUrl' => '',
                                ],
                                [
                                    'title' => 'Our Mission',
                                    'text' => 'To serve people well through dependable work, clear communication and continuous improvement.',
                                    'image' => '',
                                    'buttonText' => '',
                                    'buttonUrl' => '',
                                ],
                                [
                                    'title' => 'Our Values',
                                    'text' => 'Integrity, consistency, respect, responsibility and customer focus.',
                                    'image' => '',
                                    'buttonText' => '',
                                    'buttonUrl' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'contact' => [
                'title' => 'Contact',
                'sections' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Contact',
                            'heading' => "Let's start a conversation.",
                            'text' => 'Have a question, enquiry or project in mind? Send us a message and we will get back to you.',
                            'buttonText' => '',
                            'buttonUrl' => '',
                            'image' => '',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'We would love to hear from you.',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'Reach out using the contact form or any of the available contact details.',
                        ],
                    ],
                    [
                        'type' => 'form',
                        'data' => [
                            'formId' => '',
                        ],
                    ],
                ],
            ],

            'faqs' => [
                'title' => 'FAQs',
                'sections' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'FAQs',
                            'heading' => 'Frequently asked questions.',
                            'text' => 'Helpful answers to common questions about our business.',
                            'buttonText' => '',
                            'buttonUrl' => '',
                            'image' => '',
                        ],
                    ],
                    [
                        'type' => 'faq',
                        'data' => [
                            'heading' => 'Frequently Asked Questions',
                            'items' => [
                                [
                                    'question' => 'What services do you provide?',
                                    'answer' => 'We provide solutions tailored to the needs of our customers. Contact us to discuss your specific requirements.',
                                ],
                                [
                                    'question' => 'How can I get started?',
                                    'answer' => 'Use our contact page to send us a message. We will review your request and guide you through the next steps.',
                                ],
                                [
                                    'question' => 'How long does it take to receive a response?',
                                    'answer' => 'Response times may vary, but we aim to respond to enquiries as quickly as possible during our normal business hours.',
                                ],
                                [
                                    'question' => 'Can I request a custom solution?',
                                    'answer' => 'Yes. We understand that every customer can have different requirements, so custom requests can be discussed with our team.',
                                ],
                                [
                                    'question' => 'How can I learn more about your business?',
                                    'answer' => 'Visit our About page or contact us directly for more information.',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'terms' => [
                'title' => 'Terms',
                'sections' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Legal',
                            'heading' => 'Terms & Conditions',
                            'text' => 'The general terms governing use of this website.',
                            'buttonText' => '',
                            'buttonUrl' => '',
                            'image' => '',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'Website Use',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'By using this website, you agree to use it lawfully and in a manner that does not interfere with the rights or experience of others.',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'Information',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'We aim to keep information accurate and current, but content may be updated from time to time.',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'Services',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'Specific services, quotations, agreements or transactions may be subject to additional terms.',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'Contact',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'If you have questions about these terms, please contact us through our contact page.',
                        ],
                    ],
                ],
            ],

            'privacy' => [
                'title' => 'Privacy',
                'sections' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'eyebrow' => 'Privacy',
                            'heading' => 'Privacy Policy',
                            'text' => 'How information submitted through this website may be handled.',
                            'buttonText' => '',
                            'buttonUrl' => '',
                            'image' => '',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'Information We Receive',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'Information may be provided when you contact us, submit forms or interact with services available through this website.',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'How Information Is Used',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'Information may be used to respond to enquiries, provide requested services and improve customer experience.',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'Protection',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'Reasonable measures should be taken to protect personal information from unauthorized access, misuse or disclosure.',
                        ],
                    ],
                    [
                        'type' => 'heading',
                        'data' => [
                            'text' => 'Contact',
                            'level' => 'h2',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'text' => 'For privacy-related questions, please use the contact information provided on this website.',
                        ],
                    ],
                ],
            ],
        ];


        foreach ($definitions as $slug => $definition) {

            $page = $db
                ->table('pages')
                ->where('slug', $slug)
                ->first();

            /*
             * Existing non-theme pages are owned by the administrator.
             * Never replace them merely because their slug matches.
             */
            if ($page) {
                $settings = [];

                if (!empty($page->settings)) {
                    $decoded = json_decode(
                        $page->settings,
                        true
                    );

                    if (is_array($decoded)) {
                        $settings = $decoded;
                    }
                }

                if (
                    empty($settings['theme_page'])
                    || ($settings['theme'] ?? null) !== 'business'
                ) {
                    continue;
                }

                /*
                 * If the administrator has already customized this
                 * theme page, preserve it permanently.
                 */
                if (
                    empty($settings['theme_default'])
                ) {
                    continue;
                }

                if (
                    trim((string) ($page->content ?? '')) !== ''
                ) {
                    continue;
                }

            } else {

                $now = now();

                $pageId = $db
                    ->table('pages')
                    ->insertGetId([
                        'title' =>
                            $definition['title'],

                        'slug' =>
                            $slug,

                        'content' => '',

                        'status' =>
                            'published',

                        'is_homepage' =>
                            false,

                        'settings' =>
                            json_encode([
                                'theme_page' => true,
                                'theme' => 'business',
                                'theme_default' => true,
                            ]),

                        'published_at' =>
                            $now,

                        'created_at' =>
                            $now,

                        'updated_at' =>
                            $now,
                    ]);

                $page = $db
                    ->table('pages')
                    ->where('id', $pageId)
                    ->first();
            }


            /*
             * Convert the theme defaults into the Page Builder's
             * Page -> Section -> Column -> Widget document format.
             */
            $builder = [];

            foreach (
                $definition['sections']
                as $widgetDefinition
            ) {
                $widgetId =
                    'theme_widget_'
                    . $slug
                    . '_'
                    . count($builder);

                $columnId =
                    'theme_column_'
                    . $slug
                    . '_'
                    . count($builder);

                $sectionId =
                    'theme_section_'
                    . $slug
                    . '_'
                    . count($builder);

                $builder[] = [
                    'id' => $sectionId,
                    'type' => 'section',
                    'ratios' => [1],

                    'settings' => [
                        'background' => '#ffffff',
                        'paddingTop' => 24,
                        'paddingBottom' => 24,
                    ],

                    'columns' => [
                        [
                            'id' => $columnId,

                            'widgets' => [
                                [
                                    'id' => $widgetId,

                                    'type' =>
                                        $widgetDefinition['type'],

                                    'settings' => [
                                        'background' => '#ffffff',
                                        'textColor' => '#0f172a',
                                        'align' => 'left',
                                        'paddingTop' => 24,
                                        'paddingBottom' => 24,
                                        'hideDesktop' => false,
                                        'hideTablet' => false,
                                        'hideMobile' => false,
                                    ],

                                    'data' =>
                                        $widgetDefinition['data'],
                                ],
                            ],
                        ],
                    ],
                ];
            }


            $settings = [];

            if (!empty($page->settings)) {
                $decoded = json_decode(
                    $page->settings,
                    true
                );

                if (is_array($decoded)) {
                    $settings = $decoded;
                }
            }

            /*
             * Do not replace a populated builder document.
             */
            $existingBuilder =
                $settings['basic_builder']
                ?? [];

            if (
                is_array($existingBuilder)
                && !empty($existingBuilder)
            ) {
                continue;
            }


            $settings['theme_page'] = true;
            $settings['theme'] = 'business';
            $settings['theme_default'] = true;
            $settings['basic_builder'] = $builder;


            $db->table('pages')
                ->where('id', $page->id)
                ->update([
                    'settings' =>
                        json_encode($settings),

                    'updated_at' =>
                        now(),
                ]);


            /*
             * Keep compatibility document synchronized too.
             */
            if (
                $db->getSchemaBuilder()
                    ->hasTable(
                        'page_builder_documents'
                    )
            ) {
                $db->table(
                    'page_builder_documents'
                )
                    ->updateOrInsert(
                        [
                            'page_id' =>
                                $page->id,
                        ],
                        [
                            'content' =>
                                json_encode(
                                    $builder
                                ),

                            'updated_at' =>
                                now(),

                            'created_at' =>
                                now(),
                        ]
                    );
            }
        }
    }


    /**
     * Remove homepage status from the untouched legacy Core placeholder.
     *
     * Safe conditions are deliberately strict:
     *
     * - slug must be "home"
     * - it must currently be marked homepage
     * - content must be empty
     * - Basic Page Builder must contain no sections/widgets
     * - legacy page_builder_documents must contain no sections
     *
     * Therefore a genuine administrator-created/customized homepage
     * is never unset automatically.
     */
    protected function normalizeLegacyCoreHomepage(
        Website $website
    ): void {
        $db = DB::connection(
            'tenant'
        );

        $page = $db
            ->table('pages')
            ->where(
                'slug',
                'home'
            )
            ->where(
                'is_homepage',
                true
            )
            ->first();


        if (!$page) {
            return;
        }


        if (
            trim(
                (string) (
                    $page->content
                    ?? ''
                )
            ) !== ''
        ) {
            return;
        }


        $settings = [];

        if (!empty($page->settings)) {
            $decoded = json_decode(
                $page->settings,
                true
            );

            if (is_array($decoded)) {
                $settings = $decoded;
            }
        }


        $basicBuilder =
            $settings['basic_builder']
            ?? [];


        if (
            is_array($basicBuilder)
            && !empty($basicBuilder)
        ) {
            return;
        }


        if (
            $db->getSchemaBuilder()
                ->hasTable(
                    'page_builder_documents'
                )
        ) {
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
                $document
                && !empty(
                    $document->content
                )
            ) {
                $decoded = json_decode(
                    $document->content,
                    true
                );


                if (is_array($decoded)) {

                    /*
                     * Core placeholder format:
                     *
                     * {
                     *   type: core-default,
                     *   sections: []
                     * }
                     */
                    if (
                        isset(
                            $decoded['sections']
                        )
                        && is_array(
                            $decoded['sections']
                        )
                        && !empty(
                            $decoded['sections']
                        )
                    ) {
                        return;
                    }


                    /*
                     * Direct builder-array compatibility.
                     */
                    if (
                        array_is_list(
                            $decoded
                        )
                        && !empty(
                            $decoded
                        )
                    ) {
                        return;
                    }
                }
            }
        }


        $settings[
            'core_default_home'
        ] = true;


        $db->table('pages')
            ->where(
                'id',
                $page->id
            )
            ->update([
                'is_homepage' =>
                    false,

                'settings' =>
                    json_encode(
                        $settings
                    ),

                'updated_at' =>
                    now(),
            ]);
    }

}
