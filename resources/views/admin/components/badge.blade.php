@props(['type' => 'neutral'])

@php
$classes = match($type) {
    'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
    'danger' => 'bg-rose-50 text-rose-700 ring-rose-600/10',
    'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
    'info' => 'bg-blue-50 text-blue-700 ring-blue-600/10',
    default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
};
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$classes}"
]) }}>
    {{ $slot }}
</span>
