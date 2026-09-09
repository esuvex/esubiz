@extends('tenant.admin.layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    <div>
        @coreCan('users.view')
<a
            href="/admin/users"
            class="text-sm font-bold text-slate-500 hover:text-slate-900"
        >
            ← Users
        </a>
@endcoreCan

        <h1 class="mt-2 text-2xl font-black text-slate-900">
            {{ $user ? 'Edit User' : 'Create User' }}
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Manage this Core user's account and roles.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
    $editing = isset($user);
@endphp

<form
        method="POST"
        action="{{ $editing ? '/admin/users/'.$user->id : '/admin/users' }}"
        class="space-y-6"
    >
        @csrf

        @if($user)
            @method('PUT')
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="grid gap-5 sm:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-black text-slate-700">
                        Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        value="{{ old('name', $user->name ?? '') }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-black text-slate-700">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        required
                        value="{{ old('email', $user->email ?? '') }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                    >
                </div>

                @php
                    $coreCountryV82 =
                        strtoupper(
                            trim(
                                (string) old(
                                    'country_code',
                                    data_get(
                                        $user,
                                        'country_code',
                                        'NG'
                                    )
                                )
                            )
                        );

                    if ($coreCountryV82 === '') {
                        $coreCountryV82 = 'NG';
                    }

                    $coreAllowedCountriesV82 =
                        $website->allowed_country_codes
                            ?? ['ALL'];

                    if (is_string($coreAllowedCountriesV82)) {
                        $decodedCountriesV82 =
                            json_decode(
                                $coreAllowedCountriesV82,
                                true
                            );

                        $coreAllowedCountriesV82 =
                            is_array($decodedCountriesV82)
                                ? $decodedCountriesV82
                                : ['ALL'];
                    }

                    if (
                        !is_array($coreAllowedCountriesV82)
                        || empty($coreAllowedCountriesV82)
                    ) {
                        $coreAllowedCountriesV82 = ['ALL'];
                    }
                @endphp

                <x-core.country-phone
                    country-field="country_code"
                    phone-field="phone"
                    :selected-country="$coreCountryV82"
                    :phone-value="old(
                        'phone',
                        data_get($user, 'phone', '')
                    )"
                    :allowed-countries="$coreAllowedCountriesV82"
                    layout="two-column"
                />

                <div class="flex items-end">

                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">

                        <input
                            type="hidden"
                            name="is_active"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', $user ? (bool) $user->is_active : true))
                        >

                        <span class="text-sm font-bold text-slate-700">
                            Active account
                        </span>

                    </label>

                </div>

            </div>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="font-black text-slate-900">
                Roles
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                A Core user can hold one or more roles.
            </p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                @foreach($roles as $role)
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4">

                        <input
                            type="checkbox"
                            name="roles[]"
                            value="{{ $role->id }}"
                            @checked(
                                in_array(
                                    (int) $role->id,
                                    array_map(
                                        'intval',
                                        old(
                                            'roles',
                                            $selectedRoleIds
                                        )
                                    ),
                                    true
                                )
                            )
                        >

                        <span>
                            <span class="block text-sm font-black text-slate-800">
                                {{ $role->name }}
                            </span>

                            @if($role->description)
                                <span class="mt-1 block text-xs text-slate-500">
                                    {{ $role->description }}
                                </span>
                            @endif
                        </span>

                    </label>
                @endforeach

            </div>

        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="font-black text-slate-900">
                Password
            </h2>

            @if($user)
                <p class="mt-1 text-sm text-slate-500">
                    Leave blank to keep the existing password.
                </p>
            @endif

            <div class="mt-5 grid gap-5 sm:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-black text-slate-700">
                        {{ $user ? 'New Password' : 'Password' }}
                    </label>

                    <input
                        type="password"
                        name="password"
                        {{ $user ? '' : 'required' }}
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-black text-slate-700">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        name="password_confirmation"
                        {{ $user ? '' : 'required' }}
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                    >
                </div>

            </div>

        </div>

        <div class="flex justify-end">

            <button
                type="submit"
                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-black text-white hover:bg-slate-800"
            >
                {{ $user ? 'Save User' : 'Create User' }}
            </button>

        </div>

    

    {{-- ESUBIZ_CORE_PARTNER_MANAGEMENT_FORM_V1 --}}
    
