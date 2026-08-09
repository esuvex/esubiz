<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\CapacityProduct;
use App\Services\Developer\WebsiteCompilerService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BuilderController extends Controller
{
    public function index(): View
    {
        $capacityBundles = CapacityProduct::query()
            ->where('is_active', true)
            ->whereIn('audience', ['developer', 'both'])
            ->orderBy('name')
            ->get();

        return view('developer.builder.index', [
            'capacityBundles' => $capacityBundles,
        ]);
    }

    public function create(Request $request, WebsiteCompilerService $compiler): RedirectResponse
    {
        $request->validate([
            'project_name' => ['required', 'string', 'max:100'],
            'website_type' => [
                'required',
                'string',
                'in:business,ecommerce,portfolio,blog,landing',
            ],
            'capacity_bundle' => [
                'required',
                'integer',
                'exists:capacity_products,id',
            ],
            'theme' => [
                'required',
                'string',
                'in:default,minimal,modern,corporate',
            ],
            'modules' => ['nullable', 'array'],
            'modules.*' => [
                'string',
                'in:core,authentication,payments,commerce,notifications',
            ],
            'ai_theme' => ['nullable', 'boolean'],
        ]);

        $build = $compiler->createWorkspace($request->input('project_name'));

        $build['website_type'] = $request->input('website_type');
        $build['capacity_bundle'] = $request->input('capacity_bundle');
        $build['theme'] = $request->input('theme');
        $build['modules'] = $request->input('modules', []);
        $build['ai_theme'] = $request->boolean('ai_theme');

        return redirect()
            ->route('developer.builder')
            ->with('build', $build);
    }
}
