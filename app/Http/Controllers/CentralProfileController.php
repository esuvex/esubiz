<?php

namespace App\Http\Controllers;

use App\Services\Platform\CentralRoleAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\Platform\CentralCountryCatalog;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CentralProfileController extends Controller
{
    /**
     * ESUBIZ_CENTRAL_PROFILE_CONTROLLER_V11
     */
    public function edit(
        Request $request,
        CentralRoleAccessService $roleAccess
    ): View {
        $user = $request->user();

        $accountRole =
            $roleAccess->primaryAssignedRole(
                $user,
                'user'
            );

        $profileContext = $this->resolveProfileContext(
            $user,
            $roleAccess,
            $accountRole
        );

        return view('profile.central', [
            'countries' => app(
                CentralCountryCatalog::class
            )->all(),
            'profileUser' => $user,
            'profileAccountRole' => $accountRole,
            /*
             * ESUBIZ_CENTRAL_PROFILE_DYNAMIC_ROLE_V23
             *
             * Profile reads the assigned Central role directly.
             */
            'profileRoleLabel' =>
                $roleAccess->assignedRoleLabel(
                    $user,
                    'User'
                ),
            'profileContext' => $profileContext,
            'profileLayout' => $this->layoutForContext(
                $profileContext
            ),
        ]);
    }

    public function update(Request $request,
        CentralCountryCatalog $countryCatalog): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'country_code' => [
                'nullable',
                'string',
                'max:10',
            ],
            'phone_country_code' => [
                'nullable',
                'string',
                'max:20',
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:40',
            ],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        
        /*
         * ESUBIZ_CENTRAL_PROFILE_PHONE_AUTHORITY_V17
         *
         * The selected Country is authoritative.
         * Phone Country Code is never independently editable.
         */
        $selectedCountry = strtoupper(
            trim(
                (string) (
                    $validated['country_code']
                    ?? $user->country_code
                    ?? 'NG'
                )
            )
        );

        if (!$countryCatalog->has($selectedCountry)) {
            $selectedCountry = 'NG';
        }

        $validated['country_code'] =
            $selectedCountry;

        $validated['phone_country_code'] =
            $countryCatalog->dialCode(
                $selectedCountry,
                '+234'
            );

$emailChanged =
            isset($validated['email'])
            && $validated['email'] !== $user->email;

        foreach ($validated as $field => $value) {
            if (
                array_key_exists(
                    $field,
                    $user->getAttributes()
                )
                || in_array(
                    $field,
                    $user->getFillable(),
                    true
                )
            ) {
                $user->{$field} = $value;
            }
        }

        if (
            $emailChanged
            && array_key_exists(
                'email_verified_at',
                $user->getAttributes()
            )
        ) {
            $user->email_verified_at = null;
        }

        
        /*
         * ESUBIZ_CENTRAL_PROFILE_PHOTO_UPLOAD_V17
         *
         * Store the uploaded image first, persist its path on the
         * Central user, then remove the old file after replacement.
         */
        /*
         * ESUBIZ_CENTRAL_PROFILE_SHARED_OPTIMIZER_V36
         *
         * Canonical Central profile-photo processing:
         * - 512 x 512 centre crop
         * - JPEG EXIF orientation
         * - PNG/WEBP alpha preservation
         * - no duplicate controller-level GD pipeline
         */
        if ($request->hasFile('profile_photo')) {
            $uploadedPhoto =
                $request->file(
                    'profile_photo'
                );

            if (
                !$uploadedPhoto
                || !$uploadedPhoto->isValid()
            ) {
                return back()
                    ->withErrors([
                        'profile_photo' =>
                            'The uploaded profile photo is invalid.',
                    ])
                    ->withInput();
            }

            try {
                $newProfilePhotoPath =
                    app(
                        \App\Services\Media\EsubizImageOptimizer::class
                    )->storeUploaded(
                        $uploadedPhoto,
                        'profile-photos/'
                            . $user->id,
                        \App\Services\Media\EsubizImageOptimizer::PROFILE_PHOTO,
                        'public'
                    );
            } catch (\Throwable $e) {
                report($e);

                return back()
                    ->withErrors([
                        'profile_photo' =>
                            'The profile photo could not be processed.',
                    ])
                    ->withInput();
            }

            $oldProfilePhotoPath =
                trim(
                    (string) (
                        $user->profile_photo_path
                        ?? ''
                    )
                );

            $user->profile_photo_path =
                $newProfilePhotoPath;

            if (
                $oldProfilePhotoPath !== ''
                && $oldProfilePhotoPath
                    !== $newProfilePhotoPath
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $oldProfilePhotoPath
                );
            }
        }

