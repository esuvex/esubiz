<?php

namespace App\Contracts\Auth;

/**
 * ESUBIZ_REGISTRATION_OFFER_PROVIDER_CONTRACT_V1
 *
 * Plug-and-play contract for anything that can be offered during
 * registration: membership plans, subscriptions, products, packages,
 * services or module-defined registration choices.
 *
 * Implementations may come from Core, website types, modules or themes.
 *
 * IMPORTANT:
 * - No SaaS hostname assumptions.
 * - No off-server hostname assumptions.
 * - No direct Central database dependency.
 * - Returned IDs are provider-owned portable references.
 */
interface RegistrationOfferProvider
{
    /**
     * Stable provider key.
     *
     * Examples are implementation-defined; Core does not hardcode them.
     */
    public function key(): string;


    /**
     * Human-readable provider name for the Auth Form manager.
     */
    public function name(): string;


    /**
     * Whether this provider is available in the current website context.
     */
    public function available(): bool;


    /**
     * Return registration-selectable offers.
     *
     * Each item should use the portable shape:
     *
     * [
     *   'id'       => 'provider-owned-id',
     *   'name'     => 'Display name',
     *   'type'     => 'plan|product|membership|service|...',
     *   'paid'     => true|false,
     *   'amount'   => optional numeric display value,
     *   'currency' => optional currency,
     *   'meta'     => optional provider-owned metadata,
     * ]
     */
    public function offers(): array;


    /**
     * Validate that an offer reference still belongs to this provider
     * and remains available for registration.
     */
    public function resolve(string $offerId): ?array;
}
