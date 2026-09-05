<?php

namespace App\Services\PageBuilderPro;

/**
 * ESUBIZ_PAGE_BUILDER_PRO_ONLY_WIDGET_REGISTRY_V1
 *
 * Page Builder Pro owns Pro-only widgets and enhances Basic/module widgets without owning their content.
 *
 * OWNERSHIP:
 * - Basic widgets remain owned by the Basic Page Builder.
 * - Module widgets remain owned by their respective modules and are Basic by default.
 * - This registry owns only Pro-only widgets.
 * - BuilderSettings supplies optional Pro enhancements for sections, columns and widgets.
 *
 * DEACTIVATION SAFETY:
 * Disabling Pro must not remove or overwrite Basic/module widget content.
 * Stored Pro enhancement metadata may remain dormant for later reactivation.
 *
 * The registry itself is host-neutral and Core-compatible.
 */
class ProWidgetRegistry
{
    protected array $widgets;

    public function __construct()
    {
        $this->widgets = $this->definitions();
    }

    public function all(): array
    {
        return $this->widgets;
    }

    public function has(string $type): bool
    {
        return array_key_exists($type, $this->widgets);
    }

    public function get(string $type): ?array
    {
        return $this->widgets[$type] ?? null;
    }

    public function defaults(string $type): array
    {
        return $this->widgets[$type]['defaults'] ?? [];
    }


    public function pro(): array
    {
        return array_filter(
            $this->widgets,
            fn (array $widget) => ($widget['tier'] ?? null) === 'pro'
        );
    }

    public function grouped(): array
    {
        $groups = [];

        foreach ($this->widgets as $type => $widget) {
            $category = $widget['category'] ?? 'General';
            $groups[$category][$type] = $widget;
        }

        return $groups;
    }


    protected function proWidget(
        string $label,
        string $category,
        array $defaults = []
    ): array {
        return [
            'label' => $label,
            'category' => $category,
            'tier' => 'pro',
            'defaults' => $defaults,
        ];
    }

