<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfflinePaymentController extends Controller
{
    public function index()
    {
        $methods = DB::table('offline_payment_methods')
            ->whereNull('deleted_at')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        return view(
            'admin.offline-payment.index',
            compact('methods')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => [
                'required',
                'in:bank_transfer,cash,manual,other',
            ],
            'instructions' => ['nullable', 'string'],
            'priority' => ['nullable', 'integer', 'min:1'],
        ]);

        DB::table('offline_payment_methods')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(6)),
            'type' => $data['type'],
            'instructions' => $data['instructions'] ?? null,
            'settings' => json_encode([]),
            'is_active' => $request->boolean('is_active'),
            'priority' => $data['priority'] ?? 1,
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.payment-gateways.offline.index')
            ->with('success', 'Offline payment method added successfully.');
    }

    public function update(Request $request, int $method)
    {
        $paymentMethod = DB::table('offline_payment_methods')
            ->where('id', $method)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($paymentMethod, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => [
                'required',
                'in:bank_transfer,cash,manual,other',
            ],
            'instructions' => ['nullable', 'string'],
            'priority' => ['nullable', 'integer', 'min:1'],
        ]);

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use (
            $method,
            $data,
            $request,
            $isDefault
        ) {
            if ($isDefault) {
                DB::table('offline_payment_methods')
                    ->whereNull('deleted_at')
                    ->update([
                        'is_default' => false,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('offline_payment_methods')
                ->where('id', $method)
                ->update([
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'instructions' => $data['instructions'] ?? null,
                    'is_active' => $request->boolean('is_active'),
                    'priority' => $data['priority'] ?? 1,
                    'is_default' => $isDefault,
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('admin.payment-gateways.offline.index')
            ->with('success', 'Offline payment method updated successfully.');
    }

    public function destroy(int $method)
    {
        $paymentMethod = DB::table('offline_payment_methods')
            ->where('id', $method)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($paymentMethod, 404);

        DB::table('offline_payment_methods')
            ->where('id', $method)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.payment-gateways.offline.index')
            ->with('success', 'Offline payment method removed successfully.');
    }
}
