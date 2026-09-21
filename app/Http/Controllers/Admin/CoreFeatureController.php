<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoreFeatureController
{
    public function index()
    {
        $features = DB::table('core_features')
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(10, ['*'], 'feature_page');

        $limits = $features->mapWithKeys(function ($feature) {
            $featureMetadata = $feature->metadata
                ? (json_decode($feature->metadata, true) ?: [])
                : [];

            $resources = is_array($featureMetadata['resources'] ?? null)
                ? $featureMetadata['resources']
                : [];

            $featureLimits = DB::table('core_feature_limits')
                ->where('core_feature_id', $feature->id)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->limit(10)
                ->get()
                ->map(function ($limit) use ($resources) {
                    $resource = is_array(
                        $resources[$limit->limit_key] ?? null
                    )
                        ? $resources[$limit->limit_key]
                        : [];

                    $limit->resource_usage = is_array(
                        $resource['usage'] ?? null
                    )
                        ? $resource['usage']
                        : [];

                    return $limit;
                });

            return [
                $feature->id => $featureLimits,
            ];
        });

        return view('admin.core-features.index', compact('features', 'limits'));
    }

    public function features(Request $request)
    {
        $features = DB::table('core_features')
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(10);

        $features->getCollection()->transform(function ($feature) {
            $feature->limit_count = DB::table('core_feature_limits')
                ->where('core_feature_id', $feature->id)
                ->whereNull('deleted_at')
                ->count();

            return $feature;
        });

        return response()->json([
            'data' => $features->items(),
            'pagination' => [
                'current_page' => $features->currentPage(),
                'last_page' => $features->lastPage(),
                'per_page' => $features->perPage(),
                'total' => $features->total(),
                'from' => $features->firstItem(),
                'to' => $features->lastItem(),
                'has_previous' => $features->currentPage() > 1,
                'has_next' => $features->hasMorePages(),
            ],
        ]);
    }

    public function tenantTables(
        \App\Services\Website\WebsiteTenantDatabaseService $tenantDatabase
    ) {
        $website = \App\Models\Website::query()
            ->whereHas('databaseConnection')
            ->orderByDesc('id')
            ->first();

        if (!$website) {
            return response()->json([
                'data' => [],
                'message' => 'No initialized Core website database is currently available.',
            ]);
        }

        try {
            $tenantDatabase->connect($website);

            $connection = $tenantDatabase->connection();
            $database = $connection->getDatabaseName();

            $rows = $connection->select(
                'SELECT TABLE_NAME
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = ?
                 ORDER BY TABLE_NAME',
                [$database]
            );

            $tables = collect($rows)
                ->map(function ($row) {
                    return $row->TABLE_NAME
                        ?? $row->table_name
                        ?? null;
                })
                ->filter()
                ->values()
                ->all();

            return response()->json([
                'data' => $tables,
                'source_website_id' => $website->id,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'data' => [],
                'message' => 'Tenant tables could not be discovered.',
            ]);
        } finally {
            try {
                $tenantDatabase->disconnect();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function management(int $id)
    {
        $feature = DB::table('core_features')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($feature, 404);

        $featureMetadata = $feature->metadata
            ? (json_decode($feature->metadata, true) ?: [])
            : [];

        $resources = is_array($featureMetadata['resources'] ?? null)
            ? $featureMetadata['resources']
            : [];

        $featureLimits = DB::table('core_feature_limits')
            ->where('core_feature_id', $feature->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($limit) use ($resources) {
                $resource = is_array(
                    $resources[$limit->limit_key] ?? null
                )
                    ? $resources[$limit->limit_key]
                    : [];

                $limit->resource_usage = is_array(
                    $resource['usage'] ?? null
                )
                    ? $resource['usage']
                    : [];

                return $limit;
            });

        $limits = collect([
            $feature->id => $featureLimits,
        ]);

        return response()->json([
            'html' => view(
                'admin.core-features.partials.management-modal',
                compact('feature', 'limits')
            )->render(),
        ]);
    }

    public function storeFeature(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:feature,service,communication,resource,integration'],
        ]);

        DB::table('core_features')->insert([
            'uuid' => (string) Str::uuid(),
            'key' => $data['key'],
            'name' => $data['name'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'is_core' => true,
            'is_active' => $request->boolean('is_active'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Core feature created successfully.');
    }

    public function updateFeature(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:feature,service,communication,resource,integration'],
        ]);

        DB::table('core_features')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'category' => $data['category'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'is_active' => $request->boolean('is_active'),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Core feature updated successfully.');
    }

    public function limits(Request $request, int $featureId)
    {
        $feature = DB::table('core_features')
            ->where('id', $featureId)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($feature, 404);

        $featureMetadata = $feature->metadata
            ? (json_decode($feature->metadata, true) ?: [])
            : [];

        $resources = is_array($featureMetadata['resources'] ?? null)
            ? $featureMetadata['resources']
            : [];

        $limits = DB::table('core_feature_limits')
            ->where('core_feature_id', $featureId)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->paginate(10);

        $limits->getCollection()->transform(
            function ($limit) use ($resources) {
                $resource = is_array(
                    $resources[$limit->limit_key] ?? null
                )
                    ? $resources[$limit->limit_key]
                    : [];

                $limit->resource_usage = is_array(
                    $resource['usage'] ?? null
                )
                    ? $resource['usage']
                    : [];

                return $limit;
            }
        );

        return response()->json([
            'data' => $limits->items(),
            'pagination' => [
                'current_page' => $limits->currentPage(),
                'last_page' => $limits->lastPage(),
                'per_page' => $limits->perPage(),
                'total' => $limits->total(),
                'from' => $limits->firstItem(),
                'to' => $limits->lastItem(),
                'has_previous' => $limits->currentPage() > 1,
                'has_next' => $limits->hasMorePages(),
            ],
        ]);
    }

    public function storeLimit(Request $request, int $featureId)
    {
        $data = $request->validate([
            'limit_key' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'value_type' => ['required', 'in:quantity,boolean,storage,bandwidth,credits,unlimited'],
            'default_value' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:255'],
            'usage_driver' => ['nullable', 'in:database_count'],
            'usage_connection' => ['nullable', 'in:tenant,website_tenant'],
            'usage_table' => [
                'nullable',
                'required_if:usage_driver,database_count',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9_]+$/',
            ],
        ]);

        DB::table('core_feature_limits')->updateOrInsert(
            [
                'core_feature_id' => $featureId,
                'limit_key' => $data['limit_key'],
            ],
            [
                'name' => $data['name'],
                'value_type' => $data['value_type'],
                'default_value' => $data['default_value'] ?? null,
                'unit' => $data['unit'] ?? null,
                'is_unlimited' => $data['value_type'] === 'unlimited',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->syncResourceUsageDefinition(
            $featureId,
            $data['limit_key'],
            $data
        );

        return back()->with('success', 'Feature limit saved successfully.');
    }

    public function updateLimit(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'value_type' => ['required', 'in:quantity,boolean,storage,bandwidth,credits,unlimited'],
            'default_value' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:255'],
            'usage_driver' => ['nullable', 'in:database_count'],
            'usage_connection' => ['nullable', 'in:tenant,website_tenant'],
            'usage_table' => [
                'nullable',
                'required_if:usage_driver,database_count',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9_]+$/',
            ],
        ]);

        $existing = DB::table('core_feature_limits')
            ->where('id', $id)
            ->first();

        if (!$existing) {
            abort(404);
        }

        DB::table('core_feature_limits')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'value_type' => $data['value_type'],
                'default_value' => $data['default_value'] ?? null,
                'unit' => $data['unit'] ?? null,
                'is_unlimited' => $data['value_type'] === 'unlimited',
                'updated_at' => now(),
            ]);

        $this->syncResourceUsageDefinition(
            (int) $existing->core_feature_id,
            (string) $existing->limit_key,
            $data
        );

        return back()->with('success', 'Feature limit updated successfully.');
    }

    /**
     * Store measurement configuration on the Core resource itself.
     *
     * Core Limits own SaaS allowance only. Usage measurement remains
     * available independently, including when no SaaS limit exists.
     */
    protected function syncResourceUsageDefinition(
        int $featureId,
        string $resourceKey,
        array $data
    ): void {
        $feature = DB::table('core_features')
            ->where('id', $featureId)
            ->whereNull('deleted_at')
            ->first();

        if (!$feature) {
            abort(404);
        }

        $metadata = $feature->metadata
            ? (json_decode($feature->metadata, true) ?: [])
            : [];

        $resources = is_array($metadata['resources'] ?? null)
            ? $metadata['resources']
            : [];

        $resource = is_array($resources[$resourceKey] ?? null)
            ? $resources[$resourceKey]
            : [];

        if (!empty($data['usage_driver'])) {
            $resource['usage'] = [
                'driver' => $data['usage_driver'],
                'connection' => $data['usage_connection'] ?? 'tenant',
                'table' => $data['usage_table'] ?? null,
            ];
        } else {
            unset($resource['usage']);
        }

        if ($resource) {
            $resources[$resourceKey] = $resource;
        } else {
            unset($resources[$resourceKey]);
        }

        if ($resources) {
            $metadata['resources'] = $resources;
        } else {
            unset($metadata['resources']);
        }

        DB::table('core_features')
            ->where('id', $featureId)
            ->update([
                'metadata' => $metadata
                    ? json_encode($metadata)
                    : null,
                'updated_at' => now(),
            ]);
    }

    public function destroyFeature(int $id)
    {
        $feature = DB::table('core_features')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($feature, 404);

        DB::transaction(function () use ($id) {
            DB::table('core_features')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'is_active' => false,
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('core_feature_limits')
                ->where('core_feature_id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'is_active' => false,
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return back()->with(
            'success',
            'Core feature deleted successfully.'
        );
    }

    public function destroyLimit(int $id)
    {
        $limit = DB::table('core_feature_limits')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($limit, 404);

        DB::table('core_feature_limits')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'is_active' => false,
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Core limit deleted successfully.'
        );
    }

    public function toggleFeature(int $id)
    {
        DB::table('core_features')
            ->where('id', $id)
            ->update([
                'is_active' => DB::raw('NOT is_active'),
                'updated_at' => now(),
            ]);

        return back();
    }

    public function toggleLimit(int $id)
    {
        DB::table('core_feature_limits')
            ->where('id', $id)
            ->update([
                'is_active' => DB::raw('NOT is_active'),
                'updated_at' => now(),
            ]);

        return back();
    }
}
