<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditPackageController
{
    private array $types = [
        'ai_credits',
        'sms_credits',
        'email_credits',
        'whatsapp_credits',
    ];

    public function index()
    {
        $packages = DB::table('credit_packages')
            ->orderBy('credit_type')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $catalogProducts = DB::table('catalog_products')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'product_type',
                'credit_quantity',
                'is_active',
                'is_public',
            ]);

        return view('admin.credit-packages.index', [
            'packages' => $packages,
            'types' => $this->types,
            'catalogProducts' => $catalogProducts,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'catalog_product_id' => ['required', 'integer', 'exists:catalog_products,id'],
            'credit_type' => ['required', 'in:ai_credits,sms_credits,email_credits,whatsapp_credits'],
            'name' => ['required', 'string', 'max:255'],
            'credit_quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'expiry_days' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($data) {
            DB::table('credit_packages')->updateOrInsert(
                [
                    'catalog_product_id' => $data['catalog_product_id'],
                    'credit_type' => $data['credit_type'],
                ],
                [
                    'name' => $data['name'],
                    'credit_quantity' => $data['credit_quantity'],
                    'price' => $data['price'],
                    'currency' => strtoupper($data['currency']),
                    'is_active' => $data['is_active'],
                    'sort_order' => $data['sort_order'] ?? 0,
                    'expiry_days' => $data['expiry_days'] ?? null,
                    'description' => $data['description'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('catalog_products')
                ->where('id', $data['catalog_product_id'])
                ->update([
                    'credit_quantity' => $data['credit_quantity'],
                ]);
        });

        return back()->with('success', 'Credit package saved successfully.');
    }

    public function createProduct(Request $request)
    {
        $data = $request->validate([
            'credit_type' => ['required', 'in:ai_credits,sms_credits,email_credits,whatsapp_credits'],
            'name' => ['required', 'string', 'max:255'],
            'credit_quantity' => ['required', 'integer', 'min:1'],
        ]);

        $productId = DB::table('catalog_products')->insertGetId([
            'name' => $data['name'],
            'product_type' => $data['credit_type'],
            'credit_quantity' => $data['credit_quantity'],
            'is_active' => true,
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(
            'success',
            "Credit product created successfully. Catalogue Product #{$productId}."
        );
    }

    public function toggle(int $id)
    {
        $package = DB::table('credit_packages')->where('id', $id)->first();

        abort_unless($package, 404);

        DB::table('credit_packages')
            ->where('id', $id)
            ->update([
                'is_active' => !$package->is_active,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Credit package status updated.');
    }

    public function destroy(int $id)
    {
        DB::table('credit_packages')->where('id', $id)->delete();

        return back()->with('success', 'Credit package removed.');
    }
}
