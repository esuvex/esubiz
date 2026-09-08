<?php

namespace App\Http\Controllers;

use App\Services\Marketplace\Licensing\MarketplaceLicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

/**
 * ESUBIZ_MARKETPLACE_LICENSE_VALIDATION_API_V1
 *
 * Central Esubiz is the authoritative license validator.
 *
 * Used by:
 *
 * 1. Off-server Core automatic Marketplace fulfilment.
 * 2. Off-server Core manual ZIP installation License Validation box.
 *
 * This endpoint never reveals another customer's human-readable
 * license key. It only validates a license supplied by Core.
 */
class MarketplaceLicenseController extends Controller
{
    /**
     * ESUBIZ_MARKETPLACE_PORTABLE_LICENSE_VALIDATION_V2
     *
     * Validate and bind a Marketplace licence to an off-server
     * Core instance.
     *
     * Portable Core packages do not know Central database IDs.
     * Therefore the request identifies the intended product with:
     *
     * - product_type
     * - product_slug
     * - product_version
     *
     * The licence itself remains authoritative for entitlement
     * and Central product identity.
     *
     * Legacy callers using core_instance_uuid + product_id remain
     * supported during the migration period.
     */
    public function validateLicense(
        Request $request,
        MarketplaceLicenseService $licenses
    ): JsonResponse {
        $validated =
            $request->validate([
                'license_key' => [
                    'required',
                    'string',
                    'max:191',
                ],

                /*
                 * New portable Core request.
                 */
                'instance_uuid' => [
                    'nullable',
                    'uuid',
                    'required_without:core_instance_uuid',
                ],

                /*
                 * Existing Marketplace callers.
                 */
                'core_instance_uuid' => [
                    'nullable',
                    'uuid',
                    'required_without:instance_uuid',
                ],

                'product_type' => [
                    'required',
                    'string',
                    'max:64',
                ],

                /*
                 * Numeric Central ID is now optional.
                 * Existing callers may continue sending it.
                 */
                'product_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'product_slug' => [
                    'nullable',
                    'string',
                    'max:191',
                ],

                'product_name' => [
                    'nullable',
                    'string',
                    'max:191',
                ],

                'product_version' => [
                    'nullable',
                    'string',
                    'max:64',
                ],

                'deployment' => [
                    'nullable',
                    'string',
                    'in:off_server',
                ],

                'domain' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'installation_fingerprint' => [
                    'nullable',
                    'string',
                    'max:128',
                ],

                'core_version' => [
                    'nullable',
                    'string',
                    'max:64',
                ],
            ]);

        $instanceUuid =
            (string) (
                $validated['instance_uuid']
                ?? $validated['core_instance_uuid']
                ?? ''
            );

