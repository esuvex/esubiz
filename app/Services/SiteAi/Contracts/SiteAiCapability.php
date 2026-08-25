<?php

namespace App\Services\SiteAi\Contracts;

/**
 * A Site AI Capability describes one website area that can
 * safely use the central Site AI Engine.
 *
 * Examples:
 *
 * theme.homepage
 * page-builder.section
 * page-builder.widget
 * product
 * blog
 * seo
 * form
 * email
 */
interface SiteAiCapability
{
    /**
     * Globally unique capability key.
     */
    public function key(): string;


    /**
     * Describe exactly what AI is allowed to work with.
     *
     * The manifest is the security boundary.
     */
    public function manifest(
        array $context = []
    ): array;


    /**
     * Sanitize/normalize incoming feature data before it is
     * supplied to AI.
     */
    public function prepare(
        array $payload,
        array $context = []
    ): array;


    /**
     * Sanitize AI output before returning it to the caller.
     *
     * This prevents AI from injecting fields that the website
     * feature did not expose in its manifest.
     */
    public function normalizeResult(
        array $result,
        array $context = []
    ): array;
}
