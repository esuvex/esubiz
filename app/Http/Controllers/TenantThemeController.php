<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use App\Services\Website\WebsiteTenantDatabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Multitenancy\Models\Tenant;

class TenantThemeController extends Controller
{
    public function __construct(
        protected WebsiteTenantDatabaseService $tenantDatabaseService
    ) {
    }

    protected function website(): Website
    {
        /** @var WebsiteTenant|null $tenant */
        $tenant = Tenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        return Website::query()
            ->where('id', $tenant->website_id)
            ->where('status', 'active')
            ->firstOrFail();
    }

    public static function defaults(): array
    {
        return [
            /*
             * Brand / Header
             */
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',

            'logo_path' => '',
            'favicon_path' => '',

            'nav_home_label' => 'Home',
            'nav_about_label' => 'About',
            'nav_faq_label' => 'FAQs',
            'nav_contact_label' => 'Contact',
            'nav_login_label' => 'Login',
            'nav_register_label' => 'Register',

            /*
             * Hero
             */
            'hero_badge' => 'Welcome',
            'hero_title' => 'Helping you move forward.',
            'hero_subtitle' =>
                'Thoughtful solutions designed around people, quality and dependable service.',
            'hero_image_path' => '',

            'cta_label' => 'Get Started',
            'cta_url' => '/contact',

            'secondary_cta_label' => 'Learn More',
            'secondary_cta_url' => '/about',

            /*
             * Features
             */
            'features_badge' => 'What we offer',
            'features_title' =>
                'Everything starts with understanding what matters.',
            'features_subtitle' =>
                'Our approach combines clear thinking, practical execution and consistent support.',

            'features_json' => '[{"title": "Professional Service", "text": "Reliable solutions delivered with care, clarity and attention to detail.", "image_path": ""}, {"title": "Customer Focus", "text": "We listen first and shape the experience around the people we serve.", "image_path": ""}, {"title": "Long-term Value", "text": "Our goal is not simply to deliver today, but to create value that lasts.", "image_path": ""}]',

            /*
             * Statistics
             */
            'stats_badge' => 'Our approach',

            'stat_1_value' => '100%',
            'stat_1_label' => 'Customer Focus',

            'stat_2_value' => '24/7',
            'stat_2_label' => 'Digital Access',

            'stat_3_value' => '01',
            'stat_3_label' => 'Clear Standard',

            /*
             * About
             */
            'about_badge' => 'About us',
            'about_title' =>
                'Built on clarity, consistency and trust.',
            'about_text' =>
                'We believe good business is built by solving real problems well. We focus on practical solutions, meaningful relationships and experiences customers are happy to return to.',
            'about_image_path' => '',

            'about_cta_label' => 'Our Story',
            'about_cta_url' => '/about',

            /*
             * Testimonials
             */
            'testimonials_badge' => 'Testimonials',
            'testimonials_title' =>
                'What people say about us.',
            'testimonials_subtitle' =>
                'Real experiences from customers and clients we have served.',

            'testimonials_json' => '[{"name": "Amaka N.", "role": "Business Owner", "text": "Professional, responsive and easy to work with from beginning to end.", "photo_path": ""}, {"name": "David O.", "role": "Client", "text": "The experience was clear, efficient and thoughtfully handled.", "photo_path": ""}, {"name": "Sarah K.", "role": "Customer", "text": "Strong communication and attention to detail made the process easy.", "photo_path": ""}, {"name": "Michael A.", "role": "Business Client", "text": "A dependable team with a practical approach and consistent delivery.", "photo_path": ""}, {"name": "Ifeoma C.", "role": "Customer", "text": "Everything felt organized, professional and focused on the right outcome.", "photo_path": ""}, {"name": "Tunde B.", "role": "Returning Client", "text": "The quality of service gave us the confidence to work together again.", "photo_path": ""}]',

            /*
             * Final CTA
             */
            'final_cta_badge' => "Let's talk",
            'final_cta_title' =>
                'Ready to work with us?',
            'final_cta_text' =>
                'Tell us what you need and let us start the conversation.',
            'final_cta_label' =>
                'Contact Us',
            'final_cta_url' =>
                '/contact',

            /*
             * Footer
             */
            'footer_logo_path' => '',
            'footer_background_path' => '',

            'footer_heading' => '',
            'footer_text' =>
                'Thoughtful solutions, dependable service and a better experience for every customer.',

            'footer_links_json' => '[{"label": "About", "url": "/about"}, {"label": "Contact", "url": "/contact"}, {"label": "FAQs", "url": "/faqs"}, {"label": "Terms", "url": "/terms"}, {"label": "Privacy", "url": "/privacy"}]',

            'footer_copyright' =>
                'All rights reserved.',
        ];
    }

