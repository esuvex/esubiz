@extends('admin.layouts.app')

@section('title', ($website->name ?: 'Website') . ' | User Websites')

@section('content')

<div class="min-h-screen bg-slate-100 px-5 py-8 sm:px-6">

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">

            <div>

                <a
                    href="{{ route('admin.websites.index') }}"
                    class="text-sm font-bold text-blue-600 hover:text-blue-700"
                >
                    ← User Websites
                </a>

                <div class="mt-4 text-xs font-black uppercase tracking-widest text-blue-600">
                    Central Website Registry
                </div>

                <h1 class="mt-2 text-3xl font-black text-slate-900">
                    {{ $detail['website']['name'] ?: 'Unnamed Website' }}
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    {{ $detail['website']['registered_domain'] ?: 'No registered domain' }}
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <span
                    class="rounded-full px-3 py-1 text-xs font-black {{
                        $detail['website']['is_off_server']
                            ? 'bg-violet-100 text-violet-700'
                            : 'bg-blue-100 text-blue-700'
                    }}"
                >
                    {{
                        $detail['website']['is_off_server']
                            ? 'Off Server'
                            : 'SaaS'
                    }}
                </span>

                <span
                    class="rounded-full px-3 py-1 text-xs font-black {{
                        $detail['website']['registry_status'] === 'active'
                            ? 'bg-emerald-100 text-emerald-700'
                            : (
                                $detail['website']['registry_status'] === 'suspended'
                                    ? 'bg-amber-100 text-amber-700'
                                    : 'bg-red-100 text-red-700'
                            )
                    }}"
                >
                    {{ ucfirst($detail['website']['registry_status'] ?: 'unknown') }}
                </span>

            </div>

        </div>


        {{-- Service credits --}}
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

            @foreach([
                'ai' => 'AI Credits',
                'sms' => 'SMS Credits',
                'email' => 'Email Credits',
                'whatsapp' => 'WhatsApp Credits',
            ] as $service => $label)

                @php
                    $credit =
                        $detail['credits'][$service]
                        ?? [
                            'balance' => 0,
                            'lifetime_credited' => 0,
                            'lifetime_consumed' => 0,
                        ];
                @endphp

                <div class="esubiz-admin-card p-5">

                    <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                        {{ $label }}
                    </div>

                    <div class="mt-3 text-3xl font-black text-slate-900">
                        {{ number_format((float) $credit['balance'], 2) }}
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-xs">

                        <div>
                            <div class="font-bold text-slate-400">
                                Credited
                            </div>

                            <div class="mt-1 font-black text-slate-700">
                                {{ number_format((float) $credit['lifetime_credited'], 2) }}
                            </div>
                        </div>

                        <div>
                            <div class="font-bold text-slate-400">
                                Consumed
                            </div>

                            <div class="mt-1 font-black text-slate-700">
                                {{ number_format((float) $credit['lifetime_consumed'], 2) }}
                            </div>
                        </div>

                    </div>

                </div>

            @endforeach

        </div>


        {{-- Website identity --}}
        <div class="grid gap-6 xl:grid-cols-2">

            <div class="esubiz-admin-card p-6">

                <h2 class="text-xl font-black text-slate-900">
                    Website Identity
                </h2>

                <div class="mt-5 divide-y divide-slate-100">

                    @php
                        $identityRows = [
                            'Central Website ID' => $detail['website']['id'],
                            'Website UUID' => $detail['website']['website_uuid'],
                            'Type' => $detail['website']['type'],
                            'Edition' => $detail['website']['edition'],
                            'Registered Domain' => $detail['website']['registered_domain'],
                            'Runtime Status' => $detail['website']['status'],
                            'Registry Status' => $detail['website']['registry_status'],
                            'User Enabled' => $detail['website']['user_enabled'] ? 'Yes' : 'No',
                        ];
                    @endphp

                    @foreach($identityRows as $label => $value)

                        <div class="grid gap-2 py-4 sm:grid-cols-3">

                            <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                                {{ $label }}
                            </div>

                            <div class="break-words text-sm font-bold text-slate-800 sm:col-span-2">
                                {{ $value !== null && $value !== '' ? $value : '—' }}
                            </div>

                        </div>

                    @endforeach

                </div>

            </div>


            <div class="esubiz-admin-card p-6">

                <h2 class="text-xl font-black text-slate-900">
                    Ownership & Configuration
                </h2>

                <div class="mt-5 divide-y divide-slate-100">

                    <div class="grid gap-2 py-4 sm:grid-cols-3">

                        <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Owner
                        </div>

                        <div class="sm:col-span-2">

                            @if($website->owner)

                                <div class="font-bold text-slate-800">
                                    {{ $website->owner->name }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $website->owner->email }}
                                </div>

                            @else
                                —
                            @endif

                        </div>

                    </div>


                    <div class="grid gap-2 py-4 sm:grid-cols-3">

                        <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Developer
                        </div>

                        <div class="text-sm font-bold text-slate-800 sm:col-span-2">
                            {{ $website->developer?->name ?: '—' }}
                        </div>

                    </div>


                    <div class="grid gap-2 py-4 sm:grid-cols-3">

                        <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Plan
                        </div>

                        <div class="text-sm font-bold text-slate-800 sm:col-span-2">
                            {{ $website->plan?->name ?: 'No plan assigned' }}
                        </div>

                    </div>


                    <div class="grid gap-2 py-4 sm:grid-cols-3">

                        <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                            Workspace
                        </div>

                        <div class="text-sm font-bold text-slate-800 sm:col-span-2">
                            {{ $website->workspace?->name ?: 'No workspace' }}
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Products --}}
        <div class="grid gap-6 xl:grid-cols-3">

            {{-- Add-ons --}}
            <div class="esubiz-admin-card p-6">

                <div class="flex items-center justify-between gap-3">

                    <h2 class="text-xl font-black text-slate-900">
                        Add-ons
                    </h2>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                        {{ $detail['addons']->count() }}
                    </span>

                </div>

                <div class="mt-5 space-y-3">

                    @forelse($detail['addons'] as $addon)

                        <div class="rounded-2xl border border-slate-200 p-4">

                            <div class="font-bold text-slate-800">
                                {{
                                    $addon->product_name
                                    ?? $addon->name
                                    ?? ('Entitlement #' . $addon->id)
                                }}
                            </div>

                            <div class="mt-1 text-xs text-slate-500">
                                {{ ucfirst($addon->status ?? 'active') }}
                            </div>

                        </div>

                    @empty

                        <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">
                            No active add-ons are currently attached to this website.
                        </div>

                    @endforelse

                </div>

            </div>


            {{-- Theme --}}
            <div class="esubiz-admin-card p-6">

                <h2 class="text-xl font-black text-slate-900">
                    Theme
                </h2>

                <div class="mt-5">

                    @if($detail['theme']['selected'])

                        <div class="rounded-2xl border border-slate-200 p-4">

                            <div class="font-bold text-slate-800">
                                {{
                                    $detail['theme']['package']->name
                                    ?? $detail['theme']['selected']
                                }}
                            </div>

                            @if($detail['theme']['package'])

                                <div class="mt-1 text-xs text-slate-500">
                                    {{
                                        $detail['theme']['package']->version
                                        ?? 'Current package'
                                    }}
                                </div>

                            @endif

                        </div>

                    @else

                        <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">
                            No theme is currently recorded for this website.
                        </div>

                    @endif

                </div>

            </div>


            {{-- Modules --}}
            <div class="esubiz-admin-card p-6">

                <div class="flex items-center justify-between gap-3">

                    <h2 class="text-xl font-black text-slate-900">
                        Modules
                    </h2>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                        {{ $detail['modules']->count() }}
                    </span>

                </div>

                <div class="mt-5 space-y-3">

                    @forelse($detail['modules'] as $module)

                        <div class="rounded-2xl border border-slate-200 p-4">

                            <div class="font-bold text-slate-800">
                                {{
                                    $module->name
                                    ?? $module->module_name
                                    ?? ('Module #' . ($module->module_id ?? $module->id))
                                }}
                            </div>

                            <div class="mt-1 text-xs text-slate-500">
                                {{ ucfirst($module->status ?? 'installed') }}
                            </div>

                        </div>

                    @empty

                        <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">
                            No modules are currently installed for this website.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>


        {{-- Licence --}}
        <div class="esubiz-admin-card p-6">

            <div class="flex flex-wrap items-center justify-between gap-3">

                <div>

                    <h2 class="text-xl font-black text-slate-900">
                        Licence & Deployment
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Central deployment and licensing state for this website.
                    </p>

                </div>

                <span
                    class="rounded-full px-3 py-1 text-xs font-black {{
                        $detail['licence']['required']
                            ? 'bg-violet-100 text-violet-700'
                            : 'bg-blue-100 text-blue-700'
                    }}"
                >
                    {{
                        $detail['licence']['required']
                            ? 'Licence Required'
                            : 'SaaS Managed'
                    }}
                </span>

            </div>


            <div class="mt-5">

                @if(!$detail['licence']['required'])

                    <div class="rounded-2xl bg-blue-50 p-5 text-sm leading-6 text-blue-800">
                        This is an Esubiz-hosted SaaS website. A separate off-server Core licence is not required.
                    </div>

                @elseif($detail['licence']['licence'])

                    <div class="rounded-2xl border border-slate-200 p-5">

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

                            @foreach((array) $detail['licence']['licence'] as $key => $value)

                                @if(
                                    in_array(
                                        $key,
                                        [
                                            'id',
                                            'status',
                                            'registered_domain',
                                            'license_key',
                                            'website_id',
                                            'website_uuid',
                                        ],
                                        true
                                    )
                                )

                                    <div>

                                        <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                                            {{ str_replace('_', ' ', ucfirst($key)) }}
                                        </div>

                                        <div class="mt-1 break-words text-sm font-bold text-slate-800">
                                            {{ $value ?? '—' }}
                                        </div>

                                    </div>

                                @endif

                            @endforeach

                        </div>

                    </div>

                @else

                    <div class="rounded-2xl bg-amber-50 p-5 text-sm leading-6 text-amber-800">
                        No active off-server licence record was resolved for this website.
                    </div>

                @endif

            </div>

        </div>


        {{-- Entitlements --}}
        <div class="esubiz-admin-card p-6">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <h2 class="text-xl font-black text-slate-900">
                        Product Entitlements
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Canonical Marketplace and Core entitlements attached to this website.
                    </p>

                </div>

                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                    {{ $detail['entitlements']->count() }}
                </span>

            </div>


            <div class="mt-5 overflow-x-auto">

                @if($detail['entitlements']->isEmpty())

                    <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">
                        This website currently has no product entitlement records.
                    </div>

                @else

                    <table class="min-w-full divide-y divide-slate-200">

                        <thead>

                            <tr class="text-left text-[11px] font-black uppercase tracking-wide text-slate-400">

                                <th class="py-3 pr-5">
                                    ID
                                </th>

                                <th class="py-3 pr-5">
                                    Product Type
                                </th>

                                <th class="py-3 pr-5">
                                    Product
                                </th>

                                <th class="py-3 pr-5">
                                    Status
                                </th>

                                <th class="py-3">
                                    Expires
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-slate-100">

                            @foreach($detail['entitlements'] as $entitlement)

                                <tr>

                                    <td class="py-4 pr-5 text-sm font-bold text-slate-700">
                                        {{ $entitlement->id }}
                                    </td>

                                    <td class="py-4 pr-5 text-sm text-slate-600">
                                        {{ ucfirst(str_replace('_', ' ', $entitlement->product_type ?? '—')) }}
                                    </td>

                                    <td class="py-4 pr-5 text-sm text-slate-600">
                                        {{
                                            $entitlement->product_name
                                            ?? $entitlement->product_key
                                            ?? $entitlement->product_id
                                            ?? '—'
                                        }}
                                    </td>

                                    <td class="py-4 pr-5 text-sm font-bold text-slate-700">
                                        {{ ucfirst($entitlement->status ?? '—') }}
                                    </td>

                                    <td class="py-4 text-sm text-slate-600">
                                        {{ $entitlement->expires_at ?? '—' }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection
