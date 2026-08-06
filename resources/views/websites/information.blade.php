<x-wizard.layout
    title="Website Information"
    :step="3"
    :steps="7"
    heading="Tell us about your website"
    description="We'll use this information to personalize your website.">

    <form method="GET" action="{{ route('websites.plan') }}">

        <input type="hidden" name="type" value="{{ $website['type'] }}">
        <input type="hidden" name="theme" value="{{ $website['theme'] }}">

        <div class="max-w-5xl mx-auto space-y-8">

            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Website Name
                </label>

                <input
                    type="text"
                    name="name"
                    required
                    class="w-full rounded-2xl border border-slate-300 px-5 py-4 focus:border-blue-600 focus:ring-blue-600"
                    placeholder="Esuvex Interiors">
            </div>

            <div class="grid md:grid-cols-2 gap-6">

                <div>

                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Industry
                    </label>

                    <input
                        type="text"
                        name="industry"
                        class="w-full rounded-2xl border border-slate-300 px-5 py-4"
                        placeholder="Interior Design">

                </div>

                <div>

                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Business Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="w-full rounded-2xl border border-slate-300 px-5 py-4"
                        placeholder="info@company.com">

                </div>

            </div>

            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Business Phone
                </label>

                <input
                    type="text"
                    name="phone"
                    class="w-full rounded-2xl border border-slate-300 px-5 py-4"
                    placeholder="+234 xxx xxx xxxx">

            </div>

            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Short Description
                </label>

                <textarea
                    name="description"
                    rows="5"
                    class="w-full rounded-2xl border border-slate-300 px-5 py-4"
                    placeholder="Describe your business..."></textarea>

            </div>

            <div class="flex justify-between pt-6">

                <a
                    href="{{ route('websites.theme', request()->query()) }}"
                    class="rounded-2xl border border-slate-300 px-8 py-4 font-semibold hover:bg-slate-100">

                    ← Back

                </a>

                <button
                    type="submit"
                    class="rounded-2xl bg-blue-600 px-10 py-4 font-semibold text-white hover:bg-blue-700">

                    Continue →

                </button>

            </div>

        </div>

    </form>

</x-wizard.layout>