<style>
.ESUBIZ_PARTNER_PREMIUM_CARD_V85 {
    position: relative;
    margin-top: 1.5rem;
}

.ESUBIZ_PARTNER_PREMIUM_CARD_V85::before {
    content: "";
    display: block;
    height: 4px;
    width: 100%;
    background: linear-gradient(
        90deg,
        #4f46e5 0%,
        #2563eb 55%,
        #0ea5e9 100%
    );
}

.ESUBIZ_PARTNER_PREMIUM_CARD_V85 label {
    display: block;
    margin-bottom: .5rem;
    font-size: .875rem;
    font-weight: 700;
    color: #334155;
}

.ESUBIZ_PARTNER_PREMIUM_CARD_V85 input[type="number"],
.ESUBIZ_PARTNER_PREMIUM_CARD_V85 select,
.ESUBIZ_PARTNER_PREMIUM_CARD_V85 textarea {
    width: 100%;
}

.ESUBIZ_PARTNER_PREMIUM_CARD_V85 input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    border-radius: .25rem;
    accent-color: #4f46e5;
}

.ESUBIZ_PARTNER_PREMIUM_CARD_V85 textarea {
    resize: vertical;
}

@media (max-width: 767px) {
    .ESUBIZ_PARTNER_PREMIUM_CARD_V85 {
        border-radius: 1rem;
    }
}
</style>

@if(
        !empty($partnerRoleId)
        && !empty($canManagePartners)
    )
        @php
            $corePartnerSelected = in_array(
                (int) $partnerRoleId,
                array_map(
                    'intval',
                    old(
                        'roles',
                        $selectedRoleIds ?? []
                    )
                ),
                true
            );

            $corePartnerPercentage = old(
                'partner_investment_percentage',
                $partnerInvestment
                    ->investment_percentage
                    ?? 0
            );

            $corePartnerBasis = old(
                'partner_profit_basis',
                $partnerInvestment
                    ->profit_basis
                    ?? 'net'
            );

            $corePartnerActive = old(
                'partner_is_active',
                isset($partnerInvestment)
                    ? (bool) $partnerInvestment->is_active
                    : true
            );

            $corePartnerNotes = old(
                'partner_notes',
                $partnerInvestment
                    ->notes
                    ?? ''
            );
        @endphp

        <div
            id="corePartnerInvestmentPanel"
            class="card border-0 shadow-sm mt-4"
            style="{{
                $corePartnerSelected
                    ? ''
                    : 'display:none;'
            }}"
        >
            <div class="card-body p-4">
                <div
                    class="d-flex flex-column flex-lg-row
                           justify-content-between
                           align-items-lg-start gap-3 mb-4"
                >
                    <div
    class="ESUBIZ_PARTNER_PREMIUM_CARD_V85 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