    protected function definitions(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | PRO-ONLY PAGE BUILDER WIDGET TYPES
            |--------------------------------------------------------------------------
            */






















            /*
            |--------------------------------------------------------------------------
            | PRO — DATA / METRICS
            |--------------------------------------------------------------------------
            */

            'counter' => $this->proWidget('Animated Counter', 'Pro Metrics', [
                'prefix' => '',
                'start' => 0,
                'end' => 100,
                'suffix' => '',
                'decimals' => 0,
                'duration' => 1800,
                'label' => '',
                'trigger_on_view' => true,
            ]),

            'progress_bar' => $this->proWidget('Progress Bar', 'Pro Metrics', [
                'label' => '',
                'value' => 75,
                'max' => 100,
                'show_value' => true,
                'animate' => true,
            ]),

            'countdown' => $this->proWidget('Countdown', 'Pro Metrics', [
                'target' => '',
                'show_days' => true,
                'show_hours' => true,
                'show_minutes' => true,
                'show_seconds' => true,
                'completed_text' => '',
            ]),

            /*
            |--------------------------------------------------------------------------
            | PRO — INTERACTIVE
            |--------------------------------------------------------------------------
            */

            'tabs' => $this->proWidget('Tabs', 'Pro Interactive', [
                'items' => [],
                'active' => 0,
            ]),

            'vertical_tabs' => $this->proWidget('Vertical Tabs', 'Pro Interactive', [
                'items' => [],
                'active' => 0,
            ]),

            'modal_trigger' => $this->proWidget('Modal / Popup', 'Pro Interactive', [
                'button_text' => 'Open',
                'heading' => '',
                'content' => '',
            ]),

            'flip_card' => $this->proWidget('Flip Card', 'Pro Interactive', [
                'front_heading' => '',
                'front_text' => '',
                'back_heading' => '',
                'back_text' => '',
                'button_text' => '',
                'button_url' => '',
            ]),

            'before_after' => $this->proWidget('Before / After', 'Pro Interactive', [
                'before_image' => '',
                'after_image' => '',
                'before_label' => 'Before',
                'after_label' => 'After',
                'position' => 50,
            ]),

            /*
            |--------------------------------------------------------------------------
            | PRO — CONTENT
            |--------------------------------------------------------------------------
            */

            'icon_box' => $this->proWidget('Icon Box', 'Pro Content', [
                'icon' => '',
                'heading' => '',
                'text' => '',
                'url' => '',
            ]),

            'icon_list' => $this->proWidget('Icon List', 'Pro Content', [
                'items' => [],
            ]),

            'badge' => $this->proWidget('Badge', 'Pro Content', [
                'text' => '',
                'icon' => '',
            ]),

            'quote' => $this->proWidget('Quote', 'Pro Content', [
                'quote' => '',
                'author' => '',
                'role' => '',
            ]),

            'marquee' => $this->proWidget('Marquee / Ticker', 'Pro Content', [
                'items' => [],
                'speed' => 40,
                'direction' => 'left',
                'pause_on_hover' => true,
            ]),

            /*
            |--------------------------------------------------------------------------
            | PRO — BUSINESS / MARKETING
            |--------------------------------------------------------------------------
            */

            'pricing_table' => $this->proWidget('Pricing Table', 'Pro Marketing', [
                'heading' => '',
                'items' => [],
            ]),

            'comparison_table' => $this->proWidget('Comparison Table', 'Pro Marketing', [
                'columns' => [],
                'rows' => [],
            ]),

            'logo_cloud' => $this->proWidget('Logo Cloud', 'Pro Marketing', [
                'heading' => '',
                'items' => [],
            ]),

            'team' => $this->proWidget('Team', 'Pro Marketing', [
                'heading' => '',
                'members' => [],
            ]),

            'portfolio' => $this->proWidget('Portfolio / Project Grid', 'Pro Marketing', [
                'heading' => '',
                'items' => [],
                'filters' => [],
            ]),

            /*
            |--------------------------------------------------------------------------
            | PRO — NAVIGATION
            |--------------------------------------------------------------------------
            */

            'breadcrumbs' => $this->proWidget('Breadcrumbs', 'Pro Navigation', [
                'items' => [],
            ]),

            'table_of_contents' => $this->proWidget('Table of Contents', 'Pro Navigation', [
                'heading' => 'On this page',
                'items' => [],
                'sticky' => true,
            ]),

            'page_navigation' => $this->proWidget('Previous / Next', 'Pro Navigation', [
                'previous_label' => '',
                'previous_url' => '',
                'next_label' => '',
                'next_url' => '',
            ]),

            'search_box' => $this->proWidget('Search Box', 'Pro Navigation', [
                'placeholder' => 'Search',
                'scope' => 'page',
            ]),

            /*
            |--------------------------------------------------------------------------
            | PRO — DOCUMENTATION / TECHNICAL
            |--------------------------------------------------------------------------
            */

            'code' => $this->proWidget('Code / Command', 'Documentation', [
                'language' => 'text',
                'code' => '',
                'copy_button' => true,
                'filename' => '',
            ]),

            'callout' => $this->proWidget('Notice / Callout', 'Documentation', [
                'type' => 'info',
                'title' => '',
                'text' => '',
            ]),

            'checklist' => $this->proWidget('Checklist', 'Documentation', [
                'heading' => '',
                'items' => [],
            ]),

            'steps' => $this->proWidget('Steps / Timeline', 'Documentation', [
                'heading' => '',
                'items' => [],
            ]),

            'timeline' => $this->proWidget('Timeline', 'Documentation', [
                'heading' => '',
                'items' => [],
            ]),

            'screenshot' => $this->proWidget('Screenshot', 'Documentation', [
                'src' => '',
                'alt' => '',
                'caption' => '',
                'zoomable' => true,
            ]),

            'table' => $this->proWidget('Table', 'Documentation', [
                'headers' => [],
                'rows' => [],
                'striped' => false,
            ]),

            'resources' => $this->proWidget('Downloads / Resources', 'Documentation', [
                'heading' => '',
                'items' => [],
            ]),

            'installer' => $this->proWidget('Installer', 'Documentation', [
                'title' => 'Esubiz Installation',
                'steps' => [
                    [
                        'key' => 'licence',
                        'label' => 'Website & Licence',
                    ],
                    [
                        'key' => 'requirements',
                        'label' => 'System Check',
                    ],
                    [
                        'key' => 'database',
                        'label' => 'Database',
                    ],
                    [
                        'key' => 'administrator',
                        'label' => 'Administrator',
                    ],
                    [
                        'key' => 'installation',
                        'label' => 'Installation',
                    ],
                ],
            ]),
        ];
    }
}
