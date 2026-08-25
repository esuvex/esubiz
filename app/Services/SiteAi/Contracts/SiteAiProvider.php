<?php

namespace App\Services\SiteAi\Contracts;

/**
 * Every AI provider used by a website ultimately plugs into
 * this single contract.
 *
 * The actual provider may be:
 *
 * - Esubiz Central AI
 * - OpenAI routed through Esubiz
 * - another future Esubiz AI provider
 *
 * Individual website features must never call providers
 * directly.
 */
interface SiteAiProvider
{
    /**
     * Generate content for an approved website AI request.
     *
     * Expected result may contain:
     *
     * text
     * fields
     * images
     * metadata
     * usage
     */
    public function generate(
        array $request
    ): array;
}
