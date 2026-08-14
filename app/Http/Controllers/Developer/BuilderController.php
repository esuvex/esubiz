<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\AddonProduct;
use App\Services\Developer\WebsiteCompilerService;
use App\Models\DeveloperBuild;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BuilderController extends Controller
{
    public function index(): View
    {
        $addonBundles = AddonProduct::query()
            ->where('is_active', true)
            ->whereIn('audience', ['developer', 'both'])
            ->orderBy('name')
            ->get();

        $modules = \App\Models\CatalogProduct::query()
            ->where('product_type', 'module')
            ->where('is_active', true)
            ->whereIn('audience', ['developer', 'both'])
            ->orderBy('name')
            ->get();

        $themes = \App\Models\CatalogProduct::query()
            ->where('product_type', 'theme')
            ->where('is_active', true)
            ->whereIn('audience', ['developer', 'both'])
            ->orderBy('name')
            ->get();

        return view('developer.builder.index', [
            'addonBundles' => $addonBundles,
            'modules' => $modules,
            'themes' => $themes,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:100'],
            'website_type' => [
                'required',
                'string',
                'in:business,ecommerce,portfolio,blog,landing',
            ],
            'addon_bundle' => [
                'required',
                'integer',
                'exists:addon_products,id',
            ],
            'theme' => [
                'nullable',
                'integer',
                'exists:catalog_products,id',
            ],
            'modules' => ['nullable', 'array'],
            'modules.*' => [
                'integer',
                'exists:catalog_products,id',
            ],
            'ai_theme' => ['nullable', 'boolean'],
            'ai_theme_prompt' => ['nullable', 'string', 'max:5000'],
            'ai_theme_reference_photos' => [
                'nullable',
                'array',
                'max:10',
            ],
            'ai_theme_reference_photos.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
            'ai_theme_preferences' => ['nullable', 'array'],
            'ai_theme_preferences.mode' => [
                'nullable',
                'string',
                'in:light,dark,mixed',
            ],
            'ai_theme_preferences.style' => [
                'nullable',
                'string',
                'in:minimal,modern,corporate,bold,editorial',
            ],
            'ai_theme_preferences.animation' => [
                'nullable',
                'string',
                'in:none,subtle,dynamic',
            ],
        ]);

        $buildId = Str::slug($validated['project_name']) . '-' . Str::lower(Str::random(8));

        $build = \App\Models\DeveloperBuild::create([
            'uuid' => (string) Str::uuid(),
            'developer_id' => auth()->id(),
            'project_name' => $validated['project_name'],
            'build_id' => $buildId,
            'version' => '1.0.0',
            'website_type' => $validated['website_type'],
            'status' => 'queued',
            'stage' => 'addon',
            'build_type' => 'developer',
            'payment_status' => 'unpaid',
            'configuration' => [
                'ai_theme' => false,
            ],
        ]);

        return redirect()
            ->route('developer.builder')
            ->with('build', $build);
    }
}
