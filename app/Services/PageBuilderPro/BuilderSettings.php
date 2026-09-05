<?php

namespace App\Services\PageBuilderPro;

/**
 * ESUBIZ_PAGE_BUILDER_PRO_SETTINGS_V1
 *
 * Portable responsive/style/animation settings shared by
 * sections, columns and widgets.
 *
 * No Central or Core storage dependency.
 */
class BuilderSettings
{
    public static function element(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Responsive visibility
            |--------------------------------------------------------------------------
            */
            'visibility' => [
                'desktop' => true,
                'tablet' => true,
                'mobile' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Responsive spacing
            |--------------------------------------------------------------------------
            */
            'margin' => [
                'desktop' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                'tablet'  => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                'mobile'  => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
            ],

            'padding' => [
                'desktop' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                'tablet'  => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                'mobile'  => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
            ],

            /*
            |--------------------------------------------------------------------------
            | Responsive typography/layout helpers
            |--------------------------------------------------------------------------
            */
            'alignment' => [
                'desktop' => '',
                'tablet' => '',
                'mobile' => '',
            ],

            'order' => [
                'desktop' => null,
                'tablet' => null,
                'mobile' => null,
            ],

            /*
            |--------------------------------------------------------------------------
            | Background
            |--------------------------------------------------------------------------
            */
            'background' => [
                'type' => 'none',
                'color' => '',
                'gradient' => [
                    'from' => '',
                    'to' => '',
                    'direction' => 'to bottom right',
                ],
                'image' => '',
                'position' => 'center center',
                'size' => 'cover',
                'repeat' => 'no-repeat',
                'attachment' => 'scroll',
                'overlay_color' => '',
                'overlay_opacity' => 0,
            ],

            /*
            |--------------------------------------------------------------------------
            | Border / radius / shadow
            |--------------------------------------------------------------------------
            */
            'border' => [
                'width' => 0,
                'style' => 'solid',
                'color' => '',
            ],

            'radius' => [
                'top_left' => 0,
                'top_right' => 0,
                'bottom_right' => 0,
                'bottom_left' => 0,
            ],

            'shadow' => [
                'enabled' => false,
                'x' => 0,
                'y' => 8,
                'blur' => 24,
                'spread' => 0,
                'color' => 'rgba(15,23,42,.12)',
            ],

            /*
            |--------------------------------------------------------------------------
            | Animation
            |--------------------------------------------------------------------------
            */
            'animation' => [
                'enabled' => false,
                'type' => 'fade-up',
                'duration' => 600,
                'delay' => 0,
                'easing' => 'ease-out',
                'once' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Position / advanced
            |--------------------------------------------------------------------------
            */
            'position' => [
                'type' => 'relative',
                'sticky_top' => null,
                'z_index' => null,
            ],

            'overflow' => 'visible',

            'anchor_id' => '',

            'css_classes' => '',
        ];
    }

    public static function section(): array
    {
        return array_replace_recursive(
            self::element(),
            [
                'width' => 'container',
                'max_width' => '',
                'min_height' => '',
                'content_position' => 'center',
                'gap' => [
                    'desktop' => 24,
                    'tablet' => 20,
                    'mobile' => 16,
                ],
                'padding' => [
                    'desktop' => ['top' => 48, 'right' => 0, 'bottom' => 48, 'left' => 0],
                    'tablet'  => ['top' => 40, 'right' => 0, 'bottom' => 40, 'left' => 0],
                    'mobile'  => ['top' => 32, 'right' => 0, 'bottom' => 32, 'left' => 0],
                ],
            ]
        );
    }

    public static function column(): array
    {
        return array_replace_recursive(
            self::element(),
            [
                'vertical_align' => 'top',
                'horizontal_align' => 'stretch',
            ]
        );
    }

    public static function widget(): array
    {
        return self::element();
    }
}
