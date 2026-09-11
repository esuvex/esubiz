<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DashboardNotice;
use App\Services\Media\CentralMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardNoticeController extends Controller
{

    /*
     * ESUBIZ_DASHBOARD_NOTICE_SHARED_CREATE_UPDATE_V5
     *
     * Create and Edit use one authoritative
     * validation contract.
     *
     * Active / Inactive remains controlled through
     * the dedicated status endpoint.
     */
    private function noticeRules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:160',
            ],

            'message' => [
                'required',
                'string',
                'max:10000',
            ],

            'status' => [
                'required',
                Rule::in(['draft', 'published', 'disabled']),
            ],

            /*
             * ESUBIZ_DASHBOARD_NOTICE_GENERIC_DELIVERY_SAVE_V16
             *
             * delivery_channels is authoritative.
             * delivery_scope remains a compatibility field for
             * existing Core code and historical notice records.
             */
            'delivery_scope' => [
                'nullable',
                Rule::in(['all', 'saas', 'off_server']),
            ],

            'delivery_channels' => [
                'required',
                'array',
                'min:1',
            ],

            'delivery_channels.*' => [
                'required',
                'string',
                'distinct',
                Rule::in([
                    'saas',
                    'off_server',
                    'central',
                ]),
            ],

            'variant' => [
                'required',
                Rule::in([
                    'info',
                    'success',
                    'warning',
                    'danger',
                    'primary',
                    'secondary',
                ]),
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:8192',
            ],
            'rotation_enabled' => [
                'required',
                'boolean',
            ],
            'rotation_seconds' => [
                'required_if:rotation_enabled,1',
                'nullable',
                'integer',
                'min:3',
                'max:120',
            ],

            'dismissible' => [
                'required',
                'boolean',
            ],

            'target_type' => [
                'required',
                Rule::in(['all', 'selected']),
            ],

            'tenant_ids' => [
                'nullable',
                'array',
                'required_if:target_type,selected',
            ],

            'tenant_ids.*' => [
                'integer',
                'distinct',
                'exists:websites,id',
            ],

            'central_target_type' => [
                'required_if:delivery_channels.*,central',
                'nullable',
                Rule::in(['all', 'selected']),
            ],

            'central_role_ids' => [
                'nullable',
                'array',
            ],

            'central_role_ids.*' => [
                'integer',
                'distinct',
                'exists:roles,id',
            ],

            /*
             * ESUBIZ_DASHBOARD_NOTICE_CENTRAL_USER_TARGETING_V20
             *
             * Specific Central users can be targeted alongside
             * generic Central roles.
             */
            'central_user_ids' => [
                'nullable',
                'array',
            ],
            'central_user_ids.*' => [
                'integer',
                'distinct',
                'exists:users,id',
            ],


            'published_at' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after:published_at',
            ],
        
        ];
    }

    /*
     * Normalize Dashboard Notice delivery from one authoritative
     * multi-channel request contract.
     *
     * Central role targeting is completely generic:
     * role IDs come from the roles table and no role names are
     * embedded in Dashboard Notice code.
     */
    private function normalizeDeliveryData(
        array $data
    ): array {
        $channels = array_values(
            array_unique(
                array_intersect(
                    ['saas', 'off_server', 'central'],
                    array_map(
                        'strval',
                        $data['delivery_channels'] ?? []
                    )
                )
            )
        );

        if (empty($channels)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'delivery_channels' =>
                    'Select at least one notice delivery destination.',
            ]);
        }

        $data['delivery_channels'] = $channels;

        /*
         * Keep the legacy Core field synchronized.
         *
         * Central-only notices still receive "all" here, but the
         * Core service V15 reads delivery_channels first, therefore
         * they cannot leak into Core.
         */
        $hasSaas =
            in_array('saas', $channels, true);

        $hasOffServer =
            in_array('off_server', $channels, true);

        if ($hasSaas && $hasOffServer) {
            $data['delivery_scope'] = 'all';
        } elseif ($hasSaas) {
            $data['delivery_scope'] = 'saas';
        } elseif ($hasOffServer) {
            $data['delivery_scope'] = 'off_server';
        } else {
            $data['delivery_scope'] = 'all';
        }

        /*
         * SaaS tenant selection has meaning only when the SaaS
         * delivery channel is enabled.
         */
        if (!$hasSaas) {
            $data['target_type'] = 'all';
            $data['tenant_ids'] = null;
        } elseif (
            ($data['target_type'] ?? 'all') === 'all'
        ) {
            $data['tenant_ids'] = null;
        }

        /*
         * Central audience has meaning only when Central delivery
         * is enabled.
         *
         * Selected Central targeting supports:
         * - one or more roles
         * - one or more specific users
         * - roles and users together
         *
         * Runtime matching is OR:
         * explicitly selected user OR member of a selected role.
         */
        $hasCentral =
            in_array('central', $channels, true);

        if (!$hasCentral) {
            $data['central_target_type'] = 'all';
            $data['central_role_ids'] = null;
            $data['central_user_ids'] = null;

            return $data;
        }

        $centralTarget =
            (string) (
                $data['central_target_type']
                ?? 'all'
            );

        if ($centralTarget === 'all') {
            $data['central_target_type'] = 'all';
            $data['central_role_ids'] = null;
            $data['central_user_ids'] = null;

            return $data;
        }

        $roleIds =
            collect(
                $data['central_role_ids'] ?? []
            )
                ->map(
                    fn ($id) => (int) $id
                )
                ->filter(
                    fn ($id) => $id > 0
                )
                ->unique()
                ->values()
                ->all();

        $userIds =
            collect(
                $data['central_user_ids'] ?? []
            )
                ->map(
                    fn ($id) => (int) $id
                )
                ->filter(
                    fn ($id) => $id > 0
                )
                ->unique()
                ->values()
                ->all();

        if (
            empty($roleIds)
            && empty($userIds)
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'central_role_ids' =>
                    'Select at least one Central role or Central user.',
            ]);
        }

        $data['central_target_type'] = 'selected';
        $data['central_role_ids'] = $roleIds;
        $data['central_user_ids'] = $userIds;

        return $data;
    }


    public function store(
        Request $request,
        CentralMediaService $media
    ): JsonResponse
    {
        $data = $request->validate(
            $this->noticeRules()
        );

        $data =
            $this->normalizeDeliveryData(
                $data
            );

        $data['created_by'] = auth()->id();

        /*
         * ESUBIZ_DASHBOARD_NOTICE_CENTRAL_MEDIA_V3
         *
         * Notice images are Central-owned media.
         * Admin uploads the file and Esubiz resolves its URL.
         */
        unset(
            $data['image'],
            $data['image_url']
        );

        if ($request->hasFile('image')) {
            $data['image_path'] =
                $media->store(
                    $request->file('image'),
                    'dashboard-notices'
                );
        }

        $data['rotation_enabled'] =
            $request->boolean(
                'rotation_enabled'
            );

        $data['rotation_seconds'] =
            $data['rotation_enabled']
                ? max(
                    3,
                    min(
                        120,
                        (int) (
                            $data['rotation_seconds']
                            ?? 8
                        )
                    )
                )
                : 8;

        $notice = DashboardNotice::create($data);

        return response()->json([
            'ok' => true,
            'message' => 'Dashboard notice created.',
            'notice' => $notice,
        ]);
    }


    public function update(
        Request $request,
        int $noticeId,
        CentralMediaService $media
    ): JsonResponse
    {
        $notice =
            DashboardNotice::query()
                ->findOrFail($noticeId);

        $data =
            $request->validate(
                $this->noticeRules()
            );

        $data =
            $this->normalizeDeliveryData(
                $data
            );

        /*
         * Existing notice image persists unless:
         *
         * 1. Admin uploads a replacement image, or
         * 2. Admin explicitly removes it.
         */
        $removeImage =
            $request->boolean(
                'remove_image'
            );

        $oldImagePath =
            $notice->image_path;

        unset(
            $data['image'],
            $data['image_url'],
            $data['remove_image']
        );

        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath =
                $media->store(
                    $request->file('image'),
                    'dashboard-notices'
                );

            $data['image_path'] =
                $newImagePath;
        } elseif ($removeImage) {
            $data['image_path'] =
                null;
        }

        /*
         * Persist dashboard rotation configuration.
         */
        $data['rotation_enabled'] =
            $request->boolean(
                'rotation_enabled'
            );

        $data['rotation_seconds'] =
            $data['rotation_enabled']
                ? max(
                    3,
                    min(
                        120,
                        (int) (
                            $data['rotation_seconds']
                            ?? 8
                        )
                    )
                )
                : 8;

        /*
         * Publishing with no explicit publish time
         * means make it available immediately.
         */
        if (
            ($data['status'] ?? null)
                === 'published'
            && empty(
                $data['published_at']
            )
            && !$notice->published_at
        ) {
            $data['published_at'] =
                now();
        }

        try {
            $notice->fill($data);

            $notice->save();
        } catch (\Throwable $e) {
            /*
             * Never leave a newly uploaded replacement
             * orphaned if DB persistence fails.
             */
            if ($newImagePath) {
                $media->delete(
                    $newImagePath
                );
            }

            throw $e;
        }

        /*
         * Delete previous Central Media image only
         * after the notice has saved successfully.
         */
        if (
            $oldImagePath
            && (
                $removeImage
                || (
                    $newImagePath
                    && $newImagePath
                        !== $oldImagePath
                )
            )
        ) {
            $media->delete(
                $oldImagePath
            );
        }

        return response()->json([
            'ok' => true,

            'message' =>
                'Dashboard notice updated.',

            'notice' =>
                $notice->fresh(),
        ]);
    }

    public function status(
        Request $request,
        int $noticeId
    ): JsonResponse {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(['draft', 'published', 'disabled']),
            ],
        ]);

        $notice = DashboardNotice::query()->findOrFail($noticeId);

        $notice->status = $data['status'];

        /*
         * Publishing without an explicit schedule means:
         * make the notice available immediately.
         */
        if (
            $data['status'] === 'published'
            && !$notice->published_at
        ) {
            $notice->published_at = now();
        }

        $notice->save();

        return response()->json([
            'ok' => true,
            'message' => 'Dashboard notice status updated.',
            'notice' => $notice,
        ]);
    }

    public function destroy(
        int $noticeId,
        CentralMediaService $media
    ): JsonResponse
    {
        $notice = DashboardNotice::query()->findOrFail($noticeId);

        if ($notice->image_path) {
            $media->delete(
                $notice->image_path
            );
        }

        $notice->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Dashboard notice deleted.',
        ]);
    }
}
