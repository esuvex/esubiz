{{-- ESUBIZ_CENTRAL_PROFILE_SETTINGS_V1 --}}
{{-- ESUBIZ_CENTRAL_PROFILE_SHELL_V11 --}}
@extends($profileLayout)


@php
    /*
     * ESUBIZ_CENTRAL_PROFILE_COUNTRY_STATE_V15
     *
     * Shared Central country catalogue.
     * Saved authenticated profile value has authority.
     */
    $resolvedCountry = strtoupper(
        (string) old(
            'country_code',
            (
                !empty($profileUser->country_code)
                    ? $profileUser->country_code
                    : (
                        $esubizVisitorCountry
                            ?? 'NG'
                    )
            )
        )
    );

    if (
        !isset($countries)
        || !is_array($countries)
        || !array_key_exists(
            $resolvedCountry,
            $countries
        )
    ) {
        $resolvedCountry = 'NG';
    }

    $phoneDial = old(
        'phone_country_code',
        $profileUser->phone_country_code
            ?? ($countries[$resolvedCountry][1] ?? '+234')
    );
@endphp

@section('title', 'Profile Settings')

@section('content')
@php
    $u = $profileUser ?? auth()->user();
    $role = $profileAccountRole ?? 'user';
    $roleLabel = $profileRoleLabel ?? ucwords(str_replace('_', ' ', $role));

    $initials = collect(preg_split('/\s+/', trim((string) ($u->name ?? 'User'))))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
@endphp

<style>
    .es-profile-page {
        max-width: 1180px;
        margin: 0 auto;
        padding: 8px 0 40px;
    }

    .es-profile-heading {
        margin-bottom: 22px;
    }

    .es-profile-heading h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #0b1f3a;
    }

    .es-profile-heading p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .es-profile-grid {
        display: grid;
        grid-template-columns: 310px minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    .es-profile-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .es-profile-summary {
        padding: 28px 24px;
        text-align: center;
    }

    .es-profile-avatar {
        width: 88px;
        height: 88px;
        margin: 0 auto 16px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: #0b1f3a;
        color: #fff;
        font-size: 28px;
        font-weight: 800;
        box-shadow: 0 8px 24px rgba(11, 31, 58, .18);
    }

    .es-profile-summary h2 {
        margin: 0;
        color: #0f172a;
        font-size: 20px;
        font-weight: 800;
    }

    .es-profile-summary .email {
        margin-top: 5px;
        color: #64748b;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .es-role-badge {
        display: inline-flex;
        align-items: center;
        margin-top: 14px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #0b1f3a;
        font-size: 12px;
        font-weight: 800;
    }

    .es-profile-section {
        padding: 24px;
    }

    .es-profile-section + .es-profile-section {
        border-top: 1px solid #eef2f7;
    }

    .es-section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }

    .es-section-title h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
    }

    .es-section-title p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .es-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
    }

    .es-field-full {
        grid-column: 1 / -1;
    }

    .es-field label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .es-field input,
    .es-field select {
        width: 100%;
        min-height: 48px;
        border: 1px solid #d8e0ea;
        border-radius: 10px;
        padding: 10px 13px;
        background: #fff;
        color: #0f172a;
        outline: none;
        box-sizing: border-box;
    }

    .es-field input:focus,
    .es-field select:focus {
        border-color: #0b1f3a;
        box-shadow: 0 0 0 3px rgba(11, 31, 58, .08);
    }

    .es-readonly {
        background: #f8fafc !important;
        color: #64748b !important;
    }

    .es-profile-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .es-profile-btn {
        min-height: 44px;
        border: 0;
        border-radius: 10px;
        padding: 10px 20px;
        background: #0b1f3a;
        color: #fff;
        font-weight: 800;
        cursor: pointer;
    }

    .es-alert {
        margin-bottom: 18px;
        padding: 12px 15px;
        border-radius: 10px;
        font-size: 13px;
    }

    .es-alert-success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .es-alert-error {
        background: #fff1f2;
        color: #9f1239;
        border: 1px solid #fecdd3;
    }

    @media (max-width: 900px) {
        .es-profile-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .es-form-grid {
            grid-template-columns: 1fr;
        }

        .es-field-full {
            grid-column: auto;
        }

        .es-profile-section,
        .es-profile-summary {
            padding: 20px;
        }

        .es-profile-heading h1 {
            font-size: 23px;
        }
    }

    /*
     * ESUBIZ_CENTRAL_PROFILE_MOBILE_PHONE_ALIGNMENT_V30
     *
     * The combined phone row always occupies the same full width
     * as the surrounding Central Profile fields.
     *
     * Only the internal ratio changes:
     * compact dial code + flexible editable phone number.
     *
     * Country remains its own full-width row above this row.
     */
    .es-central-profile-phone-row {
        display: grid;
        grid-template-columns: minmax(150px, 180px) minmax(0, 1fr);
        gap: 20px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        align-items: end;
        box-sizing: border-box;
    }

    .es-central-profile-phone-code,
    .es-central-profile-phone-number {
        min-width: 0;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    .es-central-profile-phone-code input,
    .es-central-profile-phone-number input {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    @media (max-width: 640px) {
        .es-central-profile-phone-row {
            grid-template-columns: 105px minmax(0, 1fr);
            gap: 12px;
        }
    }


    /*
     * ESUBIZ_CENTRAL_PROFILE_PHOTO_FIT_V32
     *
     * The profile-photo frame is authoritative.
     * Any uploaded image is visually cropped into the fixed
     * circular avatar area without stretching or overflowing.
     *
     * Explicit dimensions are used instead of depending only
     * on utility classes so this remains reliable on desktop
     * and small mobile devices.
     */
    #central-profile-photo-placeholder,
    #central-profile-photo-preview {
        width: 96px !important;
        height: 96px !important;
        min-width: 96px !important;
        min-height: 96px !important;
        max-width: 96px !important;
        max-height: 96px !important;
        border-radius: 50% !important;
        box-sizing: border-box !important;
    }

    #central-profile-photo-placeholder {
        overflow: hidden !important;
    }

    #central-profile-photo-preview {
        display: block;
        object-fit: cover !important;
        object-position: center center !important;
        overflow: hidden !important;
        flex: 0 0 96px !important;
    }

    /*
     * Keep the upload-preview area itself from expanding
     * because of the natural dimensions of an uploaded image.
     */
    #central-profile-photo-placeholder,
    #central-profile-photo-preview {
        aspect-ratio: 1 / 1;
    }