        try {
            /*
             * -----------------------------------------------------
             * 1. Let the existing licensing service authenticate
             *    the real key and bind/revalidate this Core UUID.
             *
             * This preserves all existing transactional entitlement
             * and activation rules.
             * -----------------------------------------------------
             */
            $result =
                $licenses->validateAndActivate(
                    $validated['license_key'],
                    $instanceUuid,
                    [
                        'domain' =>
                            $validated['domain']
                            ?? null,

                        'installation_fingerprint' =>
                            $validated[
                                'installation_fingerprint'
                            ]
                            ?? null,

                        /*
                         * Initial Core installation sends its
                         * package version as product_version.
                         * Existing callers can still send
                         * core_version explicitly.
                         */
                        'core_version' =>
                            $validated['core_version']
                            ?? $validated['product_version']
                            ?? null,

                        'metadata' => [
                            'validation_source' =>
                                'core_marketplace',

                            'request_identity_mode' =>
                                isset(
                                    $validated['product_id']
                                )
                                    ? 'central_product_id'
                                    : 'portable_product_identity',

                            'requested_product_type' =>
                                $validated['product_type'],

                            'requested_product_slug' =>
                                $validated['product_slug']
                                ?? null,

                            'requested_product_version' =>
                                $validated['product_version']
                                ?? null,

                            'ip' =>
                                $request->ip(),
                        ],
                    ]
                );

            /*
             * -----------------------------------------------------
             * 2. PRODUCT TYPE BINDING
             * -----------------------------------------------------
             *
             * A valid licence for another Marketplace product type
             * must never authorize this request.
             */
            if (
                strtolower(
                    trim(
                        (string) (
                            $result['product_type']
                            ?? ''
                        )
                    )
                )
                !==
                strtolower(
                    trim(
                        (string) $validated[
                            'product_type'
                        ]
                    )
                )
            ) {
                throw new RuntimeException(
                    'License is not valid for this Marketplace product type.'
                );
            }

            /*
             * -----------------------------------------------------
             * 3. LEGACY NUMERIC PRODUCT BINDING
             * -----------------------------------------------------
             *
             * If an existing caller supplies Central product_id,
             * preserve the original exact numeric binding rule.
             */
            if (
                isset(
                    $validated['product_id']
                )
                && (
                    (int) (
                        $result['product_id']
                        ?? 0
                    )
                    !==
                    (int) $validated[
                        'product_id'
                    ]
                )
            ) {
                throw new RuntimeException(
                    'License is not valid for this Marketplace product.'
                );
            }

            /*
             * -----------------------------------------------------
             * 4. PORTABLE SLUG BINDING
             * -----------------------------------------------------
             *
             * Portable packages identify themselves by stable slug,
             * not by Central auto-increment IDs.
             *
             * The licensing service result is authoritative.
             */
            $requestedSlug =
                strtolower(
                    trim(
                        (string) (
                            $validated['product_slug']
                            ?? ''
                        )
                    )
                );

            $licensedSlug =
                strtolower(
                    trim(
                        (string) (
                            $result['product_slug']
                            ?? ''
                        )
                    )
                );

            if ($requestedSlug !== '') {
                if ($licensedSlug === '') {
                    throw new RuntimeException(
                        'Central licence entitlement does not expose a portable product slug.'
                    );
                }

                if (
                    !hash_equals(
                        $licensedSlug,
                        $requestedSlug
                    )
                ) {
                    throw new RuntimeException(
                        'License is not valid for this Marketplace product.'
                    );
                }
            }

            /*
             * -----------------------------------------------------
             * 5. VERSION BINDING
             * -----------------------------------------------------
             *
             * When the entitlement/license exposes a concrete
             * product version, the package version must match.
             *
             * If Central intentionally leaves product_version null,
             * the entitlement is version-neutral and no artificial
             * version is invented here.
             */
            $requestedVersion =
                trim(
                    (string) (
                        $validated['product_version']
                        ?? ''
                    )
                );

            $licensedVersion =
                trim(
                    (string) (
                        $result['product_version']
                        ?? ''
                    )
                );

            if (
                $requestedVersion !== ''
                && $licensedVersion !== ''
                && !hash_equals(
                    $licensedVersion,
                    $requestedVersion
                )
            ) {
                throw new RuntimeException(
                    'License is not valid for this Marketplace product version.'
                );
            }

            /*
             * -----------------------------------------------------
             * 6. SUCCESS
             * -----------------------------------------------------
             *
             * Return normalized product identity so the Core can
             * verify that Central authorized the same package it is
             * currently executing.
             */
            return response()->json([
                'ok' =>
                    true,

                'message' =>
                    'Marketplace license validated successfully.',

                'license_id' =>
                    $result['license_id']
                    ?? null,

                'entitlement_id' =>
                    $result['entitlement_id']
                    ?? null,

                'product_type' =>
                    $result['product_type']
                    ?? $validated['product_type'],

                'product_id' =>
                    $result['product_id']
                    ?? null,

                'product_slug' =>
                    $result['product_slug']
                    ?? $validated['product_slug']
                    ?? null,

                'product_version' =>
                    $result['product_version']
                    ?? $validated['product_version']
                    ?? null,

                'core_instance_uuid' =>
                    $instanceUuid,

                'instance_uuid' =>
                    $instanceUuid,

                'capabilities' =>
                    $result['capabilities']
                    ?? [],

                'expires_at' =>
                    $result['expires_at']
                    ?? null,

                'activation' =>
                    $result['activation']
                    ?? null,
            ]);
        } catch (\Throwable $e) {
            report($e);

            /*
             * Do not leak licence ownership, entitlement internals,
             * database identifiers or activation records.
             */
            return response()->json(
                [
                    'ok' =>
                        false,

                    'message' =>
                        $e instanceof RuntimeException
                            ? $e->getMessage()
                            : 'Marketplace license validation failed.',
                ],
                422
            );
        }
    }

}
