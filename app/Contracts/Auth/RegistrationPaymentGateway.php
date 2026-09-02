<?php

namespace App\Contracts\Auth;

/**
 * ESUBIZ_REGISTRATION_PAYMENT_GATEWAY_CONTRACT_V1
 *
 * Bridge between Auth Forms and the website's existing payment system.
 *
 * Registration does NOT implement Paystack/Flutterwave/etc itself.
 * The active site's payment layer supplies an implementation.
 *
 * This keeps the same Core code portable for SaaS and off-server sites.
 */
interface RegistrationPaymentGateway
{
    /**
     * Return gateways currently available to this website.
     */
    public function availableGateways(): array;


    /**
     * Start payment for a registration transaction.
     *
     * Implementations decide whether this is redirect, embedded,
     * iframe, API-driven or another site-supported transport.
     *
     * URLs must be generated from the current deployment/payment
     * implementation and must never be hardcoded by Auth Forms.
     */
    public function begin(array $registrationTransaction): array;


    /**
     * Verify payment using the site's authoritative payment layer.
     */
    public function verify(string $reference): array;
}