</style>

<div class="es-profile-page">
    <div class="es-profile-heading">
        <h1>Profile Settings</h1>
        <p>Manage your Esubiz account information and security.</p>
    </div>

    @if (session('status'))
        <div class="es-alert es-alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="es-alert es-alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="es-profile-grid">
        <aside class="es-profile-card">
            <div class="es-profile-summary">
                {{-- ESUBIZ_CENTRAL_PROFILE_SUMMARY_SAVED_PHOTO_V30 --}}
                <div class="es-profile-avatar">
                    @if(!empty($u->profile_photo_path))
                        <img
                            src="{{ route(
                                'central.profile.photo',
                                ['v' => optional($u->updated_at)->timestamp]
                            ) }}"
                            alt="{{ $u->name ?? 'Profile' }} profile photo"
                            style="
                                width:100%;
                                height:100%;
                                display:block;
                                object-fit:cover;
                                border-radius:inherit;
                            "
                        >
                    @else
                        {{ $initials ?: 'U' }}
                    @endif
                </div>
                <h2>{{ $u->name }}</h2>
                <div class="email">{{ $u->email }}</div>
                <span class="es-role-badge">{{ $roleLabel }}</span>
            </div>
        </aside>

        <div class="es-profile-card">
            <section class="es-profile-section">
                <div class="es-section-title">
                    <div>
                        <h3>Personal Information</h3>
                        <p>Your Central Esubiz account details.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('central.profile.update') }}" enctype="multipart/form-data">

