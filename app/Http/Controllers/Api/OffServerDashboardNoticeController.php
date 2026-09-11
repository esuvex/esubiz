<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CentralApi\CentralServiceAuthorizationService;
use App\Services\DashboardNotices\TenantDashboardNoticeService;
use App\Services\Media\CentralMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 * ESUBIZ OFF-SERVER CORE DASHBOARD NOTICES API V1
 * ============================================================
 *
 * Central Esubiz is authoritative for Dashboard Notices.
 *
 * Security:
 *
 * installation bearer
 *      -> current off-server installation
 *      -> Central website
 *      -> active licence
 *      -> website.identity scope
 *
 * The client never supplies authoritative website_id.
 *
 * Only notices configured for:
 *
 * - all Core installations, or
 * - off-server Core
 *
 * are returned.
 */
class OffServerDashboardNoticeController extends Controller
{
    public function index(
        Request $request,
        CentralServiceAuthorizationService $authorization,
        TenantDashboardNoticeService $notices,
        CentralMediaService $media
    ): JsonResponse {

        /*
         * Reuse the existing canonical off-server Core trust chain.
         *
         * This rejects:
         *
         * - missing/invalid bearer credentials
         * - superseded installations
         * - revoked installations
         * - invalid website identity
         * - inactive licence authority
         * - insufficient API scope
         */
        $identity =
            $authorization->authorizeOffServer(
                $request,
                'website.identity'
            );

        $items =
            $notices
                ->forOffServerCore()
                ->map(
                    function ($notice) use ($media): array {
                        return [
                            'id' =>
                                (int) $notice->id,

                            'title' =>
                                (string) $notice->title,

                            /*
                             * Trusted Central Admin-authored HTML.
                             *
                             * Core renders this field as trusted
                             * Central notice content.
                             */
                            'message_html' =>
                                (string) $notice->message,

                            'variant' =>
                                (string) (
                                    $notice->variant
                                    ?: 'info'
                                ),

                            'image_url' =>
                                $notice->image_path
                                    ? $media->url(
                                        $notice->image_path
                                    )
                                    : null,

                            'dismissible' =>
                                (bool) $notice->dismissible,

                            'rotation_enabled' =>
                                (bool) $notice->rotation_enabled,

                            'rotation_seconds' =>
                                (int) (
                                    $notice->rotation_seconds
                                    ?: 8
                                ),

                            'published_at' =>
                                $notice->published_at
                                    ? $notice
                                        ->published_at
                                        ->toIso8601String()
                                    : null,

                            'expires_at' =>
                                $notice->expires_at
                                    ? $notice
                                        ->expires_at
                                        ->toIso8601String()
                                    : null,
                        ];
                    }
                )
                ->values();

        return response()->json([
            'success' =>
                true,

            'authority' =>
                'central',

            'delivery' =>
                'off_server',

            'identity_source' =>
                $identity['identity_source']
                ?? null,

            /*
             * Expose only the already-authorized identity,
             * never a website ID supplied by the caller.
             */
            'website' => [
                'id' =>
                    isset($identity['website_id'])
                        ? (int) $identity['website_id']
                        : null,
            ],

            'notices' =>
                $items,

            'meta' => [
                'count' =>
                    $items->count(),

                'generated_at' =>
                    now()->toIso8601String(),
            ],
        ]);
    }
}
