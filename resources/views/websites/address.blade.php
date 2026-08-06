<x-wizard.layout
    title="Website Address"
    :step="5"
    :steps="7"
    heading="Choose your website address"
    description="Select a free Esubiz subdomain or connect your own custom domain later.">

    <form method="GET" action="{{ route('websites.review') }}">

        @foreach($website as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        <div class="max-w-5xl mx-auto space-y-8">

            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Esubiz Subdomain
                </label>

                <div class="flex rounded-2xl border border-slate-300 overflow-hidden">

                    <input
                        type="text"
                        name="subdomain"
                        required
                        class="flex-1 px-5 py-4 border-0 focus:ring-0"
                        placeholder="mybusiness">

                    <div class="bg-slate-100 px-6 flex items-center font-semibold text-slate-700">
                        .esubiz.com
                    </div>

                </div>

                <p class="mt-2 text-sm text-slate-500">
                    Your website will be available instantly using this address.
                </p>

            </div>

            <div>

                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Custom Domain (Optional)
                </label>

                <input
                    type="text"
                    name="domain"
                    class="w-full rounded-2xl border border-slate-300 px-5 py-4"
                    placeholder="www.yourcompany.com">

                <p class="mt-2 text-sm text-slate-500">
                    You can connect your own domain after deployment.
                </p>

            </div>

            <div class="rounded-2xl border bg-blue-50 p-6">

                <h3 class="text-lg font-bold text-slate-900">
                    Your Website URL
                </h3>

                <p class="mt-3 text-xl font-semibold text-blue-700">
                    https://<span id="preview">mybusiness</span>.esubiz.com
                </p>

            </div>

            <div class="flex justify-between">

                <a
                    href="{{ route('websites.plan', request()->query()) }}"
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

    <script>

        document.querySelector('input[name="subdomain"]').addEventListener('keyup', function(){

            document.getElementById('preview').innerText =
                this.value || 'mybusiness';

        });

    </script>

</x-wizard.layout>
