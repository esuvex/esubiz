<?php

namespace App\Services\SiteAi\Capabilities;

use App\Services\SiteAi\Contracts\SiteAiCapability;

/**
 * Generic Theme Homepage capability.
 *
 * IMPORTANT:
 *
 * This is NOT Business-theme-specific.
 *
 * Every theme supplies its own homepage manifest containing
 * the homepage sections and fields it exposes to AI.
 *
 * Business might expose:
 *
 * hero
 * features
 * stats
 * about
 * testimonials
 * cta
 *
 * Ecommerce may expose:
 *
 * hero
 * categories
 * featured_products
 * promotions
 * testimonials
 * cta
 *
 * Hotel may expose:
 *
 * hero
 * rooms
 * amenities
 * gallery
 * testimonials
 * booking_cta
 */
class ThemeHomepageCapability implements SiteAiCapability
{
    public function key(): string
    {
        return 'theme.homepage';
    }


    public function manifest(
        array $context = []
    ): array {

        $themeManifest =
            $context[
                'theme_ai_manifest'
            ] ?? [];


        return [
            /*
             * Text and image generation are supported from
             * the same capability.
             */
            'actions' => [
                'generate-text',
                'rewrite',
                'improve',
                'shorten',
                'expand',
                'generate-image',
                'generate-section',
                'generate-homepage',
            ],

            /*
             * Only the theme's declared homepage sections
             * are made available.
             */
            'sections' =>
                $themeManifest[
                    'sections'
                ] ?? [],

            'supports' => [
                'text' => true,
                'images' => true,
                'mixed' => true,
            ],

            /*
             * Explicit security boundary.
             *
             * Theme AI cannot alter these areas through the
             * homepage capability.
             */
            'forbidden' => [
                'header',
                'navigation',
                'menus',
                'footer',
                'favicon',
                'global_theme_settings',
                'floating_tools',
            ],
        ];
    }


    public function prepare(
        array $payload,
        array $context = []
    ): array {

        $manifest =
            $this->manifest(
                $context
            );


        $allowedSections =
            array_keys(
                $manifest[
                    'sections'
                ] ?? []
            );


        /*
         * Only declared homepage sections survive.
         */
        $sections = [];

        foreach (
            (array) (
                $payload[
                    'sections'
                ] ?? []
            )
            as $section => $data
        ) {
            if (
                in_array(
                    $section,
                    $allowedSections,
                    true
                )
            ) {
                $sections[$section] =
                    $data;
            }
        }


        return [
            'prompt' =>
                trim(
                    (string) (
                        $payload[
                            'prompt'
                        ] ?? ''
                    )
                ),

            'sections' =>
                $sections,

            'target_section' =>
                $payload[
                    'target_section'
                ] ?? null,
        ];
    }


    public function normalizeResult(
        array $result,
        array $context = []
    ): array {

        $manifest =
            $this->manifest(
                $context
            );


        $allowedSections =
            array_keys(
                $manifest[
                    'sections'
                ] ?? []
            );


        if (
            isset(
                $result[
                    'sections'
                ]
            )
            && is_array(
                $result[
                    'sections'
                ]
            )
        ) {
            $result[
                'sections'
            ] =
                array_intersect_key(
                    $result[
                        'sections'
                    ],
                    array_flip(
                        $allowedSections
                    )
                );
        }


        return $result;
    }
}