$user->save();

        if (!empty($validated['country_code'])) {
            $request->session()->put(
                'esubiz_country_code',
                strtoupper(
                    $validated['country_code']
                )
            );

            $request->session()->put(
                'esubiz_country_source',
                'profile'
            );
        }

        return back()->with(
            'status',
            'Profile information updated successfully.'
        );
    }

    /**
     * ESUBIZ_CENTRAL_PROFILE_PHOTO_RESPONSE_V12
     */
    public function photo(Request $request): StreamedResponse
    {
        $path = trim(
            (string) (
                $request->user()->profile_photo_path ?? ''
            )
        );

        abort_if(
            $path === ''
            || !Storage::disk('public')->exists($path),
            404
        );

        return Storage::disk('public')->response(
            $path,
            basename($path),
            [
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }


    public function password(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make(
                $validated['password']
            ),
        ])->save();

        return back()->with(
            'status',
            'Password updated successfully.'
        );
    }

    /**
     * ESUBIZ_CENTRAL_PROFILE_ROLE_AUTHORITY_V11
     *
     * Prefer the explicit account_role when available.
     * CentralRoleAccessService provides the compatibility authority
     * for existing accounts whose new account_role has not yet been
     * populated.
     */
    protected function resolveAccountRole(
        $user,
        CentralRoleAccessService $roleAccess
    ): string {
        /*
         * ESUBIZ_CENTRAL_PROFILE_ROLE_AUTHORITY_V17
         *
         * Do not trust a stale account_role='user' on an account
         * whose established Central authority exposes Admin context.
         */
        $explicit = $this->normalizeRole(
            trim(
                (string) (
                    $user->account_role ?? ''
                )
            )
        );

        $contexts = collect(
            $roleAccess->visibleContexts($user)
        )
            ->map(
                fn ($context) => strtolower(
                    trim((string) $context)
                )
            )
            ->values();

        if (
            $roleAccess->isAdminFamily($user)
            || $contexts->contains('admin')
        ) {
            if (in_array(
                $explicit,
                [
                    'staff',
                    'investor',
                    'partner',
                    'investor_partner',
                ],
                true
            )) {
                return $explicit;
            }

            return 'admin';
        }

        if (
            $contexts->contains('developer')
            || $roleAccess
                ->includesDeveloperFeatures($user)
        ) {
            return 'developer';
        }

        return 'user';

    }

    /**
     * ESUBIZ_CENTRAL_PROFILE_CONTEXT_AUTHORITY_V11
     *
     * The profile shell follows the ACCOUNT FAMILY,
     * not whichever mode the user happened to switch into.
     */
    protected function resolveProfileContext(
        $user,
        CentralRoleAccessService $roleAccess,
        string $accountRole
    ): string {
        $contexts = collect(
            $roleAccess->visibleContexts($user)
        )
            ->map(
                fn ($context) => strtolower(
                    (string) $context
                )
            )
            ->values();

        if ($contexts->contains('admin')) {
            return 'admin';
        }

        if (
            $accountRole === 'developer'
            || $contexts->contains('developer')
        ) {
            return 'developer';
        }

        return 'user';
    }

    protected function normalizeRole(
        string $role
    ): string {
        $role = str_replace(
            [' ', '-'],
            '_',
            strtolower($role)
        );

        return match ($role) {
            'superadmin',
            'super_admin',
            'platform_admin',
            'administrator' => 'admin',

            'investor/partner',
            'investorpartner' => 'investor_partner',

            default => $role,
        };
    }

    protected function layoutForContext(
        string $context
    ): string {
        return match ($context) {
            'admin' => 'admin.layouts.app',
            'developer' => 'admin.layouts.app',
            'user' => 'admin.layouts.app',
            default => 'admin.layouts.app',
        };
    }

    protected function roleLabel(
        string $accountRole,
        string $profileContext
    ): string {
        return match ($accountRole) {
            'admin' => 'Administrator',
            'staff' => 'Staff',
            'investor' => 'Investor',
            'partner' => 'Partner',
            'investor_partner' => 'Investor / Partner',
            'developer' => 'Developer',
            'user' => 'User',

            default => $profileContext === 'admin'
                ? 'Administrator'
                : ($profileContext === 'developer'
                    ? 'Developer'
                    : 'User'),
        };
    }


    /**
     * ESUBIZ_CENTRAL_PROFILE_PHOTO_DELETE_V34
     */
    public function destroyPhoto()
    {
        $user = request()->user();

        abort_unless($user, 401);

        if (!empty($user->profile_photo_path)) {
            Storage::disk('public')->delete(
                $user->profile_photo_path
            );

            $user->profile_photo_path = null;
            $user->save();
        }

        return back()->with(
            'status',
            'Profile photo removed successfully.'
        );
    }

}