>
                        <h5 class="mb-1">
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                                    ↗
                                </span>
                                <span>
                                    Partner / Investor Configuration
                                </span>
                            </span>
                        </h5>

                        <p class="text-muted mb-0">
                            Manage this business administrator's
                            investment participation and financial
                            profit/loss basis.
                        </p>
                    </div>

                    <span
                        class="badge rounded-pill text-bg-warning"
                    >
                        Business Administrator
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <label
                            class="form-label"
                            for="partnerInvestmentPercentage"
                        >
                            Investment Share
                        </label>

                        <div class="input-group">
                            <input
                                type="number"
                                id="partnerInvestmentPercentage"
                                name="partner_investment_percentage"
                                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                                min="0"
                                max="100"
                                step="0.0001"
                                value="{{ $corePartnerPercentage }}"
                            >

                            <span class="input-group-text">%</span>
                        </div>

                        @error('partner_investment_percentage')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12 col-lg-6">
                        <label
                            class="form-label"
                            for="partnerProfitBasis"
                        >
                            Profit / Loss Basis
                        </label>

                        <select
                            id="partnerProfitBasis"
                            name="partner_profit_basis"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                        >
                            <option
                                value="gross"
                                @selected(
                                    $corePartnerBasis === 'gross'
                                )
                            >
                                Gross
                            </option>

                            <option
                                value="net"
                                @selected(
                                    $corePartnerBasis === 'net'
                                )
                            >
                                Net
                            </option>
                        </select>

                        @error('partner_profit_basis')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input
                                type="hidden"
                                name="partner_is_active"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="partnerIsActive"
                                name="partner_is_active"
                                value="1"
                                @checked($corePartnerActive)
                            >

                            <label
                                class="form-check-label"
                                for="partnerIsActive"
                            >
                                Active Partner / Investor
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label
                            class="form-label"
                            for="partnerNotes"
                        >
                            Internal Notes
                        </label>

                        <textarea
                            id="partnerNotes"
                            name="partner_notes"
                            class="block min-h-[120px] w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                            rows="3"
                        >{{ $corePartnerNotes }}</textarea>

                        @error('partner_notes')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const partnerRoleId =
                    @json((string) $partnerRoleId);

                const panel =
                    document.getElementById(
                        'corePartnerInvestmentPanel'
                    );

                if (!panel) {
                    return;
                }

                const roleCheckboxes =
                    document.querySelectorAll(
                        'input[name="roles[]"]'
                    );

                function refreshPartnerPanel() {
                    let selected = false;

                    roleCheckboxes.forEach(
                        function (checkbox) {
                            if (
                                String(checkbox.value) ===
                                    String(partnerRoleId)
                                && checkbox.checked
                            ) {
                                selected = true;
                            }
                        }
                    );

                    panel.style.display =
                        selected ? '' : 'none';
                }

                roleCheckboxes.forEach(
                    function (checkbox) {
                        checkbox.addEventListener(
                            'change',
                            refreshPartnerPanel
                        );
                    }
                );

                refreshPartnerPanel();
            }
        );
        </script>
    @endif


</form>
</div>

{{-- ESUBIZ_PARTNER_LAYOUT_V86B --}}
<style>
.ESUBIZ_PARTNER_SHELL_V86B {
    overflow: hidden;
    margin-top: 1.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    background: #ffffff;
    box-shadow:
        0 1px 2px rgba(15, 23, 42, .04),
        0 8px 24px rgba(15, 23, 42, .04);
}

.ESUBIZ_PARTNER_SHELL_V86B::before {
    content: "";
    display: block;
    width: 100%;
    height: 4px;
    background: linear-gradient(
        90deg,
        #4f46e5 0%,
        #2563eb 55%,
        #0ea5e9 100%
    );
}

.ESUBIZ_PARTNER_SHELL_V86B
.ESUBIZ_PARTNER_PREMIUM_CARD_V85 {
    margin: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    background: #ffffff !important;
    padding: 1.25rem 1.5rem !important;
    border-bottom: 1px solid #e2e8f0 !important;
}

.ESUBIZ_PARTNER_SHELL_V86B
.ESUBIZ_PARTNER_PREMIUM_CARD_V85::before {
    display: none !important;
}

.ESUBIZ_PARTNER_BODY_V86B {
    padding: 1.5rem;
}

.ESUBIZ_PARTNER_META_V86B {
    margin-bottom: 1.25rem;
}

.ESUBIZ_PARTNER_META_V86B > * {
    margin-top: .25rem;
}

.ESUBIZ_PARTNER_GRID_V86B {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.25rem;
    align-items: start;
}

.ESUBIZ_PARTNER_FIELD_V86B {
    min-width: 0;
}

.ESUBIZ_PARTNER_FIELD_V86B label {
    display: block;
    margin-bottom: .5rem;
    font-size: .875rem;
    font-weight: 700;
    color: #334155;
}

.ESUBIZ_PARTNER_FIELD_V86B input[type="number"],
.ESUBIZ_PARTNER_FIELD_V86B select,
.ESUBIZ_PARTNER_FIELD_V86B textarea {
    display: block;
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: .75rem;
    background: #fff;
    padding: .75rem 1rem;
    font-size: .875rem;
    color: #0f172a;
    box-shadow: 0 1px 2px rgba(15,23,42,.04);
    outline: none;
}

