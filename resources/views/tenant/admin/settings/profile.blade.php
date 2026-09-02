@extends('tenant.admin.layouts.app')

@section('title', 'Profile Settings')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">
            Profile Settings
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Manage your personal Core account.
        </p>
    </div>


    {{-- ESUBIZ_CORE_SETTINGS_TOP_TABS_V1 --}}
    
    </div>


    @if(session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('password_success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('password_success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

        {{-- PROFILE INFORMATION --}}
        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-100 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-900">
                    Profile Information
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Manage your personal website account details.
                </p>
            </div>

            <form
                method="POST"
                enctype="multipart/form-data"
                action="{{ route(
                    'tenant.cms.settings.profile.update',
                    ['subdomain' => $website->subdomain]
                ) }}"
                class="p-6 space-y-6"
            >
                @csrf

                {{-- ESUBIZ_CORE_USER_AVATAR_UI_V1 --}}
                @php
                    /*
                     * Core-owned media must remain on the
                     * currently active website host.
                     */
                    $profileAvatarUrl =
                        !empty($profileUser->avatar_path)
                            ? route(
                                'tenant.cms.settings.profile.avatar',
                                [
                                    'subdomain' =>
                                        $website->subdomain,
                                ]
                            )
                            : null;

                    $profileInitial =
                        strtoupper(
                            substr(
                                trim(
                                    (string) (
                                        $profileUser->name
                                        ?? 'A'
                                    )
                                ),
                                0,
                                1
                            )
                        );
                @endphp

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700"
                    >
                        Profile Avatar
                    </label>

                    <div class="mt-3 flex items-center gap-5">

                        @if($profileAvatarUrl)
                            <img
                                src="{{ $profileAvatarUrl }}"
                                alt="{{ $profileUser->name ?? 'Profile avatar' }}"
                                class="h-20 w-20 rounded-full border border-gray-200 object-cover"
                                data-profile-avatar-preview
                            >
                        @else
                            <span
                                class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-600 text-2xl font-black text-white"
                                data-profile-avatar-fallback
                            >
                                {{ $profileInitial ?: 'A' }}
                            </span>

                            <img
                                src=""
                                alt="Profile avatar preview"
                                class="hidden h-20 w-20 rounded-full border border-gray-200 object-cover"
                                data-profile-avatar-preview
                            >
                        @endif

                        <div class="min-w-0">
                            <input
                                id="profile-avatar"
                                type="file"
                                name="avatar"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-800"
                                data-profile-avatar-input
                            >

                            <p class="mt-2 text-xs text-gray-500">
                                JPG, PNG or WebP. Maximum 3 MB.
                            </p>

                            @if($profileAvatarUrl)
                                <button
                                    type="submit"
                                    form="delete-profile-avatar-form"
                                    class="mt-3 inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50"
                                    onclick="return confirm('Delete your profile photo?');"
                                >
                                    Delete Photo
                                </button>
                            @endif
                        </div>

                    </div>
                </div>


                <div>
                    <label
                        for="profile-name\"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Name
                    </label>

                    <input
                        id="profile-name"
                        type="text"
                        name="name"
                        value="{{ old('name', $profileUser->name ?? '') }}"
                        required
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>


                <div>
                    <label
                        for="profile-email"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Email Address
                    </label>

                    @if($isSso)
                        <input
                            id="profile-email"
                            type="email"
                            value="{{ $profileUser->email ?? '' }}"
                            disabled
                            class="mt-2 block w-full rounded-lg border-gray-200 bg-gray-50 text-gray-500 shadow-sm"
                        >

                        <p class="mt-2 text-xs text-gray-500">
                            Your email is managed by your SSO identity provider.
                        </p>
                    @else
                        <input
                            id="profile-email"
                            type="email"
                            name="email"
                            value="{{ old('email', $profileUser->email ?? '') }}"
                            required
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    @endif
                </div>


                <div>
                    <label
                        for="profile-phone"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Phone Number
                    </label>

                    <input
                        id="profile-phone"
                        type="text"
                        name="phone"
                        value="{{ old('phone', $profileUser->phone ?? '') }}"
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>


                <div class="pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
                    >
                        Save Profile
                    </button>
                </div>

            </form>

        @if($profileAvatarUrl)
            <form
                id="delete-profile-avatar-form"
                method="POST"
                action="{{ route(
                    'tenant.cms.settings.profile.avatar.delete',
                    [
                        'subdomain' => $website->subdomain,
                    ]
                ) }}"
                class="hidden"
            >
                @csrf
                @method('DELETE')
            </form>
        @endif


        </section>


        {{-- PASSWORD --}}
        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-100 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-900">
                    Password
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    @if($isSso)
                        Create or replace the local password for this website account.
                    @else
                        Change the password for your website account.
                    @endif
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'tenant.cms.settings.profile.password.update',
                    ['subdomain' => $website->subdomain]
                ) }}"
                class="p-6 space-y-6"
            >
                @csrf

                @unless($isSso)
                    <div>
                        <label
                            for="current-password"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Current Password
                        </label>

                        <input
                            id="current-password"
                            type="password"
                            name="current_password"
                            autocomplete="current-password"
                            required
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                @endunless


                <div>
                    <label
                        for="new-password"
                        class="block text-sm font-medium text-gray-700"
                    >
                        New Password
                    </label>

                    <input
                        id="new-password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>


                <div>
                    <label
                        for="new-password-confirmation"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Confirm New Password
                    </label>

                    <input
                        id="new-password-confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        minlength="8"
                        required
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>


                <div class="pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
                    >
                        {{ $isSso ? 'Set Local Password' : 'Change Password' }}
                    </button>
                </div>

            </form>

        </section>

    </div>


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
         * ESUBIZ_IMAGE_UPLOAD_PREVIEW_V1
         *
         * Standard Core image-upload behaviour:
         * show the selected image before form submission.
         */
        const input =
            document.querySelector(
                '[data-profile-avatar-input]'
            );

        const preview =
            document.querySelector(
                '[data-profile-avatar-preview]'
            );

        const fallback =
            document.querySelector(
                '[data-profile-avatar-fallback]'
            );

        if (!input || !preview) {
            return;
        }

        let objectUrl = null;

        input.addEventListener(
            'change',
            function () {

                const file =
                    input.files
                    && input.files.length
                        ? input.files[0]
                        : null;

                if (!file) {
                    return;
                }

                if (
                    !file.type
                    || !file.type.startsWith('image/')
                ) {
                    return;
                }

                if (objectUrl) {
                    URL.revokeObjectURL(
                        objectUrl
                    );
                }

                objectUrl =
                    URL.createObjectURL(file);

                preview.src =
                    objectUrl;

                preview.classList.remove(
                    'hidden'
                );

                if (fallback) {
                    fallback.classList.add(
                        'hidden'
                    );
                }
            }
        );

        window.addEventListener(
            'beforeunload',
            function () {
                if (objectUrl) {
                    URL.revokeObjectURL(
                        objectUrl
                    );
                }
            }
        );
    }
);
</script>

</div>

@endsection
