@extends('admin.layouts.app')

@section('title', 'Offline Payment Gateway')


@section('content')

<div class="mb-6">
    <a href="{{ route('admin.payment-gateways.index') }}"
       class="inline-flex items-center gap-2 text-sm font-black text-blue-600 hover:text-blue-700">
        <span>←</span>
        Back to Gateways
    </a>
</div>


<div class="space-y-8">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">
                Offline Payment Gateway
            </h1>

            <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
                Configure payment methods that require customers to complete
                payment outside the online checkout process.
            </p>
        </div>

        <button
            type="button"
            onclick="document.getElementById('add-offline-method').classList.toggle('hidden')"
            class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg active:scale-[0.98]"
        >
            Add Offline Method
        </button>
    </div>

    <div
        id="add-offline-method"
        class="hidden overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
    >
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-black text-slate-900">
                Add Offline Payment Method
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Create another offline payment option for the central checkout.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.payment-gateways.offline.store') }}">
            @csrf

            <div class="grid gap-6 px-6 py-6 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Method Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
                        placeholder="e.g. Bank Deposit"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Method Type
                    </label>

                    <select
                        name="type"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                    >
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="manual">Manual Payment</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Priority
                    </label>

                    <input
                        type="number"
                        name="priority"
                        min="1"
                        value="1"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                    >
                </div>

                <div class="flex items-center">
                    <label class="inline-flex cursor-pointer items-center gap-3">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >

                        <span class="text-sm font-bold text-slate-700">
                            Enable method
                        </span>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Payment Instructions
                    </label>

                    <textarea
                        name="instructions"
                        rows="5"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
                        placeholder="Enter the instructions customers should follow after selecting this payment method."
                    ></textarea>
                </div>

                <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-5">
                        <h3 class="text-sm font-black text-slate-900">
                            Receipt Upload
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Allow customers to upload proof of payment after completing this offline payment.
                        </p>
                    </div>

                    <label class="mb-5 inline-flex cursor-pointer items-center gap-3">
                        <input
                            type="hidden"
                            name="receipt_upload_enabled"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="receipt_upload_enabled"
                            value="1"
                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >

                        <span class="text-sm font-bold text-slate-700">
                            Require receipt upload
                        </span>
                    </label>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                Upload Label
                            </label>

                            <input
                                type="text"
                                name="receipt_upload_label"
                                value="Upload payment receipt"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500"
                                placeholder="Upload payment receipt"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                Customer Help Text
                            </label>

                            <input
                                type="text"
                                name="receipt_upload_help"
                                value="Upload your payment receipt or proof of payment."
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500"
                                placeholder="Upload your payment receipt or proof of payment."
                            >
                        </div>

                    </div>
                </div>

            </div>

            <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg active:scale-[0.98]"
                >
                    Add Payment Method
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        @forelse($methods as $method)

            <form
                method="POST"
                action="{{ route('admin.payment-gateways.offline.update', $method->id) }}"
                x-data="{ enabled: {{ $method->is_active ? 'true' : 'false' }} }"
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-xl"
            >
                @csrf

                <input
                    type="hidden"
                    name="is_active"
                    :value="enabled ? '1' : '0'"
                >

                <div class="border-b border-slate-100 px-6 py-6">

                    <div class="flex items-start justify-between gap-4">

                        <div>
                            <h2 class="text-xl font-black text-slate-900">
                                {{ $method->name }}
                            </h2>

                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ str_replace('_', ' ', $method->type) }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3">

                            <span
                                class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider"
                                :class="enabled
                                    ? 'bg-blue-50 text-blue-700'
                                    : 'bg-slate-100 text-slate-500'"
                                x-text="enabled ? 'Enabled' : 'Disabled'"
                            ></span>

                            <button
                                type="button"
                                @click="enabled = !enabled"
                                :aria-pressed="enabled.toString()"
                                class="relative flex h-7 w-12 shrink-0 items-center rounded-full border-2 p-0.5 transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                :class="enabled
                                    ? 'bg-blue-600 border-blue-600'
                                    : 'bg-gray-500 border-gray-600'"
                                aria-label="Toggle payment method"
                            >
                                <span
                                    class="block h-6 w-6 rounded-full bg-white shadow-md transition-transform duration-200 ease-in-out"
                                    :style="enabled
                                        ? 'transform: translateX(20px)'
                                        : 'transform: translateX(0px)'"
                                ></span>
                            </button>

                        </div>

                    </div>

                </div>

                <div class="grid gap-6 px-6 py-6 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                            Method Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ $method->name }}"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                            Type
                        </label>

                        <select
                            name="type"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                        >
                            @foreach([
                                'bank_transfer' => 'Bank Transfer',
                                'cash' => 'Cash',
                                'manual' => 'Manual Payment',
                                'other' => 'Other',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    {{ $method->type === $value ? 'selected' : '' }}
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                            Priority
                        </label>

                        <input
                            type="number"
                            name="priority"
                            min="1"
                            value="{{ $method->priority }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                        >
                    </div>

                    <div class="flex items-center">
                        <label class="inline-flex cursor-pointer items-center gap-3">
                            <input
                                type="hidden"
                                name="is_default"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="is_default"
                                value="1"
                                {{ $method->is_default ? 'checked' : '' }}
                                class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <span class="text-sm font-bold text-slate-700">
                                Default method
                            </span>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                            Payment Instructions
                        </label>

                        <textarea
                            name="instructions"
                            rows="5"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
                            placeholder="Enter customer payment instructions..."
                        >{{ $method->instructions }}</textarea>
                    </div>

                    <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="mb-5">
                            <h3 class="text-sm font-black text-slate-900">
                                Receipt Upload
                            </h3>

                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Allow customers to upload proof of payment after completing this offline payment.
                            </p>
                        </div>

                        <label class="mb-5 inline-flex cursor-pointer items-center gap-3">
                            <input
                                type="hidden"
                                name="receipt_upload_enabled"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="receipt_upload_enabled"
                                value="1"
                                {{ $method->receipt_upload_enabled ? 'checked' : '' }}
                                class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <span class="text-sm font-bold text-slate-700">
                                Require receipt upload
                            </span>
                        </label>

                        <div class="grid gap-5 md:grid-cols-2">

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                    Upload Label
                                </label>

                                <input
                                    type="text"
                                    name="receipt_upload_label"
                                    value="{{ $method->receipt_upload_label ?? 'Upload payment receipt' }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500"
                                    placeholder="Upload payment receipt"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                    Customer Help Text
                                </label>

                                <input
                                    type="text"
                                    name="receipt_upload_help"
                                    value="{{ $method->receipt_upload_help ?? 'Upload your payment receipt or proof of payment.' }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500"
                                    placeholder="Upload your payment receipt or proof of payment."
                                >
                            </div>

                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-4">

                    <button
                        type="button"
                        onclick="if(confirm('Remove this offline payment method?')) document.getElementById('delete-offline-{{ $method->id }}').submit()"
                        class="rounded-xl px-4 py-3 text-sm font-black text-red-600 transition hover:bg-red-50"
                    >
                        Remove
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg active:scale-[0.98]"
                    >
                        Save Method
                    </button>

                </div>

            </form>

            <form
                id="delete-offline-{{ $method->id }}"
                method="POST"
                action="{{ route('admin.payment-gateways.offline.destroy', $method->id) }}"
                class="hidden"
            >
                @csrf
                @method('DELETE')
            </form>

        @empty

            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center lg:col-span-2">
                <div class="text-lg font-black text-slate-900">
                    No offline payment methods configured
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    Add your first offline payment method to make it available for configuration.
                </p>
            </div>

        @endforelse

    </div>

</div>

@endsection
