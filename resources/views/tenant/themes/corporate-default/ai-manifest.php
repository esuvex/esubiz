<?php

/*
 * ESUBIZ_BUSINESS_THEME_AI_MANIFEST_V2
 * ESUBIZ_THEME_AI_FUNCTION_KEY_MAP_FIX_V1
 * ESUBIZ_BUSINESS_AI_BRANDING_CTA_V1
 * ESUBIZ_BUSINESS_AI_STRUCTURED_WIDGETS_V1
 *
 * AI-editable homepage functions owned by the
 * Business / corporate-default theme.
 *
 * Central Site AI remains responsible for validation,
 * generation, proposal preview and application.
 */

return [

    'label' =>
        'Business Theme Homepage',

    'theme_name' =>
        'Business',

    'theme_version' =>
        '1.0.0',

    'functions' => [

        /*
         * ESUBIZ_BUSINESS_AI_UNIFIED_BRANDING_V3
         *
         * One simple Branding function creates one coherent
         * visual identity set:
         *
         * Header Logo = master identity.
         * Footer Logo = matching white variant.
         * Favicon = icon derived from the master identity.
         */
        'branding' => [
            'key' =>
                'branding',

            'label' =>
                'Branding',

            'description' =>
                'Generate one complete and consistent website branding set. '
                . 'Create the Header Logo as the master identity, '
                . 'create the Footer Logo as the same logo in a white variant, '
                . 'and create the Favicon from the icon or symbol of that same '
                . 'Header Logo. Never create three unrelated identities. '
                . 'Header and Footer Logos must fit the recommended maximum '
                . '1200 x 600 pixel area. The Favicon must use a square '
                . '512 x 512 pixel composition.',

            'outputs' => [
                'image',
            ],

            'targets' => [
                'logo_path',
                'footer_logo_path',
                'favicon_path',
            ],
        ],


        /*
         * HERO
         */
        'hero' => [
            'key' =>
                'hero',

            'label' =>
                'Hero',

            'description' =>
                'Generate or improve the homepage hero section including its primary and secondary calls to action.',

            'outputs' => [
                'text',
                'image',
                'structured-data',
            ],

            'targets' => [
                'hero_badge',
                'hero_title',
                'hero_subtitle',
                'hero_image_path',
                'cta_label',
                'cta_url',
                'secondary_cta_label',
                'secondary_cta_url',
            ],
        ],

        /*
         * FEATURES / CARDS
         *
         * Current structured card representation:
         *
         * enabled
         * title
         * text
         * image_path
         * show_image
         * icon
         * show_icon
         * show_button
         * button_label
         * button_url
         * button_url_active
         */
        'features' => [
            'key' =>
                'features',

            'label' =>
                'Features / Cards',

            'description' =>
                'Generate or improve the homepage feature cards. Each features_json item uses the current structured card format: enabled, title, text, image_path, show_image, icon, show_icon, show_button, button_label, button_url and button_url_active. Preserve this structure when generating cards.',

            'outputs' => [
                'text',
                'image',
                'structured-data',
            ],

            'targets' => [
                'features_badge',
                'features_title',
                'features_subtitle',
                'features_json',
            ],
        ],

        /*
         * STATISTICS
         */
        'statistics' => [
            'key' =>
                'statistics',

            'label' =>
                'Statistics',

            'description' =>
                'Generate or improve the homepage statistics section.',

            'outputs' => [
                'text',
                'structured-data',
            ],

            'targets' => [
                'stats_badge',
                'stat_1_value',
                'stat_1_label',
                'stat_2_value',
                'stat_2_label',
                'stat_3_value',
                'stat_3_label',
            ],
        ],

        /*
         * ABOUT
         */
        'about' => [
            'key' =>
                'about',

            'label' =>
                'About',

            'description' =>
                'Generate or improve the homepage about section.',

            'outputs' => [
                'text',
                'image',
                'structured-data',
            ],

            'targets' => [
                'about_badge',
                'about_title',
                'about_text',
                'about_image_path',
                'about_cta_label',
                'about_cta_url',
            ],
        ],

        /*
         * TESTIMONIALS
         *
         * Current structured testimonial representation:
         *
         * enabled
         * name
         * role
         * text
         * photo_path
         * show_image
         * icon
         * show_icon
         * show_button
         * button_label
         * button_url
         * button_url_active
         */
        'testimonials' => [
            'key' =>
                'testimonials',

            'label' =>
                'Testimonials',

            'description' =>
                'Generate or improve the homepage testimonials. Each testimonials_json item uses the current structured testimonial format: enabled, name, role, text, photo_path, show_image, icon, show_icon, show_button, button_label, button_url and button_url_active. Preserve this structure and use photo_path for generated testimonial photos.',

            'outputs' => [
                'text',
                'image',
                'structured-data',
            ],

            'targets' => [
                'testimonials_badge',
                'testimonials_title',
                'testimonials_subtitle',
                'testimonials_json',
            ],
        ],

        /*
         * FINAL CTA
         */
        'final_cta' => [
            'key' =>
                'final_cta',

            'label' =>
                'Call To Action',

            'description' =>
                'Generate or improve the final homepage call-to-action section and its button.',

            'outputs' => [
                'text',
                'structured-data',
            ],

            'targets' => [
                'final_cta_badge',
                'final_cta_title',
                'final_cta_text',
                'final_cta_label',
                'final_cta_url',
            ],
        ],

    ],

];
