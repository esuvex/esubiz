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
            'show_in_user_wizard' => ['nullable', 'boolean'],
            'show_in_developer_wizard' => ['nullable', 'boolean'],
            'wizard_settings' => ['nullable', 'array'],
            'saas_price' => ['nullable', 'numeric', 'min:0'],
            'saas_billing_period' => ['nullable', 'integer', 'min:1', 'required_with:saas_price'],
            'saas_billing_interval' => ['nullable', 'string', 'max:20', 'required_with:saas_price'],
            'off_server_price' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        /*
         * ESUBIZ_WEBSITE_TYPE_IMAGE_OPTIMIZATION_V1
         *
         * Website Type media belongs to the landlord/platform,
         * never to a tenant media bucket.
         *
         * Raster images use the central optimizer while remaining
         * inside the landlord website-types directory.
         *
         * SVG and unsupported image formats retain their original
         * storage behaviour.
         */
        if ($request->hasFile('image')) {

            $file =
                $request->file(
                    'image'
                );

            $optimized =
                /* ESUBIZ_WEBSITE_TYPE_UNIVERSAL_MEDIA_STORE_V1 */
                app(
                    \App\Services\Media\CentralMediaService::class
                )->storeMediaToDisk(
                    $file,
                    'public',
                    'website-types',
                    [
                        'maximum_edge' => 1920,
                        'image_quality' => 82,
                    ]
                );

            $validated['image'] =
                $optimized
                ?: $file->store(
                    'website-types',
                    'public'
                );
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['show_in_user_wizard'] = $request->boolean('show_in_user_wizard');
        $validated['show_in_developer_wizard'] = $request->boolean('show_in_developer_wizard');

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
            'show_in_user_wizard' => ['nullable', 'boolean'],
            'show_in_developer_wizard' => ['nullable', 'boolean'],
            'wizard_settings' => ['nullable', 'array'],
            'saas_price' => ['nullable', 'numeric', 'min:0'],
            'saas_billing_period' => ['nullable', 'integer', 'min:1', 'required_with:saas_price'],
            'saas_billing_interval' => ['nullable', 'string', 'max:20', 'required_with:saas_price'],
            'off_server_price' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        /*
         * ESUBIZ_WEBSITE_TYPE_IMAGE_UPDATE_OPTIMIZATION_V1
         *
         * Replacement Website Type media follows the same landlord
         * storage boundary and central image optimization policy.
         */
        if ($request->hasFile('image')) {

            $file =
                $request->file(
                    'image'
                );

            $optimized =
                app(
                    \App\Services\Media\CentralMediaService::class
                )->storeMediaToDisk(
                    $file,
                    'public',
                    'website-types',
                    [
                        'maximum_edge' => 1920,
                        'image_quality' => 82,
                    ]
                );

            $newImage =
                $optimized
                ?: $file->store(
                    'website-types',
                    'public'
                );


            /*
             * Delete the previous asset only after the replacement
             * has been stored successfully.
             */
            if (
                $newImage
                && $websiteType->image
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $websiteType->image
                );
            }


            $validated['image'] =
                $newImage;

        } else {

            unset(
                $validated['image']
            );
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['show_in_user_wizard'] = $request->boolean('show_in_user_wizard');
        $validated['show_in_developer_wizard'] = $request->boolean('show_in_developer_wizard');

        $websiteType->update($validated);

        return redirect()
            ->route('admin.website-types.index')
            ->with('success', 'Website type updated successfully.');
    }
}
