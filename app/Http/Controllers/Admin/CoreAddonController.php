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
                $bundles = DB::table('core_addon_bundles')
            ->orderByDesc('id')
            ->get();

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

            'coreFeatures' => DB::table('core_features')
                ->where('is_active', true)
                ->where('is_core', true)
                ->orderBy('name')
                ->get(),

            'featureLimits' => (function () {
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing(
                    'core_feature_limits'
                );

                $limits = DB::table('core_feature_limits')
                    ->where('is_active', true)
                    ->get();

                /*
                 * Normalize the registry relationship into feature_key
                 * without assuming a specific physical column name.
                 */
                $featureColumn = collect([
                    'feature_key',
                    'feature',
                    'core_feature_key',
                    'feature_id',
                    'core_feature_id',
                ])->first(
                    fn ($column) => in_array($column, $columns, true)
                );

                if ($featureColumn === null) {
                    return $limits;
                }

                if (in_array($featureColumn, ['feature_id', 'core_feature_id'], true)) {
                    $featureIds = $limits
                        ->pluck($featureColumn)
                        ->filter()
                        ->unique()
                        ->values();

                    $features = DB::table('core_features')
                        ->whereIn('id', $featureIds)
                        ->get()
                        ->keyBy('id');

                    return $limits->map(function ($limit) use (
                        $featureColumn,
                        $features
                    ) {
                        $feature = $features->get($limit->{$featureColumn});

                        $limit->feature_key = $feature->key ?? null;
                        $limit->feature_name = $feature->name ?? null;

                        return $limit;
                    })->sortBy([
                        ['feature_key', 'asc'],
                        ['key', 'asc'],
                    ])->values();
                }

                return $limits->map(function ($limit) use ($featureColumn) {
                    $limit->feature_key = $limit->{$featureColumn};
                    return $limit;
                })->sortBy([
                    ['feature_key', 'asc'],
                    ['key', 'asc'],
                ])->values();
            })(),
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

        $addonId = DB::table('core_addons')->insertGetId($data);

        $this->syncCapabilityAllocations(
            $addonId,
            $request->input('capability_allocations', []),
            $request->input('capability_unlimited', [])
        );

        return back()->with('success', 'Core add-on created successfully.');
    }

    

    public function storeBundle(Request $request)
    {
        $request->merge([
            'key' => Str::slug($request->input('name', ''), '_') . '_bundle',
        ]);

        $data = $this->validateBundle($request);

        DB::transaction(function () use ($request, $data) {
        $bundleId = DB::table('core_addon_bundles')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'key' => $data['key'],
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'unit_name' => $data['unit_name'] ?? null,
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

        $items = array_values(
            array_filter(
                $request->input('items', []),
                fn ($item) => !empty($item['addon_id'])
            )
        );

        $this->syncBundleItems($bundleId, $items);
        });

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
                    'unit_name' => $data['unit_name'] ?? null,
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
            'saas_billing_period' => ['nullable', 'string', 'max:30'],

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
            'unit_name' => ['nullable', 'string', 'max:64'],

            'saas_price' => ['nullable', 'numeric', 'min:0'],
            'saas_currency' => ['nullable', 'string', 'size:3'],
            'saas_billing_interval' => ['nullable', 'string', 'max:30'],
            'saas_billing_period' => ['nullable', 'string', 'max:30'],

            'off_server_price' => ['nullable', 'numeric', 'min:0'],
            'off_server_currency' => ['nullable', 'string', 'size:3'],

            'items' => ['nullable', 'array'],
            'items.*.addon_id' => ['nullable', 'integer', 'exists:core_addons,id'],
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
            if (!is_array($item) || empty($item['addon_id'])) {
                continue;
            }

            $rows[] = [
                'bundle_id' => $bundleId,
                'addon_id' => (int) $item['addon_id'],
                'allocation' => (
                    isset($item['allocation']) &&
                    $item['allocation'] !== ''
                )
                    ? $item['allocation']
                    : null,
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

    
    /**
     * Update a Core add-on and its per-function allocations.
     *
     * Allocation is cumulative:
     * every additional purchase/rental of the add-on contributes
     * the configured allocation again.
     */
    public function updateAddon(Request $request, int $id)
    {
        $addon = DB::table('core_addons')
            ->where('id', $id)
            ->first();

        abort_unless($addon, 404);

        $data = $request->validate([
            'key' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9_\-]+$/',
                'unique:core_addons,key,' . $id . ',id',
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'parent_capability' => ['nullable', 'string', 'max:150'],
            'entitlement_type' => ['nullable', 'string', 'max:50'],
            'default_allocation' => ['nullable', 'numeric', 'min:0'],
            'allocation_unit' => ['nullable', 'string', 'max:50'],
            'saas_price' => ['nullable', 'numeric', 'min:0'],
            'saas_currency' => ['nullable', 'string', 'size:3'],
            'saas_billing_interval' => ['nullable', 'string', 'max:30'],
            'saas_billing_period' => ['nullable', 'string', 'max:30'],
            'off_server_price' => ['nullable', 'numeric', 'min:0'],
            'off_server_currency' => ['nullable', 'string', 'size:3'],
            'saas_available' => ['nullable', 'boolean'],
            'off_server_available' => ['nullable', 'boolean'],
            'is_unlimited' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['string', 'max:150'],
            'capability_allocations' => ['nullable', 'array'],
            'capability_unlimited' => ['nullable', 'array'],
        ]);

        $data['key'] = $data['key'] ?? $addon->key;
        $data['entitlement_type'] = $data['entitlement_type'] ?? $addon->entitlement_type;
        $data['category'] = $data['category'] ?? $addon->category;
        $data['parent_capability'] = $data['parent_capability'] ?? $addon->parent_capability;

        DB::table('core_addons')
            ->where('id', $id)
            ->update([
                'key' => $data['key'],
                'name' => $data['name'],
                'category' => $data['category'] ?? null,
                'description' => $data['description'] ?? null,
                'parent_capability' => $data['parent_capability'] ?? null,
                'entitlement_type' => $data['entitlement_type'],
                'default_allocation' => $data['default_allocation'] ?? null,
                'allocation_unit' => $data['allocation_unit'] ?? null,
                'saas_available' => $request->boolean('saas_available'),
                'off_server_available' => $request->boolean('off_server_available'),
                'is_unlimited' => $request->boolean('is_unlimited'),
                'saas_price' => $data['saas_price'] ?? null,
                'saas_currency' => $data['saas_currency'] ?? null,
                'saas_billing_interval' => $data['saas_billing_interval'] ?? null,
                'saas_billing_period' => $data['saas_billing_period'] ?? null,
                'off_server_price' => $data['off_server_price'] ?? null,
                'off_server_currency' => $data['off_server_currency'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'capabilities' => $this->jsonArray($request->input('capabilities')),
                'metadata' => json_encode([
                    'deployment_types' => array_values(array_filter([
                        $request->boolean('saas_available') ? 'saas' : null,
                        $request->boolean('off_server_available') ? 'off_server' : null,
                    ])),
                    'commercial_models' => array_values(array_filter([
                        $request->boolean('saas_available') ? 'rental_or_subscription' : null,
                        $request->boolean('off_server_available') ? 'license' : null,
                    ])),
                ]),
                'updated_at' => now(),
            ]);

        /*
         * Persist each selected function independently.
         *
         * Example:
         *
         * crm_clients = 100
         * crm_invoices = 50
         *
         * The same add-on may therefore extend several CRM
         * functions at different allocations.
         */
        $allocations = $request->input(
            'capability_allocations',
            []
        );

        $unlimited = $request->input(
            'capability_unlimited',
            []
        );

        foreach ($allocations as $capabilityKey => $amount) {

            DB::table('core_addon_capability_allocations')
                ->updateOrInsert(
                    [
                        'addon_id' => $id,
                        'capability_key' => $capabilityKey,
                    ],
                    [
                        'allocation' => is_numeric($amount)
                            ? (float) $amount
                            : 0,
                        'is_unlimited' => isset(
                            $unlimited[$capabilityKey]
                        ),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
        }

        $this->syncCapabilityAllocations(
            $id,
            $request->input('capability_allocations', []),
            $request->input('capability_unlimited', [])
        );

        return redirect()
            ->route('admin.core-addons.index')
            ->with(
                'success',
                'Core add-on updated successfully.'
            );
    }

public function editAddon(int $id)
    {
        $addon = DB::table('core_addons')
            ->where('id', $id)
            ->first();

        abort_unless($addon, 404);

        $allocations = DB::table('core_addon_capability_allocations')
            ->where('addon_id', $id)
            ->get()
            ->keyBy('capability_key');

        $savedCapabilities = json_decode(
            $addon->capabilities ?? '[]',
            true
        );

        if (!is_array($savedCapabilities)) {
            $savedCapabilities = [];
        }

        /*
         * Existing allocation records are authoritative when editing an
         * add-on. Merge their capability keys with the legacy capabilities
         * JSON so previously saved Function Allocations always remain
         * visible and editable.
         */
        $allocationCapabilityKeys = $allocations
            ->keys()
            ->filter()
            ->values()
            ->all();

        $capabilityKeys = collect($savedCapabilities)
            ->merge($allocationCapabilityKeys)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $capabilities = DB::table('core_feature_limits')
            ->whereIn('limit_key', $capabilityKeys)
            ->get()
            ->keyBy('limit_key')
            ->values();

        return view('admin.core-addons.edit', [
            'addon' => $addon,
            'capabilities' => $capabilities,
            'allocations' => $allocations,
        ]);
    }



    

    /**
     * Permanently delete a Core add-on and its catalog/configuration records.
     * Active SaaS/off-server purchases/rentals are intentionally untouched.
     */
    public function destroyAddon(int $id)
    {
        $addon = DB::table('core_addons')
            ->where('id', $id)
            ->first();

        abort_unless($addon, 404);

        DB::transaction(function () use ($id) {
            DB::table('core_addon_capability_allocations')
                ->where('addon_id', $id)
                ->delete();

            DB::table('core_addon_admin_grants')
                ->where('addon_id', $id)
                ->delete();

            DB::table('core_addon_bundle_items')
                ->where('addon_id', $id)
                ->delete();

            DB::table('core_addons')
                ->where('id', $id)
                ->delete();
        });

        return redirect()
            ->route('admin.core-addons.index')
            ->with('success', 'Core add-on permanently deleted.');
    }

    /**
     * Permanently delete a Core add-on bundle and its bundle-item records.
     * Active purchases/rentals are intentionally untouched.
     */
    public function destroyBundle(int $bundle)
    {
        $record = DB::table('core_addon_bundles')
            ->where('id', $bundle)
            ->first();

        abort_unless($record, 404);

        DB::transaction(function () use ($bundle) {
            DB::table('core_addon_bundle_items')
                ->where('bundle_id', $bundle)
                ->delete();

            DB::table('core_addon_bundles')
                ->where('id', $bundle)
                ->delete();
        });

        return redirect()
            ->route('admin.core-addons.index')
            ->with('success', 'Add-on bundle permanently deleted.');
    }

    public function grantAddon(Request $request, int $id)
    {
        $addon = DB::table('core_addons')
            ->where('id', $id)
            ->first();

        abort_unless($addon, 404);

        $data = $request->validate([
            'website_id' => [
                'required',
                'integer',
                'exists:websites,id'
            ],
            'price' => [
                'required',
                'numeric',
                'min:0'
            ],
            'currency' => [
                'required',
                'string',
                'size:3'
            ],
            'duration_days' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'starts_at' => [
                'nullable',
                'date'
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at'
            ],
        ]);

        $website = DB::table('websites')
            ->where('id', $data['website_id'])
            ->first();

        abort_unless($website, 404);

        $startsAt = !empty($data['starts_at'])
            ? \Carbon\Carbon::parse($data['starts_at'])
            : now();

        $expiresAt = $data['expires_at'] ?? null;

        if (!$expiresAt && !empty($data['duration_days'])) {
            $expiresAt = $startsAt
                ->copy()
                ->addDays((int) $data['duration_days']);
        }

        DB::table('core_addon_admin_grants')->insert([
            'addon_id' => $id,
            'website_id' => $website->id,
            'workspace_id' => $website->workspace_id ?? null,
            'user_id' => $website->user_id ?? null,
            'price' => $data['price'],
            'currency' => strtoupper($data['currency']),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.core-addons.index')
            ->with(
                'success',
                'Add-on assigned to the SaaS website successfully.'
            );
    }


    /**
     * Persist per-function add-on allocations.
     *
     * Every purchase/rental of the add-on contributes the configured
     * allocation again. Unlimited is stored per capability.
     */
    private function syncCapabilityAllocations(
        int $addonId,
        array $allocations,
        array $unlimited = []
    ): void {
        foreach ($allocations as $capabilityKey => $amount) {
            DB::table('core_addon_capability_allocations')
                ->updateOrInsert(
                    [
                        'addon_id' => $addonId,
                        'capability_key' => $capabilityKey,
                    ],
                    [
                        'allocation' => is_numeric($amount)
                            ? (float) $amount
                            : 0,
                        'is_unlimited' => isset(
                            $unlimited[$capabilityKey]
                        ) ? 1 : 0,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
        }
    }


}
