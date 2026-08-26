<?php

namespace App\Services\SiteAi\Capabilities;

use App\Services\SiteAi\Contracts\SiteAiCapability;

/**
 * Theme Homepage capability.
 *
 * Central Esubiz owns the capability definition.
 * Generated content/media belongs to the requesting workspace.
 */
class ThemeHomepageCapability implements SiteAiCapability
{
    public function key(): string
    {
        return 'theme.homepage';
    }


    public function manifest(array $context = []): array
    {
        return $this->definition();
    }


    public function prepare(array $payload,
        array $context = []): array
    {
        /*
         * Keep the caller payload intact.
         * The central engine/provider performs generation.
         */
        return $context;
    }


    public function normalizeResult(array $result,
        array $context = []): array
    {
        /*
         * Generated output is returned to the requesting
         * workspace for persistence in its own storage.
         */
        return $context;
    }


    public function definition(): array
    {
        return [
            'key' => $this->key(),

            'version' => '1.0.0',

            'label' => 'Theme Homepage',

            'storage' => [
                'output_owner' =>
                    'requesting_workspace',

                'central_asset_storage' =>
                    false,

                'persist_generated_text_centrally' =>
                    false,

                'persist_generated_media_centrally' =>
                    false,

                'persistence' =>
                    'caller',

                'media_destination' =>
                    'workspace_media_library',
            ],

            'functions' => [

                [
                    'key' =>
                        'header_logo',

                    'label' =>
                        'Header Logo',

                    'description' =>
                        'Generate a website header logo using the recommended 180 × 60 px layout.',

                    'outputs' => [
                        'image',
                    ],

                    'targets' => [
                        'header_logo',
                    ],

                    'meta' => [
                        'asset_type' =>
                            'logo',

                        'recommended_width' =>
                            180,

                        'recommended_height' =>
                            60,

                        'recommended_format' =>
                            'PNG',

                        'storage_owner' =>
                            'requesting_workspace',
                    ],
                ],

                [
                    'key' =>
                        'footer_logo',

                    'label' =>
                        'Footer Logo',

                    'description' =>
                        'Generate a footer logo using the recommended 180 × 60 px layout.',

                    'outputs' => [
                        'image',
                    ],

                    'targets' => [
                        'footer_logo',
                    ],

                    'meta' => [
                        'asset_type' =>
                            'logo',

                        'recommended_width' =>
                            180,

                        'recommended_height' =>
                            60,

                        'recommended_format' =>
                            'PNG',

                        'storage_owner' =>
                            'requesting_workspace',
                    ],
                ],

                [
                    'key' =>
                        'favicon',

                    'label' =>
                        'Favicon',

                    'description' =>
                        'Generate a favicon using the recommended 64 × 64 px square format.',

                    'outputs' => [
                        'image',
                    ],

                    'targets' => [
                        'favicon',
                    ],

                    'meta' => [
                        'asset_type' =>
                            'favicon',

                        'recommended_width' =>
                            64,

                        'recommended_height' =>
                            64,

                        'recommended_format' =>
                            'PNG',

                        'storage_owner' =>
                            'requesting_workspace',
                    ],
                ],

                [
                    'key' => 'hero',
                    'label' => 'Hero',
                    'description' =>
                        'Generate or improve homepage hero content and imagery.',
                    'outputs' => [
                        'text',
                        'image',
                    ],
                    'targets' => [
                        'hero_badge',
                        'hero_title',
                        'hero_text',
                        'hero_primary_button',
                        'hero_secondary_button',
                        'hero_image',
                    ],
                ],

                [
                    'key' => 'features',
                    'label' => 'Features',
                    'description' =>
                        'Generate or improve homepage feature content.',
                    'outputs' => [
                        'text',
                        'structured-data',
                    ],
                    'targets' => [
                        'features_heading',
                        'features_text',
                        'features',
                    ],
                ],

                [
                    'key' => 'stats',
                    'label' => 'Statistics',
                    'description' =>
                        'Generate or improve homepage statistics.',
                    'outputs' => [
                        'text',
                        'structured-data',
                    ],
                    'targets' => [
                        'stats',
                    ],
                ],

                [
                    'key' => 'about',
                    'label' => 'About Us',
                    'description' =>
                        'Generate or improve About Us content and imagery.',
                    'outputs' => [
                        'text',
                        'image',
                    ],
                    'targets' => [
                        'about_heading',
                        'about_text',
                        'about_image',
                    ],
                ],

                [
                    'key' => 'testimonials',
                    'label' => 'Testimonials',
                    'description' =>
                        'Generate or improve testimonial content.',
                    'outputs' => [
                        'text',
                        'structured-data',
                    ],
                    'targets' => [
                        'testimonials_heading',
                        'testimonials',
                    ],
                ],

                [
                    'key' => 'cta',
                    'label' => 'Final Call to Action',
                    'description' =>
                        'Generate or improve the homepage final call to action.',
                    'outputs' => [
                        'text',
                    ],
                    'targets' => [
                        'cta_heading',
                        'cta_text',
                        'cta_button',
                    ],
                ],
            ],
        ];
    }
}
