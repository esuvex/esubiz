@props(['padding' => true])

<div {{ $attributes->merge([
    'class' => 'rounded-2xl border border-slate-200/80 bg-white shadow-[0_8px_30px_rgba(15,23,42,0.04)]'
        . ($padding ? ' p-6' : '')
]) }}>
    {{ $slot }}
</div>
