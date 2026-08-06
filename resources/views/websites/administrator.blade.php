<x-wizard.layout
    title="Website Administrator"
    :step="6"
    :steps="8"
    heading="Website Administrator"
    description="Create the administrator account that will be used to log into this website.">

    <form method="GET" action="{{ route('websites.review') }}">

        @foreach($website as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        <div class="max-w-5xl mx-auto space-y-8">

            <div class="rounded-3xl border border-blue-200 bg-blue-50 p-6">

                <label class="flex items-start gap-4 cursor-pointer">

                    <input
                        type="checkbox"
                        id="use_esubiz"
                        name="use_esubiz"
                        value="1"
                        class="mt-1 h-5 w-5 rounded">

                    <div>

                        <h3 class="text-lg font-bold text-slate-900">

                            Use my Esubiz login details

                        </h3>

                        <p class="mt-2 text-slate-600">

                            The current Esubiz account will become the website administrator.

                        </p>

                    </div>

                </label>

            </div>

            <div id="adminFields" class="space-y-6">

                <div>

                    <label class="block mb-2 text-sm font-semibold text-slate-700">

                        Administrator Name

                    </label>

                    <input
                        type="text"
                        name="admin_name"
                        class="w-full rounded-2xl border border-slate-300 px-5 py-4"
                        placeholder="John Doe">

                </div>

                <div>

                    <label class="block mb-2 text-sm font-semibold text-slate-700">

                        Administrator Email / Username

                    </label>

                    <input
                        type="text"
                        name="admin_email"
                        class="w-full rounded-2xl border border-slate-300 px-5 py-4"
                        placeholder="admin@company.com">

                </div>

                <div class="grid md:grid-cols-2 gap-6">

                    <div>

                        <label class="block mb-2 text-sm font-semibold text-slate-700">

                            Administrator Password

                        </label>

                        <input
                            type="password"
                            name="admin_password"
                            class="w-full rounded-2xl border border-slate-300 px-5 py-4">

                    </div>

                    <div>

                        <label class="block mb-2 text-sm font-semibold text-slate-700">

                            Confirm Password

                        </label>

                        <input
                            type="password"
                            name="admin_password_confirmation"
                            class="w-full rounded-2xl border border-slate-300 px-5 py-4">

                    </div>

                </div>

            </div>

            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6">

                <h3 class="text-lg font-bold text-amber-900">

                    Important

                </h3>

                <ul class="mt-4 space-y-2 text-amber-800">

                    <li>• These credentials will be used at <strong>/login</strong> on this website.</li>

                    <li>• The website administrator can change these credentials at any time.</li>

                    <li>• The Esubiz account owner will always retain management access from the Esubiz portal.</li>

                </ul>

            </div>

            <div class="flex justify-between pt-4">

                <a
                    href="{{ route('websites.address', request()->query()) }}"
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

        const checkbox = document.getElementById('use_esubiz');
        const fields = document.getElementById('adminFields');

        checkbox.addEventListener('change', function () {

            fields.style.display = this.checked ? 'none' : 'block';

        });

    </script>

</x-wizard.layout>
