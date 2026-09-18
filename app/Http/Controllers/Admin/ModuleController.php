<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogProduct;
use App\Models\Currency;
use App\Models\CurrencyPrice;
use App\Models\Module;
use App\Models\ModuleAsset;
use App\Models\ModuleVersion;
use App\Models\WebsiteType;
use App\Services\Core\Modules\CoreModuleFunctionService;
use App\Services\Marketplace\Themes\ThemeMarketplaceResolver;
use App\Services\Marketplace\Packages\EsubizProductPackageValidator;
use App\Services\Media\CentralMediaService;
use App\Services\Media\EsubizImageOptimizer;
use App\Services\Platform\EsubizCurrencyPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    public function __construct(
        protected CoreModuleFunctionService $coreFunctions,
        protected EsubizCurrencyPricingService $currencyPricing,
        protected ThemeMarketplaceResolver $themes,
        protected EsubizProductPackageValidator $productPackageValidator
    ) {
    }

    public function index()
    {
        $modules = Module::query()
            ->with('catalogProduct')
            ->latest('id')
            ->paginate(10);

        return view(
            'admin.modules.index',
            compact('modules')
        );
    }

    public function create()
    {
        return view(
            'admin.modules.create',
            $this->formData()
        );
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $module = DB::transaction(
            function () use ($data, $request) {
                $catalogProduct = CatalogProduct::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'product_type' => 'module',
                    'audience' => $this->audience(
                        $data['saas_available'],
                        $data['off_server_available']
                    ),
                    'fulfilment_type' => 'installer',
                    'is_featured' => $data['is_featured'],
                    'is_active' => $data['is_active'],
                    'is_public' => $data['is_public'],
                ]);

                $module = Module::create([
                    'catalog_product_id' => $catalogProduct->id,
                    'uuid' => (string) Str::uuid(),
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'type' => $data['type'],
                    'version' => $data['version'],
                    'package_name' => $data['package_name'] ?? null,
                    'sidebar_menu_name' => $data['sidebar_menu_name'],
                    'saas_wizard_visible' => $data['saas_wizard_visible'],
                    'off_server_wizard_visible' => $data['off_server_wizard_visible'],
                    'compatible_website_types' => $data['compatible_website_types'] ?? [],
                    'compatible_themes' => $data['compatible_themes'] ?? [],
                    'core_function_configuration' => $data['core_function_configuration'] ?? [],
                    'marketplace_metadata' => $data['marketplace_metadata'] ?? [],
                    'requirements' => $data['requirements'] ?? [],
                    'is_verified' => $data['is_verified'],
                    'is_active' => $data['is_active'],
                ]);

                $this->syncPrices(
                    $catalogProduct,
                    $data
                );

                $this->syncUploads(
                    $module,
                    $request,
                    $data
                );

                return $module;
            }
        );

        return redirect()
            ->route('admin.modules.edit', $module)
            ->with('success', 'Module created successfully.');
    }

    public function show(Module $module)
    {
        $module->load('catalogProduct');

        return view(
            'admin.modules.show',
            compact('module')
        );
    }

    public function edit(Module $module)
    {
        $module->load('catalogProduct');

        $modulePrices = CurrencyPrice::query()
            ->where('priceable_type', 'catalog_product')
            ->where('priceable_id', $module->catalog_product_id)
            ->where('is_active', true)
            ->whereIn('scope_type', ['saas', 'developer'])
            ->latest('id')
            ->get()
            ->keyBy('scope_type');

        $saasPrice = $modulePrices
            ->get('saas')
            ?->price;

        $offServerPrice = $modulePrices
            ->get('developer')
            ?->price;

        return view(
            'admin.modules.edit',
            array_merge(
                $this->formData(),
                compact(
                    'module',
                    'saasPrice',
                    'offServerPrice'
                )
            )
        );
    }

    public function update(
        Request $request,
        Module $module
    ) {
        $data = $this->validatedData(
            $request,
            $module
        );

        DB::transaction(
            function () use ($module, $data, $request) {
                $module->catalogProduct()->update([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'audience' => $this->audience(
                        $data['saas_available'],
                        $data['off_server_available']
                    ),
                    'is_featured' => $data['is_featured'],
                    'is_active' => $data['is_active'],
                    'is_public' => $data['is_public'],
                ]);

                $module->update([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'type' => $data['type'],
                    'version' => $data['version'],
                    'package_name' => $data['package_name'] ?? null,
                    'sidebar_menu_name' => $data['sidebar_menu_name'],
                    'saas_wizard_visible' => $data['saas_wizard_visible'],
                    'off_server_wizard_visible' => $data['off_server_wizard_visible'],
                    'compatible_website_types' => $data['compatible_website_types'] ?? [],
                    'compatible_themes' => $data['compatible_themes'] ?? [],
                    'core_function_configuration' => $data['core_function_configuration'] ?? [],
                    'marketplace_metadata' => $data['marketplace_metadata'] ?? [],
                    'requirements' => $data['requirements'] ?? [],
                    'is_verified' => $data['is_verified'],
                    'is_active' => $data['is_active'],
                ]);

                $this->syncPrices(
                    $module->catalogProduct,
                    $data
                );

                $this->syncUploads(
                    $module,
                    $request,
                    $data
                );
            }
        );

        return back()->with(
            'success',
            'Module updated successfully.'
        );
    }

    public function destroy(Module $module)
    {
        DB::transaction(
            function () use ($module) {
                $catalogProduct = $module->catalogProduct;

                $module->delete();

                if ($catalogProduct) {
                    $catalogProduct->delete();
                }
            }
        );

        return redirect()
            ->route('admin.modules.index')
            ->with('success', 'Module removed successfully.');
    }

    public function storePackage(Request $request)
    {
        return response()->json([
            'ok' => false,
            'message' => 'Module package upload handler is not configured yet.',
        ], 501);
    }

    protected function formData(): array
    {
        return [
            'websiteTypes' => WebsiteType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'coreMenus' => $this->coreFunctions
                ->adminMenus(),

            'coreFeatures' => $this->coreFunctions
                ->availableFunctions(),

            /*
             * Central Admin chooses Module ↔ Theme compatibility.
             * Use the existing Theme Marketplace authority and expose
             * the union of deployable SaaS/off-server Themes.
             */
            'themes' => $this->themes
                ->forDeployment(
                    ThemeMarketplaceResolver::DEPLOYMENT_SAAS
                )
                ->merge(
                    $this->themes->forDeployment(
                        ThemeMarketplaceResolver::DEPLOYMENT_OFF_SERVER
                    )
                )
                ->unique('id')
                ->sortBy('name')
                ->values(),

            'primaryCurrency' => $this->currencyPricing
                ->primaryCurrency(),
        ];
    }

    protected function validatedData(
        Request $request,
        ?Module $module = null
    ): array {
        $moduleId = $module?->id;
        $catalogProductId = $module?->catalog_product_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            'slug' => [
                'required',
                'string',
                'max:150',
                'alpha_dash',
                Rule::unique('modules', 'slug')
                    ->ignore($moduleId),
                Rule::unique('catalog_products', 'slug')
                    ->ignore($catalogProductId),
            ],

            'description' => ['nullable', 'string'],
            'version' => ['required', 'string', 'max:50'],
            'package_name' => ['nullable', 'string', 'max:255'],

            'preview_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'package_file' => [
                'nullable',
                'file',
                'mimes:zip',
                'max:102400',
            ],

            'type' => [
                'required',
                Rule::in([
                    'core',
                    'official',
                    'developer',
                    'private',
                ]),
            ],

            'sidebar_menu_name' => [
                'required',
                'string',
                'max:100',
            ],

            'saas_available' => ['required', 'boolean'],
            'off_server_available' => ['required', 'boolean'],

            'saas_price' => [
                'required_if:saas_available,1',
                'nullable',
                'numeric',
                'min:0',
            ],

            'off_server_price' => [
                'required_if:off_server_available,1',
                'nullable',
                'numeric',
                'min:0',
            ],

            'saas_wizard_visible' => ['required', 'boolean'],
            'off_server_wizard_visible' => ['required', 'boolean'],

            'compatible_website_types' => ['nullable', 'array'],
            'compatible_website_types.*' => ['integer'],

            'compatible_themes' => ['nullable', 'array'],
            'compatible_themes.*' => ['string', 'max:150'],

            'core_function_configuration' => ['nullable', 'array'],
            'marketplace_metadata' => ['nullable', 'array'],
            'requirements' => ['nullable', 'array'],

            'is_verified' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'is_public' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (
            !$data['saas_available']
            && !$data['off_server_available']
        ) {
            abort(
                422,
                'A module must be available for SaaS, off-server, or both.'
            );
        }

        return $data;
    }

    protected function syncUploads(
        Module $module,
        Request $request,
        array $data
    ): void {
        if ($request->hasFile('preview_image')) {
            $file = $request->file('preview_image');

            /*
             * Marketplace previews are Central-owned assets regardless
             * of whether the product publisher is Admin or Developer.
             *
             * Raster previews use the canonical 1600x1000 (8:5)
             * Marketplace optimization profile.
             */
            $path = app(CentralMediaService::class)->store(
                $file,
                'marketplace/modules/' . $module->slug . '/previews',
                EsubizImageOptimizer::PROFILE_MARKETPLACE_PREVIEW
            );

            ModuleAsset::query()
                ->where('module_id', $module->id)
                ->where('type', 'preview')
                ->update([
                    'is_public' => false,
                ]);

            ModuleAsset::create([
                'module_id' => $module->id,
                'uuid' => (string) Str::uuid(),
                'name' => $module->slug . '-preview',
                'type' => 'preview',
                'disk' => 'public',
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'metadata' => [
                    'marketplace_canvas' => '1600x1000',
                    'aspect_ratio' => '8:5',
                    'storage_owner' => 'central',
                ],
                'is_public' => true,
            ]);
        }

        if ($request->hasFile('package_file')) {
            $file = $request->file('package_file');

            /*
             * Validate the temporary uploaded ZIP BEFORE it is persisted.
             * A Module upload must contain a valid root esubiz-package.json
             * identifying the package as product_type=module.
             */
            $this->productPackageValidator->validate(
                $file->getRealPath(),
                'module'
            );

            $filename = $module->slug
                . '-v'
                . $data['version']
                . '.zip';

            $path = $file->storeAs(
                'modules/' . $module->slug . '/packages',
                $filename,
                'local'
            );

            ModuleVersion::query()
                ->where('module_id', $module->id)
                ->update([
                    'is_active' => false,
                ]);

            ModuleVersion::create([
                'module_id' => $module->id,
                'uuid' => (string) Str::uuid(),
                'version' => $data['version'],
                'release_notes' => null,
                'changes' => [],
                'package_path' => $path,
                'package_size' => $file->getSize(),
                'requirements' => $data['requirements'] ?? [],
                'is_stable' => true,
                'is_active' => true,
            ]);
        }
    }

    protected function syncPrices(
        CatalogProduct $catalogProduct,
        array $data
    ): void {
        $currencyCode = $this->currencyPricing
            ->primaryCurrency();

        $currency = Currency::query()
            ->whereRaw(
                'UPPER(code) = ?',
                [$currencyCode]
            )
            ->where('is_active', true)
            ->first();

        if (!$currency) {
            throw new \RuntimeException(
                "Central primary currency {$currencyCode} is not available in the currencies table."
            );
        }

        $prices = [
            'saas' => [
                'enabled' => (bool) $data['saas_available'],
                'price' => $data['saas_price'] ?? null,
            ],
            'developer' => [
                'enabled' => (bool) $data['off_server_available'],
                'price' => $data['off_server_price'] ?? null,
            ],
        ];

        foreach ($prices as $scope => $priceData) {
            CurrencyPrice::query()
                ->where('scope_type', $scope)
                ->where('priceable_type', 'catalog_product')
                ->where('priceable_id', $catalogProduct->id)
                ->update([
                    'is_active' => false,
                ]);

            if (!$priceData['enabled']) {
                continue;
            }

            CurrencyPrice::create([
                'scope_type' => $scope,
                'owner_id' => null,
                'priceable_type' => 'catalog_product',
                'priceable_id' => $catalogProduct->id,
                'currency_id' => $currency->id,
                'price' => (float) $priceData['price'],
                'setup_fee' => 0,
                'discount' => 0,
                'billing_period' => null,
                'conditions' => [
                    'base_currency' => $currencyCode,
                ],
                'is_active' => true,
            ]);
        }
    }

    protected function audience(
        bool $saas,
        bool $offServer
    ): string {
        if ($saas && $offServer) {
            return 'both';
        }

        return $saas
            ? 'saas'
            : 'developer';
    }
}
