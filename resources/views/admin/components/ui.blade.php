{{-- Esubiz Admin UI Foundation --}}
@props(['title' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @if($title)
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">
                    <span>Esubiz Core</span>
                    <span class="text-slate-300">/</span>
                    <span>Admin</span>
                </div>

                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">
                    {{ $title }}
                </h1>

                @if($description)
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        {{ $description }}
                    </p>
                @endif
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-3">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</div>
