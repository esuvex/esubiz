@extends('admin.layouts.app')

@section('title', 'Edit Website')

@section('content')
            {{-- SYSTEM STATUS NOTICE --}}

            <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5">

                <div class="flex items-start gap-3">

                    <span class="text-lg">
                        ℹ️
                    </span>

                    <div>

                        <p class="font-semibold text-blue-900">
                            Esubiz System Status
                        </p>

                        <p class="mt-1 text-sm leading-6 text-blue-800">

                            Your Active/Inactive setting only controls your own website
                            availability. It does not override Esubiz system controls,
                            including payment suspension, security restrictions,
                            unlawful-use suspension, or other platform enforcement.

                        </p>

                    </div>

                </div>

            </div>


            <div class="mt-6 flex justify-end">

                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-7 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>


    {{-- ================================================================ --}}
    {{-- WEBSITE INFORMATION --}}
    {{-- ================================================================ --}}

    <div class="rounded-3xl bg-white p-8 shadow">

        <div>

            <h2 class="text-2xl font-bold text-slate-900">
                Website Information
            </h2>

            <p class="mt-2 text-slate-500">
                Basic information about this website.
            </p>

        </div>


        <div class="mt-6 grid gap-6 md:grid-cols-2">

            {{-- WEBSITE TYPE --}}

            <div class="rounded-2xl bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Website Type
                </p>

                <p class="mt-2 font-semibold text-slate-900">
                    {{ ucfirst($website->type) }}
                </p>

            </div>


            {{-- WEBSITE ADDRESS --}}

            <div class="rounded-2xl bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Esubiz Website Address
                </p>

                <p class="mt-2 break-all font-semibold text-slate-900">
                    {{ $website->subdomain }}.esubiz.com
                </p>

            </div>


            {{-- SYSTEM STATUS --}}

            <div class="rounded-2xl bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    System Status
                </p>

                <p class="mt-2 font-semibold text-slate-900">
                    {{ ucfirst($website->status) }}
                </p>

            </div>


            {{-- WEBSITE CODE --}}

            <div class="rounded-2xl bg-slate-50 p-5">

                <p class="text-sm text-slate-500">
                    Website Code
                </p>

                <p class="mt-2 break-all font-semibold text-slate-900">
                    {{ $website->website_code }}
                </p>

            </div>


            {{-- CUSTOM DOMAIN --}}

            <div class="rounded-2xl bg-slate-50 p-5 md:col-span-2">

                <p class="text-sm text-slate-500">
                    Custom Domain
                </p>

                <p class="mt-2 break-all font-semibold text-slate-900">

                    {{ $website->domain ?: 'No custom domain connected' }}

                </p>

            </div>

        </div>

    </div>

</div>

@endsection
