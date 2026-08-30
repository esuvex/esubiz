<?php

namespace App\Services\SiteAi\Proposals\Contracts;

use App\Models\Ai\AiProposal;

interface AiProposalApplier
{
    /**
     * Capability handled by this applier.
     *
     * Examples:
     *
     * theme.homepage
     * theme.generate
     * branding.logo
     * branding.favicon
     * page.builder
     * ecommerce.product
     */
    public function capability(): string;


    /**
     * Validate that this approved proposal can still be
     * applied to its ORIGINAL destination.
     *
     * This must verify:
     *
     * - capability
     * - destination
     * - target scope
     * - current website/installation
     * - capability-specific constraints
     *
     * No content is written here.
     */
    public function validate(
        AiProposal $proposal
    ): void;


    /**
     * Apply approved output to the website's NORMAL
     * editable storage/location.
     *
     * IMPORTANT:
     *
     * SaaS:
     *   Write into the tenant database/storage/media system.
     *
     * Off-server:
     *   Dispatch through the authenticated installation/Core
     *   API so the off-server website writes into its own
     *   database/storage/media system.
     *
     * Central Esubiz must NOT become the permanent live
     * content/media store for website-owned output.
     *
     * Return application metadata/references only.
     */
    public function apply(
        AiProposal $proposal
    ): array;
}