    protected function themeSettings(
        Website $website
    ): array {
        $defaults = static::defaults();

        $this->tenantDatabaseService->connect($website);

        try {
            $stored = $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->where('key', 'like', 'theme.corporate.%')
                ->pluck('value', 'key')
                ->all();

            foreach ($defaults as $key => $default) {
                $defaults[$key] =
                    $stored['theme.corporate.' . $key]
                    ?? $default;
            }

            return $defaults;

        } finally {
            $this->tenantDatabaseService->disconnect();
        }
    }

    protected function activeTheme(
        Website $website
    ): string {
        $this->tenantDatabaseService->connect($website);

        try {
            return (string) (
                $this->tenantDatabaseService
                    ->connection()
                    ->table('site_settings')
                    ->where('key', 'theme.active')
                    ->value('value')
                ?: 'business'
            );

        } finally {
            $this->tenantDatabaseService->disconnect();
        }
    }

    public function index(): View
    {
        $website = $this->website();

        $activeTheme =
            $this->activeTheme($website);

        $themes = [
            [
                'slug' => 'business',
                'name' => 'Business',
                'version' => 'v1.0',
                'description' =>
                    'A clean and responsive business website theme with animations, homepage sections, testimonials, FAQs, contact and legal pages.',
                'active' =>
                    in_array(
                        $activeTheme,
                        ['business', 'corporate-default'],
                        true
                    ),
            ],
        ];

        return view(
            'tenant.admin.themes.index',
            compact(
                'website',
                'themes',
                'activeTheme'
            )
        );
    }

    public function configureBusiness(): View
    {
        return $this->configure('business');
    }

    public function configure(
        string $theme
    ): View {
        abort_unless(
            in_array(
                $theme,
                ['business', 'corporate-default'],
                true
            ),
            404,
            'Theme not found.'
        );

        $website =
            $this->website();

        $themeSettings =
            $this->themeSettings($website);

        $features = json_decode(
            (string) (
                $themeSettings['features_json']
                ?? '[]'
            ),
            true
        );

        $testimonials = json_decode(
            (string) (
                $themeSettings['testimonials_json']
                ?? '[]'
            ),
            true
        );

        $footerLinks = json_decode(
            (string) (
                $themeSettings['footer_links_json']
                ?? '[]'
            ),
            true
        );

        if (!is_array($features)) {
            $features = [];
        }

        if (!is_array($testimonials)) {
            $testimonials = [];
        }

        if (!is_array($footerLinks)) {
            $footerLinks = [];
        }

        return view(
            'tenant.admin.themes.configure',
            [
                'website' => $website,
                'theme' => $themeSettings,
                'features' => $features,
                'testimonials' => $testimonials,
                'footerLinks' => $footerLinks,
            ]
        );
    }

    public function updateBusiness(
        Request $request
    ): RedirectResponse {
        return $this->update(
            $request,
            'business'
        );
    }

    public function enable(
        string $theme
    ): RedirectResponse {
        abort_unless(
            in_array(
                $theme,
                ['business', 'corporate-default'],
                true
            ),
            404,
            'Theme not found.'
        );

        $website =
            $this->website();

        $this->tenantDatabaseService
            ->connect($website);

        try {
            $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->updateOrInsert(
                    ['key' => 'theme.active'],
                    ['value' => 'business']
                );

        } finally {
            $this->tenantDatabaseService
                ->disconnect();
        }

        return back()->with(
            'success',
            'Business theme enabled.'
        );
    }

    public function disable(
        string $theme
    ): RedirectResponse {
        abort_unless(
            in_array(
                $theme,
                ['business', 'corporate-default'],
                true
            ),
            404,
            'Theme not found.'
        );

        $website =
            $this->website();

        $this->tenantDatabaseService
            ->connect($website);

        try {
            $this->tenantDatabaseService
                ->connection()
                ->table('site_settings')
                ->updateOrInsert(
                    ['key' => 'theme.active'],
                    ['value' => '']
                );

        } finally {
            $this->tenantDatabaseService
                ->disconnect();
        }

        return back()->with(
            'success',
            'Business theme disabled.'
        );
    }

    protected function storeThemeUpload(
        Website $website,
        $file,
        string $folder
    ): string {
        $folder =
            trim(
                preg_replace(
                    '/[^a-zA-Z0-9_-]/',
                    '',
                    $folder
                ),
                '/'
            );

        $root =
            storage_path(
                'app/public/tenants/'
                . $website->id
                . '/theme/'
                . $folder
            );

        if (!is_dir($root)) {
            mkdir(
                $root,
                0755,
                true
            );
        }

        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );

        $filename =
            bin2hex(random_bytes(8))
            . '-'
            . time()
            . '.'
            . $extension;

        $file->move(
            $root,
            $filename
        );

