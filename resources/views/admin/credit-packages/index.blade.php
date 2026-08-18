@extends('admin.layouts.app')

@section('content')

<div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-bold">Configured Packages</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left">Package</th>
                            <th class="px-5 py-4 text-left">Type</th>
                            <th class="px-5 py-4 text-right">Credits</th>
                            <th class="px-5 py-4 text-right">Price</th>
                            <th class="px-5 py-4 text-center">Status</th>
                            <th class="px-5 py-4 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($packages as $package)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="font-semibold">{{ $package->name }}</div>
                                    <div class="text-xs text-slate-500">
                                        Product #{{ $package->catalog_product_id }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    {{ ucwords(str_replace('_', ' ', $package->credit_type)) }}
                                </td>

                                <td class="px-5 py-4 text-right font-semibold">
                                    {{ number_format($package->credit_quantity) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    {{ $package->currency }}
                                    {{ number_format($package->price, 2) }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <form method="POST"
                                        action="{{ route('admin.credit-packages.toggle', $package->id) }}">
                                        @csrf
                                        <button class="text-blue-600 font-semibold">
                                            {{ $package->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                    No credit packages configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@endsection
