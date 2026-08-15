<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteType;
use App\Services\ProductStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class WebsiteTypeController extends Controller
{
    public function index(): View
    {
        $websiteTypes = WebsiteType::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.website-types.index', [
            'websiteTypes' => $websiteTypes,
        ]);
    }

    public function create(ProductStorageService $storage): View
    {
        return view('admin.website-types.create', [
            'packages' => $storage->websiteTypePackages(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:website_types,slug'],
            'package_key' => ['nullable', 'string', 'max:255'],
            'package_version' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('website-types', 'public');
        }

        WebsiteType::create($validated);

        return redirect()
            ->route('admin.website-types.index')
            ->with('success', 'Website type created successfully.');
    }

    public function edit(
        WebsiteType $websiteType,
        ProductStorageService $storage
    ): View {
        return view('admin.website-types.edit', [
            'websiteType' => $websiteType,
            'packages' => $storage->websiteTypePackages(),
        ]);
    }

    public function update(
        Request $request,
        WebsiteType $websiteType
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:website_types,slug,' . $websiteType->id,
            ],
            'package_key' => ['nullable', 'string', 'max:255'],
            'package_version' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            if ($websiteType->image) {
                Storage::disk('public')->delete($websiteType->image);
            }

            $validated['image'] = $request->file('image')->store('website-types', 'public');
        } else {
            unset($validated['image']);
        }

        $websiteType->update($validated);

        return redirect()
            ->route('admin.website-types.index')
            ->with('success', 'Website type updated successfully.');
    }
}
