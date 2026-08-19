<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoreAddonController extends Controller
{
    public function index()
    {
        return view('admin.core-addons.index', [
            'addons' => DB::table('core_addons')
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),

            'bundles' => DB::table('core_addon_bundles')
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),

            'capabilities' => app(
                \App\Services\Core\CoreCapabilityRegistry::class
            )->all(),
        ]);
    }

    public function storeAddon(Request $request)
    {
        $data = $this->validateAddon($request);

        $data['uuid'] = (string) Str::uuid();
        $data['saas_available'] = $request->boolean('saas_available');
        $data['off_server_available'] = $request->boolean('off_server_available');
        $data['is_unlimited'] = $request->boolean('is_unlimited');
        $data['is_active'] = true;
        $data['capabilities'] = $this->jsonArray($request->input('capabilities'));
        $data['metadata'] = json_encode([
            'deployment_types' => array_values(array_filter([
                $request->boolean('saas_available') ? 'saas' : null,
                $request->boolean('off_server_available') ? 'off_server' : null,
            ])),
            'commercial_models' => array_values(array_filter([
                $request->boolean('saas_available') ? 'rental_or_subscription' : null,
                $request->boolean('off_server_available') ? 'license' : null,
            ])),
        ]);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('core_addons')->insert($data);

        return back()->with('success', 'Core add-on created successfully.');
    }

    public function updateAddon(Request $request, int $addon)
    {
        $record = DB::table('core_addons')
            ->where('id', $addon)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($record, 404);

        $data = $this->validateAddon($request, $addon);

        $data['saas_available'] = $request->boolean('saas_available');
        $data['off_server_available'] = $request->boolean('off_server_available');
        $data['is_unlimited'] = $request->boolean('is_unlimited');
        $data['is_active'] = $request->boolean('is_active');
        $data['capabilities'] = $this->jsonArray($request->input('capabilities'));
        $data['metadata'] = json_encode([
            'deployment_types' => array_values(array_filter([
                $request->boolean('saas_available') ? 'saas' : null,
                $request->boolean('off_server_available') ? 'off_server' : null,
            ])),
            'commercial_models' => array_values(array_filter([
                $request->boolean('saas_available') ? 'rental_or_subscription' : null,
                $request->boolean('off_server_available') ? 'license' : null,
            ])),
        ]);
        $data['updated_at'] = now();

        DB::table('core_addons')
            ->where('id', $addon)
            ->update($data);

        return back()->with('success', 'Core add-on updated successfully.');
    }

    public function storeBundle(Request $request)
    {
        $data = $this->validateBundle($request);

        $bundleId = DB::table('core_addon_bundles')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'key' => $data['key'],
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'saas_available' => $request->boolean('saas_available'),
            'off_server_available' => $request->boolean('off_server_available'),
            'saas_price' => $data['saas_price'] ?? null,
            'saas_currency' => $data['saas_currency'] ?? null,
            'saas_billing_interval' => $data['saas_billing_interval'] ?? null,
            'saas_billing_period' => $data['saas_billing_period'] ?? null,
            'off_server_price' => $data['off_server_price'] ?? null,
            'off_server_currency' => $data['off_server_currency'] ?? null,
            'version' => '1.0.0',
            'is_active' => true,
            'metadata' => json_encode([
                'commercial_models' => array_values(array_filter([
                    $request->boolean('saas_available') ? 'rental_or_subscription' : null,
                    $request->boolean('off_server_available') ? 'license' : null,
                ])),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->syncBundleItems(
            $bundleId,
            $request->input('items', [])
        );

        return back()->with('success', 'Core add-on bundle created successfully.');
    }

    public function updateBundle(Request $request, int $bundle)
    {
        $record = DB::table('core_addon_bundles')
            ->where('id', $bundle)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($record, 404);

        $data = $this->validateBundle($request, $bundle);

        DB::transaction(function () use ($request, $bundle, $data) {

            DB::table('core_addon_bundles')
                ->where('id', $bundle)
                ->update([
                    'key' => $data['key'],
                    'name' => $data['name'],
                    'category' => $data['category'] ?? null,
                    'description' => $data['description'] ?? null,
                    'saas_available' => $request->boolean('saas_available'),
                    'off_server_available' => $request->boolean('off_server_available'),
                    'saas_price' => $data['saas_price'] ?? null,
                    'saas_currency' => $data['saas_currency'] ?? null,
                    'saas_billing_interval' => $data['saas_billing_interval'] ?? null,
                    'saas_billing_period' => $data['saas_billing_period'] ?? null,
                    'off_server_price' => $data['off_server_price'] ?? null,
                    'off_server_currency' => $data['off_server_currency'] ?? null,
                    'version' => DB::raw('version'),
                    'updated_at' => now(),
                ]);

            $this->syncBundleItems(
                $bundle,
                $request->input('items', [])
            );
        });

        return back()->with('success', 'Core add-on bundle updated successfully.');
    }

    public function toggleAddon(int $addon)
    {
        DB::table('core_addons')
            ->where('id', $addon)
            ->whereNull('deleted_at')
            ->update([
                'is_active' => DB::raw('NOT is_active'),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Add-on status updated.');
    }

    public function toggleBundle(int $bundle)
    {
        DB::table('core_addon_bundles')
            ->where('id', $bundle)
            ->whereNull('deleted_at')
            ->update([
                'is_active' => DB::raw('NOT is_active'),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Bundle status updated.');
    }

    protected function validateAddon(
        Request $request,
        ?int $addon = null
    ): array {
        return $request->validate([
            'key' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_\\-]+$/',
                'unique:core_addons,key,' . ($addon ?? 'NULL') . ',id',
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'parent_capability' => ['nullable', 'string', 'max:150'],
            'entitlement_type' => ['required', 'string', 'max:50'],
            'default_allocation' => ['nullable', 'numeric', 'min:0'],
            'allocation_unit' => ['nullable', 'string', 'max:50'],

            'saas_price' => ['nullable', 'numeric', 'min:0'],
            'saas_currency' => ['nullable', 'string', 'size:3'],
            'saas_billing_interval' => ['nullable', 'string', 'max:30'],
            'saas_billing_period' => ['nullable', 'integer', 'min:1'],

            'off_server_price' => ['nullable', 'numeric', 'min:0'],
            'off_server_currency' => ['nullable', 'string', 'size:3'],

            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['string', 'max:150'],
        ]);
    }

    protected function validateBundle(
        Request $request,
        ?int $bundle = null
    ): array {
        return $request->validate([
            'key' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_\\-]+$/',
                'unique:core_addon_bundles,key,' . ($bundle ?? 'NULL') . ',id',
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],

            'saas_price' => ['nullable', 'numeric', 'min:0'],
            'saas_currency' => ['nullable', 'string', 'size:3'],
            'saas_billing_interval' => ['nullable', 'string', 'max:30'],
            'saas_billing_period' => ['nullable', 'integer', 'min:1'],

            'off_server_price' => ['nullable', 'numeric', 'min:0'],
            'off_server_currency' => ['nullable', 'string', 'size:3'],

            'items' => ['nullable', 'array'],
            'items.*.addon_id' => ['required', 'integer', 'exists:core_addons,id'],
            'items.*.allocation' => ['nullable', 'numeric', 'min:0'],
            'items.*.is_unlimited' => ['nullable', 'boolean'],
        ]);
    }

    protected function syncBundleItems(
        int $bundleId,
        array $items
    ): void {
        DB::table('core_addon_bundle_items')
            ->where('bundle_id', $bundleId)
            ->delete();

        $rows = [];

        foreach ($items as $item) {
            if (empty($item['addon_id'])) {
                continue;
            }

            $rows[] = [
                'bundle_id' => $bundleId,
                'addon_id' => (int) $item['addon_id'],
                'allocation' => $item['allocation'] ?? null,
                'is_unlimited' => !empty($item['is_unlimited']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows) {
            DB::table('core_addon_bundle_items')->insert($rows);
        }
    }

    protected function jsonArray($value): ?string
    {
        if (!is_array($value)) {
            return null;
        }

        return json_encode(
            array_values(
                array_unique(
                    array_filter($value)
                )
            )
        );
    }
}