        return $folder
            . '/'
            . $filename;
    }

    public function update(
        Request $request,
        string $theme
    ): RedirectResponse {
        abort_unless(
            in_array(
                $theme,
                ['business', 'corporate-default'],
                true
            ),
            404,
            'Theme not found.'
        );

        $website =
            $this->website();

        $data = $request->validate([
            'primary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'secondary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            /*
             * Main images
             */
            'logo' => [
                'nullable',
                'image',
                'max:2048',
                'dimensions:max_width=1200,max_height=600',
            ],

            'favicon' => [
                'nullable',
                'image',
                'max:1024',
                'dimensions:max_width=512,max_height=512',
            ],

            'hero_image' => [
                'nullable',
                'image',
                'max:4096',
                'dimensions:min_width=600,max_width=3000,min_height=400,max_height=2200',
            ],

            'about_image' => [
                'nullable',
                'image',
                'max:4096',
                'dimensions:min_width=500,max_width=3000,min_height=400,max_height=2200',
            ],

            'footer_logo' => [
                'nullable',
                'image',
                'max:2048',
                'dimensions:max_width=1200,max_height=600',
            ],

            'footer_background' => [
                'nullable',
                'image',
                'max:4096',
                'dimensions:min_width=800,max_width=3500,min_height=400,max_height=2200',
            ],

            /*
             * Repeatable features
             */
            'features' => [
                'nullable',
                'array',
            ],

            'features.*.title' => [
                'nullable',
                'string',
                'max:180',
            ],

            'features.*.text' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'features.*.existing_image' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'features.*.image' => [
                'nullable',
                'image',
                'max:3072',
                'dimensions:min_width=400,max_width=2400,min_height=250,max_height=1800',
            ],

            /*
             * Repeatable testimonials
             */
            'testimonials' => [
                'nullable',
                'array',
            ],

            'testimonials.*.name' => [
                'nullable',
                'string',
                'max:180',
            ],

            'testimonials.*.role' => [
                'nullable',
                'string',
                'max:180',
            ],

            'testimonials.*.text' => [
                'nullable',
                'string',
                'max:1200',
            ],

            'testimonials.*.existing_photo' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'testimonials.*.photo' => [
                'nullable',
                'image',
                'max:2048',
                'dimensions:max_width=1200,max_height=1200',
            ],

            /*
             * Footer links
             */
            'footer_links' => [
                'nullable',
                'array',
            ],

            'footer_links.*.label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'footer_links.*.url' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $defaults =
            static::defaults();

        $skip = [
            'logo_path',
            'favicon_path',
            'hero_image_path',
            'about_image_path',
            'footer_logo_path',
            'footer_background_path',
            'features_json',
            'testimonials_json',
            'footer_links_json',
        ];

        $this->tenantDatabaseService
            ->connect($website);

        try {
            $db =
                $this->tenantDatabaseService
                    ->connection();

            /*
             * Save every simple configurable key.
             */
            foreach ($defaults as $key => $default) {

                if (in_array($key, $skip, true)) {
                    continue;
                }

                if (!$request->exists($key)) {
                    continue;
                }

                $value =
                    (string) $request->input(
                        $key,
                        ''
                    );

                $db->table('site_settings')
                    ->updateOrInsert(
                        [
                            'key' =>
                                'theme.corporate.'
                                . $key,
                        ],
                        [
                            'value' =>
                                $value,
                        ]
                    );
            }

            /*
             * Section-level images.
             */
            $uploads = [
                'logo' => [
                    'setting' => 'logo_path',
                    'folder' => 'logo',
                ],

                'favicon' => [
                    'setting' => 'favicon_path',
                    'folder' => 'favicon',
                ],

                'hero_image' => [
                    'setting' => 'hero_image_path',
                    'folder' => 'hero',
                ],

                'about_image' => [
                    'setting' => 'about_image_path',
                    'folder' => 'about',
                ],

                'footer_logo' => [
                    'setting' => 'footer_logo_path',
                    'folder' => 'footer',
                ],

                'footer_background' => [
                    'setting' => 'footer_background_path',
                    'folder' => 'footer',
                ],
            ];

            foreach ($uploads as $input => $config) {

                if (!$request->hasFile($input)) {
                    continue;
                }

                $stored =
                    $this->storeThemeUpload(
                        $website,
                        $request->file($input),
                        $config['folder']
                    );

                $db->table('site_settings')
                    ->updateOrInsert(
                        [
                            'key' =>
                                'theme.corporate.'
                                . $config['setting'],
                        ],
                        [
                            'value' => $stored,
                        ]
                    );
            }

            /*
             * Features
             */
            $features = [];

            foreach (
                $request->input(
                    'features',
                    []
                )
                as $index => $feature
            ) {
                $title =
                    trim(
                        (string) (
                            $feature['title']
                            ?? ''
                        )
                    );

                $text =
                    trim(
                        (string) (
                            $feature['text']
                            ?? ''
                        )
                    );

                $imagePath =
                    (string) (
                        $feature['existing_image']
                        ?? ''
                    );

                if (
                    $request->hasFile(
                        'features.'
                        . $index
                        . '.image'
                    )
                ) {
                    $imagePath =
                        $this->storeThemeUpload(
                            $website,
                            $request->file(
                                'features.'
                                . $index
                                . '.image'
                            ),
                            'features'
                        );
                }

                if (
                    $title === ''
                    && $text === ''
                    && $imagePath === ''
                ) {
                    continue;
                }

                $features[] = [
                    'title' => $title,
                    'text' => $text,
                    'image_path' => $imagePath,
                ];
            }

            $db->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.features_json',
                    ],
                    [
                        'value' =>
                            json_encode(
                                $features,
                                JSON_UNESCAPED_SLASHES
                            ),
                    ]
                );

            /*
             * Testimonials
             */
            $testimonials = [];

            foreach (
                $request->input(
                    'testimonials',
                    []
                )
                as $index => $testimonial
            ) {
                $name =
                    trim(
                        (string) (
                            $testimonial['name']
                            ?? ''
                        )
                    );

                $role =
                    trim(
                        (string) (
                            $testimonial['role']
                            ?? ''
                        )
                    );

                $text =
                    trim(
                        (string) (
                            $testimonial['text']
                            ?? ''
                        )
                    );

                $photoPath =
                    (string) (
                        $testimonial['existing_photo']
                        ?? ''
                    );

                if (
                    $request->hasFile(
                        'testimonials.'
                        . $index
                        . '.photo'
                    )
                ) {
                    $photoPath =
                        $this->storeThemeUpload(
                            $website,
                            $request->file(
                                'testimonials.'
                                . $index
                                . '.photo'
                            ),
                            'testimonials'
                        );
                }

                if (
                    $name === ''
                    && $role === ''
                    && $text === ''
                    && $photoPath === ''
                ) {
                    continue;
                }

                $testimonials[] = [
                    'name' => $name,
                    'role' => $role,
                    'text' => $text,
                    'photo_path' => $photoPath,
                ];
            }

            $db->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.testimonials_json',
                    ],
                    [
                        'value' =>
                            json_encode(
                                $testimonials,
                                JSON_UNESCAPED_SLASHES
                            ),
                    ]
                );

            /*
             * Footer links
             */
            $footerLinks = [];

            foreach (
                $request->input(
                    'footer_links',
                    []
                )
                as $link
            ) {
                $label =
                    trim(
                        (string) (
                            $link['label']
                            ?? ''
                        )
                    );

                $url =
                    trim(
                        (string) (
                            $link['url']
                            ?? ''
                        )
                    );

                if (
                    $label === ''
                    && $url === ''
                ) {
                    continue;
                }

                $footerLinks[] = [
                    'label' => $label,
                    'url' => $url,
                ];
            }

            $db->table('site_settings')
                ->updateOrInsert(
                    [
                        'key' =>
                            'theme.corporate.footer_links_json',
                    ],
                    [
                        'value' =>
                            json_encode(
                                $footerLinks,
                                JSON_UNESCAPED_SLASHES
                            ),
                    ]
                );

        } finally {
            $this->tenantDatabaseService
                ->disconnect();
        }

        return back()->with(
            'success',
            'Business theme configuration saved.'
        );
    }

    public function asset(
        string $path
    ) {
        $website =
            $this->website();

        abort_if(
            str_contains($path, '..'),
            404
        );

        $base =
            storage_path(
                'app/public/tenants/'
                . $website->id
                . '/theme'
            );

        $candidate =
            $base
            . DIRECTORY_SEPARATOR
            . ltrim(
                $path,
                '/\\'
            );

        $realBase =
            realpath($base);

        $realFile =
            realpath($candidate);

        abort_unless(
            $realBase
            && $realFile
            && str_starts_with(
                $realFile,
                $realBase
                . DIRECTORY_SEPARATOR
            )
            && is_file($realFile),
            404
        );

        $mime =
            mime_content_type($realFile)
            ?: 'application/octet-stream';

        return response()->file(
            $realFile,
            [
                'Content-Type' => $mime,
                'X-Content-Type-Options' =>
                    'nosniff',
                'Cache-Control' =>
                    'public, max-age=86400',
            ]
        );
    }

    public function businessPreview()
    {
        $this->website();

        $preview =
            public_path(
                'images/themes/business-preview.svg'
            );

        abort_unless(
            is_file($preview),
            404,
            'Business theme preview not found.'
        );

        return response()->file(
            $preview,
            [
                'Content-Type' =>
                    'image/svg+xml; charset=UTF-8',

                'Cache-Control' =>
                    'public, max-age=3600',

                'X-Content-Type-Options' =>
                    'nosniff',
            ]
        );
    }
}
