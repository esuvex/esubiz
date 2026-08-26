<?php

namespace App\Services\SiteAi\Support;

/**
 * ============================================================
 * ESUBIZ AI FUNCTION CONTRIBUTOR
 * ============================================================
 *
 * The Central AI Assistant is global.
 *
 * Site areas do not build assistants.
 *
 * They CONTRIBUT the AI functions they support.
 *
 * Example:
 *
 * app(SiteAiContext::class)->contribute([
 *
 *     'source' => 'theme.homepage',
 *
 *     'functions' => [
 *
 *         [
 *             'key' => 'hero',
 *             'label' => 'Hero',
 *             'description' =>
 *                 'Generate hero text and image.',
 *             'outputs' => [
 *                 'text',
 *                 'image',
 *             ],
 *         ],
 *
 *         [
 *             'key' => 'about',
 *             'label' => 'About Us',
 *             'outputs' => [
 *                 'text',
 *                 'image',
 *             ],
 *         ],
 *     ],
 * ]);
 *
 *
 * Another feature can contribute its own functions without
 * changing the AI Assistant itself.
 */
class SiteAiContext
{
    /**
     * Contributions available on the current request/page.
     */
    protected array $contributions = [];


    /**
     * Add AI functions from a site area.
     *
     * Multiple areas/components on the same page may contribute.
     */
    public function contribute(
        array $contribution
    ): static {

        $source =
            trim(
                (string) (
                    $contribution[
                        'source'
                    ]
                    ?? $contribution[
                        'capability'
                    ]
                    ?? ''
                )
            );


        if ($source === '') {
            return $this;
        }


        $functions = [];

        foreach (
            (array) (
                $contribution[
                    'functions'
                ]
                ?? $contribution[
                    'jobs'
                ]
                ?? []
            )
            as $function
        ) {

            if (is_string($function)) {

                $function = [
                    'key' =>
                        $function,

                    'label' =>
                        $function,
                ];
            }


            if (!is_array($function)) {
                continue;
            }


            $key =
                trim(
                    (string) (
                        $function[
                            'key'
                        ]
                        ?? $function[
                            'action'
                        ]
                        ?? ''
                    )
                );


            if ($key === '') {
                continue;
            }


            $functions[$key] = [
                'key' =>
                    $key,

                'label' =>
                    trim(
                        (string) (
                            $function[
                                'label'
                            ]
                            ?? $key
                        )
                    ),

                'description' =>
                    trim(
                        (string) (
                            $function[
                                'description'
                            ]
                            ?? ''
                        )
                    ),

                /*
                 * Examples:
                 *
                 * text
                 * image
                 * structured-data
                 * code
                 */
                'outputs' =>
                    array_values(
                        array_unique(
                            (array) (
                                $function[
                                    'outputs'
                                ]
                                ?? ['text']
                            )
                        )
                    ),

                /*
                 * Fields/targets are the security boundary.
                 *
                 * AI may only return changes for targets
                 * explicitly contributed by the feature.
                 */
                'targets' =>
                    array_values(
                        (array) (
                            $function[
                                'targets'
                            ]
                            ?? []
                        )
                    ),

                'meta' =>
                    (array) (
                        $function[
                            'meta'
                        ]
                        ?? []
                    ),
            ];
        }


        $this->contributions[
            $source
        ] = [
            'source' =>
                $source,

            'label' =>
                trim(
                    (string) (
                        $contribution[
                            'label'
                        ]
                        ?? ''
                    )
                ),

            'description' =>
                trim(
                    (string) (
                        $contribution[
                            'description'
                        ]
                        ?? ''
                    )
                ),

            'functions' =>
                array_values(
                    $functions
                ),

            'context' =>
                (array) (
                    $contribution[
                        'context'
                    ]
                    ?? $contribution[
                        'data'
                    ]
                    ?? []
                ),
        ];


        return $this;
    }


    /**
     * Backward-compatible alias for the foundation we already
     * created.
     */
    public function register(
        array $context
    ): static {

        return $this->contribute(
            $context
        );
    }


    public function active(): bool
    {
        foreach (
            $this->contributions
            as $contribution
        ) {

            if (
                !empty(
                    $contribution[
                        'functions'
                    ]
                )
            ) {
                return true;
            }
        }


        return false;
    }


    /**
     * All functions contributed by the current site area.
     */
    public function contributions(): array
    {
        return array_values(
            $this->contributions
        );
    }


    /**
     * Flattened function list used by the global assistant.
     */
    public function functions(): array
    {
        $functions = [];


        foreach (
            $this->contributions()
            as $contribution
        ) {

            foreach (
                $contribution[
                    'functions'
                ]
                as $function
            ) {

                $function[
                    'source'
                ] =
                    $contribution[
                        'source'
                    ];


                $functions[] =
                    $function;
            }
        }


        return $functions;
    }


    /**
     * Compatibility with the earlier assistant component.
     */
    public function get(): array
    {
        return [
            'active' =>
                $this->active(),

            'contributions' =>
                $this->contributions(),

            'functions' =>
                $this->functions(),
        ];
    }


    public function clear(): static
    {
        $this->contributions = [];

        return $this;
    }
}