.ESUBIZ_PARTNER_FIELD_V86B input[type="number"]:focus,
.ESUBIZ_PARTNER_FIELD_V86B select:focus,
.ESUBIZ_PARTNER_FIELD_V86B textarea:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}

.ESUBIZ_PARTNER_ACTIVE_V86B {
    display: flex;
    flex-direction: column;
}

.ESUBIZ_PARTNER_ACTIVE_CONTROL_V86B {
    display: flex;
    min-height: 48px;
    align-items: center;
    gap: .7rem;
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: .75rem;
    background: #fff;
    padding: .75rem 1rem;
    box-shadow: 0 1px 2px rgba(15,23,42,.04);
}

.ESUBIZ_PARTNER_ACTIVE_CONTROL_V86B input[type="checkbox"] {
    width: 18px;
    height: 18px;
    flex: 0 0 auto;
    accent-color: #4f46e5;
}

.ESUBIZ_PARTNER_NOTES_V86B {
    grid-column: 1 / -1;
}

.ESUBIZ_PARTNER_NOTES_V86B textarea {
    min-height: 120px;
    resize: vertical;
}

.ESUBIZ_PARTNER_SAVE_V86B {
    display: flex !important;
    justify-content: flex-end !important;
    width: 100%;
    margin-top: 1.5rem !important;
}

