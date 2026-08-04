<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Dashboard
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Welcome back, {{ auth()->user()->name }}.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-6">

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                <a href="#" class="bg-white rounded-2xl shadow-sm border p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-slate-800">
                        🌐 Website Builder
                    </h3>

                    <p class="mt-2 text-slate-500">
                        Build and manage business websites.
                    </p>
                </a>

                <a href="#" class="bg-white rounded-2xl shadow-sm border p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-slate-800">
                        👥 CRM
                    </h3>

                    <p class="mt-2 text-slate-500">
                        Manage clients, projects and invoices.
                    </p>
                </a>

                <a href="#" class="bg-white rounded-2xl shadow-sm border p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-slate-800">
                        🛒 Commerce
                    </h3>

                    <p class="mt-2 text-slate-500">
                        Ecommerce, POS and inventory.
                    </p>
                </a>

                <a href="#" class="bg-white rounded-2xl shadow-sm border p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-slate-800">
                        ⭐ Showcase
                    </h3>

                    <p class="mt-2 text-slate-500">
                        Discover businesses built with Esubiz.
                    </p>
                </a>

                <a href="#" class="bg-white rounded-2xl shadow-sm border p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-slate-800">
                        🤖 AI
                    </h3>

                    <p class="mt-2 text-slate-500">
                        Generate content and automate tasks.
                    </p>
                </a>

                <a href="#" class="bg-white rounded-2xl shadow-sm border p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold text-slate-800">
                        💻 Developer
                    </h3>

                    <p class="mt-2 text-slate-500">
                        APIs, webhooks and integrations.
                    </p>
                </a>

            </div>

        </div>
    </div>
</x-app-layout>
