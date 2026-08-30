<?php

namespace App\Services\SiteAi\Proposals\Contracts;

use App\Models\Ai\AiProposal;
use App\Models\Website;

interface AiProposalDestination
{
    /**
     * Determine whether this destination transport owns
     * persistence for the supplied website/proposal.
     *
     * Examples:
     *
     * SaaS website:
     *   Esubiz tenant database + tenant storage/media.
     *
     * Off-server website:
     *   Authenticated Esubiz/Core installation API, with the
     *   remote installation writing to its own DB/storage.
     */
    public function supports(
        ?Website $website,
        AiProposal $proposal
    ): bool;


    /**
     * Validate that the original proposal destination still
     * exists and belongs to the same website/installation.
     *
     * No live content may be changed here.
     */
    public function validate(
        ?Website $website,
        AiProposal $proposal
    ): void;


    /**
     * Persist approved structured values into the website's
     * NORMAL editable content/settings location.
     *
     * $values is keyed by the manifest target_key.
     *
     * Example:
     *
     * [
     *     'about_title' => 'Who We Are',
     *     'about_text' => '...',
     *     'about_image_path' => '...',
     * ]
     *
     * These must become the same values read by the normal
     * site-admin editor. They must not be stored as a second
     * AI-only copy.
     */
    public function saveValues(
        ?Website $website,
        AiProposal $proposal,
        array $values
    ): array;


    /**
     * Persist an approved generated asset into the website's
     * own media/file storage and return its NORMAL local
     * reference/path.
     *
     * Examples:
     *
     * logo
     * favicon
     * About image
     * generated section image
     * generated downloadable file
     */
    public function saveAsset(
        ?Website $website,
        AiProposal $proposal,
        array $asset,
        array $target = []
    ): array;


    /**
     * Install an approved generated package into the
     * website's own package system.
     *
     * Primary initial use:
     * generated themes.
     *
     * The resulting theme must appear in the normal
     * Installed Themes area and behave exactly like any
     * manually installed theme.
     */
    public function installPackage(
        ?Website $website,
        AiProposal $proposal,
        array $package,
        array $target = []
    ): array;
}