@media (max-width: 1023px) {
    .ESUBIZ_PARTNER_GRID_V86B {
        grid-template-columns: 1fr;
    }

    .ESUBIZ_PARTNER_NOTES_V86B {
        grid-column: auto;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (document.querySelector(
        '.ESUBIZ_PARTNER_SHELL_V86B'
    )) {
        return;
    }

    const headingCard = document.querySelector(
        '.ESUBIZ_PARTNER_PREMIUM_CARD_V85'
    );

    if (!headingCard) {
        return;
    }

    const form =
        headingCard.closest('form')
        || document.querySelector(
            '[name="partner_investment_percentage"]'
        )?.closest('form');

    if (!form) {
        return;
    }

    const share = form.querySelector(
        '[name="partner_investment_percentage"]'
    );

    const basis = form.querySelector(
        '[name="partner_profit_basis"]'
    );

    const active = form.querySelector(
        '[name="partner_is_active"]'
    );

    const notes = form.querySelector(
        '[name="partner_notes"]'
    );

    if (!share || !basis || !active || !notes) {
        return;
    }

    const partnerControls = [
        share,
        basis,
        active,
        notes
    ];

    /*
     * Find the smallest useful wrapper for each existing field.
     * We stop before a wrapper starts containing multiple
     * Partner / Investor controls.
     */
    function getFieldWrapper(control) {
        let current = control;
        let best = control.parentElement;

        while (
            current
            && current.parentElement
            && current.parentElement !== form
        ) {
            const parent = current.parentElement;

            const count = partnerControls.filter(
                function (item) {
                    return parent.contains(item);
                }
            ).length;

            if (count > 1) {
                break;
            }

            best = parent;
            current = parent;

            if (
                parent.querySelector('label')
                || parent.tagName === 'LABEL'
            ) {
                break;
            }
        }

        return best;
    }

    const shareBox = getFieldWrapper(share);
    const basisBox = getFieldWrapper(basis);
    const activeBox = getFieldWrapper(active);
    const notesBox = getFieldWrapper(notes);

    /*
     * Build one proper premium card around the complete
     * Partner / Investor settings.
     */
    const shell = document.createElement('section');
    shell.className =
        'ESUBIZ_PARTNER_SHELL_V86B';

    headingCard.parentNode.insertBefore(
        shell,
        headingCard
    );

    shell.appendChild(headingCard);

    const body = document.createElement('div');
    body.className =
        'ESUBIZ_PARTNER_BODY_V86B';

    shell.appendChild(body);

    /*
     * Move descriptive elements located between the heading
     * and first Partner field into the card as metadata.
     */
    const meta = document.createElement('div');
    meta.className =
        'ESUBIZ_PARTNER_META_V86B';

    body.appendChild(meta);

    const commonParent = shareBox.parentElement;

    if (
        commonParent
        && headingCard.parentElement !== commonParent
    ) {
        const candidates = Array.from(
            commonParent.children
        );

        const firstIndex =
            candidates.indexOf(shareBox);

        if (firstIndex > 0) {
            candidates
                .slice(0, firstIndex)
                .forEach(function (element) {
                    if (
                        !element.contains(headingCard)
                        && !partnerControls.some(
                            function (control) {
                                return element.contains(
                                    control
                                );
                            }
                        )
                    ) {
                        const txt =
                            (element.textContent || '')
                                .trim();

                        if (
                            txt.includes(
                                'investment participation'
                            )
                            || txt.includes(
                                'Business Administrator'
                            )
                        ) {
                            meta.appendChild(element);
                        }
                    }
                });
        }
    }

    if (!meta.children.length) {
        meta.remove();
    }

    const grid = document.createElement('div');
    grid.className =
        'ESUBIZ_PARTNER_GRID_V86B';

    body.appendChild(grid);

    [
        shareBox,
        basisBox,
        activeBox,
        notesBox
    ].forEach(function (box) {
        if (!box) {
            return;
        }

        box.classList.add(
            'ESUBIZ_PARTNER_FIELD_V86B'
        );

        grid.appendChild(box);
    });

    notesBox.classList.add(
        'ESUBIZ_PARTNER_NOTES_V86B'
    );

    activeBox.classList.add(
        'ESUBIZ_PARTNER_ACTIVE_V86B'
    );

    /*
     * Give the checkbox the same premium control height
     * as the two neighbouring desktop fields.
     */
    let activeControl = active.parentElement;

    if (
        activeControl
        && activeControl !== activeBox
    ) {
        activeControl.classList.add(
            'ESUBIZ_PARTNER_ACTIVE_CONTROL_V86B'
        );
    } else {
        const controlShell =
            document.createElement('div');

        controlShell.className =
            'ESUBIZ_PARTNER_ACTIVE_CONTROL_V86B';

        active.parentNode.insertBefore(
            controlShell,
            active
        );

        controlShell.appendChild(active);
    }

    /*
     * Move the EXISTING Save User action below the complete
     * Partner / Investor card. No new submit button is created.
     */
    const submitters = Array.from(
        form.querySelectorAll(
            'button[type="submit"], input[type="submit"]'
        )
    );

    const saveButton = submitters.find(
        function (button) {
            return (
                button.textContent
                || button.value
                || ''
            )
                .trim()
                .toLowerCase()
                === 'save user';
        }
    );

    if (saveButton) {
        let saveRow = saveButton.parentElement;

        /*
         * Grow only through simple action wrappers.
         * Never capture profile cards or the Partner card.
         */
        while (
            saveRow
            && saveRow.parentElement
            && saveRow.parentElement !== form
        ) {
            const parent = saveRow.parentElement;

            if (
                parent.contains(shell)
                || parent.querySelectorAll(
                    'button[type="submit"], input[type="submit"]'
                ).length !== 1
                || parent.children.length > 2
            ) {
                break;
            }

            saveRow = parent;
        }

        if (
            saveRow
            && !saveRow.contains(shell)
        ) {
            saveRow.classList.add(
                'ESUBIZ_PARTNER_SAVE_V86B'
            );

            shell.insertAdjacentElement(
                'afterend',
                saveRow
            );
        }
    }
});
</script>


