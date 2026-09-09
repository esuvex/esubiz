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

                <div>
                    <label class="mb-2 block text-sm font-black text-slate-700">
                        Phone
                    </label>

                    <input
                        type="tel"
                        name="phone"
                        value="{{ old('phone', $user->phone ?? '') }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                    >
                </div>

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
                    <div>
                        <h5 class="mb-1">
                            Partner / Investor Configuration
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
                                class="form-control"
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
                            class="form-select"
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
                            class="form-control"
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
@endsection
