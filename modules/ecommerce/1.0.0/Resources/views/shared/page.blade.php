@extends('tenant.admin.layouts.app')

@section('title', $title ?? 'Ecommerce')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    <div>
        <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
            Ecommerce Module
        </div>

        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
            {{ $title ?? 'Ecommerce' }}
        </h1>

        @if(!empty($description))
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                {{ $description }}
            </p>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="text-sm font-black text-slate-900">
            {{ $title ?? 'Ecommerce' }}
        </div>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            This Ecommerce workspace is ready for its production interface.
        </p>
    </div>

</div>
@endsection
