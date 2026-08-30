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
                'Create your logo, footer logo and website icon.',

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
                'Create or improve the main welcome section of your homepage.',

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
                'Create or improve your service and feature cards.',

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
                'Create or improve customer reviews and testimonials.',

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
                'Create or improve the final call-to-action section.',

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
