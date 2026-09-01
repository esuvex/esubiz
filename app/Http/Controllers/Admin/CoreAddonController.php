<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Marketplace\CoreAddonMarketplaceListingSyncService;

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

        /*
         * ESUBIZ_CREATE_ADDON_INSERT_PAYLOAD_FIX_V1
         *
         * Resource Settings and Placement fields are validated form
         * configuration, not columns on core_addons. Keep $data intact
         * for the persistence steps below, but remove those fields from
         * the actual Add-on insert payload.
         */
        $addonInsertData = $data;

        unset(
            $addonInsertData['resource_enabled'],
            $addonInsertData['resource_key'],
            $addonInsertData['resource_dashboard_threshold'],
            $addonInsertData['resource_saas'],
            $addonInsertData['resource_off_server'],
            $addonInsertData['addon_placements'],
            $addonInsertData['dashboard_sales_trigger'],
            $addonInsertData['placement_title'],
            $addonInsertData['placement_description'],
            $addonInsertData['placement_cta_text']
        );

        $addonId = DB::table('core_addons')
            ->insertGetId($addonInsertData);

        $this->syncCapabilityAllocations(
            $addonId,
            $request->input('capability_allocations', []),
            $request->input('capability_unlimited', [])
        );

        /*
         * ESUBIZ_CREATE_ADDON_RESOURCE_SETTINGS_PERSISTENCE_V1
         *
         * Resource Settings remain central by resource_key.
         * Core remains responsible for default allocations.
         */
        $resourceKey = trim(
            (string) ($data['resource_key'] ?? '')
        );

        if ($resourceKey !== '') {
            DB::table('core_resource_settings')->updateOrInsert(
                [
                    'resource_key' => $resourceKey,
                ],
                [
                    'dashboard_threshold_percentage' =>
                        (float) (
                            $data['resource_dashboard_threshold']
                            ?? 80
                        ),

                    'saas_visible' =>
                        $request->boolean('resource_saas'),

                    'off_server_visible' =>
                        $request->boolean(
                            'resource_off_server'
                        ),

                    'is_active' =>
                        $request->boolean('resource_enabled'),

                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }


        /*
         * ESUBIZ_CREATE_ADDON_UNIVERSAL_PLACEMENT_MAPPING_V1
         *
         * One generic placement configuration for every Add-on:
         *
         * Dashboard:
         *   resource threshold / limit reached
         *   may retrigger after later resource usage
         *
         * Settings / Page Builder / Widgets:
         *   feature locked
         *   disappears after purchase
         */
        $selectedPlacements = array_values(
            array_intersect(
                [
                    'dashboard',
                    'settings',
                    'page_builder',
                    'widgets',
                ],
                (array) (
                    $data['addon_placements']
                    ?? []
                )
            )
        );

        $dashboardSalesTrigger = (array) (
            $data['dashboard_sales_trigger']
            ?? []
        );

        foreach ($selectedPlacements as $placementKey) {

            if ($placementKey === 'dashboard') {

                $conditionType = (string) (
                    $dashboardSalesTrigger['condition_type']
                    ?? 'resource_threshold'
                );

                if (!in_array(
                    $conditionType,
                    [
                        'resource_threshold',
                        'limit_reached',
                    ],
                    true
                )) {
                    $conditionType = 'resource_threshold';
                }

                $triggerResourceKey = trim(
                    (string) (
                        $dashboardSalesTrigger['resource_key']
                        ?? $resourceKey
                    )
                );

                $thresholdPercentage =
                    $conditionType === 'resource_threshold'
                        ? (float) (
                            $dashboardSalesTrigger[
                                'threshold_percentage'
                            ]
                            ?? $data[
                                'resource_dashboard_threshold'
                            ]
                            ?? 80
                        )
                        : null;

                $repeatPolicy = 'resource_retrigger';

            } else {

                $conditionType = 'feature_locked';
                $triggerResourceKey = null;
                $thresholdPercentage = null;
                $repeatPolicy = 'once_until_purchased';
            }

            DB::table('core_addon_sales_triggers')->insert([
                'addon_id' => $addonId,
                'location_key' => $placementKey,
                'condition_type' => $conditionType,
                'repeat_policy' => $repeatPolicy,
                'resource_key' =>
                    $triggerResourceKey !== ''
                        ? $triggerResourceKey
                        : null,
                'threshold_percentage' =>
                    $thresholdPercentage,

                'saas_visible' =>
                    $request->boolean('saas_available'),

                'off_server_visible' =>
                    $request->boolean(
                        'off_server_available'
                    ),

                'title' => $request->filled('placement_title') ? trim((string) $request->input('placement_title')) : null,
                'message' => $request->filled('placement_description') ? trim((string) $request->input('placement_description')) : null,
                'cta_text' => $request->filled('placement_cta_text') ? trim((string) $request->input('placement_cta_text')) : null,
                'priority' => 100,
                'is_active' => true,
                'condition_config' => null,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

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

            /*
             * ESUBIZ_CREATE_ADDON_UNIVERSAL_PLACEMENT_VALIDATION_V1
             */
            'resource_enabled' => ['nullable', 'boolean'],
            'resource_key' => ['nullable', 'string', 'max:150'],
            'resource_dashboard_threshold' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'resource_saas' => ['nullable', 'boolean'],
            'resource_off_server' => ['nullable', 'boolean'],

            /*
             * ESUBIZ_ADDON_PLACEMENT_ADMIN_CONTENT_VALIDATION_V1
             */
            'placement_title' => ['nullable', 'string', 'max:255'],
            'placement_description' => ['nullable', 'string', 'max:1000'],
            'placement_cta_text' => ['nullable', 'string', 'max:100'],

            'addon_placements' => ['nullable', 'array'],
            'addon_placements.*' => [
                'string',
                'in:dashboard,settings,page_builder,widgets',
            ],

            'dashboard_sales_trigger' => ['nullable', 'array'],
            'dashboard_sales_trigger.condition_type' => [
                'nullable',
                'in:resource_threshold,limit_reached',
            ],
            'dashboard_sales_trigger.resource_key' => [
                'nullable',
                'string',
                'max:150',
            ],
            'dashboard_sales_trigger.threshold_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
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

            /*
             * ESUBIZ_ADDON_RESOURCE_SALES_TRIGGER_V1
             *
             * Generic resource recommendation trigger.
             * This does not grant entitlement; the existing
             * Add-on allocation/fulfilment remains authoritative.
             */
            /*
             * ESUBIZ_ADDON_RESOURCE_SETTINGS_V1
             *
             * Resource registration is commercial/resource-management
             * configuration. Core remains authoritative for the base
             * allocation and Add-on allocations remain authoritative
             * for purchased entitlement increases.
             */
            'resource_enabled' => ['nullable', 'boolean'],
            'resource_key' => ['nullable', 'string', 'max:150'],
            'resource_dashboard_threshold' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'resource_saas' => ['nullable', 'boolean'],
            'resource_off_server' => ['nullable', 'boolean'],

            /*
             * ESUBIZ_GENERIC_ADDON_SALES_TRIGGER_VALIDATION_V1
             *
             * Locations themselves are validated again against the
             * feature/module-owned registry before persistence.
             */
            /*
             * ESUBIZ_UNIVERSAL_ADDON_PLACEMENT_VALIDATION_V1
             */
            'addon_placements' => ['nullable', 'array'],
            'addon_placements.*' => [
                'string',
                'in:dashboard,settings,page_builder,widgets',
            ],

            'dashboard_sales_trigger' => ['nullable', 'array'],
            'dashboard_sales_trigger.condition_type' => [
                'nullable',
                'in:resource_threshold,limit_reached',
            ],
            'dashboard_sales_trigger.resource_key' => [
                'nullable',
                'string',
                'max:150',
            ],
            'dashboard_sales_trigger.threshold_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'dashboard_sales_trigger.repeat_policy' => [
                'nullable',
                'in:once_until_purchased,resource_retrigger',
            ],

            'sales_triggers' => ['nullable', 'array'],
            'sales_triggers.*.location_key' => [
                'required',
                'string',
                'max:150',
            ],
            'sales_triggers.*.condition_type' => [
                'nullable',
                'string',
                'max:50',
            ],
            /*
             * ESUBIZ_GENERIC_TRIGGER_REPEAT_POLICY_VALIDATION_V1
             */
            'sales_triggers.*.repeat_policy' => [
                'nullable',
                'in:once_until_purchased,resource_retrigger',
            ],
            'sales_triggers.*.resource_key' => [
                'nullable',
                'string',
                'max:150',
            ],
            'sales_triggers.*.threshold_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'sales_triggers.*.saas_visible' => [
                'nullable',
                'boolean',
            ],
            'sales_triggers.*.off_server_visible' => [
                'nullable',
                'boolean',
            ],
            'sales_triggers.*.title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'sales_triggers.*.message' => [
                'nullable',
                'string',
            ],
            'sales_triggers.*.cta_text' => [
                'nullable',
                'string',
                'max:100',
            ],
            'sales_triggers.*.priority' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'sales_triggers.*.is_active' => [
                'nullable',
                'boolean',
            ],
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
                /*
                 * ESUBIZ_ADDON_PRESERVE_CAPABILITIES_WHEN_ABSENT_V1
                 * Preserve existing capability grants when the edit form
                 * does not submit a capabilities field.
                 */
                'capabilities' => $request->has('capabilities')
                    ? $this->jsonArray($request->input('capabilities'))
                    : $addon->capabilities,
                /*
                 * ESUBIZ_ADDON_RESOURCE_SALES_TRIGGER_METADATA_V1
                 */
                'metadata' => json_encode(array_merge(
                    (array) (
                        json_decode(
                            (string) ($addon->metadata ?? ''),
                            true
                        ) ?: []
                    ),
                    [
                        'deployment_types' => array_values(array_filter([
                            $request->boolean('saas_available') ? 'saas' : null,
                            $request->boolean('off_server_available') ? 'off_server' : null,
                        ])),
                        'commercial_models' => array_values(array_filter([
                            $request->boolean('saas_available') ? 'rental_or_subscription' : null,
                            $request->boolean('off_server_available') ? 'license' : null,
                        ])),
                    ]
                )),
                'updated_at' => now(),
            ]);

        /*
         * ESUBIZ_CENTRAL_RESOURCE_SETTINGS_PERSISTENCE_V1
         *
         * One authoritative Resource Settings record per Core
         * resource. Core owns base allocation. Add-ons own purchased
         * allocation. Sales trigger remains Add-on-specific.
         */
        $resourceKey =
            $data['resource_key']
            ?? null;

        if ($resourceKey) {

            DB::table('core_resource_settings')
                ->updateOrInsert(
                    [
                        'resource_key' => $resourceKey,
                    ],
                    [
                        'dashboard_threshold_percentage' =>
                            isset($data['resource_dashboard_threshold'])
                            && $data['resource_dashboard_threshold'] !== null
                                ? (float) $data['resource_dashboard_threshold']
                                : 50,

                        'saas_visible' =>
                            $request->boolean('resource_saas'),

                        'off_server_visible' =>
                            $request->boolean('resource_off_server'),

                        'is_active' =>
                            $request->boolean('resource_enabled'),

                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
        }


        /*
         * ESUBIZ_GENERIC_ADDON_SALES_TRIGGER_PERSISTENCE_V1
         *
         * The feature/module registry is authoritative for valid
         * trigger locations.
         *
         * Admin configures Add-on recommendation behaviour only.
         * Entitlement and Marketplace fulfilment remain unchanged.
         */
        $salesTriggerRegistry = app(
            \App\Services\Core\CoreAddonSalesTriggerRegistry::class
        );

        $availableTriggerConditions =
            $salesTriggerRegistry->conditions();

        /*
         * ESUBIZ_UNIVERSAL_ADDON_PLACEMENT_MAPPING_V1
         *
         * Admin edits only:
         *
         *   1. Placement
         *   2. Dashboard Sales Trigger
         *
         * They are translated here into the existing generic
         * core_addon_sales_triggers rows.
         *
         * Add-on deployment availability automatically controls
         * SaaS/off-server visibility. No duplicate visibility
         * configuration is required in Placement settings.
         */
        /*
         * ESUBIZ_UNIVERSAL_PLACEMENT_CONTENT_READBACK_V1
         *
         * Premium presentation content is stored on the universal
         * placement trigger rows. Read it directly from those rows
         * so Edit always displays exactly what Admin saved.
         */
        $universalPlacementContentTrigger =
            DB::table('core_addon_sales_triggers')
                ->where('addon_id', $id)
                ->whereIn(
                    'location_key',
                    [
                        'dashboard',
                        'settings',
                        'page_builder',
                        'widgets',
                    ]
                )
                ->orderBy('id')
                ->first();

        $universalPlacementContent = [
            'title' =>
                $universalPlacementContentTrigger->title
                ?? null,

            'description' =>
                $universalPlacementContentTrigger->message
                ?? null,

            'cta_text' =>
                $universalPlacementContentTrigger->cta_text
                ?? null,
        ];


        $selectedPlacements = array_values(
            array_unique(
                array_intersect(
                    (array) ($data['addon_placements'] ?? []),
                    [
                        'dashboard',
                        'settings',
                        'page_builder',
                        'widgets',
                    ]
                )
            )
        );

        $dashboardSalesTrigger =
            (array) ($data['dashboard_sales_trigger'] ?? []);

        $submittedSalesTriggers = [];

        /*
         * ESUBIZ_UNIVERSAL_ADDON_PLACEMENT_MAPPING_V2
         *
         * Settings, Page Builder and Widgets are normal feature
         * placements. They disappear once the Add-on is purchased.
         *
         * Dashboard is the resource sales placement. Its configured
         * Threshold/Limit condition determines when the recommendation
         * appears. Resource Add-ons may retrigger after later usage.
         *
         * Removing a placement from Admin configuration removes its
         * trigger row during persistence.
         */
        foreach ($selectedPlacements as $placementKey) {

            if ($placementKey === 'dashboard') {

                $conditionType =
                    $dashboardSalesTrigger['condition_type']
                    ?? 'resource_threshold';

                if (!in_array(
                    $conditionType,
                    [
                        'resource_threshold',
                        'limit_reached',
                    ],
                    true
                )) {
                    $conditionType = 'resource_threshold';
                }

                $submittedSalesTriggers[] = [
                    'location_key' => 'dashboard',

                    'condition_type' =>
                        $conditionType,

                    'resource_key' =>
                        $dashboardSalesTrigger['resource_key']
                        ?? null,

                    'threshold_percentage' =>
                        $conditionType === 'resource_threshold'
                            ? (
                                $dashboardSalesTrigger[
                                    'threshold_percentage'
                                ]
                                ?? 80
                            )
                            : null,

                    /*
                     * Dashboard resource selling is designed to
                     * become eligible again after additional capacity
                     * has been purchased and later consumed.
                     */
                    'repeat_policy' =>
                        'resource_retrigger',

                    'saas_visible' =>
                        $request->boolean('saas_available'),

                    'off_server_visible' =>
                        $request->boolean('off_server_available'),

                    'is_active' => true,

                    'priority' => 100,
                    'title' => $request->filled('placement_title') ? trim((string) $request->input('placement_title')) : null,
                    'message' => $request->filled('placement_description') ? trim((string) $request->input('placement_description')) : null,
                    'cta_text' => $request->filled('placement_cta_text') ? trim((string) $request->input('placement_cta_text')) : null,
                ];

                continue;
            }

            /*
             * Normal feature placement.
             * Purchase satisfies the requirement and stops the
             * recommendation from appearing again.
             */
            $submittedSalesTriggers[] = [
                'location_key' =>
                    $placementKey,

                'condition_type' =>
                    'feature_locked',

                'resource_key' => null,
                'threshold_percentage' => null,

                'repeat_policy' =>
                    'once_until_purchased',

                'saas_visible' =>
                    $request->boolean('saas_available'),

                'off_server_visible' =>
                    $request->boolean('off_server_available'),

                'is_active' => true,

                'priority' => 100,
                'title' => $request->filled('placement_title') ? trim((string) $request->input('placement_title')) : null,
                'message' => $request->filled('placement_description') ? trim((string) $request->input('placement_description')) : null,
                'cta_text' => $request->filled('placement_cta_text') ? trim((string) $request->input('placement_cta_text')) : null,
            ];
        }

        $validSalesTriggerRows = [];

        foreach ($submittedSalesTriggers as $trigger) {

            if (!is_array($trigger)) {
                continue;
            }

            $locationKey =
                $trigger['location_key']
                ?? null;

            if (
                !$locationKey
                || !$salesTriggerRegistry->hasLocation($locationKey)
            ) {
                continue;
            }

            $conditionType =
                $trigger['condition_type']
                ?? 'always';

            if (!array_key_exists(
                $conditionType,
                $availableTriggerConditions
            )) {
                $conditionType = 'always';
            }

            $location =
                $salesTriggerRegistry->location($locationKey)
                ?? [];

            $supportsResourceCondition =
                (bool) (
                    $location['supports_resource_condition']
                    ?? false
                );

            $resourceKey =
                $supportsResourceCondition
                    ? (
                        $trigger['resource_key']
                        ?? $location['resource_key']
                        ?? null
                    )
                    : null;

            $thresholdPercentage =
                $supportsResourceCondition
                && isset($trigger['threshold_percentage'])
                && $trigger['threshold_percentage'] !== null
                    ? (float) $trigger['threshold_percentage']
                    : null;

            $validSalesTriggerRows[] = [
                'addon_id' => $id,
                'location_key' => $locationKey,
                'condition_type' => $conditionType,

                /*
                 * ESUBIZ_GENERIC_TRIGGER_REPEAT_POLICY_PERSISTENCE_V1
                 *
                 * Normal/feature Add-ons disappear after purchase.
                 * Resource Add-ons may explicitly be configured to
                 * retrigger when their usage condition becomes true again.
                 */
                'repeat_policy' =>
                    (
                        ($trigger['repeat_policy'] ?? null)
                        === 'resource_retrigger'
                        && $supportsResourceCondition
                    )
                        ? 'resource_retrigger'
                        : 'once_until_purchased',

                'resource_key' => $resourceKey,
                'threshold_percentage' => $thresholdPercentage,
                'saas_visible' =>
                    !empty($trigger['saas_visible']),
                'off_server_visible' =>
                    !empty($trigger['off_server_visible']),
                'title' =>
                    $trigger['title']
                    ?? null,
                'message' =>
                    $trigger['message']
                    ?? null,
                'cta_text' =>
                    $trigger['cta_text']
                    ?? null,
                'priority' =>
                    isset($trigger['priority'])
                        ? (int) $trigger['priority']
                        : 100,
                'is_active' =>
                    !empty($trigger['is_active']),
                'condition_config' => null,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use (
            $id,
            $validSalesTriggerRows
        ): void {

            DB::table('core_addon_sales_triggers')
                ->where('addon_id', $id)
                ->delete();

            if ($validSalesTriggerRows) {
                DB::table('core_addon_sales_triggers')
                    ->insert($validSalesTriggerRows);
            }
        });



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

        /*
         * ESUBIZ_GENERIC_ADDON_SALES_TRIGGER_EDIT_DATA_V1
         *
         * Trigger locations are supplied by the Core features/modules
         * that own them. The Add-on controller only consumes the
         * submitted registry.
         */
        $salesTriggerRegistry = app(
            \App\Services\Core\CoreAddonSalesTriggerRegistry::class
        );

        $triggerLocations = $salesTriggerRegistry->locations();
        $triggerConditions = $salesTriggerRegistry->conditions();

        $salesTriggers = DB::table('core_addon_sales_triggers')
            ->where('addon_id', $id)
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        /*
         * ESUBIZ_CENTRAL_RESOURCE_SETTINGS_FORM_READ_V3
         *
         * Resource Settings are central by resource_key.
         *
         * Resolve the resource from the Add-on's actual saved allocation
         * records instead of depending only on the collection index/key.
         * This keeps the managed-resource checkbox and settings correctly
         * populated after save/reopen.
         */
        $resourceSetting = null;

        foreach ($allocations as $allocationKey => $allocation) {

            $candidateKeys = array_values(
                array_unique(
                    array_filter([
                        is_string($allocationKey)
                            ? $allocationKey
                            : null,

                        is_object($allocation)
                            ? ($allocation->capability_key ?? null)
                            : (
                                is_array($allocation)
                                    ? ($allocation['capability_key'] ?? null)
                                    : null
                            ),

                        is_object($allocation)
                            ? ($allocation->resource_key ?? null)
                            : (
                                is_array($allocation)
                                    ? ($allocation['resource_key'] ?? null)
                                    : null
                            ),

                        is_object($allocation)
                            ? ($allocation->limit_key ?? null)
                            : (
                                is_array($allocation)
                                    ? ($allocation['limit_key'] ?? null)
                                    : null
                            ),
                    ])
                )
            );

            foreach ($candidateKeys as $candidateKey) {

                $matchedResourceSetting =
                    DB::table('core_resource_settings')
                        ->where('resource_key', $candidateKey)
                        ->first();

                if ($matchedResourceSetting) {
                    $resourceSetting = $matchedResourceSetting;
                    break 2;
                }
            }
        }

        $resourceSettings = $resourceSetting
            ? [
                'enabled' =>
                    (bool) $resourceSetting->is_active,

                'resource_key' =>
                    $resourceSetting->resource_key,

                'dashboard_threshold_percentage' =>
                    (float) $resourceSetting
                        ->dashboard_threshold_percentage,

                'saas' =>
                    (bool) $resourceSetting->saas_visible,

                'off_server' =>
                    (bool) $resourceSetting->off_server_visible,
            ]
            : [];

                /*
         * ESUBIZ_UNIVERSAL_PLACEMENT_CONTENT_EDIT_SCOPE_FIX_V1
         *
         * Premium content belongs to the saved universal placement rows.
         * Define it in this exact Edit request scope before rendering.
         */
        $universalPlacementContentTrigger =
            DB::table('core_addon_sales_triggers')
                ->where('addon_id', $id)
                ->whereIn(
                    'location_key',
                    [
                        'dashboard',
                        'settings',
                        'page_builder',
                        'widgets',
                    ]
                )
                ->orderBy('id')
                ->first();

        $universalPlacementContent = [
            'title' =>
                $universalPlacementContentTrigger->title ?? null,

            'description' =>
                $universalPlacementContentTrigger->message ?? null,

            'cta_text' =>
                $universalPlacementContentTrigger->cta_text ?? null,
        ];

return view('admin.core-addons.edit', [
            'addon' => $addon,
            'capabilities' => $capabilities,
            'allocations' => $allocations,
            'triggerLocations' => $triggerLocations,
            'triggerConditions' => $triggerConditions,
            'salesTriggers' => $salesTriggers,
            'resourceSettings' => $resourceSettings,
        ])
            ->with('universalPlacementContent', $universalPlacementContent);
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



    /*
     * ESUBIZ_CORE_ADDON_MARKETPLACE_AUTO_SYNC_V1
     */
    protected function syncMarketplaceListing(int $addonId): void
    {
        app(CoreAddonMarketplaceListingSyncService::class)
            ->syncById($addonId);
    }

}
