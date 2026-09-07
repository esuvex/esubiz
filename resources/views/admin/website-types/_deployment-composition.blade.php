{{-- ESUBIZ_WEBSITE_TYPE_DEPLOYMENT_COMPOSITION_UI_V1 --}}

@php
    $deploymentLabels = [
        'saas' => [
            'title' => 'SaaS Package',
            'description' => 'Components automatically installed when this Website Type is created on Esubiz.',
        ],
        'off_server' => [
            'title' => 'Off-server Package',
            'description' => 'Components automatically packaged with this Website Type for external hosting.',
        ],
    ];

    $componentGroups = [
        'themes' => [
            'title' => 'Themes',
            'description' => 'Select the themes available for this Website Type.',
        ],
        'modules' => [
            'title' => 'Modules',
            'description' => 'Select the modules automatically included with this Website Type.',
        ],
        'addons' => [
            'title' => 'Add-ons',
            'description' => 'Select the add-ons automatically included with this Website Type.',
        ],
        'bundles' => [
            'title' => 'Add-on Bundles',
            'description' => 'Select the bundles automatically included with this Website Type.',
        ],
    ];
@endphp

<div>

    <div>
        <h2 class="text-xl font-bold text-slate-900">
            Website Type Composition
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            Configure what Esubiz automatically installs or packages for this Website Type.
            Available Themes, Modules, Add-ons and Bundles are fetched from the saved Esubiz catalog.
        </p>
    </div>

    <div class="mt-6 space-y-6">

        @foreach($deploymentLabels as $deployment => $deploymentMeta)

            @php
                $profile = $deploymentProfiles[$deployment] ?? [];
                $profileEnabled = old(
                    "deployment.{$deployment}.enabled",
                    $profile['is_enabled'] ?? true
                );

                $selectedThemes = collect(
                    old(
                        "deployment.{$deployment}.themes",
                        collect($profile['themes'] ?? [])->pluck('id')->all()
                    )
                )->map(fn ($id) => (int) $id)->all();

                $selectedModules = collect(
                    old(
                        "deployment.{$deployment}.modules",
                        collect($profile['modules'] ?? [])->pluck('id')->all()
                    )
                )->map(fn ($id) => (int) $id)->all();

                $selectedAddons = collect(
                    old(
                        "deployment.{$deployment}.addons",
                        collect($profile['addons'] ?? [])->pluck('id')->all()
                    )
                )->map(fn ($id) => (int) $id)->all();

                $selectedBundles = collect(
                    old(
                        "deployment.{$deployment}.bundles",
                        collect($profile['bundles'] ?? [])->pluck('id')->all()
                    )
                )->map(fn ($id) => (int) $id)->all();

                $selectedByGroup = [
                    'themes' => $selectedThemes,
                    'modules' => $selectedModules,
                    'addons' => $selectedAddons,
                    'bundles' => $selectedBundles,
                ];

                $profileDefaultTheme = data_get(
                    $profile,
                    'default_theme.id'
                );

                $defaultTheme = old(
                    "deployment.{$deployment}.default_theme",
                    $profileDefaultTheme
                );
            @endphp

            <section class="w-full min-w-0 max-w-full overflow-hidden rounded-3xl bg-white p-5 shadow-sm sm:p-8">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            {{ $deploymentMeta['title'] }}
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $deploymentMeta['description'] }}
                        </p>
                    </div>

                    <label class="flex shrink-0 cursor-pointer items-center gap-3">
                        <input
                            type="hidden"
                            name="deployment[{{ $deployment }}][enabled]"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="deployment[{{ $deployment }}][enabled]"
                            value="1"
                            @checked($profileEnabled)
                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >

                        <span class="text-sm font-semibold text-slate-700">
                            Enabled
                        </span>
                    </label>

                </div>

                <div class="mt-6 grid w-full min-w-0 grid-cols-1 gap-6 md:grid-cols-2">

                    @foreach($componentGroups as $group => $groupMeta)

                        @php
                            $products = $deploymentCatalog[$group] ?? collect();
                            $selected = $selectedByGroup[$group] ?? [];
                        @endphp

                        <div class="w-full min-w-0 max-w-full overflow-hidden rounded-2xl bg-slate-50 p-4 sm:p-5">

                            <div>
                                <h4 class="font-bold text-slate-900">
                                    {{ $groupMeta['title'] }}
                                </h4>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    {{ $groupMeta['description'] }}
                                </p>
                            </div>

                            <div class="mt-4 max-h-64 space-y-2 overflow-y-auto pr-1">

                                @forelse($products as $product)

                                    <label class="flex w-full min-w-0 max-w-full cursor-pointer items-start gap-3 overflow-hidden rounded-xl border border-slate-200 bg-white px-4 py-3 hover:border-blue-300">

                                        <input
                                            type="checkbox"
                                            name="deployment[{{ $deployment }}][{{ $group }}][]"
                                            value="{{ $product->id }}"
                                            @checked(in_array((int) $product->id, $selected, true))
                                            class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        >

                                        <span class="min-w-0 flex-1 overflow-hidden">
                                            <span class="block break-words text-sm font-semibold text-slate-800">
                                                {{ $product->name }}
                                            </span>

                                            <span class="block truncate text-xs text-slate-400">
                                                {{ $product->slug }}
                                            </span>
                                        </span>

                                    </label>

                                @empty

                                    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm text-slate-500">
                                        No saved {{ strtolower($groupMeta['title']) }} found.
                                    </div>

                                @endforelse

                            </div>

                            @if($group === 'themes')

                                <div class="mt-5 border-t border-slate-200 pt-5">

                                    <label class="block text-sm font-semibold text-slate-700">
                                        Default Theme
                                    </label>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Used automatically when no other assigned theme is selected in the wizard.
                                    </p>

                                    <select
                                        name="deployment[{{ $deployment }}][default_theme]"
                                        class="mt-3 block w-full min-w-0 max-w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >
                                        <option value="">Select default theme</option>

                                        @foreach($products as $product)
                                            <option
                                                value="{{ $product->id }}"
                                                @selected((int) $defaultTheme === (int) $product->id)
                                            >
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>

                            @endif

                        </div>

                    @endforeach

                </div>

            </section>

        @endforeach

    </div>

</div>