{{-- ESUBIZ_CENTRAL_PROFILE_PHOTO_UI_V12 --}}
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

        {{-- ESUBIZ_CENTRAL_PROFILE_PHOTO_FRAME_V33 --}}
        <div
            id="central-profile-photo-frame"
            class="shrink-0"
            style="
                position:relative;
                width:96px;
                height:96px;
                min-width:96px;
                min-height:96px;
                max-width:96px;
                max-height:96px;
                flex:0 0 96px;
                overflow:hidden;
                border-radius:50%;
                box-sizing:border-box;
                background:#f1f5f9;
                box-shadow:0 1px 3px rgba(15,23,42,.12);
            "
        >
            <div
                id="central-profile-photo-placeholder"
                style="
                    position:absolute;
                    inset:0;
                    width:100%;
                    height:100%;
                    display:{{ !empty($profileUser->profile_photo_path)
                        ? 'none'
                        : 'flex' }};
                    align-items:center;
                    justify-content:center;
                    overflow:hidden;
                    border-radius:50%;
                    box-sizing:border-box;
                    font-size:24px;
                    font-weight:700;
                    color:#64748b;
                "
            >
                {{ strtoupper(
                    mb_substr(
                        trim((string) ($profileUser->name ?? 'U')),
                        0,
                        1
                    )
                ) }}
            </div>

            <img
                id="central-profile-photo-preview"
                src="{{ !empty($profileUser->profile_photo_path)
                    ? route(
                        'central.profile.photo',
                        ['v' => optional($profileUser->updated_at)->timestamp]
                    )
                    : '' }}"
                alt="Profile photo"
                style="
                    position:absolute;
                    inset:0;
                    width:100%;
                    height:100%;
                    min-width:0;
                    min-height:0;
                    max-width:100%;
                    max-height:100%;
                    display:{{ empty($profileUser->profile_photo_path)
                        ? 'none'
                        : 'block' }};
                    object-fit:cover;
                    object-position:center center;
                    border-radius:50%;
                    box-sizing:border-box;
                "
            >
        </div>

        <div class="min-w-0 flex-1">
            <h3 class="text-base font-semibold text-slate-900">
                Profile Photo
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                JPG, PNG or WEBP. Maximum file size 5MB.
            </p>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <label
                    for="central-profile-photo-input"
                    class="inline-flex cursor-pointer items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    Choose Photo
                </label>

                <input
                    id="central-profile-photo-input"
                    type="file"
                    name="profile_photo"
                    accept="image/jpeg,image/png,image/webp"
                    class="sr-only"
                >

                {{-- ESUBIZ_CENTRAL_PROFILE_PHOTO_DELETE_UI_V34 --}}
                @if(!empty($profileUser->profile_photo_path))
                    <button
                        type="submit"
                        form="central-profile-photo-delete-form"
                        class="inline-flex items-center rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50"
                        onclick="return confirm('Remove your current profile photo?')"
                    >
                        Remove Photo
                    </button>
                @endif


                <span
                    id="central-profile-photo-name"
                    class="text-sm text-slate-500"
                ></span>
            </div>

            @error('profile_photo')
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

    </div>
