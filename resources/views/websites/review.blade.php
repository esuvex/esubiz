<x-wizard.layout
    title="Review Website"
    :step="6"
    :steps="7"
    heading="Review your website"
    description="Confirm your selections before Esubiz provisions your website.">

    <form method="POST" action="{{ route('websites.deploy') }}">

        @csrf

        @foreach($website as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        <div class="max-w-5xl mx-auto">

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b px-8 py-6">

                    <h2 class="text-2xl font-bold text-slate-900">

                        Website Summary

                    </h2>

                </div>

                <div class="grid gap-6 p-8 md:grid-cols-2">

                    <div>

                        <p class="text-sm text-slate-500">Website Name</p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ $website['name'] ?? '-' }}
                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-slate-500">Website Type</p>

                        <p class="mt-1 text-lg font-semibold capitalize">
                            {{ $website['type'] ?? '-' }}
                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-slate-500">Theme</p>

                        <p class="mt-1 text-lg font-semibold capitalize">
                            {{ $website['theme'] ?? '-' }}
                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-slate-500">Plan</p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ $website['plan_id'] ?? '-' }}
                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-slate-500">Industry</p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ $website['industry'] ?? '-' }}
                        </p>

                    </div>

                    <div>

                        <p class="text-sm text-slate-500">Subdomain</p>

                        <p class="mt-1 text-lg font-semibold text-blue-600">
                            https://{{ $website['subdomain'] ?? 'mybusiness' }}.esubiz.com
                        </p>

                    </div>

                    <div class="md:col-span-2">

                        <p class="text-sm text-slate-500">Description</p>

                        <p class="mt-2 leading-7 text-slate-700">
                            {{ $website['description'] ?? '-' }}
                        </p>

                    </div>

                </div>

            </div>

            <div class="mt-8 rounded-3xl border border-blue-200 bg-blue-50 p-6">

                <h3 class="text-lg font-bold text-blue-900">

                    What happens next?

                </h3>

                <ul class="mt-4 space-y-3 text-blue-800">

                    <li>✓ Your website will be provisioned automatically.</li>
                    <li>✓ Your workspace will be configured.</li>
                    <li>✓ Your selected plan will be activated.</li>
                    <li>✓ Your Esubiz dashboard will be ready.</li>

                </ul>

            </div>

            <div class="mt-10 flex justify-between">

                <a
                    href="{{ route('websites.address', request()->query()) }}"
                    class="rounded-2xl border border-slate-300 px-8 py-4 font-semibold hover:bg-slate-100">

                    ← Back

                </a>

                <button
                    type="submit"
                    class="rounded-2xl bg-blue-600 px-10 py-4 font-semibold text-white hover:bg-blue-700">

                    Create Website →

                </button>

            </div>

        </div>

    </form>

</x-wizard.layout>