{{-- ESUBIZ_PARTNER_TOGGLE_V87 --}}
<style>
.ESUBIZ_PARTNER_TOGGLE_WRAP_V87 {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.ESUBIZ_PARTNER_TOGGLE_LABEL_V87 {
    display: block;
    margin-bottom: .5rem;
    font-size: .875rem;
    font-weight: 700;
    color: #334155;
}

.ESUBIZ_PARTNER_TOGGLE_V87 {
    position: relative;
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
    min-height: 48px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    border-radius: .75rem;
    background: #f1f5f9;
    box-shadow: 0 1px 2px rgba(15,23,42,.04);
    user-select: none;
}

.ESUBIZ_PARTNER_TOGGLE_V87 button {
    position: relative;
    z-index: 2;
    border: 0;
    outline: 0;
    padding: .75rem 1rem;
    font-size: .82rem;
    font-weight: 800;
    cursor: pointer;
    transition:
        background-color .2s ease,
        color .2s ease,
        box-shadow .2s ease;
}

.ESUBIZ_PARTNER_TOGGLE_DISABLE_V87 {
    background: #e2e8f0;
    color: #64748b;
}

.ESUBIZ_PARTNER_TOGGLE_ACTIVE_V87 {
    background: #ffffff;
    color: #64748b;
}

.ESUBIZ_PARTNER_TOGGLE_V87[data-state="active"]
.ESUBIZ_PARTNER_TOGGLE_ACTIVE_V87 {
    background: #2563eb;
    color: #ffffff;
    box-shadow:
        inset 0 0 0 1px rgba(255,255,255,.15),
        0 2px 6px rgba(37,99,235,.22);
}

.ESUBIZ_PARTNER_TOGGLE_V87[data-state="active"]
.ESUBIZ_PARTNER_TOGGLE_DISABLE_V87 {
    background: #e2e8f0;
    color: #94a3b8;
}

.ESUBIZ_PARTNER_TOGGLE_V87[data-state="disabled"]
.ESUBIZ_PARTNER_TOGGLE_DISABLE_V87 {
    background: #64748b;
    color: #ffffff;
    box-shadow:
        inset 0 0 0 1px rgba(255,255,255,.08);
}

.ESUBIZ_PARTNER_TOGGLE_V87[data-state="disabled"]
.ESUBIZ_PARTNER_TOGGLE_ACTIVE_V87 {
    background: #f1f5f9;
    color: #94a3b8;
}

.ESUBIZ_PARTNER_TOGGLE_V87 button:focus-visible {
    box-shadow:
        inset 0 0 0 2px #ffffff,
        inset 0 0 0 4px #2563eb;
}

.ESUBIZ_PARTNER_ORIGINAL_CHECKBOX_V87 {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    opacity: 0 !important;
    pointer-events: none !important;
    overflow: hidden !important;
}

.ESUBIZ_PARTNER_GRID_V86B
.ESUBIZ_PARTNER_ACTIVE_V86B {
    display: block !important;
    min-height: auto !important;
}

.ESUBIZ_PARTNER_GRID_V86B
.ESUBIZ_PARTNER_ACTIVE_CONTROL_V86B {
    display: block !important;
    min-height: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
}

@media (max-width: 1023px) {
    .ESUBIZ_PARTNER_TOGGLE_V87 {
        max-width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const shell = document.querySelector(
        '.ESUBIZ_PARTNER_SHELL_V86B'
    );

    if (!shell) {
        return;
    }

    const grid = shell.querySelector(
        '.ESUBIZ_PARTNER_GRID_V86B'
    );

    const active = shell.querySelector(
        '[name="partner_is_active"]'
    );

    const share = shell.querySelector(
        '[name="partner_investment_percentage"]'
    );

    const basis = shell.querySelector(
        '[name="partner_profit_basis"]'
    );

    const notes = shell.querySelector(
        '[name="partner_notes"]'
    );

    if (
        !grid
        || !active
        || !share
        || !basis
        || !notes
    ) {
        return;
    }

    function fieldBox(control) {
        return control.closest(
            '.ESUBIZ_PARTNER_FIELD_V86B'
        );
    }

    const activeBox = fieldBox(active);
    const shareBox = fieldBox(share);
    const basisBox = fieldBox(basis);
    const notesBox = fieldBox(notes);

    /*
     * Required desktop order:
     *
     * 1. Active / Disabled
     * 2. Investment Share
     * 3. Profit / Loss Basis
     * 4. Notes full-width
     */
    [
        activeBox,
        shareBox,
        basisBox,
        notesBox
    ].forEach(function (box) {
        if (box) {
            grid.appendChild(box);
        }
    });

    if (
        activeBox
        && !activeBox.querySelector(
            '.ESUBIZ_PARTNER_TOGGLE_V87'
        )
    ) {
        /*
         * Remove the old visual checkbox label/text only.
         * The real checkbox itself remains in the DOM and
         * continues to submit to the existing Laravel backend.
         */
        const existingLabels = Array.from(
            activeBox.querySelectorAll('label')
        );

        existingLabels.forEach(function (label) {
            if (label.contains(active)) {
                const parent = active.parentNode;

                if (parent !== activeBox) {
                    activeBox.insertBefore(
                        active,
                        activeBox.firstChild
                    );
                }

                label.remove();
            }
        });

        /*
         * Also remove any loose old "Active Partner / Investor"
         * text left by previous markup.
         */
        Array.from(activeBox.childNodes).forEach(
            function (node) {
                if (
                    node.nodeType === Node.TEXT_NODE
                    && (
                        node.textContent || ''
                    ).trim()
                        .toLowerCase()
                        === 'active partner / investor'
                ) {
                    node.remove();
                }
            }
        );

        active.classList.add(
            'ESUBIZ_PARTNER_ORIGINAL_CHECKBOX_V87'
        );

        const wrap = document.createElement('div');
        wrap.className =
            'ESUBIZ_PARTNER_TOGGLE_WRAP_V87';

        const label = document.createElement('div');
        label.className =
            'ESUBIZ_PARTNER_TOGGLE_LABEL_V87';

        label.textContent =
            'Partner / Investor Status';

        const toggle = document.createElement('div');
        toggle.className =
            'ESUBIZ_PARTNER_TOGGLE_V87';

        const disabledButton =
            document.createElement('button');

        disabledButton.type = 'button';
        disabledButton.className =
            'ESUBIZ_PARTNER_TOGGLE_DISABLE_V87';

        disabledButton.textContent = 'Disabled';

        const activeButton =
            document.createElement('button');

        activeButton.type = 'button';
        activeButton.className =
            'ESUBIZ_PARTNER_TOGGLE_ACTIVE_V87';

        activeButton.textContent = 'Active';

        toggle.appendChild(disabledButton);
        toggle.appendChild(activeButton);

        wrap.appendChild(label);
        wrap.appendChild(toggle);

        active.insertAdjacentElement(
            'afterend',
            wrap
        );

        function syncToggle() {
            toggle.dataset.state =
                active.checked
                    ? 'active'
                    : 'disabled';

            disabledButton.setAttribute(
                'aria-pressed',
                active.checked
                    ? 'false'
                    : 'true'
            );

            activeButton.setAttribute(
                'aria-pressed',
                active.checked
                    ? 'true'
                    : 'false'
            );
        }

        disabledButton.addEventListener(
            'click',
            function () {
                active.checked = false;

                active.dispatchEvent(
                    new Event(
                        'change',
                        { bubbles: true }
                    )
                );

                syncToggle();
            }
        );

        activeButton.addEventListener(
            'click',
            function () {
                active.checked = true;

                active.dispatchEvent(
                    new Event(
                        'change',
                        { bubbles: true }
                    )
                );

                syncToggle();
            }
        );

        active.addEventListener(
            'change',
            syncToggle
        );

        syncToggle();
    }

    /*
     * Remove the stray "Business Administrator" left below
     * the card by the previous DOM relocation.
     *
     * Only exact matching standalone text outside the
     * Partner card is removed.
     */
    const walker = document.createTreeWalker(
        document.body,
        NodeFilter.SHOW_TEXT
    );

    const staleNodes = [];

    while (walker.nextNode()) {
        const node = walker.currentNode;

        if (
            (
                node.textContent || ''
            ).trim()
                === 'Business Administrator'
            && !shell.contains(node)
        ) {
            staleNodes.push(node);
        }
    }

    staleNodes.forEach(function (node) {
        const parent = node.parentElement;

        if (
            parent
            && (
                parent.textContent || ''
            ).trim()
                === 'Business Administrator'
            && parent.children.length === 0
        ) {
            parent.remove();
        } else {
            node.remove();
        }
    });

});
</script>

@endsection