</div>

                    @csrf
                    @method('PUT')

                    <div class="es-form-grid">
                        <div class="es-field es-field-full">
                            <label for="profile_name">Full Name</label>
                            <input
                                id="profile_name"
                                type="text"
                                name="name"
                                value="{{ old('name', $u->name) }}"
                                required
                            >
                        </div>

                        <div class="es-field es-field-full">
                            <label for="profile_email">Email Address</label>
                            <input
                                id="profile_email"
                                type="email"
                                name="email"
                                value="{{ old('email', $u->email) }}"
                                required
                            >
                        </div>

                        <div class="es-field">
                            <label for="profile_country">Country Code</label>

                <select
                    id="central-profile-country"
                    name="country_code"
                    required
                    class="esubiz-auth-select"
                >
                    @foreach($countries as $code => $country)
                        <option
                            value="{{ $code }}"
                            data-dial="{{ $country[1] }}"
                            @selected($resolvedCountry === $code)
                        >
                            {{ $country[0] }}
                        </option>
                    @endforeach
                </select>

                @error('country_code')
                    <p class="mt-2 text-sm font-medium text-red-600">
                        {{ $message }}
                    </p>
                @enderror

                        </div>

                                                {{-- ESUBIZ_CENTRAL_PROFILE_PHONE_ROW_V25 --}}
                        {{-- ESUBIZ_CENTRAL_PROFILE_MOBILE_PHONE_ALIGNMENT_V30 --}}
                        <div
                            id="central-profile-phone-row"
                            class="es-field es-field-full es-central-profile-phone-row"
                        >
                            <div class="es-central-profile-phone-code">
                                <label for="central-profile-phone-country-code">
                                    Phone Country Code
                                </label>

                                <input
                                    id="central-profile-phone-country-code"
                                    type="text"
                                    name="phone_country_code"
                                    value="{{ $phoneDial }}"
                                    readonly
                                    aria-readonly="true"
                                    autocomplete="off"
                                    class="es-readonly cursor-not-allowed"
                                >
                            </div>

                            <div class="es-central-profile-phone-number">
                                <label for="central-profile-phone-number">
                                    Phone Number
                                </label>

                                <input
                                    id="central-profile-phone-number"
                                    type="tel"
                                    name="phone_number"
                                    value="{{ old('phone_number', $profileUser->phone_number ?? '') }}"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    class="es-input"
                                >
                            </div>
                        </div>

                        <div class="es-field">
                            <label>Account Type</label>
                            <input
                                class="es-readonly"
                                type="text"
                                value="{{ $roleLabel }}"
                                readonly
                            >
                        </div>

                        <div class="es-field">
                            <label>Account Status</label>
                            <input
                                class="es-readonly"
                                type="text"
                                value="{{ ucfirst((string) ($u->account_status ?? 'active')) }}"
                                readonly
                            >
                        </div>
                    </div>

                    <div class="es-profile-actions">
                        <button class="es-profile-btn" type="submit">
                            Save Profile
                        </button>
                    </div>
                </form>

                {{-- ESUBIZ_CENTRAL_PROFILE_PHOTO_DELETE_FORM_V34 --}}
                <form
                    id="central-profile-photo-delete-form"
                    method="POST"
                    action="{{ route('central.profile.photo.destroy') }}"
                    style="display:none"
                >
                    @csrf
                    @method('DELETE')
                </form>

            </section>

            <section class="es-profile-section">
                <div class="es-section-title">
                    <div>
                        <h3>Password & Security</h3>
                        <p>Change the password used for your Central Esubiz account.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('central.profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="es-form-grid">
                        <div class="es-field es-field-full">
                            <label for="current_password">Current Password</label>
                            <input
                                id="current_password"
                                type="password"
                                name="current_password"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <div class="es-field">
                            <label for="new_password">New Password</label>
                            <input
                                id="new_password"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div class="es-field">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                autocomplete="new-password"
                                required
                            >
                        </div>
                    </div>

                    <div class="es-profile-actions">
                        <button class="es-profile-btn" type="submit">
                            Update Password
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>


{{-- ESUBIZ_CENTRAL_PROFILE_UI_JS_V12 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const country = document.getElementById(
        'central-profile-country'
    );

    if (country) {
        const current = String(
            country.dataset.profileCountry || ''
        ).toUpperCase();

        if (current) {
            country.value = current;
        }
    }

    const input = document.getElementById(
        'central-profile-photo-input'
    );

    const preview = document.getElementById(
        'central-profile-photo-preview'
    );

    const placeholder = document.getElementById(
        'central-profile-photo-placeholder'
    );

    const filename = document.getElementById(
        'central-profile-photo-name'
    );

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];

        if (!file) {
            return;
        }

        if (filename) {
            filename.textContent = file.name;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            preview.src = event.target.result;
            preview.style.display = '';

            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };

        reader.readAsDataURL(file);
    });
});
</script>





{{-- ESUBIZ_CENTRAL_PROFILE_COUNTRY_SYNC_V17 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const country = document.getElementById(
        'central-profile-country'
    );

    const phoneCode = document.getElementById(
        'central-profile-phone-country-code'
    );

    if (!country || !phoneCode) {
        return;
    }

    const syncPhoneCountryCode = function () {
        const selected =
            country.options[country.selectedIndex];

        phoneCode.value =
            selected?.dataset?.dial || '';
    };

    country.addEventListener(
        'change',
        syncPhoneCountryCode
    );

    /*
     * Synchronize on initial render as well.
     */
    syncPhoneCountryCode();
});
</script>

@endsection
