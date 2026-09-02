<?php

namespace App\Services\SiteAi;

use App\Models\Website;
use App\Services\SiteAi\Contracts\SiteAiProvider;
use RuntimeException;

/**
 * ============================================================
 * ESUBIZ SITE AI ENGINE
 * ============================================================
 *
 * One AI engine for every website feature.
 *
 * Features do NOT communicate with AI providers directly.
 *
 * Flow:
 *
 * Website Feature
 *      ↓
 * Site AI Capability
 *      ↓
 * Site AI Engine
 *      ↓
 * Central Esubiz AI Provider
 *      ↓
 * AI Credit System
 *      ↓
 * Model Router
 *
 * Examples:
 *
 * Theme Homepage
 * Page Builder
 * Product Editor
 * Blog
 * SEO
 * Forms
 * Email
 * Hotel content
 * Ecommerce content
 * etc.
 */
class SiteAiEngine
{
    protected ?SiteAiProvider $provider = null;


    public function __construct(
        protected SiteAiRegistry $registry
    ) {
    }


    /**
     * Provider is installed centrally.
     *
     * Website features never choose providers themselves.
     */
    public function useProvider(
        SiteAiProvider $provider
    ): static {
        $this->provider = $provider;

        return $this;
    }


    /**
     * Execute one website AI operation.
     */
    public function generate(
        Website $website,
        string $capabilityKey,
        string $action,
        array $payload = [],
        array $context = []
    ): array {

        if (!$this->provider) {
            throw new RuntimeException(
                'The central Site AI provider has not been configured.'
            );
        }


        $capability =
            $this->registry->get(
                $capabilityKey
            );


        $manifest =
            $capability->manifest(
                $context
            );


        $allowedActions =
            $manifest[
                'actions'
            ] ?? [];


        if (
            !in_array(
                $action,
                $allowedActions,
                true
            )
        ) {
            throw new RuntimeException(
                "AI action [{$action}] is not allowed "
                . "for capability [{$capabilityKey}]."
            );
        }


        $prepared =
            $capability->prepare(
                $payload,
                $context
            );


        /*
         * This is the canonical request contract sent to the
         * central Esubiz AI provider.
         */
        $request = [
            'website_id' =>
                $website->id,

            'capability' =>
                $capabilityKey,

            'action' =>
                $action,

            'manifest' =>
                $manifest,

            'payload' =>
                $prepared,

            'context' =>
                $context,
        ];


        $result =
            $this->provider
                ->generate(
                    $request
                );


        return $capability
            ->normalizeResult(
                $result,
                $context
            );
    }
}
