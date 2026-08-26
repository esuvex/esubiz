<?php

namespace App\Services\SiteAi\Capabilities;

use App\Services\SiteAi\Contracts\SiteAiCapability;

/**
 * Generic Site AI capability.
 *
 * This is the universal bridge used by the global AI assistant
 * when a website area does not yet have a specialised capability.
 *
 * Specialised capabilities can still be registered later for
 * products, themes, pages, SEO, forms, modules, etc.
 */
class GenericSiteCapability implements SiteAiCapability
{
    public function __construct(
        protected string $capabilityKey = 'site'
    ) {
    }


    public function key(): string
    {
        return $this->capabilityKey;
    }


    public function manifest(
        array $context = []
    ): array {
        return [
            'key' => $this->capabilityKey,

            /*
             * Generic Site AI actions.
             *
             * These describe AI content operations only.
             * They do not grant filesystem, database, shell or
             * server execution privileges.
             */
            'actions' => [
                'generate',
                'improve',
                'rewrite',
                'shorten',
                'expand',
                'suggest',
            ],

            'allowed_inputs' => [
                'prompt',
                'selected',
                'features',
                'reference_files',
                'instructions',
                'content',
                'data',
            ],

            'allowed_outputs' => [
                'text',
                'content',
                'html',
                'title',
                'description',
                'image_prompt',
                'suggestions',
                'data',
            ],

            'context' => [
                'website_id' =>
                    $context['website_id'] ?? null,

                'website_type' =>
                    $context['website_type'] ?? null,

                'area' =>
                    $context['area'] ?? null,

                'route' =>
                    $context['route'] ?? null,
            ],
        ];
    }


    public function prepare(
        array $payload,
        array $context = []
    ): array {
        $allowed = [
            'prompt',
            'selected',
            'features',
            'reference_files',
            'instructions',
            'content',
            'data',
        ];

        $prepared = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $payload)) {
                $prepared[$key] = $payload[$key];
            }
        }


        /*
         * The global assistant may send the selected feature
         * collection using either selected or features.
         */
        if (
            !isset($prepared['selected'])
            && isset($prepared['features'])
        ) {
            $prepared['selected'] =
                $prepared['features'];
        }


        $prepared['capability'] =
            $this->capabilityKey;

        $prepared['context'] = [
            'website_id' =>
                $context['website_id'] ?? null,

            'website_type' =>
                $context['website_type'] ?? null,

            'area' =>
                $context['area'] ?? null,

            'route' =>
                $context['route'] ?? null,
        ];


        return $prepared;
    }


    public function normalizeResult(
        array $result,
        array $context = []
    ): array {
        /*
         * Keep the central engine's normal response structure.
         *
         * We intentionally remove executable/server-side fields
         * should a model ever attempt to return them.
         */
        foreach (
            [
                'command',
                'shell',
                'php',
                'sql',
                'migration',
                'filesystem',
                'server_command',
            ]
            as $unsafe
        ) {
            unset($result[$unsafe]);
        }


        return $result;
    }
}
