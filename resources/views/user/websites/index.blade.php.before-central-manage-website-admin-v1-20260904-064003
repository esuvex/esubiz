@extends('admin.layouts.app')

@section('title', 'My Websites')

@section('content')

<div class="space-y-8">

    <div class="rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 p-8 text-white shadow-2xl">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-blue-300">
                    ESUBIZ
                </p>

                <h1 class="mt-3 text-4xl font-bold">
                    My Websites
                </h1>

                <p class="mt-3 max-w-2xl text-slate-300">
                    View and manage all your websites from one place.
                </p>
            </div>



        </div>

    </div>


    @if($websites->count())

        <div class="grid grid-cols-1 gap-7 lg:grid-cols-2">

            @foreach($websites as $website)

                @php
                    $websiteIsActive =
                        strtolower((string) $website->status) === 'active'
                        && (bool) $website->user_enabled;

                    $websiteName =
                        trim(
                            (string) (
                                $website->name
                                ?: 'Website'
                            )
                        );

                    $websiteInitial =
                        strtoupper(
                            mb_substr(
                                $websiteName,
                                0,
                                1
                            )
                        );

                    $websiteAddress =
                        (
                            $website->deployment_type === 'saas'
                            && !empty($website->subdomain)
                        )
                            ? 'https://'
                                . $website->subdomain
                                . '.esubiz.com'
                            : (
                                !empty($website->domain)
                                    ? (
                                        str_starts_with(
                                            (string) $website->domain,
                                            'http'
                                        )
                                            ? $website->domain
                                            : 'https://'
                                                . $website->domain
                                    )
                                    : (
                                        !empty($website->registered_domain)
                                            ? (
                                                str_starts_with(
                                                    (string) $website->registered_domain,
                                                    'http'
                                                )
                                                    ? $website->registered_domain
                                                    : 'https://'
                                                        . $website->registered_domain
                                            )
                                            : null
                                    )
                            );

                    /*
                     * If a website logo exists, use it.
                     * Otherwise show the website initial.
                     */
                    $websiteLogo =
                        $website->logo
                        ?? null;
                @endphp


                <article
                    class="overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 transition duration-300 hover:-translate-y-1 hover:shadow-2xl"
                >

                    {{-- FACEBOOK-STYLE WEBSITE PROFILE --}}

                    <div class="relative">

                        {{-- Cover --}}
                        <div
                            class="relative h-40 overflow-hidden bg-slate-950 sm:h-44"
                        >
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-black via-slate-950 to-blue-950"
                            ></div>

                            <div
                                class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-blue-600/25 blur-3xl"
                            ></div>

                            <div
                                class="absolute -bottom-24 -left-10 h-48 w-48 rounded-full bg-blue-500/10 blur-3xl"
                            ></div>


                            <div
                                class="absolute right-5 top-5"
                            >
                                <span
                                    class="inline-flex rounded-full border px-3 py-1.5 text-xs font-bold backdrop-blur {{
                                        $websiteIsActive
                                            ? 'border-emerald-300/30 bg-emerald-400/15 text-emerald-200'
                                            : 'border-red-300/30 bg-red-400/15 text-red-200'
                                    }}"
                                >
                                    {{
                                        $websiteIsActive
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </div>

                        </div>


                        {{-- Profile identity --}}
                        <div
                            class="px-6 pb-6 pt-6 text-center"
                        >

                            <h2
                                class="text-2xl font-black tracking-tight text-slate-950"
                            >
                                {{ $websiteName }}
                            </h2>

                            @if($websiteAddress)

                                <a
                                    href="{{ $websiteAddress }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-2 inline-block max-w-full truncate text-sm font-semibold text-blue-600 hover:text-blue-700"
                                >
                                    {{ preg_replace('#^https?://#', '', $websiteAddress) }}
                                </a>

                            @endif

                        </div>

                    </div>


                    <div class="p-6">

                        {{-- SUMMARY --}}
                        <div
                            class="grid grid-cols-2 gap-3 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100"
                        >

                            <div>

                                <div
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Website Type
                                </div>

                                <div
                                    class="mt-1 text-sm font-bold text-slate-900"
                                >
                                    {{
                                        ucfirst(
                                            $website->type
                                            ?? 'Business'
                                        )
                                    }}
                                </div>

                            </div>


                            <div>

                                <div
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Status
                                </div>

                                <div
                                    class="mt-1 text-sm font-bold {{
                                        $websiteIsActive
                                            ? 'text-emerald-600'
                                            : 'text-red-600'
                                    }}"
                                >
                                    {{
                                        $websiteIsActive
                                            ? 'Online'
                                            : 'Disabled'
                                    }}
                                </div>

                            </div>

                        </div>


                        {{-- ACCESS TOGGLE --}}
                        <div
                            class="mt-5 flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-3"
                        >

                            <div>

                                <div
                                    class="text-sm font-bold text-slate-900"
                                >
                                    Website Access
                                </div>

                                <div
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Enable or disable this website
                                </div>

                            </div>


                            <form
                                method="POST"
                                action="{{ route('user.websites.toggle', $website->id) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-bold transition {{
                                        $website->user_enabled
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-slate-800 bg-white text-slate-800 hover:bg-slate-50'
                                    }}"
                                >

                                    <span
                                        class="relative inline-flex h-5 w-9 rounded-full {{
                                            $website->user_enabled
                                                ? 'bg-emerald-500'
                                                : 'bg-slate-700'
                                        }}"
                                    >

                                        <span
                                            class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition {{
                                                $website->user_enabled
                                                    ? 'left-[18px]'
                                                    : 'left-0.5'
                                            }}"
                                        ></span>

                                    </span>

                                    {{
                                        $website->user_enabled
                                            ? 'Enabled'
                                            : 'Disabled'
                                    }}

                                </button>

                            </form>

                        </div>


                        {{-- FIVE PRIMARY ACTIONS --}}
                        @if($website->status === 'failed')

                            <div
                                class="mt-6 border-t border-slate-100 pt-6"
                            >

                                <a
                                    href="{{ route('user.websites.dashboard', ['website' => $website->id]) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex w-full min-h-[52px] items-center justify-center rounded-xl bg-red-600 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-red-700"
                                >
                                    Redeploy Website
                                </a>

                            </div>

                        @else

                            <div
                                class="mt-6 grid grid-cols-2 gap-3 border-t border-slate-100 pt-6"
                            >

                                @if($websiteAddress)

                                    <a
                                        href="{{ $websiteAddress }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex min-h-[52px] items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-center text-sm font-bold text-white shadow-sm transition hover:bg-blue-700"
                                    >
                                        Visit Website
                                    </a>

                                @else

                                    <div
                                        class="inline-flex min-h-[52px] cursor-not-allowed items-center justify-center rounded-xl bg-slate-100 px-4 py-3 text-center text-sm font-bold text-slate-400"
                                    >
                                        Visit Website
                                    </div>

                                @endif


                                <a
                                    href="{{ route('user.websites.dashboard', ['website' => $website->id]) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex min-h-[52px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                >
                                    Website Dashboard
                                </a>


                                <a
                                    href="{{ route('user.websites.info', $website->id) }}"
                                    class="inline-flex min-h-[52px] items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-center text-sm font-bold text-blue-700 transition hover:bg-blue-100"
                                >
                                    View Website Info
                                </a>


                                <a
                                    href="{{ route('user.websites.edit', $website) }}"
                                    class="inline-flex min-h-[52px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                >
                                    Edit Website
                                </a>


                                <div
                                    class="col-span-2 flex justify-center"
                                >



                                </div>

                            </div>

                        @endif


                        {{-- DESTRUCTIVE ACTION --}}
                        <div
                            class="mt-6 border-t border-slate-100 pt-5"
                        >

                            <form
                                method="POST"
                                action="{{ route('user.websites.destroy', $website->id) }}"
                                onsubmit="return confirm(
                                    'PERMANENTLY DELETE {{ addslashes($website->name ?: 'this website') }}?\\n\\n'
                                    + 'This action cannot be undone. The website, tenant database, credits, entitlements, add-ons, modules, licences, API access and other operational website records will be removed immediately.'
                                );"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 transition hover:border-red-300 hover:bg-red-100"
                                >
                                    Delete Website
                                </button>

                            </form>

                        </div>

                    </div>

                </article>

            @endforeach

        </div>

    @else

        <div class="rounded-3xl bg-white p-12 text-center shadow-sm ring-1 ring-slate-200">

            <h2 class="text-2xl font-bold text-slate-900">
                You don't have a website yet
            </h2>

            <p class="mx-auto mt-3 max-w-lg text-slate-500">
                Create your first Esubiz website and manage it from this page.
            </p>

            <a
                href="{{ route('websites.create') }}"
                class="mt-7 inline-flex items-center rounded-2xl bg-blue-600 px-7 py-4 font-semibold text-white hover:bg-blue-700"
            >
                Create Your First Website
            </a>

        </div>

    @endif

</div>

@endsection
