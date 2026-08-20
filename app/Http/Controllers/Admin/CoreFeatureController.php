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
            ->get();

        $limits = DB::table('core_feature_limits')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->groupBy('core_feature_id');

        return view('admin.core-features.index', compact('features', 'limits'));
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

    public function storeLimit(Request $request, int $featureId)
    {
        $data = $request->validate([
            'limit_key' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'value_type' => ['required', 'in:quantity,boolean,storage,bandwidth,credits,unlimited'],
            'default_value' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:255'],
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

        return back()->with('success', 'Feature limit saved successfully.');
    }

    public function updateLimit(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'value_type' => ['required', 'in:quantity,boolean,storage,bandwidth,credits,unlimited'],
            'default_value' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:255'],
        ]);

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

        return back()->with('success', 'Feature limit updated successfully.');
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
